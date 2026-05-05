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
        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', 'All');

        // 👮 Role-Based Isolation
        $query = User::where('status', 'Active')->where('id', '!=', 1);

        $showSales = true;
        if ($user->hasRole('Project-Manager')) {
            $departmentId = $user->departments->department ?? null;
            if ($departmentId) {
                $query->whereHas('departments', function ($q) use ($departmentId) {
                    $q->where('department', $departmentId);
                });

                // Determine if this department is Sales (assuming 1 is Sales, 2 is OD based on HomeController logic)
                if ($departmentId != 1) {
                    $showSales = false;
                }
            }
        }

        $employeesCount = $query->count();
        $months = ['All', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // 📊 Calculate Summary KPIs
        $opsQuery = Task::whereYear('updated_at', $selectedYear);
        if ($user->hasRole('Project-Manager')) {
            $opsQuery->whereHas('user.departments', function ($q) use ($departmentId) {
                $q->where('department', $departmentId);
            });
        }
        $totalOps = (clone $opsQuery)->count();
        $completedOps = (clone $opsQuery)->where('status', 'Completed')->count();
        $opsRate = $totalOps > 0 ? round(($completedOps / $totalOps) * 100) : 0;

        $salesRate = 0;
        if ($showSales) {
            $clientsQuery = DB::table('clients')->whereYear('created_at', $selectedYear);
            if ($user->hasRole('Project-Manager')) {
                $clientsQuery->whereExists(function ($q) use ($departmentId) {
                    $q->select(DB::raw(1))->from('users_departments')->whereColumn('users_departments.user', 'clients.ref_user')->where('department', $departmentId);
                });
            }
            $totalClients = (clone $clientsQuery)->count();
            $maturedClients = (clone $clientsQuery)->where('status', 'Matured')->count();
            $salesRate = $totalClients > 0 ? round(($maturedClients / $totalClients) * 100) : 0;
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
        }
        $opsTrendRaw = $opsTrendQ->get()->keyBy('month');

        // 💰 Sales Trend
        $salesTrendRaw = collect();
        if ($showSales) {
            $salesTrendQ = DB::table('clients')->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
                ->where('status', 'Matured')->whereYear('created_at', $selectedYear)->groupBy('month');
            if ($user->hasRole('Project-Manager')) {
                $salesTrendQ->whereExists(function ($q) use ($departmentId) {
                    $q->select(DB::raw(1))->from('users_departments')->whereColumn('users_departments.user', 'clients.ref_user')->where('department', $departmentId);
                });
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

        return view('components.reports.employees', compact('employeesCount', 'performanceTrend', 'selectedYear', 'selectedMonth', 'months', 'showSales', 'opsRate', 'salesRate'));
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
        }

        $data = $query->get()->map(function ($emp) use ($range, $year, $monthName) {
            $tasksQuery = Task::where('assigned_to', $emp->id);
            $logsQuery = TaskLog::where('userid', $emp->id);
            $clientsQuery = DB::table('clients')->where('ref_user', $emp->id);

            if ($range == 'weekly') {
                $tasksQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                $logsQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                $clientsQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
            } elseif ($range == 'monthly') {
                $tasksQuery->whereYear('created_at', $year);
                $logsQuery->whereYear('created_at', $year);
                $clientsQuery->whereYear('created_at', $year);

                if ($monthName != 'All') {
                    $monthNum = date('m', strtotime($monthName));
                    $tasksQuery->whereMonth('created_at', $monthNum);
                    $logsQuery->whereMonth('created_at', $monthNum);
                    $clientsQuery->whereMonth('created_at', $monthNum);
                }
            } elseif ($range == 'yearly') {
                $tasksQuery->whereYear('created_at', $year);
                $logsQuery->whereYear('created_at', $year);
                $clientsQuery->whereYear('created_at', $year);
            }

            $emp->active_tasks = (clone $tasksQuery)->where('status', 'InProgress')->count();
            $emp->completed_tasks = (clone $tasksQuery)->where('status', 'Completed')->count();
            $emp->matured_clients = (clone $clientsQuery)->where('status', 'Matured')->count();
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
        if (Auth::user()->hasRole('Project-Manager')) {
            $myDept = Auth::user()->departments->department ?? null;
            $empDept = $employee->departments->department ?? null;
            if ($myDept != $empDept) abort(403, 'Unauthorized access.');
        }

        // 📊 Performance Metrics for Selected Timeframe
        $compTasksQ = Task::where('assigned_to', $userId)->where('status', 'Completed')->whereYear('updated_at', $selectedYear);
        $pendingTasksQ = Task::where('assigned_to', $userId)->where('status', '!=', 'Completed')->whereYear('created_at', $selectedYear);
        $compProjsQ = DepartmentProjects::where('status', 'Completed')->whereYear('updated_at', $selectedYear);
        $logsQ = TaskLog::where('userid', $userId)->whereYear('created_at', $selectedYear);

        // Sales Queries
        $maturedQ = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')->whereYear('created_at', $selectedYear);
        $followupQ = DB::table('clients')->where('ref_user', $userId)->where('status', 'Followup')->whereYear('created_at', $selectedYear);
        $salesQ = DB::table('client_payments')->where('created_by', $userId)->whereYear('created_at', $selectedYear);

        if ($selectedMonth != 'All') {
            $monthNum = date('m', strtotime($selectedMonth));
            $compTasksQ->whereMonth('created_at', $monthNum);
            $pendingTasksQ->whereMonth('created_at', $monthNum);
            $compProjsQ->whereMonth('updated_at', $monthNum);
            $logsQ->whereMonth('created_at', $monthNum);
            $maturedQ->whereMonth('created_at', $monthNum);
            $followupQ->whereMonth('created_at', $monthNum);
            $salesQ->whereMonth('created_at', $monthNum);
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

        $monthlyHours = TaskLog::where('userid', $userId)->whereYear('created_at', $selectedYear)
            ->select(DB::raw('SUM(time_spend) as minutes'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $monthlyTrend = collect($trendMonths)->map(function ($m) use ($monthlyTasks, $monthlyClients, $monthlyHours) {
            return (object)[
                'month' => $m,
                'tasks' => $monthlyTasks->has($m) ? $monthlyTasks->get($m)->count : 0,
                'clients' => $monthlyClients->has($m) ? $monthlyClients->get($m)->count : 0,
                'hours' => $monthlyHours->has($m) ? round($monthlyHours->get($m)->minutes, 1) : 0
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

        $isSales = $employee->hasRole('Sales-Executive');
        $tasks = Task::with('project.clients')->where('assigned_to', $userId)->latest()->take(10)->get();
        $logs = TaskLog::with('task.project.clients')->where('userid', $userId)->latest()->take(15)->get();
        $activities = \App\Models\UserActivity::where('user_id', $userId)->latest()->take(10)->get();

        $recentMatured = collect();
        if ($isSales) {
            $recentMatured = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')->latest()->take(10)->get();
        }

        return view('components.reports.employee_detail', compact('employee', 'tasks', 'logs', 'stats', 'selectedYear', 'selectedMonth', 'months', 'monthlyTrend', 'dailyLogs', 'isSales', 'recentMatured', 'activities'));
    }
}
