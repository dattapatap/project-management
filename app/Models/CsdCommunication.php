<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CsdCommunication extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'communication_date' => 'datetime',
        'next_followup' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(CsdClientAssignment::class, 'assignment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
