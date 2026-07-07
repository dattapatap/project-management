<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CsdCommunication;
use App\Models\CsdOpportunity;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use App\Services\Reports\OdWorkReportService;
use App\Services\Reports\NsdWorkReportService;
use App\Services\Reports\CsdWorkReportService;
use App\Services\Reports\ReportDateRangeService;
use App\Services\Reports\ReportScopeService;
use App\Services\UserPerformanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeReportController extends Controller
{
    public function __construct(
        private ReportScopeService $reportScope,
        private ReportDateRangeService $dateRange,
        private OdWorkReportService $odWork,
        private NsdWorkReportService $nsdWork,
        private CsdWorkReportService $csdWork
    ) {
        $this->middleware('auth');
    }

    /**
     * Employees Intelligence View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 👮 Sales / CSD executives go directly to My Insights
        if ($user->hasRole('Sales-Executive') || $user->hasRole('CSD-Executive')) {
            return redirect()->route('my-insights');
        }

        $selectedYear = $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', 'All');
        $range = $this->dateRange->resolve($request);

        $departmentId = $user->departments->department ?? null;
        $query = $this->reportScope->visibleEmployeesQuery($user);

        $showSales = true;
        if ($departmentId && $departmentId != 1 && !$user->hasBranchWideAccess()) {
            $showSales = false;
        }

        $employeesCount = $query->count();
        $months = ['All', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // 📊 Calculate Summary KPIs (scoped user IDs)
        $visibleUserIds = $query->pluck('id')->toArray();
        $opsQuery = Task::whereYear('updated_at', $selectedYear)->whereIn('assigned_to', $visibleUserIds);

        $leadsQuery = DB::table('clients')->whereYear('created_at', $selectedYear)->whereIn('ref_user', $visibleUserIds);
        $maturedQuery = DB::table('clients')->where('status', 'Matured')->whereYear('updated_at', $selectedYear)->whereIn('ref_user', $visibleUserIds);
        $activeFollowupQuery = DB::table('clients')->whereIn('status', ['Followup', 'Meeting Fixed'])->whereIn('ref_user', $visibleUserIds);

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

        $opsTrendQ = Task::select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->where('status', 'Completed')->whereYear('updated_at', $selectedYear)
            ->whereIn('assigned_to', $visibleUserIds)
            ->groupBy('month');
        $opsTrendRaw = $opsTrendQ->get()->keyBy('month');

        // 💰 Sales Trend
        $salesTrendRaw = collect();
        if ($showSales) {
            $salesTrendQ = DB::table('clients')->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
                ->where('status', 'Matured')->whereYear('updated_at', $selectedYear)
                ->whereIn('ref_user', $visibleUserIds)
                ->groupBy('month');
            $salesTrendRaw = $salesTrendQ->get()->keyBy('month');
        }

        $performanceTrend = collect($trendMonths)->map(function ($month) use ($opsTrendRaw, $salesTrendRaw) {
            return (object)[
                'month' => $month,
                'ops' => $opsTrendRaw->has($month) ? $opsTrendRaw->get($month)->count : 0,
                'sales' => $salesTrendRaw->has($month) ? $salesTrendRaw->get($month)->count : 0
            ];
        });

        return view('components.reports.employees', compact(
            'employeesCount',
            'performanceTrend',
            'selectedYear',
            'selectedMonth',
            'months',
            'showSales',
            'opsRate',
            'salesRate',
            'totalLeadsCount',
            'maturedCount',
            'activeFollowupCount',
            'range'
        ));
    }

    /**
     * Employees Performance DataTable Data
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        $range = $this->dateRange->resolve($request);
        $year = (int) $request->get('year', date('Y'));
        $monthName = $request->get('month', 'All');

        $employees = $this->reportScope->visibleEmployeesQuery($user)->get()->map(function ($emp) use ($range, $year, $monthName) {
            $performance = app(UserPerformanceService::class);
            $deptType = $performance->departmentType($emp);

            if ($deptType === 'od') {
                return $this->odWork->enrichEmployeeRow($emp, $range['from'], $range['to']);
            }

            if ($deptType === 'csd') {
                $metrics = $performance->buildMetrics($emp, (int) $year, $monthName);
                $emp->active_tasks = $metrics['open_tickets'] ?? 0;
                $emp->completed_tasks = $metrics['tickets_resolved'] ?? 0;
                $emp->matured_clients = $metrics['opportunities_won'] ?? 0;
                $emp->total_leads = $metrics['active_clients'] ?? 0;
                $emp->active_followups = $metrics['communications'] ?? 0;
                $emp->total_hours = $metrics['collections_paid'] ?? 0;
                $emp->productivity = $performance->performanceScore($metrics, 'csd');

                return $emp;
            }

            $tasksQuery = Task::where('assigned_to', $emp->id);
            $logsQuery = TaskLog::where('userid', $emp->id);
            $leadsQuery = DB::table('clients')->where('ref_user', $emp->id);
            $maturedClientsQuery = DB::table('clients')->where('ref_user', $emp->id)->where('status', 'Matured');
            $activeFollowupsQuery = DB::table('clients')->where('ref_user', $emp->id)->whereIn('status', ['Followup', 'Meeting Fixed']);

            if ($range['preset'] === 'daily') {
                $tasksQuery->whereDate('created_at', $range['from']);
                $logsQuery->whereDate('log_date', $range['from']);
                $leadsQuery->whereDate('created_at', $range['from']);
                $maturedClientsQuery->whereDate('updated_at', $range['from']);
            } elseif ($range['preset'] === 'weekly') {
                $tasksQuery->whereBetween('created_at', [$range['from'], $range['to']]);
                $logsQuery->whereBetween('log_date', [$range['from']->toDateString(), $range['to']->toDateString()]);
                $leadsQuery->whereBetween('created_at', [$range['from'], $range['to']]);
                $maturedClientsQuery->whereBetween('updated_at', [$range['from'], $range['to']]);
            } elseif ($range['preset'] === 'custom') {
                $tasksQuery->whereBetween('created_at', [$range['from'], $range['to']]);
                $logsQuery->whereBetween('log_date', [$range['from']->toDateString(), $range['to']->toDateString()]);
                $leadsQuery->whereBetween('created_at', [$range['from'], $range['to']]);
                $maturedClientsQuery->whereBetween('updated_at', [$range['from'], $range['to']]);
            } elseif ($range['preset'] === 'yearly') {
                $tasksQuery->whereYear('created_at', $year);
                $logsQuery->whereYear('log_date', $year);
                $leadsQuery->whereYear('created_at', $year);
                $maturedClientsQuery->whereYear('updated_at', $year);
            } else {
                $tasksQuery->whereYear('created_at', $year);
                $logsQuery->whereYear('log_date', $year);
                $leadsQuery->whereYear('created_at', $year);
                $maturedClientsQuery->whereYear('updated_at', $year);

                if ($monthName != 'All') {
                    $monthNum = date('m', strtotime($monthName));
                    $tasksQuery->whereMonth('created_at', $monthNum);
                    $logsQuery->whereMonth('log_date', $monthNum);
                    $leadsQuery->whereMonth('created_at', $monthNum);
                    $maturedClientsQuery->whereMonth('updated_at', $monthNum);
                }
            }

            $emp->active_tasks = (clone $tasksQuery)->where('status', 'InProgress')->count();
            $emp->completed_tasks = (clone $tasksQuery)->where('status', 'Completed')->count();
            $emp->matured_clients = $maturedClientsQuery->count();
            $emp->total_leads = $leadsQuery->count();
            $emp->active_followups = $activeFollowupsQuery->count();
            $emp->total_hours = round((float) $logsQuery->sum('time_spend'), 2);

            // Efficiency based on Role
            if ($deptType === 'nsd' && $emp->hasRole(['Sales-Executive', 'Team-Leader'])) {
                $target = 5;
                $emp->productivity = min(round(($emp->matured_clients / $target) * 100), 100);
            } else {
                $targetHours = 160;
                if ($range['preset'] === 'weekly') {
                    $targetHours = 40;
                } elseif ($range['preset'] === 'daily') {
                    $targetHours = 8;
                } elseif ($range['preset'] === 'custom') {
                    $targetHours = max(1, $range['from']->diffInDays($range['to']) + 1) * 8;
                }
                $emp->productivity = $targetHours > 0 ? min(round(($emp->total_hours / $targetHours) * 100), 100) : 0;
            }

            return $emp;
        });

        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('action_link', function ($row) use ($request) {
                return route('reports.employee.detail', array_filter([
                    'id' => base64_encode($row->id),
                    'preset' => $request->get('preset', $request->get('range')),
                    'date_from' => $request->get('date_from'),
                    'date_to' => $request->get('date_to'),
                    'year' => $request->get('year'),
                    'month' => $request->get('month'),
                ]));
            })
            ->rawColumns(['action_link'])
            ->make(true);
    }


    public function myInsights(Request $request)
    {
        return $this->detail($request, base64_encode(Auth::id()));
    }


    public function detail(Request $request, $id)
    {
        $data = $this->getDetailData($request, $id);
        return view('components.reports.employee_detail', $data);
    }

    public function downloadPdf(Request $request, $id)
    {
        $data = $this->getDetailData($request, $id);
        $pdf = Pdf::loadView('components.reports.employee_detail_pdf', $data);

        $filename = "performance_report_" . str_replace(' ', '_', strtolower($data['employee']->name)) . ".pdf";
        return $pdf->download($filename);
    }
    private function getDetailData(Request $request, $id)
    {
        $userId = base64_decode($id);
        $employee = User::with(['emp', 'departments.dept', 'roles'])->findOrFail($userId);

        $selectedYear = (int) $request->get('year', date('Y'));
        $selectedMonth = $request->get('month', 'All');
        $months = ['All', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $range = $this->dateRange->resolve($request);

        $user = Auth::user();
        if (!$this->reportScope->canViewEmployee($user, (int) $userId)) {
            abort(403, 'Unauthorized access.');
        }

        $performance = app(UserPerformanceService::class);
        $deptType = $performance->departmentType($employee);
        $isSales = $deptType === 'nsd';
        $isCsd = $deptType === 'csd';
        $isOd = $deptType === 'od';

        $stats = $performance->buildMetrics($employee, $selectedYear, $selectedMonth);
        $performanceScore = $performance->performanceScore($stats, $deptType);

        $odSummary = null;
        $odDailyBreakdown = collect();
        $odTaskBreakdown = collect();
        $currentProjects = collect();

        if ($isOd) {
            $odSummary = $this->odWork->summaryForUser((int) $userId, $range['from'], $range['to']);
            $odDailyBreakdown = $this->odWork->dailyBreakdown((int) $userId, $range['from'], $range['to']);
            $odTaskBreakdown = $this->odWork->taskBreakdown((int) $userId, $range['from'], $range['to']);
            $currentProjects = $this->odWork->currentProjects((int) $userId);
            $stats = array_merge($stats, $odSummary);

            // Range-aware productivity override
            $targetHours = max(1, $range['from']->diffInDays($range['to']) + 1) * 8;
            $performanceScore = $targetHours > 0 ? min(100, (int) round(($odSummary['total_hours'] / $targetHours) * 100)) : 0;
        } elseif ($isSales) {
            $nsdSummary = $this->nsdWork->summaryForUser((int) $userId, $range['from'], $range['to']);
            $odDailyBreakdown = $this->nsdWork->dailyBreakdown((int) $userId, $range['from'], $range['to']);
            $currentProjects = $this->nsdWork->currentProjects((int) $userId);
            $stats = array_merge($stats, $nsdSummary);

            // Range-aware productivity override
            $targetMatured = 5;
            $daysDiff = max(1, $range['from']->diffInDays($range['to']) + 1);
            if ($daysDiff < 7) {
                $targetMatured = 1;
            } elseif ($daysDiff <= 31) {
                $targetMatured = 5;
            } else {
                $targetMatured = ceil($daysDiff / 30) * 5;
            }
            $performanceScore = min(100, (int) round(($nsdSummary['matured_count'] / $targetMatured) * 100));
        } elseif ($isCsd) {
            $csdSummary = $this->csdWork->summaryForUser((int) $userId, $range['from'], $range['to']);
            $odDailyBreakdown = $this->csdWork->dailyBreakdown((int) $userId, $range['from'], $range['to']);
            $currentProjects = $this->csdWork->currentProjects((int) $userId);
            $stats = array_merge($stats, $csdSummary);

            // Range-aware productivity override
            $score = $csdSummary['active_clients'] * 3
                + $csdSummary['comms_count'] * 2
                + $csdSummary['tickets_resolved'] * 5
                + $csdSummary['collections_paid'] * 4
                + $csdSummary['opportunities_won'] * 10
                + $csdSummary['change_requests_completed'] * 6;
            $performanceScore = min(100, (int) $score);
        }

        $dailyLogs = collect();
        if ($isOd) {
            $dailyLogs = TaskLog::with('task.project.clients')->where('userid', $userId)
                ->whereBetween('log_date', [$range['from']->toDateString(), $range['to']->toDateString()])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($log) {
                    return Carbon::parse($log->created_at)->format('d M, Y');
                });
        } elseif ($isSales) {
            $dailyLogs = \App\Models\ClientHistory::with('client')->where('created', $userId)
                ->whereBetween('created_at', [$range['from'], $range['to']])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($log) {
                    return Carbon::parse($log->created_at)->format('d M, Y');
                });
        } elseif ($isCsd) {
            $dailyLogs = CsdCommunication::with('client')
                ->where('created_by', $userId)
                ->whereBetween('communication_date', [$range['from'], $range['to']])
                ->orderBy('communication_date', 'desc')
                ->get()
                ->groupBy(function ($log) {
                    return Carbon::parse($log->created_at)->format('d M, Y');
                });
        }

        $tasks = Task::with('project.clients')->where('assigned_to', $userId)->latest()->take(10)->get();
        $logs = TaskLog::with('task.project.clients')->where('userid', $userId)->latest()->take(15)->get();
        $activities = \App\Models\UserActivity::where('user_id', $userId)->latest()->take(10)->get();

        $recentMatured = collect();
        $salesLogs = collect();
        $recentCsdComms = collect();
        $recentWonOpps = collect();

        if ($isSales) {
            $recentMaturedQuery = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')
                ->whereBetween('updated_at', [$range['from'], $range['to']]);
            $salesLogsQuery = \App\Models\ClientHistory::with('client')->where('created', $userId)
                ->whereBetween('created_at', [$range['from'], $range['to']]);

            $recentMatured = $recentMaturedQuery->latest('updated_at')->take(10)->get();
            $salesLogs = $salesLogsQuery->latest()->take(15)->get();
        }

        if ($isCsd) {
            $recentCsdComms = CsdCommunication::with('client')
                ->where('created_by', $userId)
                ->whereBetween('communication_date', [$range['from'], $range['to']])
                ->latest('communication_date')
                ->take(15)
                ->get();
            $recentWonOpps = CsdOpportunity::with('clients')
                ->where('assigned_to', $userId)
                ->where('status', 'won')
                ->whereBetween('updated_at', [$range['from'], $range['to']])
                ->latest('updated_at')
                ->take(10)
                ->get();
        }

        $pastClosings = \App\Models\DayClosing::where('user_id', $userId)
            ->orderBy('closing_date', 'desc')
            ->limit(30)
            ->get();

        return compact(
            'employee',
            'tasks',
            'logs',
            'salesLogs',
            'stats',
            'selectedYear',
            'selectedMonth',
            'months',
            'dailyLogs',
            'isSales',
            'isCsd',
            'isOd',
            'deptType',
            'performanceScore',
            'recentMatured',
            'recentCsdComms',
            'recentWonOpps',
            'activities',
            'range',
            'odSummary',
            'odDailyBreakdown',
            'odTaskBreakdown',
            'currentProjects',
            'pastClosings'
        );
    }
}
