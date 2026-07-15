<?php

namespace App\Services\Csd;

use App\Models\CsdOpportunity;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use Illuminate\Database\Eloquent\Builder;

class CsdOpportunityService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user): Builder
    {
        $query = CsdOpportunity::with(['clients', 'assignee', 'engagement'])->latest();
        $this->scope->applyAssigneeScope($query, $user);

        return $query;
    }

    public function create(array $data, User $user): CsdOpportunity
    {
        $this->assertClientAccess($user, (int) $data['client']);

        return CsdOpportunity::create([
            ...$data,
            'assigned_to' => $this->resolveAssignee($user, $data['assigned_to'] ?? null),
            'created_by' => $user->id,
        ]);
    }

    public function update(CsdOpportunity $opportunity, array $data, User $user): CsdOpportunity
    {
        if ($opportunity->status === 'won') {
            throw new \InvalidArgumentException(
                'This opportunity is already Won. Use Commercial Orders to track and close the deal.'
            );
        }

        if (!$this->scope->canAssignToOthers($user)) {
            unset($data['assigned_to']);
        }

        $wasWon = $opportunity->status === 'won';
        $opportunity->update($data);
        $fresh = $opportunity->fresh();

        if (!$wasWon && $fresh->status === 'won') {
            $fresh->loadMissing(['clients', 'assignee']);
            app(CsdOpportunityHandoffService::class)->notifySalesOnWon($fresh);
        }

        return $fresh;
    }

    public function assignToSales(CsdOpportunity $opportunity, int $salesRepId, User $user): CsdOpportunity
    {
        if ($opportunity->status === 'won') {
            throw new \InvalidArgumentException('This opportunity is already assigned to Sales (Won).');
        }

        $opportunity->status = 'won';
        $opportunity->save();

        $fresh = $opportunity->fresh(['clients', 'assignee']);
        app(CsdOpportunityHandoffService::class)->notifySalesOnWon($fresh, $salesRepId);

        return $fresh;
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
