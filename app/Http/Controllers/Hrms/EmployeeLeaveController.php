<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLeave;
use App\Services\Hrms\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Display Employee's own leaves and leave balances.
     */
    public function index()
    {
        $user = Auth::user();
        $leaves = $this->leaveService->getUserLeaves($user->id);
        $balances = $this->leaveService->getUserLeaveBalances($user->id);
        $leaveTypes = $this->leaveService->getActiveLeaveTypes();

        return view('components.hrms.leaves.index', compact('leaves', 'balances', 'leaveTypes'));
    }

    /**
     * Submit a new leave application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|in:first_half,second_half',
            'reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $this->leaveService->applyLeave($user, $validated);

        return redirect()->route('hrms.my-leaves.index')->with('success', 'Leave application submitted successfully. Pending approval.');
    }

    /**
     * Admin & Branch Manager: Review and approve/reject employee leaves.
     */
    public function approvals(Request $request)
    {
        $user = Auth::user();
        if (!$user->isGlobalAdmin() && !$user->isBranchManager()) {
            abort(403, 'Unauthorized access to leave approvals.');
        }

        $statusFilter = $request->input('status', 'pending');
        $query = EmployeeLeave::with(['user.departments.dept', 'leaveType', 'approver'])
            ->orderBy('id', 'desc');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $leaves = $query->paginate(20);

        return view('components.hrms.leaves.approvals', compact('leaves', 'statusFilter'));
    }

    /**
     * Approve or reject a leave request.
     */
    public function updateStatus(Request $request, EmployeeLeave $leave)
    {
        $user = Auth::user();
        if (!$user->isGlobalAdmin() && !$user->isBranchManager()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string|max:1000',
        ]);

        $this->leaveService->updateLeaveStatus($leave, $validated['status'], $validated['admin_remarks'] ?? null, $user);

        return redirect()->back()->with('success', 'Leave application has been ' . $validated['status'] . ' successfully.');
    }
}
