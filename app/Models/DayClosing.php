<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'closing_date',
        'department',
        'achieved_metrics',
        'target_status',
        'executive_remarks',
        'status',
        'approved_by',
        'approved_at',
        'tl_remarks',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'achieved_metrics' => 'array',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
