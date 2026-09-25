<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalAttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'global_attendance_logs';

    public const LOCATION_OFFICE = 'Office';
    public const LOCATION_WFH = 'Work from Home';
    public const LOCATION_CLIENT = 'Client Place';

    public const LOCATIONS = [
        self::LOCATION_OFFICE,
        self::LOCATION_WFH,
        self::LOCATION_CLIENT,
    ];

    protected $fillable = [
        'userid',
        'log_date',
        'starttime',
        'endtime',
        'time_spend',
        'status',
        'work_location',
        'work_location_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
