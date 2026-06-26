<?php

namespace App\Services\Csd;

use App\Models\CsdSupportTicket;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CsdSupportService
{
    private const SLA_HOURS = ['low' => 72, 'medium' => 48, 'high' => 24, 'critical' => 8];

    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user): Builder
    {
        $query = CsdSupportTicket::with(['client', 'assignee'])->latest();
        $this->scope->applyAssigneeScope($query, $user);

        return $query;
    }

    public function create(array $data, User $user): CsdSupportTicket
    {
        $this->assertClientAccess($user, (int) $data['client']);
        $priority = $data['priority'];

        return CsdSupportTicket::create([
            ...$data,
            'ticket_no' => CsdSupportTicket::generateTicketNo(),
            'status' => 'open',
            'assigned_to' => $this->resolveAssignee($user, $data['assigned_to'] ?? null),
            'sla_due_at' => Carbon::now()->addHours(self::SLA_HOURS[$priority] ?? 48),
            'created_by' => $user->id,
        ]);
    }

    public function update(CsdSupportTicket $ticket, array $data, User $user): CsdSupportTicket
    {
        if (!$this->scope->canAssignToOthers($user)) {
            unset($data['assigned_to']);
        }

        if (in_array($data['status'], ['resolved', 'closed'], true) && !$ticket->resolved_at) {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        return $ticket->fresh();
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
