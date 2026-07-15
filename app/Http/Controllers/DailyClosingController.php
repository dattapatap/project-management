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

            if ($count < 3 || $count > 50) {
                return redirect()->route('day-closing.index')->with('error', 'Executive Remarks must be between 3 and 50 words. Current count: ' . $count . ' words.');
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
     * Show approvals board for TLs, Managers, and Admins.
     */
    public function approvals(Request $request)
    {
        $user = Auth::user();

        // Retrieve filtered date, default to today, and avoid future dates
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        if (Carbon::parse($selectedDate)->gt(Carbon::today())) {
            $selectedDate = Carbon::today()->format('Y-m-d');
        }

        // Resolve which users this acting user can approve
        $subordinateIds = [];

        if ($user->isGlobalAdmin()) {
            // Global Admin can see all users
            $subordinateIds = User::pluck('id')->toArray();
        } elseif ($user->isBranchManager()) {
            // Branch manager can see all users in their branch
            $subordinateIds = $this->branchScope->getBranchUserIds($user);
        } elseif ($user->hasRole('Team-Leader')) {
            // Team leader can see their team members
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

            // Exclude other Team Leaders from subordinate list for Team Leader role
            $subordinateIds = User::whereIn('id', $subordinateIds)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'Team-Leader');
                })
                ->pluck('id')
                ->toArray();
        } else {
            abort(403, 'Unauthorized action.');
        }

        // Fetch all active subordinates in scope (except current user)
        $subordinates = User::whereIn('id', $subordinateIds)
            ->where('status', 'Active')
            ->where('id', '!=', $user->id)
            ->orderBy('name', 'asc')
            ->get();

        // Fetch day closing submissions for the selected date
        $submissionsOnDate = DayClosing::whereIn('user_id', $subordinateIds)
            ->where('closing_date', $selectedDate)
            ->get()
            ->keyBy('user_id');

        // Build a list of all subordinates with their submission status on the selected date
        $auditList = $subordinates->map(function ($sub) use ($submissionsOnDate) {
            $sub->submission = $submissionsOnDate->get($sub->id);
            return $sub;
        });

        // Fetch pending day closing submissions for the selected date only
        $pending = DayClosing::whereIn('user_id', $subordinateIds)
            ->where('status', 'Pending')
            ->where('closing_date', $selectedDate)
            ->with('user')
            ->orderBy('closing_date', 'asc')
            ->get();

        return view('components.day-closing.approvals', compact(
            'pending',
            'subordinates',
            'selectedDate',
            'auditList'
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

        $date = $request->input('date');
        return redirect()->route('day-closing.approvals', ['date' => $date])->with('success', 'Day closing approved successfully!');
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

        $date = $request->input('date');
        return redirect()->route('day-closing.approvals', ['date' => $date])->with('success', 'Day closing rejected successfully.');
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
        if (!empty($errorNames)) {
            return redirect()->route('day-closing.approvals', ['date' => $leaveDate])
                ->with('success', $message)
                ->with('error', 'Failed for: ' . implode(', ', $errorNames));
        }

        return redirect()->route('day-closing.approvals', ['date' => $leaveDate])->with('success', $message);
    }
}
