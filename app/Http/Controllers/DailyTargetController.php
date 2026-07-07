<?php

namespace App\Http\Controllers;

use App\Models\DailyTarget;
use App\Models\User;
use App\Services\BranchScopeService;
use App\Services\DailyClosingService;
use App\Services\UserPerformanceService;
use Auth;
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
            $rawQuery = User::where('status', 'Active')
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments']);
        } else {
            $branchUserIds = $this->branchScope->getBranchUserIds($user);
            $rawQuery = User::whereIn('id', $branchUserIds)
                ->where('status', 'Active')
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
            $rawUsers = User::where('status', 'Active')
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments'])
                ->get();
        } else {
            $branchUserIds = $this->branchScope->getBranchUserIds($user);
            $rawUsers = User::whereIn('id', $branchUserIds)
                ->where('status', 'Active')
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

        // Generate date list desc (most recent first)
        $dateList = [];
        for ($d = $endDate->copy(); $d->gte($startDate); $d->subDay()) {
            $dateList[] = $d->format('Y-m-d');
        }

        // 2. Fetch Employees in Scope
        if ($user->isGlobalAdmin()) {
            $employeesQuery = User::where('status', 'Active')
                ->where('id', '!=', $user->id)
                ->with(['roles', 'departments']);
        } else {
            $employeesQuery = User::whereIn('id', $branchUserIds)
                ->where('status', 'Active')
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
            ->get()
            ->groupBy(function ($dc) {
                return $dc->user_id . '_' . $dc->closing_date;
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

        // 7. Format Output Data
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

                if ($dateStr === \Carbon\Carbon::today()->format('Y-m-d')) {
                    $targetStatus = 'Pending';
                    $closingStatus = 'Not Submitted';
                } else {
                    $targetStatus = 'Not Met';
                    $closingStatus = 'Not Submitted';
                }
            }

            // Build Target Parameters presentation
            $paramHTML = '<div class="font-size-12" style="line-height: 1.6;">';
            if ($deptUpper === 'NSD') {
                $actualSts = $metrics['sts'] ?? 0;
                $targetSts = $userTargets['sts_updates'] ?? 45;
                $actualDsr = $metrics['dsr'] ?? 0;
                $targetDsr = $userTargets['dsr_updates'] ?? 2;

                $paramHTML .= '<div>STS: <strong>' . $actualSts . '</strong> / ' . $targetSts . '</div>';
                $paramHTML .= '<div>DSR: <strong>' . $actualDsr . '</strong> / ' . $targetDsr . '</div>';
            } elseif ($deptUpper === 'CSD') {
                $actualGlobal = $metrics['global_hours'] ?? 0;
                $targetGlobal = $userTargets['global_hours'] ?? 7;
                $actualComms = $metrics['communications'] ?? 0;
                $targetComms = $userTargets['communications'] ?? 15;

                $paramHTML .= '<div>Work Hours: <strong>' . $actualGlobal . 'h</strong> / ' . $targetGlobal . 'h</div>';
                $paramHTML .= '<div>Comms: <strong>' . $actualComms . '</strong> / ' . $targetComms . '</div>';
            } elseif ($deptUpper === 'OD') {
                $actualGlobal = $metrics['global_hours'] ?? 0;
                $targetGlobal = $userTargets['global_hours'] ?? 7;
                $actualHours = $metrics['hours'] ?? 0;
                $targetHours = $userTargets['hours_logged'] ?? 6;
                $actualTasks = $metrics['tasks'] ?? 0;
                $targetTasks = $userTargets['tasks_completed'] ?? 1;

                $paramHTML .= '<div>Work Hours: <strong>' . $actualGlobal . 'h</strong> / ' . $targetGlobal . 'h</div>';
                $paramHTML .= '<div>Task Hours: <strong>' . $actualHours . 'h</strong> / ' . $targetHours . 'h</div>';
                $paramHTML .= '<div>Tasks: <strong>' . $actualTasks . '</strong> / ' . $targetTasks . '</div>';
            }
            $paramHTML .= '</div>';

            // Target Status Badge
            $targetStatusBadge = '';
            if ($targetStatus === 'Met') {
                $targetStatusBadge = '<span class="badge badge-soft-success px-2.5 py-1">Met</span>';
            } elseif ($targetStatus === 'Not Met') {
                $targetStatusBadge = '<span class="badge badge-soft-danger px-2.5 py-1">Not Met</span>';
            } else {
                $targetStatusBadge = '<span class="badge badge-soft-warning px-2.5 py-1">' . htmlspecialchars($targetStatus) . '</span>';
            }

            // Approval Status Badge
            $approvalBadge = '';
            if ($closingStatus === 'Approved') {
                $approvalBadge = '<span class="badge badge-success px-2 py-0.5">Approved</span>';
            } elseif ($closingStatus === 'Pending') {
                $approvalBadge = '<span class="badge badge-warning px-2 py-0.5">Pending</span>';
            } elseif ($closingStatus === 'Not Submitted') {
                $approvalBadge = '<span class="badge badge-soft-secondary px-2 py-0.5" style="background-color: rgba(108, 117, 125, 0.15); color: #6c757d;">Not Submitted</span>';
            } else {
                $approvalBadge = '<span class="badge badge-danger px-2 py-0.5">' . htmlspecialchars($closingStatus) . '</span>';
            }

            $formattedData[] = [
                'date' => \Carbon\Carbon::parse($dateStr)->format('d-M-Y'),
                'employee' => '<strong>' . htmlspecialchars($emp->name) . '</strong>',
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
}
