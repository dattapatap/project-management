<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalAttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'global_attendance_logs';

    protected $fillable = [
        'userid',
        'log_date',
        'starttime',
        'endtime',
        'time_spend',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
