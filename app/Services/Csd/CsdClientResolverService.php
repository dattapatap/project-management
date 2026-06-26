<?php

namespace App\Services\Csd;

use App\Models\Clients;
use App\Models\CsdClientAssignment;
use App\Models\User;
use App\Services\BranchScopeService;
use App\Services\CsdTeamScopeService;
use Illuminate\Database\Eloquent\Collection;

class CsdClientResolverService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private BranchScopeService $branchScope
    ) {
    }

    /**
     * Clients the user may select in CSD forms (branch / assignee scoped).
     */
    public function getSelectableClients(User $user): Collection
    {
        $ids = $this->resolveAccessibleClientIds($user);

        if ($ids === null) {
            return Clients::query()
                ->select(['id', 'name'])
                ->where(function ($q) {
                    $q->where('is_active', true)->orWhere('status', 'Matured');
                })
                ->orderBy('name')
                ->get();
        }

        if (empty($ids)) {
            return new Collection();
        }

        return Clients::query()
            ->select(['id', 'name'])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    public function userCanAccessClient(User $user, int $clientId): bool
    {
        if ($user->isGlobalAdmin()) {
            return true;
        }

        $ids = $this->resolveAccessibleClientIds($user);

        if ($ids === null) {
            return true;
        }

        return in_array($clientId, $ids, true);
    }

    /**
     * Restrict queries to clients the user may access in CSD (forms + listings).
     */
    public function applyAccessibleClientScope(\Illuminate\Database\Eloquent\Builder $query, User $user, string $clientColumn = 'client'): \Illuminate\Database\Eloquent\Builder
    {
        $ids = $this->resolveAccessibleClientIds($user);

        if ($ids === null) {
            return $query;
        }

        if (empty($ids)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($clientColumn, $ids);
    }

    /**
     * @return array<int>|null null = unrestricted (admin)
     */
    private function resolveAccessibleClientIds(User $user): ?array
    {
        if ($user->isGlobalAdmin()) {
            return null;
        }

        $ids = collect($this->scope->getScopedClientIds($user) ?? []);

        if ($user->isBranchManager()) {
            $branchUserIds = $this->branchScope->getBranchUserIds($user);
            $maturedInBranch = Clients::query()
                ->where('status', 'Matured')
                ->whereIn('ref_user', $branchUserIds)
                ->pluck('id');

            $ids = $ids->merge($maturedInBranch);
        }

        if ($user->hasRole('Team-Leader') && $this->scope->isCsdDepartmentUser($user)) {
            $teamMemberIds = $this->scope->getTeamMemberIds($user);
            $teamClientIds = CsdClientAssignment::query()
                ->whereIn('assigned_to', $teamMemberIds)
                ->pluck('client');

            $ids = $ids->merge($teamClientIds);
        }

        return $ids->unique()->filter()->values()->all();
    }
}
