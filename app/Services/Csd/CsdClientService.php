<?php

namespace App\Services\Csd;

use App\Models\CsdClientAssignment;
use App\Models\CsdContactPerson;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use Illuminate\Database\Eloquent\Builder;

class CsdClientService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user): Builder
    {
        $query = CsdClientAssignment::with(['client', 'project', 'assignee', 'latestOpenUpsellEngagement'])->latest();
        $this->scope->applyAssigneeScope($query, $user);

        return $query;
    }

    public function create(array $data, User $user): CsdClientAssignment
    {
        if (!$this->resolver->userCanAccessClient($user, (int) $data['client'])) {
            throw new \InvalidArgumentException('You cannot assign this client.');
        }

        $clientId = (int) $data['client'];
        $existing = CsdClientAssignment::where('client', $clientId)
            ->where('status', 'active')
            ->first();
            
        if ($existing) {
            $existing->load('assignee');
            $assigneeName = $existing->assignee?->name ?? 'another executive';
            throw new \InvalidArgumentException("This client is already actively assigned to {$assigneeName}.");
        }

        return CsdClientAssignment::create([
            ...$data,
            'assigned_to' => $data['assigned_to'] ?? $user->id,
            'handoff_date' => now()->toDateString(),
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    }

    public function update(CsdClientAssignment $assignment, array $data, User $user): CsdClientAssignment
    {
        if (!app(CsdTeamScopeService::class)->canAssignToOthers($user)) {
            unset($data['assigned_to']);
        }

        $assignment->update($data);

        return $assignment->fresh();
    }

    public function addContact(array $data, User $user): CsdContactPerson
    {
        if (!$this->resolver->userCanAccessClient($user, (int) $data['client'])) {
            throw new \InvalidArgumentException('You cannot add contacts for this client.');
        }

        if (!empty($data['is_primary'])) {
            CsdContactPerson::where('client', $data['client'])->update(['is_primary' => false]);
        }

        return CsdContactPerson::create([
            ...$data,
            'created_by' => $user->id,
        ]);
    }
}
