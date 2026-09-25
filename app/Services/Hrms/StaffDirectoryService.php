<?php

namespace App\Services\Hrms;

use App\Models\Branches;
use App\Models\Department;
use App\Models\Employees;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StaffDirectoryService
{
    /**
     * Get active staff directory with optional filtering.
     */
    public function getDirectory(array $filters = []): Collection
    {
        $query = Employees::whereNull('deleted_at')
            ->with([
                'userAccount.departments.dept',
                'userAccount.branch.branch',
                'userAccount.roles',
            ]);

        // Status filter
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'separated') {
                $query->whereIn('status', Employees::SEPARATED_STATUSES);
            } elseif ($filters['status'] === 'all_working') {
                $query->whereIn('status', Employees::WORKING_STATUSES);
            } elseif (in_array($filters['status'], Employees::ALL_STATUSES ?? [], true) || in_array($filters['status'], User::ALL_STATUSES, true)) {
                $query->where('status', $filters['status']);
            }
        } else {
            // Default to all active working staff
            $query->whereIn('status', Employees::WORKING_STATUSES);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('mem_code', 'like', "%{$search}%")
                  ->orWhere('alt_email', 'like', "%{$search}%")
                  ->orWhere('alt_number', 'like', "%{$search}%")
                  ->orWhereHas('userAccount', function (Builder $uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Department filter
        if (!empty($filters['department_id'])) {
            $deptId = (int) $filters['department_id'];
            $query->whereHas('userAccount.departments', function (Builder $dq) use ($deptId) {
                $dq->where('department', $deptId);
            });
        }

        // Branch filter
        if (!empty($filters['branch_id'])) {
            $branchId = (int) $filters['branch_id'];
            $query->whereHas('userAccount.branch', function (Builder $bq) use ($branchId) {
                $bq->where('branch', $branchId);
            });
        }

        $today = Carbon::today();

        return $query->get()->map(function ($emp) use ($today) {
            if ($emp->joining_dt) {
                $joining = Carbon::parse($emp->joining_dt);
                $emp->formatted_joining = $joining->format('d M Y');
                $years = $today->diffInYears($joining);
                $months = $today->diffInMonths($joining);
                $emp->experience = $years >= 1 ? "{$years} " . ($years > 1 ? 'Years' : 'Year') : "{$months} " . ($months > 1 ? 'Months' : 'Month');
            } else {
                $emp->formatted_joining = 'N/A';
                $emp->experience = 'N/A';
            }

            return $emp;
        })->sortBy(function ($emp) {
            return $emp->userAccount?->departments?->dept?->name ?? 'Z';
        })->values();
    }

    /**
     * Get lookup options for directory filters.
     */
    public function getFilterOptions(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'branches'    => Branches::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Get summary metrics for the staff directory.
     */
    public function getDirectoryStats(): array
    {
        $totalWorking = Employees::whereIn('status', Employees::WORKING_STATUSES)->whereNull('deleted_at')->count();

        return [
            'total_active' => $totalWorking,
            'total_depts'  => Department::count(),
            'total_branches'=> Branches::count(),
        ];
    }
}
