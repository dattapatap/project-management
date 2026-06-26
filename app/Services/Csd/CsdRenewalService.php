<?php

namespace App\Services\Csd;

use App\Models\ClientDomains;
use App\Models\CsdAmcContract;
use App\Models\CsdRenewal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CsdRenewalService
{
    public const GRACE_DAYS = 30;

    public const DUE_WINDOW_DAYS = 30;

    public function __construct(
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user, ?string $statusFilter = null): Builder
    {
        $this->syncOpenStatuses($user);

        $query = CsdRenewal::with('client')->orderBy('due_date');

        $this->resolver->applyAccessibleClientScope($query, $user);

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        return $query;
    }

    public function create(array $data, User $user): CsdRenewal
    {
        $this->assertClientAccess($user, (int) $data['client']);

        $data['status'] = $this->resolveStatusForDueDate($data['due_date']);
        $data['created_by'] = $user->id;

        if (($data['renewal_type'] ?? '') === 'amc' && empty($data['reference_id'])) {
            $data['reference_id'] = $this->guessAmcReference((int) $data['client'], $data['due_date']);
        }

        return CsdRenewal::create($data);
    }

    public function update(CsdRenewal $renewal, array $data, User $user): CsdRenewal
    {
        $this->assertClientAccess($user, (int) $renewal->client);

        $previousStatus = $renewal->status;
        $newStatus = $data['status'] ?? $previousStatus;

        if (!in_array($newStatus, ['renewed', 'lapsed'], true)) {
            $newStatus = $this->resolveStatusForDueDate($data['due_date'], null);
        }

        $data['status'] = $newStatus;

        if ($newStatus === 'renewed' && $previousStatus !== 'renewed') {
            $data['renewed_at'] = now();
        }

        $renewal->update($data);
        $fresh = $renewal->fresh();

        if ($newStatus === 'renewed' && $previousStatus !== 'renewed') {
            $this->applyRenewalSideEffects($fresh, $user);
        }

        return $fresh;
    }

    public function markRenewed(CsdRenewal $renewal, User $user, ?string $notes = null): CsdRenewal
    {
        if (!in_array($renewal->status, ['upcoming', 'due'], true)) {
            throw new \InvalidArgumentException('Only upcoming or due renewals can be marked as renewed.');
        }

        return $this->update($renewal, [
            'title' => $renewal->title,
            'due_date' => $renewal->due_date->toDateString(),
            'amount' => $renewal->amount,
            'status' => 'renewed',
            'notes' => $notes ?? $renewal->notes,
        ], $user);
    }

    public function markLapsed(CsdRenewal $renewal, User $user, ?string $notes = null): CsdRenewal
    {
        if ($renewal->status === 'renewed') {
            throw new \InvalidArgumentException('Renewed items cannot be marked as lapsed.');
        }

        $renewal->update([
            'status' => 'lapsed',
            'notes' => $notes ?? $renewal->notes,
        ]);

        return $renewal->fresh();
    }

    public function syncFromSources(User $user): array
    {
        $clientIds = $this->resolver->getSelectableClients($user)->pluck('id')->all();

        return [
            'amc' => $this->syncFromAmcContracts($clientIds, $user),
            'domains' => $this->syncFromClientDomains($clientIds, $user),
            'statuses' => $this->syncOpenStatuses($user),
        ];
    }

    public function syncOpenStatuses(User $user): int
    {
        $clientIds = $this->resolver->getSelectableClients($user)->pluck('id')->all();

        if (empty($clientIds)) {
            return 0;
        }

        $updated = 0;
        $renewals = CsdRenewal::whereIn('client', $clientIds)
            ->whereIn('status', ['upcoming', 'due'])
            ->get();

        foreach ($renewals as $renewal) {
            $resolved = $this->resolveStatusForDueDate($renewal->due_date);
            if ($resolved !== $renewal->status) {
                $renewal->update(['status' => $resolved]);
                $updated++;
            }
        }

        return $updated;
    }

    public function findForUser(int $renewalId, User $user): CsdRenewal
    {
        $renewal = CsdRenewal::with(['client'])->findOrFail($renewalId);
        $this->assertClientAccess($user, (int) $renewal->client);

        return $renewal;
    }

    public function amcOptionsForClient(int $clientId, User $user): \Illuminate\Support\Collection
    {
        $this->assertClientAccess($user, $clientId);

        return CsdAmcContract::where('client', $clientId)
            ->where('status', 'active')
            ->orderByDesc('end_date')
            ->get(['id', 'contract_type', 'end_date', 'amount']);
    }

    public function resolveStatusForDueDate($dueDate, ?string $lockedStatus = null): string
    {
        if (in_array($lockedStatus, ['renewed', 'lapsed'], true)) {
            return $lockedStatus;
        }

        $today = Carbon::today();
        $due = Carbon::parse($dueDate)->startOfDay();

        if ($due->lt($today->copy()->subDays(self::GRACE_DAYS))) {
            return 'lapsed';
        }

        if ($due->lte($today->copy()->addDays(self::DUE_WINDOW_DAYS))) {
            return 'due';
        }

        return 'upcoming';
    }

    private function syncFromAmcContracts(array $clientIds, User $user): int
    {
        if (empty($clientIds)) {
            return 0;
        }

        $created = 0;
        $contracts = CsdAmcContract::with('client')
            ->whereIn('client', $clientIds)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where(function ($m) {
                    $m->where('billing_cycle', CsdAmcContract::CYCLE_MONTHLY)
                        ->whereDate('end_date', '<=', now()->addDays(14))
                        ->whereDate('end_date', '>=', now()->subDays(3));
                })->orWhere(function ($y) {
                    $y->where('billing_cycle', CsdAmcContract::CYCLE_YEARLY)
                        ->whereDate('end_date', '<=', now()->addDays(60))
                        ->whereDate('end_date', '>=', now()->subDays(7));
                });
            })
            ->get();

        foreach ($contracts as $amc) {
            if ($this->hasOpenRenewal('amc', $amc->id, (int) $amc->client)) {
                continue;
            }

            CsdRenewal::create([
                'client' => $amc->client,
                'renewal_type' => 'amc',
                'reference_id' => $amc->id,
                'title' => 'AMC renewal — ' . ($amc->client->name ?? 'Client'),
                'due_date' => $amc->end_date,
                'amount' => $amc->amount,
                'status' => $this->resolveStatusForDueDate($amc->end_date),
                'notes' => 'Auto-created from AMC contract ending ' . $amc->end_date->format('d M Y'),
                'created_by' => $user->id,
            ]);
            $created++;
        }

        return $created;
    }

    private function syncFromClientDomains(array $clientIds, User $user): int
    {
        if (empty($clientIds)) {
            return 0;
        }

        $created = 0;
        $domains = ClientDomains::whereIn('client', $clientIds)
            ->where('renewed', false)
            ->whereDate('expiry_dt', '<=', now()->addDays(60))
            ->whereDate('expiry_dt', '>=', now()->subDays(7))
            ->get();

        foreach ($domains as $domain) {
            if ($this->hasOpenRenewal('domain', $domain->id, (int) $domain->client)) {
                continue;
            }

            CsdRenewal::create([
                'client' => $domain->client,
                'renewal_type' => 'domain',
                'reference_id' => $domain->id,
                'title' => 'Domain renewal — ' . $domain->domain,
                'due_date' => $domain->expiry_dt,
                'status' => $this->resolveStatusForDueDate($domain->expiry_dt),
                'notes' => 'Auto-created from domain expiry on ' . Carbon::parse($domain->expiry_dt)->format('d M Y'),
                'created_by' => $user->id,
            ]);
            $created++;
        }

        return $created;
    }

    private function hasOpenRenewal(string $type, int $referenceId, int $clientId): bool
    {
        return CsdRenewal::where('renewal_type', $type)
            ->where('reference_id', $referenceId)
            ->where('client', $clientId)
            ->whereIn('status', ['upcoming', 'due'])
            ->exists();
    }

    private function guessAmcReference(int $clientId, $dueDate): ?int
    {
        $due = Carbon::parse($dueDate);

        return CsdAmcContract::where('client', $clientId)
            ->where('status', 'active')
            ->whereDate('end_date', $due->toDateString())
            ->value('id');
    }

    private function applyRenewalSideEffects(CsdRenewal $renewal, User $user): void
    {
        if ($renewal->renewal_type === 'amc' && $renewal->reference_id) {
            $amc = CsdAmcContract::find($renewal->reference_id);
            if ($amc) {
                $newEnd = CsdAmcContract::extendEndDate(Carbon::parse($amc->end_date), $amc->billing_cycle ?? CsdAmcContract::CYCLE_YEARLY);
                if ($newEnd->lte(now())) {
                    $newEnd = ($amc->billing_cycle === CsdAmcContract::CYCLE_MONTHLY)
                        ? now()->addMonth()
                        : now()->addYear();
                }

                $amc->update([
                    'end_date' => $newEnd,
                    'status' => 'active',
                ]);

                if (!$this->hasOpenRenewal('amc', $amc->id, (int) $amc->client)) {
                    CsdRenewal::create([
                        'client' => $amc->client,
                        'renewal_type' => 'amc',
                        'reference_id' => $amc->id,
                        'title' => 'AMC renewal — ' . ($renewal->client->name ?? 'Client'),
                        'due_date' => $newEnd,
                        'amount' => $amc->amount,
                        'status' => $this->resolveStatusForDueDate($newEnd),
                        'notes' => 'Next cycle scheduled after renewal on ' . now()->format('d M Y'),
                        'created_by' => $user->id,
                    ]);
                }
            }
        }

        if ($renewal->renewal_type === 'domain' && $renewal->reference_id) {
            ClientDomains::where('id', $renewal->reference_id)->update([
                'renewed' => true,
                'renewd_dt' => now()->toDateString(),
            ]);
        }
    }

    private function assertClientAccess(User $user, int $clientId): void
    {
        if (!$this->resolver->userCanAccessClient($user, $clientId)) {
            throw new \InvalidArgumentException('You cannot access this client.');
        }
    }
}
