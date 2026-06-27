<?php

namespace App\Services\Sales;

use App\Repositories\ServiceCatalogRepository;
use App\Models\ServiceCatalog;
use App\Models\User;

class ServiceCatalogService
{
    public function __construct(
        private ServiceCatalogRepository $catalogRepo
    ) {}

    public function getAllCatalogs()
    {
        return $this->catalogRepo->query()->orderBy('name', 'asc')->get();
    }

    public function getActiveCatalogs()
    {
        return $this->catalogRepo->activeOnly();
    }

    public function createCatalogItem(array $data, User $creator): ServiceCatalog
    {
        $data['created_by'] = $creator->id;
        $data['is_active'] = $data['is_active'] ?? true;
        return $this->catalogRepo->create($data);
    }

    public function updateCatalogItem(int $id, array $data): bool
    {
        $item = $this->catalogRepo->findOrFail($id);
        return $this->catalogRepo->update($item, $data);
    }

    public function toggleStatus(int $id): bool
    {
        $item = $this->catalogRepo->findOrFail($id);
        return $this->catalogRepo->update($item, [
            'is_active' => !$item->is_active
        ]);
    }
}
