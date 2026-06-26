<?php

namespace App\Services;

use App\Models\CsdClientAssignment;
use App\Models\TeamMembers;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CsdTeamScopeService
{
    public const DEPARTMENT_ID = BranchScopeService::DEPT_CSD;

    public function __construct(private BranchScopeService $branchScope)
    {
    }

    public function isCsdDepartmentUser(User $user): bool
    {
        return (int) ($user->departments->department ?? 0) === self::DEPARTMENT_ID;
    }

    public function isUnrestricted(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function isBranchWideCsd(User $user): bool
    {
        return $user->isBranchManager();
    }

    /**
     * User IDs whose CSD work this user may see. Null = no restriction (admin).
     */
    public function getScopedUserIds(User $user): ?array
    {
        if ($this->isUnrestricted($user)) {
            return null;
        }

        if ($this->isBranchWideCsd($user)) {
            return $this->branchScope->getBranchCsdUserIds($user);
        }

        if ($user->hasRole('Team-Leader') && $this->isCsdDepartmentUser($user)) {
            return $this->getTeamMemberIds($user);
        }

        if ($user->hasRole('CSD-Executive')) {
            return [$user->id];
        }

        return [$user->id];
    }

    public function getTeamMemberIds(User $user, bool $csdExecutivesOnly = false): array
    {
        $teams = DB::table('team_members')
            ->where('user', $user->id)
            ->where('status', true)
            ->pluck('team')
            ->toArray();

        $query = TeamMembers::whereIn('team', $teams)->where('status', true);

        if ($csdExecutivesOnly) {
            $query->whereHas('users.roles', function ($q) {
                $q->whereIn('name', ['CSD-Executive', 'Team-Leader']);
            });
        }

        $members = $query->pluck('user')->toArray();

        if (!in_array($user->id, $members, true)) {
            $members[] = $user->id;
        }

        return array_values(array_unique($members));
    }

    public function getBranchCsdUserIds(User $user): array
    {
        return $this->branchScope->getBranchCsdUserIds($user);
    }

    public function getAllocatableExecutives(User $user): \Illuminate\Support\Collection
    {
        if ($this->isUnrestricted($user)) {
            return User::role(['CSD-Executive', 'Team-Leader'])
                ->whereHas('departments', fn ($q) => $q->where('department', self::DEPARTMENT_ID))
                ->get();
        }

        if ($this->isBranchWideCsd($user)) {
            $ids = $this->getBranchCsdUserIds($user);

            return User::whereIn('id', $ids)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['CSD-Executive', 'Team-Leader']))
                ->get();
        }

        if ($user->hasRole('Team-Leader') && $this->isCsdDepartmentUser($user)) {
            $ids = $this->getTeamMemberIds($user, true);

            return User::whereIn('id', $ids)->get();
        }

        return collect([$user]);
    }

    /**
     * CSD Team Leader(s) for the same team(s) as the given CSD member.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function resolveTeamLeadersForMember(User $member): \Illuminate\Support\Collection
    {
        if ($member->hasRole('Team-Leader') && $this->isCsdDepartmentUser($member)) {
            return collect([$member]);
        }

        $teams = DB::table('team_members')
            ->where('user', $member->id)
            ->where('status', true)
            ->pluck('team')
            ->toArray();

        if (empty($teams)) {
            return $this->fallbackCsdLeaders($member);
        }

        $leaderIds = TeamMembers::whereIn('team', $teams)
            ->where('status', true)
            ->whereHas('users', function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'Team-Leader'))
                    ->whereHas('departments', fn ($d) => $d->where('department', self::DEPARTMENT_ID));
            })
            ->pluck('user')
            ->unique()
            ->values();

        if ($leaderIds->isEmpty()) {
            return $this->fallbackCsdLeaders($member);
        }

        return User::whereIn('id', $leaderIds)->get();
    }

    public function getDepartmentLabel(): string
    {
        return (string) (DB::table('departments')->where('id', self::DEPARTMENT_ID)->value('name') ?? 'Customer Success (CSD)');
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function fallbackCsdLeaders(User $member): \Illuminate\Support\Collection
    {
        $branchId = $this->branchScope->resolveBranchId($member);
        if (!$branchId) {
            return collect();
        }

        $branchUserIds = $this->branchScope->getBranchCsdUserIds(
            User::find($member->id) ?? $member
        );

        return User::whereIn('id', $branchUserIds)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Team-Leader'))
            ->whereHas('departments', fn ($d) => $d->where('department', self::DEPARTMENT_ID))
            ->get();
    }

    public function canAssignClients(User $user): bool
    {
        return $this->isUnrestricted($user)
            || $this->isBranchWideCsd($user)
            || ($user->hasRole('Team-Leader') && $this->isCsdDepartmentUser($user));
    }

    /** Whether the user may assign CSD work to other team members (not only themselves). */
    public function canAssignToOthers(User $user): bool
    {
        return $this->canAssignClients($user);
    }

    public function applyAssigneeScope(Builder $query, User $user, string $column = 'assigned_to'): Builder
    {
        $ids = $this->getScopedUserIds($user);

        if ($ids === null) {
            return $query;
        }

        if (
            ($user->hasRole('Team-Leader') && $this->isCsdDepartmentUser($user)) ||
            $this->isBranchWideCsd($user)
        ) {
            return $query->where(function ($q) use ($ids, $column) {
                $q->whereIn($column, $ids)->orWhereNull($column);
            });
        }

        return $query->whereIn($column, $ids);
    }

    public function applyCreatorScope(Builder $query, User $user, string $column = 'created_by'): Builder
    {
        $ids = $this->getScopedUserIds($user);

        if ($ids === null) {
            return $query;
        }

        return $query->whereIn($column, $ids);
    }

    public function getScopedClientIds(User $user): ?array
    {
        $ids = $this->getScopedUserIds($user);

        if ($ids === null) {
            return null;
        }

        return CsdClientAssignment::query()
            ->when(
                ($user->hasRole('Team-Leader') && $this->isCsdDepartmentUser($user)) || $this->isBranchWideCsd($user),
                fn ($q) => $q->where(function ($inner) use ($ids) {
                    $inner->whereIn('assigned_to', $ids)->orWhereNull('assigned_to');
                }),
                fn ($q) => $q->whereIn('assigned_to', $ids)
            )
            ->pluck('client')
            ->unique()
            ->values()
            ->all();
    }

    public function applyClientScope(Builder $query, User $user, string $clientColumn = 'client'): Builder
    {
        $clientIds = $this->getScopedClientIds($user);

        if ($clientIds === null) {
            return $query;
        }

        if (empty($clientIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($clientColumn, $clientIds);
    }
}
