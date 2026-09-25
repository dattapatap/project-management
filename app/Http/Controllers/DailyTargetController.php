<?php

namespace App\Http\Controllers;

use App\Exports\DailyTargetsExport;
use App\Models\DailyTarget;
use App\Models\User;
use App\Services\BranchScopeService;
use App\Services\DailyClosingService;
use App\Services\UserPerformanceService;
use Auth;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Validator;

class DailyTargetController extends Controller
{
    public function __construct(
        private DailyClosingService $closingService,
        private BranchScopeService $branchScope
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        $performanceService = new UserPerformanceService();

        if ($user->isGlobalAdmin()) {
            $rawQuery = User::whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments']);
        } else {
            $branchUserIds = $this->branchScope->getBranchUserIds($user);
            $rawQuery = User::whereIn('id', $branchUserIds)
                ->whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments']);
        }

        $employees = $rawQuery->get()
            ->filter(function ($u) use ($performanceService) {
                $deptType = $performanceService->departmentType($u);
                return in_array($deptType, ['nsd', 'csd', 'od'], true);
            })->values();

        return view('components.targets.daily-targets', compact('employees'));
    }

    public function configure(Request $request)
    {
        $user = Auth::user();

        // Fetch active employees - Admin sees all, Branch Manager sees branch-scoped
        $performanceService = new UserPerformanceService();

        if ($user->isGlobalAdmin()) {
            $rawUsers = User::whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments'])
                ->get();
        } else {
            $branchUserIds = $this->branchScope->getBranchUserIds($user);
            $rawUsers = User::whereIn('id', $branchUserIds)
                ->whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments'])
                ->get();
        }

        $users = $rawUsers->map(function ($u) use ($performanceService) {
            $u->dept_type = $performanceService->departmentType($u);
            $u->configured_targets = $this->closingService->getDailyTargets($u);
            return $u;
        })->filter(function ($u) {
            // Keep only NSD, CSD, and OD users
            return in_array($u->dept_type, ['nsd', 'csd', 'od'], true);
        })->values();

        // Automatically pre-configure missing targets in the database
        foreach ($users as $u) {
            $defaultTargets = [];
            if ($u->dept_type === 'nsd') {
                $defaultTargets = [
                    'global_hours' => 7,
                    'sts_updates' => 45,
                    'dsr_updates' => 2,
                ];
            } elseif ($u->dept_type === 'csd') {
                $defaultTargets = [
                    'global_hours' => 7,
                    'communications' => 15,
                ];
            } elseif ($u->dept_type === 'od') {
                $defaultTargets = [
                    'global_hours' => 7,
                    'hours_logged' => 6,
                    'tasks_completed' => 1,
                ];
            }

            $changed = false;
            foreach ($defaultTargets as $type => $val) {
                $exists = DailyTarget::where('user_id', $u->id)
                    ->where('target_type', $type)
                    ->exists();
                if (!$exists) {
                    DailyTarget::create([
                        'user_id' => $u->id,
                        'target_type' => $type,
                        'target_value' => $val,
                        'created_by' => $user->id,
                    ]);
                    $changed = true;
                }
            }

            if ($changed) {
                $u->configured_targets = $this->closingService->getDailyTargets($u);
            }
        }

        return view('components.targets.configure', compact('users'));
    }

