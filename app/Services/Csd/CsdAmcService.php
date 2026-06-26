<?php

namespace App\Services\Csd;

use App\Models\CsdAmcContract;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CsdAmcService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user, ?string $billingCycle = null): Builder
    {
        $query = CsdAmcContract::with(['client', 'project'])->latest();

        $this->scope->applyClientScope($query, $user);

        if ($billingCycle && $billingCycle !== 'all') {
            $query->where('billing_cycle', $billingCycle);
        }

        return $query;
    }

    public function create(array $data, User $user, ?UploadedFile $document = null): CsdAmcContract
    {
        $this->assertClientAccess($user, (int) $data['client']);

        if (empty($data['end_date']) && !empty($data['start_date']) && !empty($data['billing_cycle'])) {
            $data['end_date'] = CsdAmcContract::computeEndDate($data['start_date'], $data['billing_cycle']);
        }

        $contract = CsdAmcContract::create([
            ...$data,
            'created_by' => $user->id,
        ]);

        if ($document) {
            $this->storeDocument($contract, $document);
        }

        return $contract->fresh();
    }

    public function update(CsdAmcContract $amc, array $data, ?UploadedFile $document = null, bool $removeDocument = false): CsdAmcContract
    {
        if ($removeDocument) {
            $this->deleteDocument($amc);
            $data['document_path'] = null;
            $data['document_name'] = null;
        }

        if ($document) {
            $this->deleteDocument($amc);
            $this->storeDocument($amc, $document);
        }

        $amc->update($data);

        return $amc->fresh();
    }

    public function storeDocument(CsdAmcContract $amc, UploadedFile $file): void
    {
        $name = 'amc_' . $amc->id . '_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $ext = $file->getClientOriginalExtension();
        $filename = $ext ? $name . '.' . $ext : $name;
        $path = $file->storeAs('csd/amc', $filename, 'public');

        $amc->update([
            'document_path' => $path,
            'document_name' => $file->getClientOriginalName(),
        ]);
    }

    public function deleteDocument(CsdAmcContract $amc): void
    {
        if ($amc->document_path && Storage::disk('public')->exists($amc->document_path)) {
            Storage::disk('public')->delete($amc->document_path);
        }
    }

    private function assertClientAccess(User $user, int $clientId): void
    {
        if (!$this->resolver->userCanAccessClient($user, $clientId)) {
            throw new \InvalidArgumentException('You cannot access this client.');
        }
    }
}
