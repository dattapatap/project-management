<?php

namespace App\Services\Csd;

use App\Models\CsdCollectionFollowup;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use Illuminate\Database\Eloquent\Builder;

class CsdCollectionService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user): Builder
    {
        $query = CsdCollectionFollowup::with(['client', 'package', 'assignee'])->latest();
        $this->scope->applyAssigneeScope($query, $user);

        return $query;
    }

    public function create(array $data, User $user): CsdCollectionFollowup
    {
        $this->assertClientAccess($user, (int) $data['client']);

        return CsdCollectionFollowup::create([
            ...$data,
            'assigned_to' => $this->resolveAssignee($user, $data['assigned_to'] ?? null),
            'created_by' => $user->id,
        ]);
    }

    public function update(CsdCollectionFollowup $collection, array $data, User $user): CsdCollectionFollowup
    {
        if (!$this->scope->canAssignToOthers($user)) {
            unset($data['assigned_to']);
        }

        $collection->update($data);

        return $collection->fresh();
    }

    private function resolveAssignee(User $user, ?int $assignedTo): int
    {
        if (!$this->scope->canAssignToOthers($user)) {
            return $user->id;
        }

        return $assignedTo ?: $user->id;
    }

    private function assertClientAccess(User $user, int $clientId): void
    {
        if (!$this->resolver->userCanAccessClient($user, $clientId)) {
            throw new \InvalidArgumentException('You cannot access this client.');
        }
    }
}
