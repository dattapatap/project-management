<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Employees Intelligence View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 👮 Sales Executive is redirected directly to My Insights
        if ($user->hasRole('Sales-Executive')) {
            return redirect()->route('my-insights');
        }

        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', 'All');

        // 👮 Role-Based Isolation
        $query = User::where('status', 'Active')->where('id', '!=', 1);

        $showSales = true;
        $departmentId = $user->departments->department ?? null;

        if ($user->hasRole('Project-Manager')) {
            if ($departmentId) {
                $query->whereHas('departments', function ($q) use ($departmentId) {
                    $q->where('department', $departmentId);
                });

                if ($departmentId != 1) {
                    $showSales = false;
                }
            }
        } elseif ($user->hasRole('Team-Leader')) {
            // Find teams managed by the Team Leader
            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();

            // Find all Sales/Operations Executives registered in those teams
            $allMembers = \App\Models\TeamMembers::whereIn('team', $teams)
                ->where('status', true)
                ->pluck('user')
                ->toArray();

            if (!in_array($user->id, $allMembers)) {
                $allMembers[] = $user->id;
            }

            $query->whereIn('id', $allMembers);

            if ($departmentId != 1) {
                $showSales = false;
            }
        }

        $employeesCount = $query->count();
        $months = ['All', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // 📊 Calculate Summary KPIs
        $opsQuery = Task::whereYear('updated_at', $selectedYear);
        if ($user->hasRole('Project-Manager') || $user->hasRole('Team-Leader')) {
            if ($user->hasRole('Project-Manager')) {
                $opsQuery->whereHas('user.departments', function ($q) use ($departmentId) {
                    $q->where('department', $departmentId);
                });
            } else {
                $opsQuery->whereIn('assigned_to', $allMembers);
            }
        }

        $leadsQuery = DB::table('clients')->whereYear('created_at', $selectedYear);
        $maturedQuery = DB::table('clients')->where('status', 'Matured')->whereYear('updated_at', $selectedYear);
        $activeFollowupQuery = DB::table('clients')->whereIn('status', ['Followup', 'Meeting Fixed']);

        if ($showSales && ($user->hasRole('Project-Manager') || $user->hasRole('Team-Leader'))) {
            if ($user->hasRole('Project-Manager')) {
                $existsClosure = function ($q) use ($departmentId) {
                    $q->select(DB::raw(1))->from('users_departments')->whereColumn('users_departments.user', 'clients.ref_user')->where('department', $departmentId);
                };
                $leadsQuery->whereExists($existsClosure);
                $maturedQuery->whereExists($existsClosure);
                $activeFollowupQuery->whereExists($existsClosure);
            } else {
                $leadsQuery->whereIn('ref_user', $allMembers);
                $maturedQuery->whereIn('ref_user', $allMembers);
                $activeFollowupQuery->whereIn('ref_user', $allMembers);
            }
        }

        if ($selectedMonth != 'All') {
            $monthNum = date('m', strtotime($selectedMonth));
            $opsQuery->whereMonth('updated_at', $monthNum);
            $leadsQuery->whereMonth('created_at', $monthNum);
            $maturedQuery->whereMonth('updated_at', $monthNum);
        }

        $totalOps = (clone $opsQuery)->count();
        $completedOps = (clone $opsQuery)->where('status', 'Completed')->count();
        $opsRate = $totalOps > 0 ? round(($completedOps / $totalOps) * 100) : 0;

        $salesRate = 0;
        $totalLeadsCount = 0;
        $maturedCount = 0;
        $activeFollowupCount = 0;
        if ($showSales) {
            $totalLeadsCount = $leadsQuery->count();
            $maturedCount = $maturedQuery->count();
            $activeFollowupCount = $activeFollowupQuery->count();
            $salesRate = $totalLeadsCount > 0 ? round(($maturedCount / $totalLeadsCount) * 100) : 0;
        }

        // 📈 12-Month Dual Trend (Operations vs Sales)
        $trendMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // 🏗️ Ops Trend
        $opsTrendQ = Task::select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->where('status', 'Completed')->whereYear('updated_at', $selectedYear)->groupBy('month');
        if ($user->hasRole('Project-Manager')) {
            $opsTrendQ->whereHas('user.departments', function ($q) use ($departmentId) {
                $q->where('department', $departmentId);
            });
        } elseif ($user->hasRole('Team-Leader')) {
            $opsTrendQ->whereIn('assigned_to', $allMembers);
        }
        $opsTrendRaw = $opsTrendQ->get()->keyBy('month');

        // 💰 Sales Trend
        $salesTrendRaw = collect();
        if ($showSales) {
            $salesTrendQ = DB::table('clients')->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
                ->where('status', 'Matured')->whereYear('updated_at', $selectedYear)->groupBy('month');
            if ($user->hasRole('Project-Manager')) {
                $salesTrendQ->whereExists(function ($q) use ($departmentId) {
                    $q->select(DB::raw(1))->from('users_departments')->whereColumn('users_departments.user', 'clients.ref_user')->where('department', $departmentId);
                });
            } elseif ($user->hasRole('Team-Leader')) {
                $salesTrendQ->whereIn('ref_user', $allMembers);
            }
            $salesTrendRaw = $salesTrendQ->get()->keyBy('month');
        }

        $performanceTrend = collect($trendMonths)->map(function ($month) use ($opsTrendRaw, $salesTrendRaw) {
            return (object)[
                'month' => $month,
                'ops' => $opsTrendRaw->has($month) ? $opsTrendRaw->get($month)->count : 0,
                'sales' => $salesTrendRaw->has($month) ? $salesTrendRaw->get($month)->count : 0
            ];
        });

        return view('components.reports.employees', compact('employeesCount', 'performanceTrend', 'selectedYear', 'selectedMonth', 'months', 'showSales', 'opsRate', 'salesRate', 'totalLeadsCount', 'maturedCount', 'activeFollowupCount'));
    }

    /**
     * Employees Performance DataTable Data
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        $range = $request->get('range', 'monthly');
        $year = $request->get('year', date('Y'));
        $monthName = $request->get('month', 'All');

        $query = User::with(['emp', 'roles', 'departments.dept'])
            ->where('status', 'Active')
            ->where('id', '!=', 1);

        if ($user->hasRole('Project-Manager')) {
            $departmentId = $user->departments->department ?? null;
            if ($departmentId) {
                $query->whereHas('departments', function ($q) use ($departmentId) {
                    $q->where('department', $departmentId);
                });
            }
        } elseif ($user->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $allMembers = \App\Models\TeamMembers::whereIn('team', $teams)
                ->where('status', true)
                ->pluck('user')
                ->toArray();

            if (!in_array($user->id, $allMembers)) {
                $allMembers[] = $user->id;
            }

            $query->whereIn('id', $allMembers);
        }

        $data = $query->get()->map(function ($emp) use ($range, $year, $monthName) {
            $tasksQuery = Task::where('assigned_to', $emp->id);
            $logsQuery = TaskLog::where('userid', $emp->id);
            $leadsQuery = DB::table('clients')->where('ref_user', $emp->id);
            $maturedClientsQuery = DB::table('clients')->where('ref_user', $emp->id)->where('status', 'Matured');
            $activeFollowupsQuery = DB::table('clients')->where('ref_user', $emp->id)->whereIn('status', ['Followup', 'Meeting Fixed']);

            if ($range == 'weekly') {
                $tasksQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                $logsQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                $leadsQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                $maturedClientsQuery->where('updated_at', '>=', Carbon::now()->startOfWeek());
            } elseif ($range == 'monthly') {
                $tasksQuery->whereYear('created_at', $year);
                $logsQuery->whereYear('created_at', $year);
                $leadsQuery->whereYear('created_at', $year);
                $maturedClientsQuery->whereYear('updated_at', $year);

                if ($monthName != 'All') {
                    $monthNum = date('m', strtotime($monthName));
                    $tasksQuery->whereMonth('created_at', $monthNum);
                    $logsQuery->whereMonth('created_at', $monthNum);
                    $leadsQuery->whereMonth('created_at', $monthNum);
                    $maturedClientsQuery->whereMonth('updated_at', $monthNum);
                }
            } elseif ($range == 'yearly') {
                $tasksQuery->whereYear('created_at', $year);
                $logsQuery->whereYear('created_at', $year);
                $leadsQuery->whereYear('created_at', $year);
                $maturedClientsQuery->whereYear('updated_at', $year);
            }

            $emp->active_tasks = (clone $tasksQuery)->where('status', 'InProgress')->count();
            $emp->completed_tasks = (clone $tasksQuery)->where('status', 'Completed')->count();
            $emp->matured_clients = $maturedClientsQuery->count();
            $emp->total_leads = $leadsQuery->count();
            $emp->active_followups = $activeFollowupsQuery->count();
            $emp->total_hours = round($logsQuery->sum('time_spend'), 1);

            // Efficiency based on Role
            if ($emp->hasRole('Sales-Executive')) {
                $target = 5; // 5 matured clients per month target
                $emp->productivity = min(round(($emp->matured_clients / $target) * 100), 100);
            } else {
                $targetHours = 160;
                if ($range == 'weekly') $targetHours = 40;
                $emp->productivity = $targetHours > 0 ? min(round(($emp->total_hours / $targetHours) * 100), 100) : 0;
            }

            return $emp;
        });

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action_link', function ($row) {
                return route('reports.employee.detail', base64_encode($row->id));
            })
            ->rawColumns(['action_link'])
            ->make(true);
    }

    /**
     * My Personal Insights (For regular employees)
     */
    public function myInsights(Request $request)
    {
        return $this->detail($request, base64_encode(Auth::id()));
    }

    /**
     * Individual Employee Detail Dossier
     */
    public function detail(Request $request, $id)
    {
        $userId = base64_decode($id);
        $employee = User::with(['emp', 'departments.dept', 'roles'])->findOrFail($userId);

        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', 'All');
        $months = ['All', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // 👮 Authorization Check
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
            if ($userId == $user->id) {
                // Anyone can view their own details/insights
            } elseif ($user->hasRole('Project-Manager')) {
                $myDept = $user->departments->department ?? null;
                $empDept = $employee->departments->department ?? null;
                if ($myDept != $empDept) abort(403, 'Unauthorized access.');
            } elseif ($user->hasRole('Team-Leader')) {
                $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
                $isTeamMember = DB::table('team_members')
                    ->whereIn('team', $teams)
                    ->where('user', $userId)
                    ->where('status', true)
                    ->exists();
                if (!$isTeamMember) {
                    abort(403, 'Unauthorized access. You can only view reports of your team members or yourself.');
                }
            } else {
                // Regular employees can only view themselves
                abort(403, 'Unauthorized access.');
            }
        }

        // 📊 Performance Metrics for Selected Timeframe
        $compTasksQ = Task::where('assigned_to', $userId)->where('status', 'Completed')->whereYear('updated_at', $selectedYear);
        $pendingTasksQ = Task::where('assigned_to', $userId)->where('status', '!=', 'Completed')->whereYear('created_at', $selectedYear);
        $compProjsQ = DepartmentProjects::where('status', 'Completed')->whereYear('updated_at', $selectedYear);
        $logsQ = TaskLog::where('userid', $userId)->whereYear('created_at', $selectedYear);

        // Sales Queries
        $maturedQ = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')->whereYear('updated_at', $selectedYear);
        $followupQ = DB::table('clients')->where('ref_user', $userId)->where('status', 'Followup')->whereYear('created_at', $selectedYear);
        $salesQ = DB::table('client_payments')->where('created_by', $userId)->whereYear('created_at', $selectedYear);

        $leadsQ = DB::table('clients')->where('ref_user', $userId)->whereYear('created_at', $selectedYear);
        $activeFollowupQ = DB::table('clients')->where('ref_user', $userId)->whereIn('status', ['Followup', 'Meeting Fixed']);

        if ($selectedMonth != 'All') {
            $monthNum = date('m', strtotime($selectedMonth));
            $compTasksQ->whereMonth('created_at', $monthNum);
            $pendingTasksQ->whereMonth('created_at', $monthNum);
            $compProjsQ->whereMonth('updated_at', $monthNum);
            $logsQ->whereMonth('created_at', $monthNum);
            $maturedQ->whereMonth('updated_at', $monthNum);
            $followupQ->whereMonth('created_at', $monthNum);
            $salesQ->whereMonth('created_at', $monthNum);
            $leadsQ->whereMonth('created_at', $monthNum);
        }

        $stats = [
            'completed_tasks' => $compTasksQ->count(),
            'pending_tasks' => $pendingTasksQ->count(),
            'active_tasks' => Task::where('assigned_to', $userId)->where('status', 'InProgress')->count(),
            'completed_projects' => $compProjsQ->whereHas('tasks', function ($q) use ($userId) {
                $q->where('assigned_to', $userId);
            })->count(),
            'total_hours' => round($logsQ->sum('time_spend'), 1),
            'matured' => $maturedQ->count(),
            'followup' => $followupQ->count(),
            'total_sales' => round($salesQ->sum('amount'), 2),
            'total_leads' => $leadsQ->count(),
            'active_followups' => $activeFollowupQ->count(),
        ];

        // 🧠 Advanced Productivity Insights
        $daysWorked = $logsQ->distinct()->count(DB::raw('DATE(created_at)'));
        $stats['avg_daily_hours'] = $daysWorked > 0 ? round($stats['total_hours'] / $daysWorked, 1) : 0;

        $totalHoursOnCompleted = Task::where('assigned_to', $userId)->where('status', 'Completed')
            ->whereYear('created_at', $selectedYear)
            ->withSum('logs as total_hours', 'time_spend')
            ->get()->sum('total_hours');
        $stats['avg_task_delivery_time'] = $stats['completed_tasks'] > 0 ? round($totalHoursOnCompleted / $stats['completed_tasks'], 1) : 0;

        // 📅 Month-wise Trends for current year
        $trendMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyTasks = Task::where('assigned_to', $userId)->where('status', 'Completed')->whereYear('updated_at', $selectedYear)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $monthlyClients = DB::table('clients')->where('ref_user', $userId)->whereYear('created_at', $selectedYear)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $monthlyMatured = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')->whereYear('created_at', $selectedYear)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $monthlyHours = TaskLog::where('userid', $userId)->whereYear('created_at', $selectedYear)
            ->select(DB::raw('SUM(time_spend) as minutes'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $monthlyTrend = collect($trendMonths)->map(function ($m) use ($monthlyTasks, $monthlyClients, $monthlyHours, $monthlyMatured) {
            return (object)[
                'month' => $m,
                'tasks' => $monthlyTasks->has($m) ? $monthlyTasks->get($m)->count : 0,
                'clients' => $monthlyClients->has($m) ? $monthlyClients->get($m)->count : 0,
                'hours' => $monthlyHours->has($m) ? round($monthlyHours->get($m)->minutes, 1) : 0,
                'matured' => $monthlyMatured->has($m) ? $monthlyMatured->get($m)->count : 0
            ];
        });

        // ☀️ Morning to Evening Activity (Daily Work Rhythm)
        $dailyLogsQuery = TaskLog::with('task.project.clients')->where('userid', $userId);

        if ($selectedMonth != 'All') {
            $monthNum = date('m', strtotime($selectedMonth));
            $dailyLogsQuery->whereYear('created_at', $selectedYear)->whereMonth('created_at', $monthNum);
        } else {
            // If Year only, show Last 30 Days (as per user request)
            $dailyLogsQuery->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $dailyLogs = $dailyLogsQuery->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('d M, Y');
            });

        $isSales = $employee->hasRole('Sales-Executive') || ($employee->departments && $employee->departments->department == 1);
        $tasks = Task::with('project.clients')->where('assigned_to', $userId)->latest()->take(10)->get();
        $logs = TaskLog::with('task.project.clients')->where('userid', $userId)->latest()->take(15)->get();
        $activities = \App\Models\UserActivity::where('user_id', $userId)->latest()->take(10)->get();

        $recentMatured = collect();
        $salesLogs = collect();
        if ($isSales) {
            $recentMaturedQuery = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')->whereYear('updated_at', $selectedYear);
            $salesLogsQuery = \App\Models\ClientHistory::with('client')->where('created', $userId)->whereYear('created_at', $selectedYear);

            if ($selectedMonth != 'All') {
                $monthNum = date('m', strtotime($selectedMonth));
                $recentMaturedQuery->whereMonth('updated_at', $monthNum);
                $salesLogsQuery->whereMonth('created_at', $monthNum);
            }

            $recentMatured = $recentMaturedQuery->latest('updated_at')->take(10)->get();
            $salesLogs = $salesLogsQuery->latest()->take(15)->get();
        }

        return view('components.reports.employee_detail', compact('employee', 'tasks', 'logs', 'salesLogs', 'stats', 'selectedYear', 'selectedMonth', 'months', 'monthlyTrend', 'dailyLogs', 'isSales', 'recentMatured', 'activities'));
    }
}
