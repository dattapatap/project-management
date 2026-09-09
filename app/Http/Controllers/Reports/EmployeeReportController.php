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

        $startDateStr = $request->input('start_date', $request->input('date_from'));
        $endDateStr = $request->input('end_date', $request->input('date_to'));
        $deptId = $request->input('dept_id');

        if (!$startDateStr || !$endDateStr) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
            $startDateStr = $startDate->format('Y-m-d');
            $endDateStr = $endDate->format('Y-m-d');
        } else {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($endDateStr)->endOfDay();
        }

        $departmentId = $user->departments->department ?? null;
        $query = $this->reportScope->visibleEmployeesQuery($user);

        if (!empty($deptId)) {
            $query->whereHas('departments', function ($q) use ($deptId) {
                $q->where('department', $deptId);
            });
        }

        $showSales = true;
        if ($departmentId && $departmentId != 1 && !$user->hasBranchWideAccess()) {
            $showSales = false;
        }

        $employeesCount = $query->count();
        $departments = \App\Models\Department::where('status', true)->get();

        // 📊 Calculate Summary KPIs within the selected date range
        $visibleUserIds = $query->pluck('id')->toArray();
        $opsQuery = Task::whereBetween('updated_at', [$startDate, $endDate])->whereIn('assigned_to', $visibleUserIds);
        $leadsQuery = DB::table('clients')->whereBetween('created_at', [$startDate, $endDate])->whereIn('ref_user', $visibleUserIds);
        $maturedQuery = DB::table('clients')->where('status', 'Matured')->whereBetween('updated_at', [$startDate, $endDate])->whereIn('ref_user', $visibleUserIds);
        $activeFollowupQuery = DB::table('clients')->whereIn('status', ['Followup', 'Meeting Fixed'])->whereIn('ref_user', $visibleUserIds);

        $totalOps = (clone $opsQuery)->count();
        $completedOps = (clone $opsQuery)->where('status', 'Completed')->count();
        $opsRate = $totalOps > 0 ? round(($completedOps / $totalOps) * 100) : 0;

        $totalHoursLogged = (float) TaskLog::whereIn('userid', $visibleUserIds)
            ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('time_spend');

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

        // 📈 12-Month Performance Trend (Current Year)
        $selectedYear = (int) $startDate->format('Y');
        $trendMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $opsTrendQ = Task::select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->where('status', 'Completed')->whereYear('updated_at', $selectedYear)
            ->whereIn('assigned_to', $visibleUserIds)
            ->groupBy('month');
        $opsTrendRaw = $opsTrendQ->get()->keyBy('month');

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
            'departments',
            'performanceTrend',
            'selectedYear',
            'startDateStr',
            'endDateStr',
            'deptId',
            'showSales',
            'opsRate',
            'completedOps',
            'totalHoursLogged',
            'salesRate',
            'totalLeadsCount',
            'maturedCount',
            'activeFollowupCount'
        ));
    }

    /**
     * Employees Performance DataTable Data
     */
    public function data(Request $request)
    {
        $user = Auth::user();

        $startDateStr = $request->input('start_date', $request->input('date_from'));
        $endDateStr = $request->input('end_date', $request->input('date_to'));
        $deptId = $request->input('dept_id');

        if (!$startDateStr || !$endDateStr) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        } else {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($endDateStr)->endOfDay();
        }

        $query = $this->reportScope->visibleEmployeesQuery($user);

        if (!empty($deptId)) {
            $query->whereHas('departments', function ($q) use ($deptId) {
                $q->where('department', $deptId);
            });
        }

        $performance = app(UserPerformanceService::class);

        $employees = $query->with(['roles', 'departments.dept'])->get()->map(function ($emp) use ($performance, $startDate, $endDate) {
            $deptType = $performance->departmentType($emp);
            $emp->dept_type = $deptType;

            if ($deptType === 'od') {
                $emp = $this->odWork->enrichEmployeeRow($emp, $startDate, $endDate);
            } elseif ($deptType === 'nsd') {
                $emp = $this->nsdWork->enrichEmployeeRow($emp, $startDate, $endDate);
            } elseif ($deptType === 'csd') {
                $emp = $this->csdWork->enrichEmployeeRow($emp, $startDate, $endDate);
            } else {
                $emp = $this->odWork->enrichEmployeeRow($emp, $startDate, $endDate);
            }

            return $emp;
        });

        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('action_link', function ($row) use ($startDate, $endDate) {
                return route('reports.employee.detail', [
                    'id' => base64_encode($row->id),
                    'date_from' => $startDate->toDateString(),
                    'date_to' => $endDate->toDateString(),
                ]);
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

        $startDateInput = $request->input('start_date', $request->input('date_from'));
        $endDateInput = $request->input('end_date', $request->input('date_to'));

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
        } else {
            // Default to current month (e.g. 1st of month to today)
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        }

        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();
        $range = [
            'from' => $startDate,
            'to' => $endDate,
            'preset' => 'custom',
            'label' => $startDate->format('d M, Y') . ' – ' . $endDate->format('d M, Y'),
        ];

        $selectedYear = (int) $startDate->format('Y');
        $selectedMonth = $startDate->format('M');
        $months = ['All', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

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

        // 🟢 1. Live Working Status (Is he working right now or not?)
        $activeTaskLog = TaskLog::with('task.project.clients')
            ->where('userid', $userId)
            ->whereNull('endtime')
            ->latest('id')
            ->first();

        $activeShiftLog = \App\Models\GlobalAttendanceLog::where('userid', $userId)
            ->whereDate('log_date', Carbon::today())
            ->whereNull('endtime')
            ->latest('id')
            ->first();

        $lastActivityLog = TaskLog::with('task.project.clients')
            ->where('userid', $userId)
            ->latest('id')
            ->first();

        $liveStatus = [
            'is_working' => false,
            'status_label' => 'Offline / Idle',
            'badge_class' => 'badge-soft-secondary',
            'detail' => 'No active shift or running timer.',
            'task_title' => null,
            'project_name' => null,
            'started_at' => null,
        ];

        if ($activeTaskLog) {
            $liveStatus['is_working'] = true;
            $liveStatus['status_label'] = 'Currently Working on Task';
            $liveStatus['badge_class'] = 'badge-soft-success';
            $liveStatus['task_title'] = $activeTaskLog->task->title ?? 'Active Task';
            $liveStatus['project_name'] = $activeTaskLog->task->project->project_name ?? ($activeTaskLog->task->project->clients->name ?? 'Internal');
            $liveStatus['started_at'] = $activeTaskLog->starttime ? Carbon::parse($activeTaskLog->starttime)->format('h:i A') : Carbon::parse($activeTaskLog->created_at)->format('h:i A');
            $liveStatus['detail'] = "Working on \"{$liveStatus['task_title']}\" ({$liveStatus['project_name']}) since {$liveStatus['started_at']}";
        } elseif ($activeShiftLog) {
            $liveStatus['is_working'] = true;
            $liveStatus['status_label'] = 'Shift Active (In-Between Tasks)';
            $liveStatus['badge_class'] = 'badge-soft-primary';
            $liveStatus['started_at'] = Carbon::parse($activeShiftLog->starttime)->format('h:i A');
            $liveStatus['detail'] = "Shift started at {$liveStatus['started_at']}. Currently not recording a specific task.";
        } elseif ($lastActivityLog) {
            $lastSeenTime = Carbon::parse($lastActivityLog->updated_at)->diffForHumans();
            $liveStatus['detail'] = "Last logged task activity {$lastSeenTime} on " . Carbon::parse($lastActivityLog->log_date)->format('d M, Y');
        }

        // 📅 2. Day-by-day Task Spend & Missing Day Closings (Excluding Sundays)
        $closingsInRange = \App\Models\DayClosing::where('user_id', $userId)
            ->whereBetween('closing_date', [$startDateStr, $endDateStr])
            ->get()
            ->keyBy(fn($dc) => Carbon::parse($dc->closing_date)->format('Y-m-d'));

        $attendanceByDate = \App\Models\GlobalAttendanceLog::where('userid', $userId)
            ->whereBetween('log_date', [$startDateStr, $endDateStr])
            ->get()
            ->groupBy(fn($l) => Carbon::parse($l->log_date)->format('Y-m-d'));

        $taskLogsByDate = TaskLog::with('task.project.clients')
            ->where('userid', $userId)
            ->whereBetween('log_date', [$startDateStr, $endDateStr])
            ->get()
            ->groupBy(fn($l) => Carbon::parse($l->log_date)->format('Y-m-d'));

        $dailyWorkDays = collect();
        $totalWorkingDaysCount = 0;
        $unsubmittedClosingDaysCount = 0;
        $submittedClosingDaysCount = 0;
        $totalTaskHoursLogged = 0.0;
        $totalShiftHoursLogged = 0.0;

        $cursor = $startDate->copy()->startOfDay();
        $today = Carbon::today();
        $todayDate = $today->toDateString();
        $effectiveEndDate = $endDate->gt($today) ? $today->copy()->endOfDay() : $endDate;
        $userJoinedDate = $employee->created_at ? Carbon::parse($employee->created_at)->startOfDay() : null;

        while ($cursor->lte($effectiveEndDate)) {
            $dateStr = $cursor->format('Y-m-d');
            $isSunday = $cursor->isSunday();
            $isBeforeJoin = $userJoinedDate && $cursor->lt($userJoinedDate);

            // Skip Sundays completely from listing and calculations per requirement
            if ($isSunday) {
                $cursor->addDay();
                continue;
            }

            $isCountableWorkingDay = !$isBeforeJoin;

            $dayTaskLogs = $taskLogsByDate->get($dateStr, collect());
            $dayAttendance = $attendanceByDate->get($dateStr, collect());
            $dayClosing = $closingsInRange->get($dateStr);

            $dayTaskHours = round((float) $dayTaskLogs->sum('time_spend'), 2);
            $dayShiftHours = round((float) $dayAttendance->sum('time_spend'), 2);

            $totalTaskHoursLogged += $dayTaskHours;
            $totalShiftHoursLogged += $dayShiftHours;

            $closingStatus = 'Not Submitted';
            if ($dayClosing) {
                $closingStatus = $dayClosing->status ?? 'Submitted';
                if ($isCountableWorkingDay) {
                    $submittedClosingDaysCount++;
                }
            } elseif ($isBeforeJoin) {
                $closingStatus = 'Pre-Employment';
            } else {
                $closingStatus = 'Not Submitted';
                $unsubmittedClosingDaysCount++;
            }

            if ($isCountableWorkingDay) {
                $totalWorkingDaysCount++;
            }

            $dailyWorkDays->push((object)[
                'date' => $dateStr,
                'label' => $cursor->format('d M, Y'),
                'day_name' => $cursor->format('l'),
                'is_sunday' => false,
                'is_today' => $dateStr === $todayDate,
                'is_future' => false,
                'task_hours' => $dayTaskHours,
                'shift_hours' => $dayShiftHours,
                'task_count' => $dayTaskLogs->unique('taskid')->count(),
                'closing_status' => $closingStatus,
                'target_status' => $dayClosing ? ($dayClosing->target_status ?? 'Met') : ($dayTaskHours >= 6.0 ? 'Met' : 'Not Met'),
                'tasks' => $dayTaskLogs->map(function($log) {
                    $task = $log->task;
                    $project = $task?->project;
                    $client = $project?->clients;

                    $taskTitle = $task?->title ?? ('Task #' . $log->taskid);
                    $projectName = $project?->project_name ?? 'Internal Project';
                    $clientName = $client?->name ?? ($project?->project_name ?? 'Direct');

                    return (object)[
                        'task_id' => $log->taskid,
                        'task_title' => $taskTitle,
                        'task_name' => $taskTitle,
                        'project_name' => $projectName,
                        'client_name' => $clientName,
                        'hours' => round((float)$log->time_spend, 2),
                        'hours_formatted' => OdWorkReportService::formatToTimingHours((float)$log->time_spend),
                        'description' => $log->log_description ?: 'No detailed note provided.',
                        'starttime' => $log->starttime ? Carbon::parse($log->starttime)->format('h:i A') : '',
                        'endtime' => $log->endtime ? Carbon::parse($log->endtime)->format('h:i A') : '',
                        'time' => $log->created_at ? Carbon::parse($log->created_at)->format('h:i A') : '—',
                    ];
                }),
            ]);

            $cursor->addDay();
        }

        // ⏱️ 3. Average Hours & Task-Driven Performance Score
        $avgDailyTaskHours = $totalWorkingDaysCount > 0 ? round($totalTaskHoursLogged / $totalWorkingDaysCount, 2) : 0;
        $avgDailyShiftHours = $totalWorkingDaysCount > 0 ? round($totalShiftHoursLogged / $totalWorkingDaysCount, 2) : 0;

        $totalTaskHoursFormatted = OdWorkReportService::formatToTimingHours($totalTaskHoursLogged);
        $totalShiftHoursFormatted = OdWorkReportService::formatToTimingHours($totalShiftHoursLogged);
        $avgDailyTaskHoursFormatted = OdWorkReportService::formatToTimingHours($avgDailyTaskHours);
        $avgDailyShiftHoursFormatted = OdWorkReportService::formatToTimingHours($avgDailyShiftHours);

        // Daily standard benchmark is 6.5 task hours / day
        $expectedTaskHours = $totalWorkingDaysCount * 6.5;
        $taskPerformanceScore = $expectedTaskHours > 0 ? min(100, (int) round(($totalTaskHoursLogged / $expectedTaskHours) * 100)) : 0;

        $odSummary = null;
        $odDailyBreakdown = collect();
        $odTaskBreakdown = collect();
        $currentProjects = collect();

        if ($isOd) {
            $odSummary = $this->odWork->summaryForUser((int) $userId, $startDate, $endDate);
            $odDailyBreakdown = $this->odWork->dailyBreakdown((int) $userId, $startDate, $endDate);
            $odTaskBreakdown = $this->odWork->taskBreakdown((int) $userId, $startDate, $endDate);
            $currentProjects = $this->odWork->currentProjects((int) $userId);
            $stats = array_merge($stats, $odSummary);
        } elseif ($isSales) {
            $nsdSummary = $this->nsdWork->summaryForUser((int) $userId, $startDate, $endDate);
            $odDailyBreakdown = $this->nsdWork->dailyBreakdown((int) $userId, $startDate, $endDate);
            $currentProjects = $this->nsdWork->currentProjects((int) $userId);
            $stats = array_merge($stats, $nsdSummary);
        } elseif ($isCsd) {
            $csdSummary = $this->csdWork->summaryForUser((int) $userId, $startDate, $endDate);
            $odDailyBreakdown = $this->csdWork->dailyBreakdown((int) $userId, $startDate, $endDate);
            $currentProjects = $this->csdWork->currentProjects((int) $userId);
            $stats = array_merge($stats, $csdSummary);
        }

        // 🔥 4. Identify Maximum Time Took Task
        $maxTaskHours = $odTaskBreakdown->max('total_hours') ?? 0;

        $dailyLogs = collect();
        if ($isOd) {
            $dailyLogs = TaskLog::with('task.project.clients')->where('userid', $userId)
                ->whereBetween('log_date', [$startDateStr, $endDateStr])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($log) {
                    return Carbon::parse($log->created_at)->format('d M, Y');
                });
        } elseif ($isSales) {
            $dailyLogs = \App\Models\ClientHistory::with('client')->where('created', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($log) {
                    return Carbon::parse($log->created_at)->format('d M, Y');
                });
        } elseif ($isCsd) {
            $dailyLogs = CsdCommunication::with('client')
                ->where('created_by', $userId)
                ->whereBetween('communication_date', [$startDate, $endDate])
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
            $recentMatured = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->latest('updated_at')->take(10)->get();
            $salesLogs = \App\Models\ClientHistory::with('client')->where('created', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->latest()->take(15)->get();
        }

        if ($isCsd) {
            $recentCsdComms = CsdCommunication::with('client')
                ->where('created_by', $userId)
                ->whereBetween('communication_date', [$startDate, $endDate])
                ->latest('communication_date')
                ->take(15)
                ->get();
            $recentWonOpps = CsdOpportunity::with('clients')
                ->where('assigned_to', $userId)
                ->where('status', 'won')
                ->whereBetween('updated_at', [$startDate, $endDate])
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
            'taskPerformanceScore',
            'recentMatured',
            'recentCsdComms',
            'recentWonOpps',
            'activities',
            'range',
            'startDateStr',
            'endDateStr',
            'odSummary',
            'odDailyBreakdown',
            'odTaskBreakdown',
            'maxTaskHours',
            'currentProjects',
            'pastClosings',
            'liveStatus',
            'dailyWorkDays',
            'totalWorkingDaysCount',
            'unsubmittedClosingDaysCount',
            'submittedClosingDaysCount',
            'totalTaskHoursLogged',
            'totalShiftHoursLogged',
            'avgDailyTaskHours',
            'avgDailyShiftHours',
            'totalTaskHoursFormatted',
            'totalShiftHoursFormatted',
            'avgDailyTaskHoursFormatted',
            'avgDailyShiftHoursFormatted'
        );
    }
}
