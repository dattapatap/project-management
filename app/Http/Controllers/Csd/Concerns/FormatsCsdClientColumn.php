<?php

namespace App\Http\Controllers\Csd\Concerns;

use Yajra\DataTables\EloquentDataTable;

trait FormatsCsdClientColumn
{
    protected function withCsdClientName(EloquentDataTable $table): EloquentDataTable
    {
        return $table
            ->addColumn('client_name', function ($row) {
                $client = null;
                if ($row->relationLoaded('clients')) {
                    $client = $row->getRelation('clients');
                } elseif ($row->relationLoaded('client')) {
                    $related = $row->getRelation('client');
                    $client = $related instanceof \App\Models\Clients ? $related : null;
                }

                return e($client?->name ?? '-');
            })
            ->filterColumn('client_name', function ($query, $keyword) {
                $relation = method_exists($query->getModel(), 'clients') ? 'clients' : 'client';
                $query->whereHas($relation, fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            });
    }
}