    public function getData(Request $request)
    {
        $user = Auth::user();
        $branchUserIds = $this->branchScope->getBranchUserIds($user);
        $performanceService = new UserPerformanceService();

        // 1. Resolve date range. Default to last 7 days.
        $startDateStr = $request->input('start_date');
        $endDateStr = $request->input('end_date');

        if (!$startDateStr || !$endDateStr) {
            $startDateStr = \Carbon\Carbon::today()->subDays(6)->format('Y-m-d');
            $endDateStr = \Carbon\Carbon::today()->format('Y-m-d');
        }

        $startDate = \Carbon\Carbon::parse($startDateStr);
        $endDate = \Carbon\Carbon::parse($endDateStr);

        // Cap range to 31 days max for performance safety
        if ($startDate->diffInDays($endDate) > 31) {
            $endDate = $startDate->copy()->addDays(31);
        }

        // Generate date list desc (most recent first), excluding Sundays
        $dateList = [];
        for ($d = $endDate->copy(); $d->gte($startDate); $d->subDay()) {
            if ($d->isSunday()) {
                continue;
            }
            $dateList[] = $d->format('Y-m-d');
        }

        // 2. Fetch Employees in Scope
        if ($user->isGlobalAdmin()) {
            $employeesQuery = User::whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments']);
        } else {
            $employeesQuery = User::whereIn('id', $branchUserIds)
                ->whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments']);
        }

        if ($request->filled('employee_id')) {
            $employeesQuery->where('id', $request->employee_id);
        }

        $allEmployees = $employeesQuery->get()->filter(function ($u) use ($performanceService) {
            $u->dept_type = $performanceService->departmentType($u);
            return in_array($u->dept_type, ['nsd', 'csd', 'od'], true);
        })->values();

        // 3. Fetch Day Closings in range to avoid N+1 query
        $employeeIds = $allEmployees->pluck('id')->toArray();
        $dayClosings = \App\Models\DayClosing::whereIn('user_id', $employeeIds)
            ->whereBetween('closing_date', [$startDateStr, $endDateStr])
            ->with('approver')
            ->get()
            ->groupBy(function ($dc) {
                $date = $dc->closing_date instanceof \Carbon\Carbon ? $dc->closing_date->format('Y-m-d') : \Carbon\Carbon::parse($dc->closing_date)->format('Y-m-d');
                return $dc->user_id . '_' . $date;
            });

        // 4. Generate all rows combinations
        $searchValue = $request->input('search.value');
        $allRows = [];
        foreach ($dateList as $dateStr) {
            foreach ($allEmployees as $emp) {
                // If searched, filter matching employee name
                if ($searchValue) {
                    if (stripos($emp->name, $searchValue) === false && stripos($emp->email, $searchValue) === false) {
                        continue;
                    }
                }

                $key = $emp->id . '_' . $dateStr;
                $dc = isset($dayClosings[$key]) ? $dayClosings[$key]->first() : null;

                $allRows[] = [
                    'date' => $dateStr,
                    'employee' => $emp,
                    'day_closing' => $dc
                ];
            }
        }

        // 5. Apply sorting
        $orderColumnIdx = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        usort($allRows, function ($a, $b) use ($orderColumnIdx, $orderDir) {
            if ($orderColumnIdx == 0) { // Date
                $valA = $a['date'];
                $valB = $b['date'];
            } elseif ($orderColumnIdx == 1) { // Employee Name
                $valA = $a['employee']->name;
                $valB = $b['employee']->name;
            } elseif ($orderColumnIdx == 2) { // Department
                $valA = $a['employee']->dept_type;
                $valB = $b['employee']->dept_type;
            } else {
                $valA = $a['date'];
                $valB = $b['date'];
            }

            if ($valA == $valB) return 0;
            if ($orderDir === 'asc') {
                return $valA > $valB ? 1 : -1;
            } else {
                return $valA < $valB ? 1 : -1;
            }
        });

        // 6. Paginate Rows
        $totalRecords = count($allRows);
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 25));
        $paginatedRows = array_slice($allRows, $start, $length);

        // 7. Calculate Summary Metrics (especially when specific user is selected)
        $selectedEmp = null;
        if ($request->filled('employee_id')) {
            $selectedEmp = $allEmployees->firstWhere('id', (int) $request->employee_id);
        }

        $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
        $totalWorkingDays = 0;
        $submittedDaysCount = 0;
        $missedDaysCount = 0;
        $metTargetsCount = 0;
        $missedDates = [];

        if ($selectedEmp) {
            foreach ($dateList as $dStr) {
                $dCarbon = \Carbon\Carbon::parse($dStr);
                if ($dCarbon->gt(\Carbon\Carbon::today())) {
                    continue;
                }

                $key = $selectedEmp->id . '_' . $dStr;
                $dc = isset($dayClosings[$key]) ? $dayClosings[$key]->first() : null;

                $totalWorkingDays++;
                if ($dc) {
                    $submittedDaysCount++;
                    if ($dc->target_status === 'Met') {
                        $metTargetsCount++;
                    }
                } else {
                    if ($dStr < $todayStr) {
                        $missedDaysCount++;
                        $missedDates[] = [
                            'date' => $dStr,
                            'formatted' => $dCarbon->format('d M (D)'),
                        ];
                    }
                }
            }
        }

        // 8. Format Output Data
        $formattedData = [];
        foreach ($paginatedRows as $row) {
            $dateStr = $row['date'];
            $emp = $row['employee'];
            $item = $row['day_closing'];

            $roleName = $emp->getRoleNames()->first() ?? '-';
            $userTargets = $this->closingService->getDailyTargets($emp);

            $deptType = $emp->dept_type;
            $deptUpper = strtoupper($deptType);

            // Fetch actual achieved metrics (dynamically resolved if not submitted)
            if ($item) {
                $metrics = $item->achieved_metrics ?? [];
                $targetStatus = $item->target_status;
                $closingStatus = $item->status;
                $remarks = $item->executive_remarks ?? '-';
            } else {
                // Dynamically load metrics for that date
                $metrics = $this->closingService->getTodayMetrics($emp, $dateStr);
                $remarks = '-';

                if ($dateStr === $todayStr) {
                    $targetStatus = 'Pending';
                    $closingStatus = 'Not Submitted';
                } else {
                    $targetStatus = 'Not Met';
                    $closingStatus = 'Not Submitted';
                }
            }

            // Build Target Parameters presentation with timing format (Calculated values only)
            $paramHTML = '<div class="font-size-12" style="line-height: 1.6;">';
            if ($deptUpper === 'NSD') {
                $actualSts = $metrics['sts'] ?? 0;
                $actualDsr = $metrics['dsr'] ?? 0;

                $paramHTML .= '<div class="d-flex justify-content-between"><span class="text-muted mr-2">STS Updates:</span> <strong>' . $actualSts . '</strong></div>';
                $paramHTML .= '<div class="d-flex justify-content-between"><span class="text-muted mr-2">DSR Updates:</span> <strong>' . $actualDsr . '</strong></div>';
            } elseif ($deptUpper === 'CSD') {
                $actualGlobal = format_timing_hours($metrics['global_hours'] ?? 0);
                $actualComms = $metrics['communications'] ?? 0;

                $paramHTML .= '<div class="d-flex justify-content-between"><span class="text-muted mr-2">Work Hours:</span> <strong>' . $actualGlobal . 'h</strong></div>';
                $paramHTML .= '<div class="d-flex justify-content-between"><span class="text-muted mr-2">Communications:</span> <strong>' . $actualComms . '</strong></div>';
            } elseif ($deptUpper === 'OD') {
                $actualGlobal = format_timing_hours($metrics['global_hours'] ?? 0);
                $actualHours = format_timing_hours($metrics['hours'] ?? 0);
                $actualTasks = $metrics['tasks'] ?? 0;

                $paramHTML .= '<div class="d-flex justify-content-between"><span class="text-muted mr-2">Work Hours:</span> <strong>' . $actualGlobal . 'h</strong></div>';
                $paramHTML .= '<div class="d-flex justify-content-between"><span class="text-muted mr-2">Task Hours:</span> <strong>' . $actualHours . 'h</strong></div>';
                $paramHTML .= '<div class="d-flex justify-content-between"><span class="text-muted mr-2">Tasks Done:</span> <strong>' . $actualTasks . '</strong></div>';
            }
            $paramHTML .= '</div>';

            // Target Status Badge
            $targetStatusBadge = '';
            if ($targetStatus === 'Met') {
                $targetStatusBadge = '<span class="badge badge-soft-success px-2.5 py-1 font-size-11 font-weight-bold"><i class="mdi mdi-check-circle mr-0.5"></i> Met</span>';
            } elseif ($targetStatus === 'Not Met') {
                $targetStatusBadge = '<span class="badge badge-soft-danger px-2.5 py-1 font-size-11 font-weight-bold"><i class="mdi mdi-close-circle mr-0.5"></i> Not Met</span>';
            } else {
                $targetStatusBadge = '<span class="badge badge-soft-warning px-2.5 py-1 font-size-11 font-weight-medium">' . htmlspecialchars($targetStatus) . '</span>';
            }

            // Approval Status Badge
            $approvalBadge = '';
            if ($closingStatus === 'Approved') {
                $approverName = $item?->approver?->name;
                $approvalBadge = '<span class="badge badge-soft-success px-2.5 py-1 font-size-11 font-weight-medium"><i class="mdi mdi-check-decagram mr-0.5"></i> Approved</span>';
                if ($approverName) {
                    $approvalBadge .= '<br><small class="text-muted font-size-11 mt-0.5 d-inline-block"><i class="mdi mdi-account-check mr-0.5 text-success"></i> by ' . htmlspecialchars($approverName) . '</small>';
                }
            } elseif ($closingStatus === 'Pending') {
                $approvalBadge = '<span class="badge badge-soft-warning px-2.5 py-1 font-size-11 font-weight-medium"><i class="mdi mdi-timer-sand mr-0.5"></i> Awaiting Approval</span>';
            } elseif ($closingStatus === 'Not Submitted') {
                if ($dateStr === $todayStr) {
                    $approvalBadge = '<span class="badge badge-soft-info px-2.5 py-1 font-size-11 font-weight-medium"><i class="mdi mdi-clock-outline mr-0.5"></i> Pending Today</span>';
                } else {
                    $approvalBadge = '<span class="badge badge-soft-danger px-2.5 py-1 font-size-11 font-weight-bold" style="background-color: #fee2e2; color: #dc2626;"><i class="mdi mdi-alert-circle mr-0.5"></i> Submission Missed</span>';
                }
            } else {
                $approvalBadge = '<span class="badge badge-soft-secondary px-2.5 py-1 font-size-11">' . htmlspecialchars($closingStatus) . '</span>';
            }

            $dateCarbon = \Carbon\Carbon::parse($dateStr);
            $dateFormatted = '<strong>' . $dateCarbon->format('d-M-Y') . '</strong><br><small class="text-muted font-size-11">' . $dateCarbon->format('l') . '</small>';

            $formattedData[] = [
                'date' => $dateFormatted,
                'employee' => '<strong>' . htmlspecialchars($emp->name) . '</strong><br><small class="text-muted">#EMP-' . ($emp->id + 1000) . '</small>',
                'department' => '<span class="badge badge-dept text-uppercase badge-' . strtolower($deptType) . '">' . htmlspecialchars($deptType) . '</span><br><small class="text-muted">' . htmlspecialchars($roleName) . '</small>',
                'parameters' => $paramHTML,
                'target_status' => $targetStatusBadge,
                'status' => $approvalBadge,
                'remarks' => htmlspecialchars($remarks)
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $formattedData,
            'summary' => [
                'has_employee' => !is_null($selectedEmp),
                'employee_name' => $selectedEmp?->name,
                'department' => strtoupper($selectedEmp?->dept_type ?? ''),
                'total_working_days' => $totalWorkingDays,
                'submitted_days_count' => $submittedDaysCount,
                'missed_days_count' => $missedDaysCount,
                'missed_dates' => $missedDates,
                'met_targets_count' => $metTargetsCount,
                'submission_rate' => $totalWorkingDays > 0 ? round(($submittedDaysCount / $totalWorkingDays) * 100) : 0,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'targets' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()->all()], 400);
        }

        $targetUserId = $request->input('user_id');
        $targetUser = User::findOrFail($targetUserId);

        // Verify target user belongs to same branch as manager (unless global admin)
        if (!$user->isGlobalAdmin()) {
            $managerBranch = $this->branchScope->resolveBranchId($user);
            $userBranch = $this->branchScope->resolveBranchId($targetUser);
            if ($managerBranch !== $userBranch) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. User belongs to a different branch.'], 403);
            }
        }

        try {
            foreach ($request->input('targets') as $type => $value) {
                if (is_null($value) || $value === '') {
                    continue;
                }

                if ($type === 'global_hours' && (int) $value < 7) {
                    return response()->json(['success' => false, 'message' => 'Daily work hours target must be at least 7 hours.'], 400);
                }

                DailyTarget::updateOrCreate(
                    [
                        'user_id' => $targetUserId,
                        'target_type' => $type,
                    ],
                    [
                        'target_value' => (int) $value,
                        'created_by' => $user->id,
                    ]
                );
            }

            return response()->json(['success' => true, 'message' => 'Daily targets updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        if (!$user->isGlobalAdmin() && !$user->hasRole('Branch-Manager')) {
            abort(403, 'Unauthorized action.');
        }

        $employeeId = $request->input('employee_id');
        $startDateStr = $request->input('start_date');
        $endDateStr = $request->input('end_date');

        return Excel::download(
            new DailyTargetsExport($employeeId, $startDateStr, $endDateStr, $user),
            Carbon::today()->toDateString() . '_daily_targets.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
