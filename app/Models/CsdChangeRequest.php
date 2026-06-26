<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsdChangeRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(DepartmentProjects::class, 'project_id');
    }

    public function odProject(): BelongsTo
    {
        return $this->belongsTo(DepartmentProjects::class, 'od_project_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
