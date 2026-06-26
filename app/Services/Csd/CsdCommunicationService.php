<?php

namespace App\Services\Csd;

use App\Models\CsdCommunication;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use Illuminate\Database\Eloquent\Builder;

class CsdCommunicationService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user): Builder
    {
        $query = CsdCommunication::with(['client', 'creator'])->latest();
        $this->scope->applyClientScope($query, $user);

        return $query;
    }

    public function create(array $data, User $user): CsdCommunication
    {
        $this->assertClientAccess($user, (int) $data['client']);

        return CsdCommunication::create([
            ...$data,
            'created_by' => $user->id,
        ]);
    }

    public function update(CsdCommunication $communication, array $data, User $user): CsdCommunication
    {
        $this->assertClientAccess($user, (int) $communication->client);
        $communication->update($data);

        return $communication->fresh(['client', 'creator']);
    }

    private function assertClientAccess(User $user, int $clientId): void
    {
        if (!$this->resolver->userCanAccessClient($user, $clientId)) {
            throw new \InvalidArgumentException('You cannot access this client.');
        }
    }
}
