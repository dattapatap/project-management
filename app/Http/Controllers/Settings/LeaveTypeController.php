<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Services\Hrms\LeaveService;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index()
    {
        $leaveTypes = $this->leaveService->getActiveLeaveTypes();
        return view('components.settings.leave_types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:leave_types,code',
            'days_allowed_per_year' => 'required|integer|min:0|max:365',
            'monthly_limit' => 'nullable|numeric|min:0|max:31',
            'is_monthly_accrual' => 'nullable|boolean',
            'is_paid' => 'required|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['is_monthly_accrual'] = !empty($validated['is_monthly_accrual']);

        LeaveType::create($validated);

        return redirect()->route('settings.leave-types.index')->with('success', 'Leave Type created successfully.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:leave_types,code,' . $leaveType->id,
            'days_allowed_per_year' => 'required|integer|min:0|max:365',
            'monthly_limit' => 'nullable|numeric|min:0|max:31',
            'is_monthly_accrual' => 'nullable|boolean',
            'is_paid' => 'required|boolean',
            'status' => 'required|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['is_monthly_accrual'] = !empty($validated['is_monthly_accrual']);

        $leaveType->update($validated);

        return redirect()->route('settings.leave-types.index')->with('success', 'Leave Type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaves()->exists()) {
            return redirect()->route('settings.leave-types.index')->with('error', 'Cannot delete Leave Type because employee applications are associated with it.');
        }

        $leaveType->delete();

        return redirect()->route('settings.leave-types.index')->with('success', 'Leave Type deleted successfully.');
    }
}
