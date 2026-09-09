<?php

namespace App\Services\Hrms;

use App\Models\EmployeeLeave;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * Seed or sync initial default leave types.
     */
    public function ensureDefaultLeaveTypes(): void
    {
        if (LeaveType::count() === 0) {
            LeaveType::create([
                'name' => 'Earned Leave (EL)',
                'code' => 'EL',
                'days_allowed_per_year' => 24,
                'monthly_limit' => 2.0,
                'is_monthly_accrual' => true,
                'is_paid' => true,
                'status' => true,
                'description' => '2 Earned Leaves per month (accrues monthly). Employees can apply for up to 2 ELs per month.',
            ]);

            LeaveType::create([
                'name' => 'Casual Leave (CL)',
                'code' => 'CL',
                'days_allowed_per_year' => 12,
                'monthly_limit' => 2.0,
                'is_monthly_accrual' => false,
                'is_paid' => true,
                'status' => true,
                'description' => 'For personal matters and urgent unplanned work.',
            ]);

            LeaveType::create([
                'name' => 'Sick Leave (SL)',
                'code' => 'SL',
                'days_allowed_per_year' => 7,
                'monthly_limit' => null,
                'is_monthly_accrual' => false,
                'is_paid' => true,
                'status' => true,
                'description' => 'For illness and medical emergencies.',
            ]);

            LeaveType::create([
                'name' => 'Leave Without Pay (LWP)',
                'code' => 'LWP',
                'days_allowed_per_year' => 30,
                'monthly_limit' => null,
                'is_monthly_accrual' => false,
                'is_paid' => false,
                'status' => true,
                'description' => 'Unpaid leave when paid quotas are exhausted.',
            ]);
        }
    }

    /**
     * Get active leave types for employee selection.
     */
    public function getActiveLeaveTypes(): Collection
    {
        $this->ensureDefaultLeaveTypes();
        return LeaveType::where('status', true)->orderBy('id', 'asc')->get();
    }

    /**
     * Apply for employee leave with monthly limit and accrual enforcement.
     */
    public function applyLeave(User $user, array $data): EmployeeLeave
    {
        $startDate = Carbon::parse($data['start_date'])->format('Y-m-d');
        $endDate = Carbon::parse($data['end_date'] ?? $data['start_date'])->format('Y-m-d');
        $isHalfDay = !empty($data['is_half_day']);

        if ($startDate > $endDate) {
            $endDate = $startDate;
        }

        // Calculate total days excluding Sundays
        $totalDays = 0.0;
        if ($isHalfDay) {
            $totalDays = 0.5;
            $endDate = $startDate;
        } else {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                if (!$d->isSunday()) {
                    $totalDays += 1.0;
                }
            }
            if ($totalDays <= 0) {
                $totalDays = 1.0;
            }
        }

        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $startCarbon = Carbon::parse($startDate);
        $month = $startCarbon->month;
        $year = $startCarbon->year;
        $monthName = $startCarbon->format('F Y');

        // Check Monthly Limit Rule (e.g. Max 2 ELs per month)
        if ($leaveType->monthly_limit !== null && $leaveType->monthly_limit > 0) {
            $existingInMonth = (float) EmployeeLeave::where('user_id', $user->id)
                ->where('leave_type_id', $leaveType->id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->sum('total_days');

            if (($existingInMonth + $totalDays) > (float) $leaveType->monthly_limit) {
                $remainingInMonth = max(0, (float) $leaveType->monthly_limit - $existingInMonth);
                throw ValidationException::withMessages([
                    'leave_type_id' => "Monthly Limit Exceeded: You can only apply for up to {$leaveType->monthly_limit} days of {$leaveType->name} in {$monthName}. You currently have {$existingInMonth} day(s) applied/approved this month (Available: {$remainingInMonth} days).",
                ]);
            }
        }

        // Check Accrual Rule (e.g. 2 EL per month up to current month)
        if ($leaveType->is_monthly_accrual) {
            $currentMonth = Carbon::now()->month;
            $accruedTotal = $month * (float) ($leaveType->monthly_limit ?: 2.0); // Total accrued up to applied month
            $usedSoFarThisYear = (float) EmployeeLeave::where('user_id', $user->id)
                ->where('leave_type_id', $leaveType->id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereYear('start_date', $year)
                ->sum('total_days');

            if (($usedSoFarThisYear + $totalDays) > $accruedTotal) {
                $availableAccrued = max(0, $accruedTotal - $usedSoFarThisYear);
                throw ValidationException::withMessages([
                    'leave_type_id' => "Accrual Balance Insufficient: Earned leaves accrue at {$leaveType->monthly_limit} days/month (Total accrued up to {$monthName}: {$accruedTotal} days). You have already used {$usedSoFarThisYear} days this year. Available balance: {$availableAccrued} days.",
                ]);
            }
        }

        return EmployeeLeave::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'is_half_day' => $isHalfDay,
            'half_day_type' => $isHalfDay ? ($data['half_day_type'] ?? 'first_half') : null,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
    }

    /**
     * Get user's leave history.
     */
    public function getUserLeaves(int $userId): Collection
    {
        return EmployeeLeave::where('user_id', $userId)
            ->with(['leaveType', 'approver'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get user leave balances summary for current year with monthly limit info.
     */
    public function getUserLeaveBalances(int $userId): Collection
    {
        $this->ensureDefaultLeaveTypes();
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $leaveTypes = LeaveType::where('status', true)->get();

        $leavesThisYear = EmployeeLeave::where('user_id', $userId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', $currentYear)
            ->get();

        return $leaveTypes->map(function ($type) use ($leavesThisYear, $currentMonth) {
            $typeLeaves = $leavesThisYear->where('leave_type_id', $type->id);
            $takenYear = (float) $typeLeaves->where('status', 'approved')->sum('total_days');
            $pendingYear = (float) $typeLeaves->where('status', 'pending')->sum('total_days');
            
            $takenThisMonth = (float) $typeLeaves->filter(function($l) use ($currentMonth) {
                return Carbon::parse($l->start_date)->month === $currentMonth && $l->status === 'approved';
            })->sum('total_days');

            $allowedYear = (float) $type->days_allowed_per_year;
            
            // Accrued calculation if monthly accrual
            if ($type->is_monthly_accrual) {
                $accruedTillNow = round($currentMonth * ($type->monthly_limit ?: 2.0), 1);
                $remaining = max(0, $accruedTillNow - $takenYear);
            } else {
                $accruedTillNow = $allowedYear;
                $remaining = max(0, $allowedYear - $takenYear);
            }

            return [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'allowed' => $allowedYear,
                'accrued_till_now' => $accruedTillNow,
                'monthly_limit' => $type->monthly_limit,
                'is_monthly_accrual' => $type->is_monthly_accrual,
                'taken_this_month' => $takenThisMonth,
                'taken' => $takenYear,
                'pending' => $pendingYear,
                'remaining' => $remaining,
                'is_paid' => $type->is_paid,
            ];
        });
    }

    /**
     * Approve or reject a leave application.
     */
    public function updateLeaveStatus(EmployeeLeave $leave, string $status, ?string $remarks, User $adminUser): bool
    {
        return $leave->update([
            'status' => $status,
            'approved_by' => $adminUser->id,
            'approved_at' => Carbon::now(),
            'admin_remarks' => $remarks,
        ]);
    }

    /**
     * Check if an employee has an approved leave on a given date.
     */
    public function getApprovedLeaveForDate(int $userId, string $dateStr): ?EmployeeLeave
    {
        return EmployeeLeave::where('user_id', $userId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->with('leaveType')
            ->first();
    }
}
