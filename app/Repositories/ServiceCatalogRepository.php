<?php

namespace App\Repositories;

use App\Models\ServiceCatalog;

class ServiceCatalogRepository extends BaseRepository
{
    public function __construct(ServiceCatalog $model)
    {
        parent::__construct($model);
    }

    public function activeOnly()
    {
        return $this->query()->where('is_active', true)->get();
    }
}
