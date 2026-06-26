<?php

namespace App\Services\Reports;

use App\Models\User;
use App\Services\BranchScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportScopeService
{
    public function __construct(private BranchScopeService $branchScope)
    {
    }

    public function canViewEmployee(User $actingUser, int $targetUserId): bool
    {
        if ((int) $actingUser->id === (int) $targetUserId) {
            return true;
        }

        if ($actingUser->hasBranchWideAccess()) {
            if ($actingUser->isGlobalAdmin()) {
                return true;
            }

            return in_array($targetUserId, $this->branchScope->getBranchUserIds($actingUser), true);
        }

        if ($actingUser->hasRole('Project-Manager')) {
            $employee = User::with('departments')->find($targetUserId);
            if (!$employee) {
                return false;
            }

            return (int) ($actingUser->departments->department ?? 0) === (int) ($employee->departments->department ?? -1);
        }

        if ($actingUser->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')->where('user', $actingUser->id)->where('status', true)->pluck('team');

            return DB::table('team_members')
                ->whereIn('team', $teams)
                ->where('user', $targetUserId)
                ->where('status', true)
                ->exists();
        }

        return false;
    }

    public function visibleEmployeesQuery(User $actingUser, ?int $departmentId = null): Builder
    {
        $query = User::with(['emp', 'roles', 'departments.dept'])
            ->where('status', 'Active')
            ->where('id', '!=', 1);

        if ($actingUser->hasBranchWideAccess()) {
            if ($actingUser->isBranchManager() && !$actingUser->isGlobalAdmin()) {
                $query->whereIn('id', $this->branchScope->getBranchUserIds($actingUser));
            }
        } elseif ($actingUser->hasRole('Project-Manager')) {
            $deptId = $actingUser->departments->department ?? null;
            if ($deptId) {
                $query->whereHas('departments', fn ($q) => $q->where('department', $deptId));
            }
        } elseif ($actingUser->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')->where('user', $actingUser->id)->where('status', true)->pluck('team')->toArray();
            $memberIds = \App\Models\TeamMembers::whereIn('team', $teams)->where('status', true)->pluck('user')->toArray();

            if (!in_array($actingUser->id, $memberIds, true)) {
                $memberIds[] = $actingUser->id;
            }

            $query->whereIn('id', $memberIds);
        } else {
            $query->where('id', $actingUser->id);
        }

        if ($departmentId) {
            $query->whereHas('departments', fn ($q) => $q->where('department', $departmentId));
        }

        return $query;
    }
}
