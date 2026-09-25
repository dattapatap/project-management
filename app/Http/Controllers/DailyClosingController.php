<?php

namespace App\Http\Controllers;

use App\Models\DayClosing;
use App\Models\User;
use App\Services\BranchScopeService;
use App\Services\DailyClosingService;
use App\Services\UserPerformanceService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class DailyClosingController extends Controller
{
    public function __construct(
        private DailyClosingService $closingService,
        private BranchScopeService $branchScope
    ) {}

    /**
     * Show the day closing dashboard for the authenticated executive.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole(['Admin', 'Branch-Manager'])) {
            return redirect()->route('day-closing.approvals');
        }

        $todayDate = Carbon::today()->format('Y-m-d');
        $performanceService = new UserPerformanceService();
        $deptType = $performanceService->departmentType($user);

        // Prefilled metrics
        $metrics = $this->closingService->getTodayMetrics($user, $todayDate);
        $targets = $this->closingService->getDailyTargets($user);
        $targetStatus = $this->closingService->resolveTargetStatus($user, $metrics);

        // Check if already submitted today
        $todaySubmission = DayClosing::where('user_id', $user->id)
            ->where('closing_date', $todayDate)
            ->first();

        // Submissions history
        $history = DayClosing::where('user_id', $user->id)
            ->with('approver')
            ->orderBy('closing_date', 'desc')
            ->limit(15)
            ->get();

        // Fetch today's activity log summary for quick copy helper
        $todayActivities = [];
        if ($deptType === 'OD') {
            $todayActivities = \App\Models\Task::where('assigned_to', $user->id)
                ->where('status', 'Completed')
                ->whereDate('updated_at', Carbon::today())
                ->pluck('title')
                ->toArray();
            if (empty($todayActivities)) {
                $todayActivities = \App\Models\Task::whereHas('logs', function ($q) use ($user) {
                    $q->where('userid', $user->id)->whereDate('log_date', Carbon::today());
                })->pluck('title')->toArray();
            }
        } elseif ($deptType === 'CSD') {
            $todayActivities = \App\Models\CsdCommunication::where('created_by', $user->id)
                ->whereDate('communication_date', Carbon::today())
                ->pluck('subject')
                ->toArray();
        } elseif ($deptType === 'NSD') {
            $todayActivities = \Illuminate\Support\Facades\DB::table('client_histories')
                ->join('clients', 'client_histories.client', '=', 'clients.id')
                ->where('client_histories.created', $user->id)
                ->whereDate('client_histories.created_at', Carbon::today())
                ->select(DB::raw("CONCAT(clients.name, ' (', client_histories.status, '): ', client_histories.remarks) as activity_detail"))
                ->pluck("activity_detail")
                ->toArray();
        }

        return view('components.day-closing.index', compact(
            'deptType',
            'metrics',
            'targets',
            'targetStatus',
            'todaySubmission',
            'history',
            'todayActivities'
        ));
    }

    public function submit(Request $request)
    {
        $user = Auth::user();
        $onLeave = $request->has('on_leave');

        if (!$onLeave) {
            $remarks = trim($request->input('remarks', ''));
            // Split by whitespace to count words
            $words = array_filter(preg_split('/\s+/', $remarks));
            $count = count($words);

            if ($count < 10 || $count > 80) {
                return redirect()->route('day-closing.index')->with('error', 'Executive Remarks must be between 10 and 80 words. Current count: ' . $count . ' words.');
            }
        }

        try {
            $submission = $this->closingService->submitClosing($user, $request->input('remarks'), $onLeave);
            return redirect()->route('day-closing.index')->with('success', 'Day closing submitted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('day-closing.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Parse date or date-range input.
     * Admin/BM can use date ranges capped at today.
     * Team Leaders can only view a single date (capped at today, min 2 days back).
     */
    private function parseDateRange(Request $request, bool $isAdminOrBranchManager, ?string $minDate): array
    {
        $today = Carbon::today();

        if ($isAdminOrBranchManager) {
            $dateInput = $request->input('date') ?? $request->input('date_range');

            if (!empty($dateInput)) {
                if (str_contains($dateInput, ' to ')) {
                    $parts = explode(' to ', $dateInput);
                    $startDate = Carbon::parse(trim($parts[0]))->startOfDay();
                    $endDate = Carbon::parse(trim($parts[1]))->endOfDay();
                } elseif (str_contains($dateInput, ' - ')) {
                    $parts = explode(' - ', $dateInput);
                    $startDate = Carbon::parse(trim($parts[0]))->startOfDay();
                    $endDate = Carbon::parse(trim($parts[1]))->endOfDay();
                } else {
                    $single = Carbon::parse($dateInput);
                    $startDate = $single->copy()->startOfDay();
                    $endDate = $single->copy()->endOfDay();
                }
            } else {
                $startDate = $today->copy()->startOfDay();
                $endDate = $today->copy()->endOfDay();
            }

            // Cap at today (no future dates)
            if ($endDate->gt($today->copy()->endOfDay())) {
                $endDate = $today->copy()->endOfDay();
            }
            if ($startDate->gt($endDate)) {
                $startDate = $endDate->copy()->startOfDay();
            }

            // Limit range to max 60 days for peak performance
            if ($startDate->diffInDays($endDate) > 60) {
                $startDate = $endDate->copy()->subDays(60)->startOfDay();
            }
        } else {
            // Team Leader: Single date only, up to 2 days back, max today
            $dateInput = $request->input('date', $today->format('Y-m-d'));
            $single = Carbon::parse($dateInput);
            if ($single->gt($today)) {
                $single = $today->copy();
            }
            if ($minDate && $single->format('Y-m-d') < $minDate) {
                $single = Carbon::parse($minDate);
            }
            $startDate = $single->copy()->startOfDay();
            $endDate = $single->copy()->endOfDay();
        }

        $isSingleDay = $startDate->isSameDay($endDate);
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        $selectedDateQuery = $isSingleDay ? $startDateStr : "{$startDateStr} - {$endDateStr}";
        $selectedDateDisplay = $isSingleDay ? $startDate->format('d-M-Y') : $startDate->format('d-M-Y') . ' to ' . $endDate->format('d-M-Y');

        return [$startDate, $endDate, $startDateStr, $endDateStr, $selectedDateQuery, $selectedDateDisplay, $isSingleDay];
    }

    /**
     * Show approvals board for TLs, Managers, and Admins.
     */
    public function approvals(Request $request)
    {
        $user = Auth::user();
        $isAdminOrBranchManager = $user->isGlobalAdmin() || $user->isBranchManager();
        $isTeamLeaderOnly = $user->hasRole('Team-Leader') && !$isAdminOrBranchManager;

        $minDate = null;
        if ($isTeamLeaderOnly) {
            // Team Leaders can only go up to 2 days back (Today, Yesterday, and Day Before Yesterday)
            $minDate = Carbon::today()->subDays(2)->format('Y-m-d');
        }

        [$startDate, $endDate, $startDateStr, $endDateStr, $selectedDateQuery, $selectedDateDisplay, $isSingleDay] = 
            $this->parseDateRange($request, $isAdminOrBranchManager, $minDate);

        // Resolve which users this acting user can approve
        $allSubordinateIds = [];

        if ($user->isGlobalAdmin()) {
            // Global Admin can see all users (excluding Admin & Client)
            $allSubordinateIds = User::whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
                ->pluck('id')
                ->toArray();
        } elseif ($user->isBranchManager()) {
            // Branch manager can see all users in their branch
            $branchUserIds = $this->branchScope->getBranchUserIds($user);
            $allSubordinateIds = User::whereIn('id', $branchUserIds)
                ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
                ->pluck('id')
                ->toArray();
        } elseif ($user->hasRole('Team-Leader')) {
            // Team leader can see their team members
            $teams = DB::table('team_members')
                ->where('user', $user->id)
                ->where('status', true)
                ->pluck('team')
                ->toArray();

            $allSubordinateIds = DB::table('team_members')
                ->whereIn('team', $teams)
                ->where('status', true)
                ->where('user', '!=', $user->id)
                ->pluck('user')
                ->toArray();

            // Exclude other Team Leaders from subordinate list for Team Leader role
            $allSubordinateIds = User::whereIn('id', $allSubordinateIds)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'Team-Leader');
                })
                ->pluck('id')
                ->toArray();
        } else {
            abort(403, 'Unauthorized action.');
        }

        $performanceService = new UserPerformanceService();

        // Fetch all active subordinates in scope (except current user)
        $allSubordinates = User::whereIn('id', $allSubordinateIds)
            ->whereIn('status', User::WORKING_STATUSES)
            ->where('id', '!=', $user->id)
            ->with(['roles', 'departments.dept'])
            ->orderBy('name', 'asc')
            ->get();

        // Employee filter: ONLY for Admin and Branch Manager
        $selectedEmployeeId = $request->input('employee_id');
        if (!$isAdminOrBranchManager) {
            $selectedEmployeeId = null;
        }

        if (!empty($selectedEmployeeId) && in_array((int)$selectedEmployeeId, $allSubordinateIds)) {
            $subordinateIds = [(int)$selectedEmployeeId];
            $subordinates = $allSubordinates->where('id', (int)$selectedEmployeeId);
        } else {
            $selectedEmployeeId = '';
            $subordinateIds = $allSubordinateIds;
            $subordinates = $allSubordinates;
        }

        foreach ($subordinates as $sub) {
            $sub->dept_type = $performanceService->departmentType($sub);
            $sub->userTargets = $this->closingService->getDailyTargets($sub);
        }

        // Fetch day closing submissions for the selected date or date range
        $submissions = DayClosing::whereIn('user_id', $subordinateIds)
            ->whereBetween('closing_date', [$startDateStr, $endDateStr])
            ->with(['user.departments.dept', 'user.roles', 'approver'])
            ->orderBy('closing_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 1. Submitted List: ONLY employees who have submitted day closing in this range
        $submittedList = $submissions->map(function ($submission) use ($performanceService) {
            $subUser = $submission->user;
            if (!$subUser) {
                return null;
            }
            $item = clone $subUser;
            $item->dept_type = $performanceService->departmentType($subUser);
            $item->submission = $submission;
            return $item;
        })->filter()->values();

        // 2. Pending Submissions List: Active employees who have NOT submitted day closing
        if ($isSingleDay) {
            $submittedUserIdsOnDate = $submissions->where('closing_date', $startDateStr)->pluck('user_id')->toArray();
            $notSubmittedList = $subordinates->filter(function ($sub) use ($submittedUserIdsOnDate) {
                return !in_array($sub->id, $submittedUserIdsOnDate);
            })->map(function ($sub) use ($startDateStr) {
                $subCopy = clone $sub;
                $subCopy->closing_date = $startDateStr;
                $subCopy->currentMetrics = $this->closingService->getTodayMetrics($sub, $startDateStr);
                return $subCopy;
            })->values();
        } else {
            // Group submissions by user_id and date for fast O(1) lookup
            $submissionsByKey = $submissions->groupBy(fn($s) => $s->user_id . '_' . Carbon::parse($s->closing_date)->format('Y-m-d'));

            // Query approved leaves in date range to avoid false pending alerts
            $approvedLeaves = \App\Models\EmployeeLeave::whereIn('user_id', $subordinateIds)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $endDateStr)
                ->whereDate('end_date', '>=', $startDateStr)
                ->get();

            $dateList = [];
            for ($d = $endDate->copy(); $d->gte($startDate); $d->subDay()) {
                if ($d->isSunday()) continue;
                $dateList[] = $d->format('Y-m-d');
            }

            $notSubmittedList = collect();
            foreach ($dateList as $dStr) {
                foreach ($subordinates as $sub) {
                    $k = $sub->id . '_' . $dStr;
                    if ($submissionsByKey->has($k)) {
                        continue;
                    }

                    // Check if employee was on approved leave on this date
                    $isOnLeave = $approvedLeaves->contains(function ($leave) use ($sub, $dStr) {
                        $lStart = Carbon::parse($leave->start_date)->format('Y-m-d');
                        $lEnd = Carbon::parse($leave->end_date)->format('Y-m-d');
                        return (int)$leave->user_id === (int)$sub->id && $lStart <= $dStr && $lEnd >= $dStr;
                    });

                    if ($isOnLeave) {
                        continue;
                    }

                    $subCopy = clone $sub;
                    $subCopy->closing_date = $dStr;
                    $subCopy->currentMetrics = $this->closingService->getTodayMetrics($sub, $dStr);
                    $notSubmittedList->push($subCopy);
                }
            }
        }

        $selectedDate = $startDateStr;

        return view('components.day-closing.approvals', compact(
            'submittedList',
            'notSubmittedList',
            'subordinates',
            'allSubordinates',
            'selectedDateQuery',
            'selectedDateDisplay',
            'selectedDate',
            'startDateStr',
            'endDateStr',
            'isSingleDay',
            'minDate',
            'isTeamLeaderOnly',
            'isAdminOrBranchManager',
            'selectedEmployeeId'
        ));
    }

    /**
     * Approve submission.
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $submission = DayClosing::findOrFail($id);

        if ($user->hasRole('Team-Leader') && !$user->hasRole(['Admin', 'Branch-Manager'])) {
            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $isMember = DB::table('team_members')->whereIn('team', $teams)->where('status', true)->where('user', $submission->user_id)->exists();
            if (!$isMember) {
                return redirect()->route('day-closing.approvals')->with('error', 'Unauthorized. You can only approve day closings for members of your own team.');
            }

            // Restrict Team Leader to 2 days back only
            $closingDateStr = $submission->closing_date instanceof Carbon ? $submission->closing_date->format('Y-m-d') : Carbon::parse($submission->closing_date)->format('Y-m-d');
            $twoDaysBack = Carbon::today()->subDays(2)->format('Y-m-d');
            if ($closingDateStr < $twoDaysBack) {
                return redirect()->route('day-closing.approvals', ['date' => $request->input('date')])
                    ->with('error', 'Unauthorized. Team Leaders are only permitted to approve day closings up to 2 days back (Yesterday and Day Before Yesterday).');
            }
        }

        if ($submission->user->hasRole('Team-Leader')) {
            if (!$user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager'])) {
                return redirect()->route('day-closing.approvals')->with('error', 'Only Project Managers and Administrators can approve or reject a Team Leader\'s day closing.');
            }
        }

        $submission->update([
            'status' => 'Approved',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
            'tl_remarks' => $request->input('remarks'),
        ]);

        $redirectParams = [];
        if ($request->filled('date')) {
            $redirectParams['date'] = $request->input('date');
        }
        if ($request->filled('employee_id')) {
            $redirectParams['employee_id'] = $request->input('employee_id');
        }

        return redirect()->route('day-closing.approvals', $redirectParams)->with('success', 'Day closing approved successfully!');
    }

    /**
     * Reject submission.
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $submission = DayClosing::findOrFail($id);

        if ($user->hasRole('Team-Leader') && !$user->hasRole(['Admin', 'Branch-Manager'])) {
            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $isMember = DB::table('team_members')->whereIn('team', $teams)->where('status', true)->where('user', $submission->user_id)->exists();
            if (!$isMember) {
                return redirect()->route('day-closing.approvals')->with('error', 'Unauthorized. You can only reject day closings for members of your own team.');
            }

            // Restrict Team Leader to 2 days back only
            $closingDateStr = $submission->closing_date instanceof Carbon ? $submission->closing_date->format('Y-m-d') : Carbon::parse($submission->closing_date)->format('Y-m-d');
            $twoDaysBack = Carbon::today()->subDays(2)->format('Y-m-d');
            if ($closingDateStr < $twoDaysBack) {
                return redirect()->route('day-closing.approvals', ['date' => $request->input('date')])
                    ->with('error', 'Unauthorized. Team Leaders are only permitted to reject day closings up to 2 days back (Yesterday and Day Before Yesterday).');
            }
        }

        if ($submission->user->hasRole('Team-Leader')) {
            if (!$user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager'])) {
                return redirect()->route('day-closing.approvals')->with('error', 'Only Project Managers and Administrators can approve or reject a Team Leader\'s day closing.');
            }
        }

        $submission->update([
            'status' => 'Rejected',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
            'tl_remarks' => $request->input('remarks'),
        ]);

        $redirectParams = [];
        if ($request->filled('date')) {
            $redirectParams['date'] = $request->input('date');
        }
        if ($request->filled('employee_id')) {
            $redirectParams['employee_id'] = $request->input('employee_id');
        }

        return redirect()->route('day-closing.approvals', $redirectParams)->with('success', 'Day closing rejected successfully.');
    }

    /**
     * Submit leave on behalf of employee.
     */
    public function submitLeaveOnBehalf(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|integer|exists:users,id',
            'leave_date' => 'required|date',
            'remarks' => 'required|string',
        ]);

        $targetUserIds = $request->input('user_ids');
        $leaveDate = $request->input('leave_date');
        $remarks = $request->input('remarks');

        // Resolve which users this acting user can manage
        $subordinateIds = [];

        if ($user->isGlobalAdmin()) {
            $subordinateIds = User::pluck('id')->toArray();
        } elseif ($user->isBranchManager()) {
            $subordinateIds = $this->branchScope->getBranchUserIds($user);
        } elseif ($user->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')
                ->where('user', $user->id)
                ->where('status', true)
                ->pluck('team')
                ->toArray();

            $subordinateIds = DB::table('team_members')
                ->whereIn('team', $teams)
                ->where('status', true)
                ->where('user', '!=', $user->id)
                ->pluck('user')
                ->toArray();
        }

        $successNames = [];
        $errorNames = [];

        foreach ($targetUserIds as $targetUserId) {
            $targetUser = User::find($targetUserId);
            if (!$targetUser) continue;

            if (!in_array((int)$targetUserId, $subordinateIds, true)) {
                $errorNames[] = $targetUser->name . " (Unauthorized)";
                continue;
            }

            // Check if day closing already exists for this date
            $existing = DayClosing::where('user_id', $targetUserId)
                ->where('closing_date', $leaveDate)
                ->exists();

            if ($existing) {
                $errorNames[] = $targetUser->name . " (Record already exists)";
                continue;
            }

            $performanceService = new UserPerformanceService();
            $deptType = strtoupper($performanceService->departmentType($targetUser));

            // Create approved leave record
            DayClosing::create([
                'user_id' => $targetUserId,
                'closing_date' => $leaveDate,
                'department' => $deptType,
                'achieved_metrics' => ($deptType === 'NSD') ? ['sts' => 0, 'dsr' => 0] : (($deptType === 'CSD') ? ['communications' => 0] : ['hours' => 0.0, 'tasks' => 0]),
                'target_status' => 'On Leave',
                'executive_remarks' => 'Leave submitted by ' . $user->name,
                'status' => 'Approved',
                'approved_by' => $user->id,
                'approved_at' => Carbon::now(),
                'tl_remarks' => $remarks,
            ]);

            $successNames[] = $targetUser->name;
        }

        $message = '';
        if (!empty($successNames)) {
            $message .= 'Leave successfully recorded for: ' . implode(', ', $successNames) . '. ';
        }
        $redirectParams = [];
        if ($request->filled('filter_date')) {
            $redirectParams['date'] = $request->input('filter_date');
        } elseif ($request->filled('date')) {
            $redirectParams['date'] = $request->input('date');
        } else {
            $redirectParams['date'] = $leaveDate;
        }
        if ($request->filled('filter_employee_id')) {
            $redirectParams['employee_id'] = $request->input('filter_employee_id');
        } elseif ($request->filled('employee_id')) {
            $redirectParams['employee_id'] = $request->input('employee_id');
        }

        if (!empty($errorNames)) {
            return redirect()->route('day-closing.approvals', $redirectParams)
                ->with('success', $message)
                ->with('error', 'Failed for: ' . implode(', ', $errorNames));
        }

        return redirect()->route('day-closing.approvals', $redirectParams)->with('success', $message);
    }
}
