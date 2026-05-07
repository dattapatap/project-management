<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\Clients;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Teams;
use App\Models\TeamMembers;
use App\Models\TaskLog;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab');
        if ($tab) {
            session(['active_dashboard_tab' => $tab]);
            session()->save();
        }

        $adminData = [];
        $selectedYear = $request->get('year', date('Y'));
        $departmentId = $user->departments->department ?? null;

        // 🔀 Route Sales Department requests to SalesDashboardController
        if ($user->hasRole('Sales-Executive') || ($user->hasRole('Team-Leader') && $departmentId == 1)) {
            return app(\App\Http\Controllers\SalesDashboardController::class)->index($request);
        }

        // 🔀 Modular Routing for Dashboard Role-Based Data Loading
        if ($user->hasRole('Admin')) {
            $adminData = $this->getAdminDashboardData($selectedYear);
        } elseif ($user->hasRole('Project-Manager')) {
            $adminData = $this->getPMDashboardData($selectedYear);
        } elseif ($user->hasRole('Team-Leader')) {
            // 💻 WMS Department Team Leader Dashboard
            $adminData = $this->getWmsTLDashboardData($user, $request, $selectedYear);
            
            if ($departmentId == 2) {
                $personalData = $this->getWmsEmployeeDashboardData($user, $selectedYear);
                $adminData = array_merge($adminData, $personalData);
            }
        } elseif ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant'])) {
            // 🛠 WMS (OD) Employees Dashboard
            $adminData = $this->getWmsEmployeeDashboardData($user, $selectedYear);
        }

        // ⚡ Eager Loading Safe-Guards to Prevent Eloquent Lazy-Loading Strict Violations
        if (isset($adminData['employee_performance'])) {
            $adminData['employee_performance']->load(['taskLogs.task']);
        }
        if (isset($adminData['team_employees'])) {
            $adminData['team_employees']->load(['taskLogs.task']);
        }
        if (isset($adminData['team_performance'])) {
            $allUsers = new \Illuminate\Database\Eloquent\Collection();
            foreach ($adminData['team_performance'] as $team) {
                if (isset($team->teammembers)) {
                    foreach ($team->teammembers as $tm) {
                        if ($tm->users) {
                            $allUsers->push($tm->users);
                        }
                    }
                }
            }
            if ($allUsers->isNotEmpty()) {
                $allUsers->load(['taskLogs.task']);
            }
        }

        return view('home', compact('adminData'));
    }

    /* =========================================================================
     * 👑 ADMIN DASHBOARD DATA LOADER
     * ========================================================================= */
    private function getAdminDashboardData($selectedYear)
    {
        $adminData = [];
        $adminData['total_users'] = User::count();
        $adminData['total_departments'] = Department::count();
        $adminData['total_projects'] = DepartmentProjects::count();
        $adminData['total_tasks'] = Task::count();
        $adminData['total_clients'] = Clients::count();

        $adminData['recent_projects'] = DepartmentProjects::with(['category', 'clients'])->latest()->take(5)->get();

        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $adminData['proj_todo'] = DepartmentProjects::where('status', 'ToDo')->count();
        $adminData['proj_in_progress'] = DepartmentProjects::where('status', 'InProgress')->count();
        $adminData['proj_completed'] = DepartmentProjects::where('status', 'Completed')->count();

        $sevenDaysFromNow = Carbon::now()->addDays(7);
        $adminData['near_deadline_projects'] = DepartmentProjects::with('clients')
            ->where('status', '!=', 'Completed')
            ->where('end_date', '<=', $sevenDaysFromNow)
            ->orderBy('end_date', 'asc')
            ->take(5)
            ->get();

        // Sales Performance (Department 1) - Comparing This Month vs Last Month
        $adminData['sales_performance'] = User::whereHas('departments', function ($q) {
            $q->where('department', 1);
        })
            ->withCount(['clients as total_matured' => function ($q) {
                $q->where('status', 'Matured');
            }])
            ->withCount(['clients as followup_clients' => function ($q) {
                $q->where('status', 'Followup');
            }])
            ->withCount(['clients as this_month_matured' => function ($q) use ($startOfMonth) {
                $q->where('status', 'Matured')->where('created_at', '>=', $startOfMonth);
            }])
            ->withCount(['clients as last_month_matured' => function ($q) use ($startOfLastMonth, $endOfLastMonth) {
                $q->where('status', 'Matured')->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
            }])
            ->orderBy('this_month_matured', 'desc')
            ->take(5)
            ->get();

        // OD Performance (Department 2) - Comparing This Month vs Last Month
        $adminData['od_performance'] = User::whereHas('departments', function ($q) {
            $q->where('department', 2);
        })
            ->withCount(['tasks as total_completed' => function ($q) {
                $q->where('status', 'Completed');
            }])
            ->withCount(['tasks as active_tasks' => function ($q) {
                $q->where('status', 'InProgress');
            }])
            ->withSum('taskLogs as total_hours', 'time_spend')
            ->withCount(['tasks as this_month_completed' => function ($q) use ($startOfMonth) {
                $q->where('status', 'Completed')->where('created_at', '>=', $startOfMonth);
            }])
            ->withCount(['tasks as last_month_completed' => function ($q) use ($startOfLastMonth, $endOfLastMonth) {
                $q->where('status', 'Completed')->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
            }])
            ->orderBy('this_month_completed', 'desc')
            ->take(5)
            ->get();

        // Team-wise Performance (OD Department Only)
        $odDept = Department::where('name', 'OD')->first();
        $odDeptId = $odDept ? $odDept->id : 2;

        $adminData['team_performance'] = Teams::where('department', $odDeptId)
            ->with(['teammembers.users' => function ($q) {
                $q->withCount(['tasks as active_tasks' => function ($sq) {
                    $sq->whereIn('status', ['ToDo', 'InProgress']);
                }])
                    ->withCount(['tasks as completed_tasks' => function ($sq) {
                        $sq->where('status', 'Completed');
                    }])
                    ->withSum('taskLogs as total_hours', 'time_spend')
                    ->with(['taskLogs' => function ($sq) {
                        $sq->latest()->with('task');
                    }]);
            }])->get();

        return $adminData;
    }

    /* =========================================================================
     * 👔 PROJECT MANAGER DASHBOARD DATA LOADER
     * ========================================================================= */
    private function getPMDashboardData($selectedYear)
    {
        $adminData = [];
        $adminData['pm_total_projects'] = DepartmentProjects::count();
        $adminData['pm_total_tasks'] = Task::count();

        $sevenDaysFromNow = Carbon::now()->addDays(7);
        $adminData['near_deadline_projects'] = DepartmentProjects::with('clients')
            ->where('status', '!=', 'Completed')
            ->where('end_date', '<=', $sevenDaysFromNow)
            ->orderBy('end_date', 'asc')
            ->take(5)
            ->get();

        $adminData['active_tasks'] = Task::with(['project', 'user'])
            ->where('status', 'InProgress')
            ->latest()
            ->take(5)
            ->get();

        $adminData['pm_proj_todo'] = DepartmentProjects::where('status', 'ToDo')->count();
        $adminData['pm_proj_in_progress'] = DepartmentProjects::where('status', 'InProgress')->count();
        $adminData['pm_proj_completed'] = DepartmentProjects::where('status', 'Completed')->count();

        $adminData['pm_task_todo'] = Task::where('status', 'ToDo')->count();
        $adminData['pm_task_in_progress'] = Task::where('status', 'InProgress')->count();
        $adminData['pm_task_completed'] = Task::where('status', 'Completed')->count();

        // Employee Performance Summary (Top 5)
        $adminData['employee_performance'] = User::whereHas('tasks')
            ->withCount(['tasks as active_tasks' => function ($q) {
                $q->whereIn('status', ['ToDo', 'InProgress']);
            }])
            ->withCount(['tasks as completed_tasks' => function ($q) {
                $q->where('status', 'Completed');
            }])
            ->withSum('taskLogs as total_hours', 'time_spend')
            ->with(['taskLogs' => function ($q) {
                $q->latest()->with('task');
            }])
            ->orderBy('completed_tasks', 'desc')
            ->take(5)
            ->get();

        $odDept = Department::where('name', 'OD')->first();
        $odDeptId = $odDept ? $odDept->id : 2;

        $adminData['team_performance'] = Teams::where('department', $odDeptId)
            ->with(['teammembers.users' => function ($q) {
                $q->withCount(['tasks as active_tasks' => function ($sq) {
                    $sq->where('status', 'InProgress');
                }])
                    ->withCount(['tasks as completed_tasks' => function ($sq) {
                        $sq->where('status', 'Completed');
                    }])
                    ->withSum('taskLogs as total_hours', 'time_spend');
            }])->get();

        return $adminData;
    }

    /* =========================================================================
     * 💻 WMS DEPARTMENT TEAM LEADER DATA LOADER
     * ========================================================================= */
    private function getWmsTLDashboardData($user, Request $request, $selectedYear)
    {
        $adminData = [];
        $teamMember = TeamMembers::where('user', $user->id)->where('status', true)->first();
        if (!$teamMember) {
            return $adminData;
        }

        $teamId = $teamMember->team;
        $userDeptId = $user->departments->department ?? null;

        $adminData['selected_year'] = $selectedYear;

        // Generate year range from 2023 to current year
        $currentYear = date('Y');
        $startYear = 2023;
        $earliestProject = DepartmentProjects::orderBy('created_date', 'asc')->first();
        if ($earliestProject) {
            $startYear = min($startYear, Carbon::parse($earliestProject->created_date)->year);
        }

        $availableYears = [];
        for ($i = $currentYear; $i >= $startYear; $i--) {
            $availableYears[] = $i;
        }
        $adminData['available_years'] = $availableYears;

        $now = Carbon::now();
        $sevenDaysFromNow = $now->copy()->addDays(7);

        // Base query logic for YEARLY projects
        $yearlyQuery = function ($q) use ($user, $teamId, $userDeptId, $selectedYear) {
            $q->with(['clients', 'category', 'project_team'])
                ->where(function ($sq) use ($user, $teamId) {
                    $sq->where('assigned_to', $user->id);
                    if ($teamId) {
                        $sq->orWhereHas('project_team', function ($ssq) use ($teamId) {
                            $ssq->where('teamid', $teamId);
                        });
                    }
                })
                ->when($userDeptId, function ($sq) use ($userDeptId) {
                    $sq->whereHas('category', function ($ssq) use ($userDeptId) {
                        $ssq->where('dept_id', $userDeptId);
                    });
                })
                ->where(function ($sq) use ($selectedYear) {
                    $sq->whereYear('created_date', '<=', $selectedYear)
                        ->where(function ($ssq) use ($selectedYear) {
                            $ssq->whereNull('act_end_date')
                                ->orWhereYear('act_end_date', '>=', $selectedYear);
                        });
                });
        };

        // Urgent Deadlines (contextual to the selected year)
        $comparisonDate = ($selectedYear == date('Y')) ? $now : Carbon::create($selectedYear, 12, 31, 23, 59, 59);

        $adminData['near_deadline_projects'] = DepartmentProjects::where($yearlyQuery)
            ->where(function ($q) use ($selectedYear) {
                // Project was not completed yet by the end of selected year
                $q->where('status', '!=', 'Completed')
                    ->orWhereYear('act_end_date', '>', $selectedYear);
            })
            ->where('end_date', '<=', ($selectedYear == date('Y') ? $sevenDaysFromNow : $comparisonDate))
            ->orderBy('end_date', 'asc')
            ->get();

        // Active Team Projects (InProgress or ToDo in that year context)
        $adminData['active_team_projects'] = DepartmentProjects::where($yearlyQuery)
            ->where(function ($q) use ($selectedYear) {
                // Project was not completed yet by the end of selected year
                $q->where('status', '!=', 'Completed')
                    ->orWhereYear('act_end_date', '>', $selectedYear);
            })
            ->withCount([
                'tasks' => function ($sq) use ($selectedYear) {
                    $sq->whereYear('created_at', '<=', $selectedYear);
                },
                'completedTask' => function ($sq) use ($selectedYear) {
                    $sq->whereYear('created_at', '<=', $selectedYear)
                        ->whereYear('act_enddate', '<=', $selectedYear);
                }
            ])
            ->get();

        // Health Overview - YEARLY
        $teamProjects = DepartmentProjects::where($yearlyQuery)
            ->withCount([
                'tasks' => function ($sq) use ($selectedYear) {
                    $sq->whereYear('created_at', '<=', $selectedYear);
                },
                'completedTask' => function ($sq) use ($selectedYear) {
                    $sq->whereYear('created_at', '<=', $selectedYear)
                        ->whereYear('act_enddate', '<=', $selectedYear);
                }
            ])
            ->get();

        $health = ['On Track' => 0, 'At Risk' => 0, 'Delayed' => 0, 'Completed' => 0];
        foreach ($teamProjects as $proj) {
            // If project was completed IN OR BEFORE the selected year
            if ($proj->status == 'Completed' && $proj->act_end_date && Carbon::parse($proj->act_end_date)->year <= $selectedYear) {
                $health['Completed']++;
                continue;
            }

            $startDate = Carbon::parse($proj->start_date);
            $endDate = Carbon::parse($proj->end_date);

            // If deadline already passed relative to the comparison date
            if ($comparisonDate > $endDate) {
                $health['Delayed']++;
                continue;
            }

            $totalDays = $startDate->diffInDays($endDate) ?: 1;
            $elapsedDays = $startDate->diffInDays($comparisonDate);
            if ($elapsedDays < 0) {
                $elapsedDays = 0; // Not started yet in that year context
            }

            $timeElapsedPercent = ($elapsedDays / $totalDays);
            $progressPercent = $proj->tasks_count > 0 ? ($proj->completed_task_count / $proj->tasks_count) : 0;

            if ($progressPercent >= $timeElapsedPercent) {
                $health['On Track']++;
            } else {
                $health['At Risk']++;
            }
        }
        $adminData['project_health'] = $health;

        // Team Employees & Active Work (Include leader, exclude other managers)
        $adminData['team_employees'] = User::whereHas('teamMember', function ($q) use ($teamId) {
            $q->where('team', $teamId);
        })
            ->where(function ($q) use ($user) {
                $q->where('id', $user->id)
                    ->orWhereDoesntHave('roles', function ($sq) {
                        $sq->whereIn('name', ['Team-Leader', 'Project-Manager', 'Admin']);
                    });
            })
            ->withCount(['tasks as active_tasks_count' => function ($sq) {
                $sq->whereIn('status', ['ToDo', 'InProgress']);
            }])
            ->with(['taskLogs' => function ($q) {
                $q->latest()->with('task');
            }, 'tasks' => function ($q) {
                $q->whereIn('status', ['ToDo', 'InProgress'])->with('project');
            }])
            ->get();

        return $adminData;
    }

    /* =========================================================================
     * 🛠 WMS (OD) EMPLOYEES DATA LOADER
     * ========================================================================= */
    private function getWmsEmployeeDashboardData($user, $selectedYear)
    {
        $adminData = [];
        $year = $selectedYear ?? date('Y');

        // Task Stats (Year-wise)
        $tasksQuery = Task::where('assigned_to', $user->id)->whereYear('created_at', $year);
        $adminData['total_tasks_assigned'] = (clone $tasksQuery)->count();
        $adminData['completed_tasks_count'] = Task::where('assigned_to', $user->id)->where('status', 'Completed')->whereYear('updated_at', $year)->count();
        $adminData['pending_tasks_count'] = Task::where('assigned_to', $user->id)->whereIn('status', ['ToDo', 'InProgress'])->count();
        $adminData['active_tasks_count'] = Task::where('assigned_to', $user->id)->where('status', 'InProgress')->count();
        $adminData['todo_tasks_count'] = Task::where('assigned_to', $user->id)->where('status', 'ToDo')->count();

        // Project Stats (Year-wise)
        $projectsQuery = DepartmentProjects::whereHas('tasks', function ($q) use ($user, $year) {
            $q->where('assigned_to', $user->id)->whereYear('created_at', $year);
        });
        $adminData['projects_assigned_count'] = (clone $projectsQuery)->count();
        $adminData['completed_projects_count'] = (clone $projectsQuery)->where('status', 'Completed')->count();

        // Hours & Performance (Year-wise)
        $logsQuery = TaskLog::where('userid', $user->id)->whereYear('created_at', $year);
        $adminData['total_hours'] = round($logsQuery->sum('time_spend'), 1);

        $totalHoursOnCompleted = Task::where('assigned_to', $user->id)->where('status', 'Completed')
            ->whereYear('created_at', $year)
            ->withSum('logs as total_hours', 'time_spend')
            ->get()->sum('total_hours');
        $adminData['avg_task_duration'] = $adminData['completed_tasks_count'] > 0 ? round($totalHoursOnCompleted / $adminData['completed_tasks_count'], 1) : 0;

        // Growth / Trends
        $trendMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyCompleted = Task::where('assigned_to', $user->id)->where('status', 'Completed')->whereYear('updated_at', $year)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $adminData['growth_trend'] = collect($trendMonths)->map(function ($m) use ($monthlyCompleted) {
            return (object)[
                'month' => $m,
                'count' => $monthlyCompleted->has($m) ? $monthlyCompleted->get($m)->count : 0
            ];
        });

        // Current Tasks for Board
        $adminData['my_tasks'] = Task::with(['project.category', 'project.clients'])->where('assigned_to', $user->id)
            ->whereIn('status', ['ToDo', 'InProgress'])
            ->orderBy('priority', 'desc')
            ->get();

        $adminData['recently_completed_tasks'] = Task::with(['project.category', 'project.clients'])->where('assigned_to', $user->id)
            ->where('status', 'Completed')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $adminData['recent_projects'] = DepartmentProjects::whereHas('tasks', function ($q) use ($user, $year) {
            $q->where('assigned_to', $user->id);
        })->with(['category', 'clients'])
            ->withCount(['tasks as user_tasks_count' => function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            }])
            ->latest()->take(5)->get();

        $adminData['recent_logs'] = TaskLog::with('task.project')->where('userid', $user->id)->latest()->take(10)->get();

        // Daily Pulse (Today's specific metrics)
        $startOfToday = Carbon::now()->startOfDay();
        $adminData['daily_pulse'] = [
            'tasks_completed_today' => Task::where('assigned_to', $user->id)
                ->where('status', 'Completed')
                ->where('updated_at', '>=', $startOfToday)
                ->count(),
            'hours_logged_today' => round(TaskLog::where('userid', $user->id)
                ->where('created_at', '>=', $startOfToday)
                ->sum('time_spend'), 1)
        ];

        // Year selection support: From creation year to current year
        $startYear = $user->created_at ? $user->created_at->year : date('Y');
        $currentYear = (int)date('Y');
        $adminData['available_years'] = range($currentYear, $startYear);
        $adminData['selected_year'] = $year;

        return $adminData;
    }
}

