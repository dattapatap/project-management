<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CsdAmcContract extends Model
{
    public const CYCLE_MONTHLY = 'monthly';

    public const CYCLE_YEARLY = 'yearly';

    public const REMINDER_DAYS = [
        self::CYCLE_MONTHLY => 5,
        self::CYCLE_YEARLY => 30,
    ];

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(DepartmentProjects::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reminderDays(): int
    {
        return self::REMINDER_DAYS[$this->billing_cycle] ?? self::REMINDER_DAYS[self::CYCLE_YEARLY];
    }

    public function isExpiringSoon(?Carbon $today = null): bool
    {
        $today = $today ?? Carbon::today();
        $daysLeft = $today->diffInDays($this->end_date, false);

        return $daysLeft >= 0 && $daysLeft <= $this->reminderDays();
    }

    public function documentUrl(): ?string
    {
        if (!$this->document_path || !Storage::disk('public')->exists($this->document_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->document_path);
    }

    public static function computeEndDate(string $startDate, string $billingCycle): string
    {
        $start = Carbon::parse($startDate);

        return $billingCycle === self::CYCLE_MONTHLY
            ? $start->copy()->addMonth()->subDay()->toDateString()
            : $start->copy()->addYear()->subDay()->toDateString();
    }

    public static function extendEndDate(Carbon $currentEnd, string $billingCycle): Carbon
    {
        return $billingCycle === self::CYCLE_MONTHLY
            ? $currentEnd->copy()->addMonth()
            : $currentEnd->copy()->addYear();
    }
}
