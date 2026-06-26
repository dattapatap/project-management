<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientEngagementEvent extends Model
{
    protected $guarded = [];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(ClientEngagement::class, 'engagement_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
