<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsdClientAssignment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'handoff_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(DepartmentProjects::class, 'project_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(CsdCommunication::class, 'assignment_id');
    }

    public function latestOpenUpsellEngagement(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ClientEngagement::class, 'client_id', 'client')
            ->whereIn('engagement_type', [ClientEngagement::TYPE_UPSELL, ClientEngagement::TYPE_CROSS_SELL])
            ->whereIn('status', [
                ClientEngagement::STATUS_WON_PENDING_COMMERCIAL,
                ClientEngagement::STATUS_COMMERCIAL_IN_PROGRESS,
                ClientEngagement::STATUS_COMMERCIAL_CLOSED,
                ClientEngagement::STATUS_IN_DELIVERY,
            ])
            ->latest('id');
    }
}
