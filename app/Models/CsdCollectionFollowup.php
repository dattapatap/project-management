<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsdCollectionFollowup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'followup_date' => 'date',
        'commitment_date' => 'date',
        'amount_due' => 'decimal:2',
        'commitment_amount' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ClientPackages::class, 'package_id');
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
