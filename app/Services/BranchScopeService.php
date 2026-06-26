<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BranchScopeService
{
    public const DEPT_NSD = 1;

    public const DEPT_OD = 2;

    public const DEPT_CSD = 3;

    public function getBranchId(User $user): ?int
    {
        $branchId = DB::table('user_branches')->where('user', $user->id)->value('branch');

        return $branchId ? (int) $branchId : null;
    }

    /**
     * Resolve branch from user_branches or the user's department record.
     */
    public function resolveBranchId(User $user): ?int
    {
        $branchId = $this->getBranchId($user);

        if ($branchId) {
            return $branchId;
        }

        $deptId = $user->departments->department ?? null;

        if (!$deptId) {
            return null;
        }

        $fromDept = DB::table('departments')->where('id', $deptId)->value('branchid');

        return $fromDept ? (int) $fromDept : null;
    }

    /**
     * User IDs belonging to the acting user's branch, optionally filtered by department.
     * Merges user_branches linkage with users whose department belongs to the same branch.
     */
    public function getBranchUserIds(User $user, ?int $departmentId = null): array
    {
        $branchId = $this->resolveBranchId($user);

        if (!$branchId) {
            return [$user->id];
        }

        $viaUserBranch = DB::table('user_branches')
            ->where('branch', $branchId)
            ->pluck('user');

        $viaDept = DB::table('user_departments')
            ->join('departments', 'departments.id', '=', 'user_departments.department')
            ->where('departments.branchid', $branchId)
            ->pluck('user_departments.user');

        $ids = $viaUserBranch->merge($viaDept)->unique()->values()->toArray();

        if ($departmentId !== null) {
            $ids = User::whereIn('id', $ids)
                ->whereHas('departments', fn ($q) => $q->where('department', $departmentId))
                ->pluck('id')
                ->toArray();

            if (empty($ids)) {
                $ids = $this->fallbackDepartmentUserIds($branchId, $departmentId);
            }
        }

        return empty($ids) ? [$user->id] : array_values(array_unique($ids));
    }

    /**
     * Role-based fallback when users lack user_departments rows but belong to the branch.
     */
    private function fallbackDepartmentUserIds(int $branchId, int $departmentId): array
    {
        $branchUserIds = DB::table('user_branches')
            ->where('branch', $branchId)
            ->pluck('user')
            ->merge(
                DB::table('user_departments')
                    ->join('departments', 'departments.id', '=', 'user_departments.department')
                    ->where('departments.branchid', $branchId)
                    ->pluck('user_departments.user')
            )
            ->unique()
            ->values()
            ->toArray();

        if (empty($branchUserIds)) {
            return [];
        }

        $roleMap = [
            self::DEPT_NSD => ['Sales-Executive', 'Team-Leader'],
            self::DEPT_CSD => ['CSD-Executive', 'Team-Leader'],
            self::DEPT_OD => ['Developer', 'Designer', 'Seo-Developer', 'Accountant', 'Team-Leader', 'Project-Manager'],
        ];

        $roles = $roleMap[$departmentId] ?? [];

        if (empty($roles)) {
            return [];
        }

        return User::whereIn('id', $branchUserIds)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->pluck('id')
            ->toArray();
    }

    public function getBranchSalesUserIds(User $user): array
    {
        return $this->getBranchUserIds($user, self::DEPT_NSD);
    }

    public function getBranchCsdUserIds(User $user): array
    {
        return $this->getBranchUserIds($user, self::DEPT_CSD);
    }

    public function getBranchOdUserIds(User $user): array
    {
        return $this->getBranchUserIds($user, self::DEPT_OD);
    }

    public function applyBranchUserScope(Builder $query, User $actingUser): Builder
    {
        if ($actingUser->isGlobalAdmin()) {
            return $query;
        }

        if ($actingUser->isBranchManager()) {
            $ids = $this->getBranchUserIds($actingUser);

            if (count($ids) === 1 && $ids[0] === $actingUser->id && !$this->resolveBranchId($actingUser)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $ids);
        }

        return $query;
    }

    public function scopeClientsForBranch(Builder $query, User $user): Builder
    {
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        if (!$user->isBranchManager()) {
            return $query;
        }

        $salesUserIds = $this->getBranchSalesUserIds($user);

        return $query->where(function ($q) use ($salesUserIds) {
            $q->whereIn('ref_user', $salesUserIds)
                ->orWhereIn('tele_ref_user', $salesUserIds);
        });
    }
}
