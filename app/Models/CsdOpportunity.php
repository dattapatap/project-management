<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsdOpportunity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'followup_date' => 'date',
    ];

    public function clients(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client');
    }

    /** @deprecated Use clients() — the client FK column conflicts with a client() relation. */
    public function client(): BelongsTo
    {
        return $this->clients();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(ClientEngagement::class, 'engagement_id');
    }
}
