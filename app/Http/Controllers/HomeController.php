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
use App\Models\DayClosing;
use App\Models\CsdClientAssignment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing('departments');
        }
        $todayDate = Carbon::today()->format('Y-m-d');
        $hasSubmittedClosingToday = \App\Models\DayClosing::where('user_id', $user->id)
            ->where('closing_date', $todayDate)
            ->exists();
        view()->share('hasSubmittedClosingToday', $hasSubmittedClosingToday);

        $tab = $request->query('tab');
        if ($tab) {
            session(['active_dashboard_tab' => $tab]);
            session()->save();
        }

        $adminData = [];
        $selectedYear = $request->get('year', date('Y'));
        $departmentId = $user->departments->department ?? null;

        // Branch Manager — cross-department branch oversight dashboard
        if ($user->isBranchManager()) {
            return app(BranchManagerDashboardController::class)->index($request);
        }

        // 🔀 Route Sales Department requests to SalesDashboardController
        if ($user->hasRole('Sales-Executive') || ($user->hasRole('Team-Leader') && $departmentId == 1)) {
            return app(\App\Http\Controllers\SalesDashboardController::class)->index($request);
        }

        // 🔀 Route CSD Department requests
        if ($user->hasRole('CSD-Executive') || ($user->hasRole('Team-Leader') && $departmentId == 3)) {
            return app(\App\Http\Controllers\Csd\CsdDashboardController::class)->index($request);
        }

        // 🔀 Modular Routing for Dashboard Role-Based Data Loading
        if ($user->hasRole('Admin')) {
            $adminData = $this->getAdminDashboardData($selectedYear, $request);
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
    private function getAdminDashboardData($selectedYear, $request = null)
    {
        $preset = $request ? $request->input('preset', 'today') : 'today';
        $customDate = $request ? $request->input('date') : null;

        if (!empty($customDate)) {
            $preset = 'custom';
            $startDate = Carbon::parse($customDate)->startOfDay();
            $endDate = Carbon::parse($customDate)->endOfDay();
            $filterLabel = 'Custom: ' . $startDate->format('d M Y');
        } elseif ($preset === 'yesterday') {
            $startDate = Carbon::yesterday()->startOfDay();
            $endDate = Carbon::yesterday()->endOfDay();
            $filterLabel = 'Yesterday: ' . $startDate->format('d M Y');
        } elseif ($preset === 'this_week') {
            $startDate = Carbon::today()->startOfWeek();
            $endDate = Carbon::today()->endOfWeek();
            $filterLabel = 'This Week (' . $startDate->format('d M') . ' - ' . $endDate->format('d M') . ')';
        } elseif ($preset === 'this_month') {
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::today()->endOfMonth();
            $filterLabel = 'This Month (' . $startDate->format('M Y') . ')';
        } else {
            $preset = 'today';
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $filterLabel = 'Today: ' . $startDate->format('d M Y');
        }

        $dateKey = $preset . '_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');
        $cacheKey = 'wms_admin_dashboard_' . $selectedYear . '_' . $dateKey . '_' . date('H_') . floor(date('i') / 5);

        return Cache::remember($cacheKey, 300, function () use ($selectedYear, $preset, $startDate, $endDate, $filterLabel, $customDate) {
            $adminData = [];
            $adminData['selected_preset'] = $preset;
            $adminData['filter_label'] = $filterLabel;
            $adminData['custom_date_val'] = $customDate ?? $startDate->format('Y-m-d');

            $adminData['total_users'] = User::count();
            $adminData['total_departments'] = Department::count();
            $adminData['total_projects'] = DepartmentProjects::count();
            $adminData['total_tasks'] = Task::count();
            $adminData['total_clients'] = Clients::count();

            $adminData['recent_projects'] = DepartmentProjects::with(['projectCategory', 'clients'])->latest()->take(5)->get();

            // ── Date Markers ──────────────────────────────────────────────────────
            $todayDate = $startDate->format('Y-m-d');
            $isSingleDay = in_array($preset, ['today', 'yesterday', 'custom']);
            $isSunday = $startDate->isSunday();
            $sevenDaysFromNow = $endDate->copy()->addDays(7);

            // Previous Period calculation for comparisons
            if ($preset === 'this_month') {
                $startOfPrevPeriod = $startDate->copy()->subMonth()->startOfMonth();
                $endOfPrevPeriod = $startDate->copy()->subMonth()->endOfMonth();
            } elseif ($preset === 'this_week') {
                $startOfPrevPeriod = $startDate->copy()->subWeek()->startOfWeek();
                $endOfPrevPeriod = $startDate->copy()->subWeek()->endOfWeek();
            } elseif ($preset === 'yesterday') {
                $startOfPrevPeriod = $startDate->copy()->subDay()->startOfDay();
                $endOfPrevPeriod = $startDate->copy()->subDay()->endOfDay();
            } else {
                $startOfPrevPeriod = Carbon::yesterday()->startOfDay();
                $endOfPrevPeriod = Carbon::yesterday()->endOfDay();
            }

            // ── 1. Top 5 KPI Metrics ──────────────────────────────────────────────
            $totalStaff = User::where('status', 'Active')
                ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
                ->count();
            $adminData['employees_count'] = $totalStaff;

            if ($isSingleDay) {
                $presentCount = \App\Models\GlobalAttendanceLog::where('log_date', $todayDate)->distinct('userid')->count('userid');
                $onLeaveCount = \App\Models\EmployeeLeave::where('status', 'approved')
                    ->where('start_date', '<=', $todayDate)
                    ->where('end_date', '>=', $todayDate)
                    ->count();
            } else {
                $presentCount = \App\Models\GlobalAttendanceLog::whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])->distinct('userid')->count('userid');
                $onLeaveCount = \App\Models\EmployeeLeave::where('status', 'approved')
                    ->where('start_date', '<=', $endDate->toDateString())
                    ->where('end_date', '>=', $startDate->toDateString())
                    ->distinct('user_id')
                    ->count('user_id');
            }

            $adminData['present_today_count'] = $presentCount;
            $adminData['present_today_pct'] = $totalStaff > 0 ? round(($presentCount / $totalStaff) * 100) : 0;
            $adminData['on_leave_today_count'] = $onLeaveCount;
            $adminData['on_leave_today_pct'] = $totalStaff > 0 ? round(($onLeaveCount / $totalStaff) * 100) : 0;
            $absentCount = $isSunday ? 0 : max(0, $totalStaff - $presentCount - $onLeaveCount);
            $adminData['absent_today_count'] = $absentCount;
            $adminData['absent_today_pct'] = $totalStaff > 0 ? round(($absentCount / $totalStaff) * 100) : 0;

            // Won Deals
            $adminData['won_deals_total'] = Clients::where('status', 'Matured')->where('created_at', '<=', $endDate)->count();
            $wonThisPeriod = Clients::where('status', 'Matured')->whereBetween('created_at', [$startDate, $endDate])->count();
            $wonPrevPeriod = Clients::where('status', 'Matured')->whereBetween('created_at', [$startOfPrevPeriod, $endOfPrevPeriod])->count();
            $adminData['won_deals_this_month'] = $wonThisPeriod;
            $adminData['won_deals_growth_pct'] = $wonPrevPeriod > 0 ? round((($wonThisPeriod - $wonPrevPeriod) / $wonPrevPeriod) * 100, 1) : ($wonThisPeriod > 0 ? 100 : 0);

            // Leads (Pipeline)
            $adminData['leads_total'] = Clients::where('created_at', '<=', $endDate)->count();
            $leadsThisPeriod = Clients::whereBetween('created_at', [$startDate, $endDate])->count();
            $leadsPrevPeriod = Clients::whereBetween('created_at', [$startOfPrevPeriod, $endOfPrevPeriod])->count();
            $adminData['leads_this_month'] = $leadsThisPeriod;
            $adminData['leads_growth_pct'] = $leadsPrevPeriod > 0 ? round((($leadsThisPeriod - $leadsPrevPeriod) / $leadsPrevPeriod) * 100, 1) : ($leadsThisPeriod > 0 ? 100 : 0);

            // Active Projects & Overdue
            $adminData['active_projects_count'] = DepartmentProjects::where('status', 'InProgress')->count();
            $adminData['overdue_projects_count'] = DepartmentProjects::where('status', '!=', 'Completed')->where('end_date', '<', $endDate)->count();

            // Customers (CSD accounts)
            $csdActiveAccounts = CsdClientAssignment::where('status', 'active')->count();
            $adminData['customers_count'] = $csdActiveAccounts > 0 ? $csdActiveAccounts : max(1, Clients::where('status', 'Matured')->count());
            $adminData['customers_at_risk_count'] = CsdClientAssignment::where('status', 'active')->whereIn('health_status', ['at_risk', 'churning'])->count();

            // ── 2. Needs Your Attention ───────────────────────────────────────────
            $adminData['leads_no_followup_count'] = Clients::where('status', 'Fresh')->where('created_at', '<=', $endDate)->count();
            if ($isSingleDay) {
                $submittedUserIds = DayClosing::where('closing_date', $todayDate)->pluck('user_id')->toArray();
            } else {
                $submittedUserIds = DayClosing::whereBetween('closing_date', [$startDate->toDateString(), $endDate->toDateString()])->pluck('user_id')->toArray();
            }
            $adminData['pending_closing_users_count'] = User::whereNotIn('id', $submittedUserIds)
                ->where('status', 'Active')
                ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
                ->whereNull('deleted_at')
                ->count();

            // ── 3. Today's Priorities ─────────────────────────────────────────────
            $adminData['day_closings_count'] = $adminData['pending_closing_users_count'];
            $adminData['closing_approvals_count'] = DayClosing::where('status', 'pending')->count();
            $adminData['leave_approvals_count'] = \App\Models\EmployeeLeave::where('status', 'pending')->count();
            $adminData['project_deadlines_count'] = DepartmentProjects::where('status', '!=', 'Completed')
                ->whereBetween('end_date', [$startDate, $sevenDaysFromNow])
                ->count();

            // ── 4. NSD (Sales) Overview ───────────────────────────────────────────
            $adminData['nsd_leads'] = $adminData['leads_total'];
            $adminData['nsd_qualified'] = Clients::whereIn('status', ['Followup', 'Matured'])->where('created_at', '<=', $endDate)->count();
            $adminData['nsd_won'] = $adminData['won_deals_total'];
            $adminData['nsd_conversion_pct'] = $adminData['leads_total'] > 0 ? round(($adminData['nsd_won'] / $adminData['leads_total']) * 100, 1) : 0;

            // Sales Funnel
            $adminData['funnel_new_leads'] = Clients::where('status', 'Fresh')->where('created_at', '<=', $endDate)->count();
            $adminData['funnel_contacted'] = Clients::where('status', 'Followup')->where('created_at', '<=', $endDate)->count();
            $adminData['funnel_qualified'] = Clients::whereNotIn('status', ['Fresh', 'Not Interested'])->where('created_at', '<=', $endDate)->count();
            $adminData['funnel_proposals'] = Clients::whereIn('status', ['Matured', 'Followup'])->where('created_at', '<=', $endDate)->count();
            $adminData['funnel_won'] = Clients::where('status', 'Matured')->where('created_at', '<=', $endDate)->count();

            // ── 5. OD (Operations) Overview ───────────────────────────────────────
            $adminData['od_on_track'] = DepartmentProjects::where('status', 'InProgress')->where('end_date', '>=', $sevenDaysFromNow)->count();
            $adminData['od_at_risk'] = DepartmentProjects::where('status', 'InProgress')->whereBetween('end_date', [$startDate, $sevenDaysFromNow])->count();
            $adminData['od_overdue'] = $adminData['overdue_projects_count'];
            $adminData['od_total_active'] = $adminData['active_projects_count'] > 0 ? $adminData['active_projects_count'] : 1;

            // Upcoming Deadlines with Progress
            $upcomingProjects = DepartmentProjects::with(['clients', 'tasks', 'projectCategory'])
                ->where('status', '!=', 'Completed')
                ->whereNotNull('end_date')
                ->orderBy('end_date', 'asc')
                ->take(5)
                ->get();
            $adminData['upcoming_deadlines'] = $upcomingProjects->map(function ($p) use ($startDate) {
                $endDate = Carbon::parse($p->end_date)->endOfDay();
                $now = $startDate->copy();

                if ($endDate->isPast() && !$endDate->isSameDay($now)) {
                    $daysAgo = max(1, (int) abs($now->diffInDays($endDate, false)));
                    $chipText = 'Overdue ' . $daysAgo . 'd';
                    $chipClass = 'danger';
                } elseif ($endDate->isSameDay($now)) {
                    $chipText = 'Due Today';
                    $chipClass = 'danger';
                } elseif ($endDate->isSameDay($now->copy()->addDay())) {
                    $chipText = 'Due Tomorrow';
                    $chipClass = 'warning';
                } else {
                    $daysLeft = max(1, (int) ceil($now->diffInDays($endDate, false)));
                    $chipText = 'In ' . $daysLeft . ' days';
                    $chipClass = $daysLeft <= 3 ? 'warning' : 'primary';
                }

                $totalTasks = $p->tasks->count();
                $doneTasks = $p->tasks->where('status', 'Completed')->count();
                $progressPct = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

                return (object)[
                    'id' => $p->id,
                    'name' => $p->project_name,
                    'client' => optional($p->clients)->name ?? 'Internal Client',
                    'category' => optional($p->projectCategory)->category_name ?? 'Web App',
                    'due_text' => (string) $chipText,
                    'due_class' => $chipClass,
                    'progress' => $progressPct,
                    'end_date' => $endDate->format('d M'),
                ];
            });

            // ── 6. CSD (Customer Success) Overview ────────────────────────────────
            $healthyCount = CsdClientAssignment::where('status', 'active')->where('health_status', 'healthy')->count();
            $attentionCount = CsdClientAssignment::where('status', 'active')->where('health_status', 'at_risk')->count();
            $churningCount = CsdClientAssignment::where('status', 'active')->where('health_status', 'churning')->count();
            $adminData['csd_healthy'] = $healthyCount;
            $adminData['csd_attention'] = $attentionCount;
            $adminData['csd_at_risk'] = $churningCount;

            // At-risk customers list (Real records only, no dummy data)
            $adminData['at_risk_customers'] = CsdClientAssignment::with(['client'])
                ->where('status', 'active')
                ->whereIn('health_status', ['at_risk', 'churning'])
                ->take(5)
                ->get()
                ->map(function ($a) {
                    return (object)[
                        'name' => optional($a->client)->name ?? 'Customer Account',
                        'reason' => $a->health_status === 'churning' ? 'Payment overdue / Inactive' : 'Pending escalations',
                        'risk_level' => $a->health_status === 'churning' ? 'High Risk' : 'Medium Risk',
                        'risk_class' => $a->health_status === 'churning' ? 'danger' : 'warning',
                    ];
                });

            // ── 7. Today's Employee Status Mini Table ─────────────────────────────
            $activeRunningTimers = TaskLog::whereNull('endtime')
                ->with(['task.project.clients'])
                ->get()
                ->keyBy('userid');

            $recentAttendanceUsers = User::where('status', 'Active')
                ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
                ->with(['departments.dept'])
                ->take(8)
                ->get()
                ->map(function ($u) use ($todayDate, $isSingleDay, $startDate, $endDate, $activeRunningTimers) {
                    if ($isSingleDay) {
                        $log = \App\Models\GlobalAttendanceLog::where('userid', $u->id)->where('log_date', $todayDate)->first();
                        $leave = \App\Models\EmployeeLeave::where('user_id', $u->id)
                            ->where('status', 'approved')
                            ->where('start_date', '<=', $todayDate)
                            ->where('end_date', '>=', $todayDate)
                            ->first();
                        $taskHours = round((float) TaskLog::where('userid', $u->id)->where('log_date', $todayDate)->sum('time_spend'), 1);
                    } else {
                        $log = \App\Models\GlobalAttendanceLog::where('userid', $u->id)->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])->latest('log_date')->first();
                        $leave = \App\Models\EmployeeLeave::where('user_id', $u->id)
                            ->where('status', 'approved')
                            ->where('start_date', '<=', $endDate->toDateString())
                            ->where('end_date', '>=', $startDate->toDateString())
                            ->first();
                        $taskHours = round((float) TaskLog::where('userid', $u->id)->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])->sum('time_spend'), 1);
                    }

                    $runningTimer = $activeRunningTimers->get($u->id);
                    $isWorkingNow = ($runningTimer !== null);
                    $activeTaskTitle = null;
                    $activeProjectTitle = null;
                    if ($isWorkingNow && $runningTimer->task) {
                        $activeTaskTitle = $runningTimer->task->title ?? $runningTimer->task->task_name ?? 'Active Task';
                        $activeProjectTitle = optional($runningTimer->task->project)->project_name ?? 'Active Project';

                        $now = Carbon::now();
                        $logDate = $runningTimer->log_date ? Carbon::parse($runningTimer->log_date)->format('Y-m-d') : Carbon::parse($runningTimer->created_at)->format('Y-m-d');
                        $startedAt = Carbon::parse($logDate . ' ' . ($runningTimer->starttime ?: $runningTimer->created_at->format('H:i:s')));
                        if ($now->gt($startedAt)) {
                            $taskHours = round($taskHours + ($startedAt->diffInSeconds($now) / 3600), 1);
                        }
                    }

                    $status = 'Absent';
                    $statusClass = 'danger';
                    $checkIn = '-';
                    $checkOut = '-';
                    $punctuality = null;

                    if ($log) {
                        $status = 'Present';
                        $statusClass = 'success';
                        if ($log->starttime) {
                            $cIn = Carbon::parse($log->starttime);
                            $checkIn = $cIn->format('h:i A');
                            $punctuality = ($cIn->format('H:i:s') > '09:45:00') ? 'Late' : 'On Time';
                        }
                        $checkOut = $log->endtime ? Carbon::parse($log->endtime)->format('h:i A') : '-';
                    } elseif ($leave) {
                        $status = 'On Leave';
                        $statusClass = 'warning';
                    }

                    $shiftHours = $log ? round((float) $log->time_spend, 1) : 0;

                    return (object)[
                        'id' => $u->id,
                        'name' => $u->name,
                        'dept' => optional(optional($u->departments)->dept)->name ?? 'Operations',
                        'status' => $status,
                        'status_class' => $statusClass,
                        'is_working_now' => $isWorkingNow,
                        'active_task' => $activeTaskTitle,
                        'active_project' => $activeProjectTitle,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'punctuality' => $punctuality,
                        'task_hours' => $taskHours,
                        'shift_hours' => $shiftHours,
                    ];
                });
            $adminData['employee_status_list'] = $recentAttendanceUsers;

            // ── 8. Top Performers (NSD, OD, CSD) ──────────────────────────────────
            $adminData['top_nsd_performers'] = User::where('status', 'Active')
                ->whereHas('departments', fn($q) => $q->where('department', 1))
                ->with(['roles'])
                ->withCount([
                    'clients as deals_count' => fn($q) => $q->where('status', 'Matured')->whereBetween('created_at', [$startDate, $endDate]),
                    'clients as leads_count' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]),
                ])
                ->orderByDesc('deals_count')
                ->take(5)
                ->get()
                ->map(function ($u) {
                    $u->role_name = optional($u->roles->first())->name ?? 'Sales Executive';
                    return $u;
                });

            $adminData['top_od_performers'] = User::where('status', 'Active')
                ->whereHas('departments', fn($q) => $q->where('department', 2))
                ->with(['roles'])
                ->withCount([
                    'tasks as tasks_count' => fn($q) => $q->where('status', 'Completed')->whereBetween('updated_at', [$startDate, $endDate]),
                    'tasks as active_tasks_count' => fn($q) => $q->where('status', 'InProgress'),
                ])
                ->withSum(['taskLogs as total_hours' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])], 'time_spend')
                ->orderByDesc('total_hours')
                ->orderByDesc('tasks_count')
                ->take(5)
                ->get()
                ->map(function ($u) {
                    $u->formatted_hours = round((float) ($u->total_hours ?? 0), 1);
                    $u->role_name = optional($u->roles->first())->name ?? 'Developer';
                    return $u;
                });

            $adminData['top_csd_performers'] = User::where('status', 'Active')
                ->whereHas('departments', fn($q) => $q->where('department', 3))
                ->with(['roles'])
                ->withCount(['csdAssignments as retentions_count' => fn($q) => $q->where('status', 'active')])
                ->orderByDesc('retentions_count')
                ->take(5)
                ->get()
                ->map(function ($u) {
                    $u->role_name = optional($u->roles->first())->name ?? 'CSD Executive';
                    return $u;
                });

            // ── 9. Real Recent Activity Feed ───────────────────────────────────────
            $realActivities = collect();

            $recentLeads = Clients::latest()->take(3)->get();
            foreach ($recentLeads as $lead) {
                $realActivities->push((object)[
                    'icon' => 'mdi-account-plus',
                    'color' => 'success',
                    'bg' => '#ecfdf5',
                    'text' => 'New lead "' . Str::limit($lead->name, 22) . '" added to pipeline',
                    'time' => $lead->created_at ? $lead->created_at->diffForHumans(null, true) : 'Recently',
                    'timestamp' => $lead->created_at ? $lead->created_at->timestamp : 0,
                ]);
            }

            $recentTaskLogs = TaskLog::with(['task'])->latest()->take(3)->get();
            foreach ($recentTaskLogs as $tLog) {
                if ($tLog->task) {
                    $realActivities->push((object)[
                        'icon' => 'mdi-check-circle',
                        'color' => 'primary',
                        'bg' => '#eff6ff',
                        'text' => 'Task "' . Str::limit($tLog->task->title ?? $tLog->task->task_name, 22) . '" worked on',
                        'time' => $tLog->created_at ? $tLog->created_at->diffForHumans(null, true) : 'Recently',
                        'timestamp' => $tLog->created_at ? $tLog->created_at->timestamp : 0,
                    ]);
                }
            }

            $recentClosings = DayClosing::with(['user'])->latest()->take(2)->get();
            foreach ($recentClosings as $cl) {
                $realActivities->push((object)[
                    'icon' => 'mdi-clipboard-check',
                    'color' => 'warning',
                    'bg' => '#fffbeb',
                    'text' => 'Daily closing report submitted by ' . optional($cl->user)->name,
                    'time' => $cl->created_at ? $cl->created_at->diffForHumans(null, true) : 'Recently',
                    'timestamp' => $cl->created_at ? $cl->created_at->timestamp : 0,
                ]);
            }

            $adminData['recent_activities'] = $realActivities->sortByDesc('timestamp')->take(5)->values();

            return $adminData;
        });
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
            $q->with(['clients', 'projectCategory', 'project_team'])
                ->where(function ($sq) use ($user, $teamId) {
                    $sq->where('assigned_to', $user->id);
                    if ($teamId) {
                        $sq->orWhereHas('project_team', function ($ssq) use ($teamId) {
                            $ssq->where('teamid', $teamId);
                        });
                    }
                })
                ->when($userDeptId, function ($sq) use ($userDeptId) {
                    $sq->whereHas('projectCategory', function ($ssq) use ($userDeptId) {
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

        // Workload Heatmap — task load per team member
        $memberIds = $adminData['team_employees']->pluck('id')->toArray();
        $adminData['workload_heatmap'] = !empty($memberIds)
            ? app(\App\Repositories\ProjectRepository::class)->getWorkloadByTeamMembers($memberIds)
            : collect();

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
        $adminData['projects_not_started_count'] = (clone $projectsQuery)->where('status', 'ToDo')->count();
        $adminData['projects_in_progress_count'] = (clone $projectsQuery)->where('status', 'InProgress')->count();

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
        $adminData['my_tasks'] = Task::with(['project.projectCategory', 'project.clients'])->where('assigned_to', $user->id)
            ->whereIn('status', ['ToDo', 'InProgress'])
            ->orderBy('priority', 'desc')
            ->get();

        $adminData['recently_completed_tasks'] = Task::with(['project.projectCategory', 'project.clients'])->where('assigned_to', $user->id)
            ->where('status', 'Completed')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $adminData['recent_projects'] = DepartmentProjects::whereHas('tasks', function ($q) use ($user, $year) {
            $q->where('assigned_to', $user->id);
        })->with(['projectCategory', 'clients'])
            ->withCount(['tasks as user_tasks_count' => function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            }])
            ->latest()->take(5)->get();

        $adminData['recent_logs'] = TaskLog::with('task.project')->where('userid', $user->id)->latest()->take(10)->get();

        // Daily Pulse (Today's specific metrics)
        $startOfToday = Carbon::now()->startOfDay();
        $todaysTaskIds = TaskLog::where('userid', $user->id)
            ->where('created_at', '>=', $startOfToday)
            ->pluck('taskid')
            ->toArray();

        $todaysCreatedOrUpdatedTaskIds = Task::where('assigned_to', $user->id)
            ->where(function ($q) use ($startOfToday) {
                $q->where('created_at', '>=', $startOfToday)
                    ->orWhere('updated_at', '>=', $startOfToday);
            })
            ->pluck('id')
            ->toArray();

        $allTodaysTaskIds = array_unique(array_merge($todaysTaskIds, $todaysCreatedOrUpdatedTaskIds));

        $adminData['todays_tasks'] = Task::with(['project.projectCategory', 'project.clients'])
            ->whereIn('id', $allTodaysTaskIds)
            ->orderBy('updated_at', 'desc')
            ->get();

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
