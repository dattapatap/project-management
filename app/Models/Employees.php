<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employees extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'user',
        'name',
        'alt_email',
        'alt_number',
        'gender',
        'dob',
        'joining_dt',
        'end_dt',
        'mem_code',
        'designation',
        'status',
        'fb',
        'insta',
        'linkedin',
        'twitter',
        'youtube',
        'github',
        'created_by',
        'updated_by',
        'last_login',
    ];

    protected $casts = [
        'dob'        => 'date:Y-m-d',
        'joining_dt' => 'date:Y-m-d',
        'end_dt'     => 'date:Y-m-d',
        'last_login' => 'datetime',
    ];

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_PROBATION = 'Probation';
    public const STATUS_NOTICE_PERIOD = 'Notice Period';
    public const STATUS_SUSPENDED = 'Suspended';
    public const STATUS_RESIGNED = 'Resigned';
    public const STATUS_TERMINATED = 'Terminated';

    public const WORKING_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_PROBATION,
        self::STATUS_NOTICE_PERIOD,
    ];

    public const SEPARATED_STATUSES = [
        self::STATUS_SUSPENDED,
        self::STATUS_RESIGNED,
        self::STATUS_TERMINATED,
    ];

    public const ALL_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_PROBATION,
        self::STATUS_NOTICE_PERIOD,
        self::STATUS_SUSPENDED,
        self::STATUS_RESIGNED,
        self::STATUS_TERMINATED,
    ];

    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    /**
     * Scope to working staff
     */
    public function scopeWorking($query)
    {
        return $query->whereIn('status', self::WORKING_STATUSES);
    }

    public function isWorking(): bool
    {
        return in_array($this->status, self::WORKING_STATUSES, true);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => [
                'label' => 'Active',
                'class' => 'badge-soft-success',
                'border' => '#10b981',
                'color' => '#065f46',
                'bg' => '#ecfdf5',
                'icon' => 'mdi-check-circle',
                'dot_class' => 'status-dot-active',
            ],
            self::STATUS_PROBATION => [
                'label' => 'Probation',
                'class' => 'badge-soft-info',
                'border' => '#3b82f6',
                'color' => '#1e40af',
                'bg' => '#eff6ff',
                'icon' => 'mdi-school-outline',
                'dot_class' => 'status-dot-probation',
            ],
            self::STATUS_NOTICE_PERIOD => [
                'label' => 'Notice Period',
                'class' => 'badge-soft-warning',
                'border' => '#f59e0b',
                'color' => '#92400e',
                'bg' => '#fffbeb',
                'icon' => 'mdi-clock-alert-outline',
                'dot_class' => 'status-dot-notice',
            ],
            self::STATUS_SUSPENDED => [
                'label' => 'Suspended',
                'class' => 'badge-soft-danger',
                'border' => '#ef4444',
                'color' => '#991b1b',
                'bg' => '#fef2f2',
                'icon' => 'mdi-pause-circle-outline',
                'dot_class' => 'status-dot-suspended',
            ],
            self::STATUS_RESIGNED => [
                'label' => 'Resigned',
                'class' => 'badge-soft-secondary',
                'border' => '#64748b',
                'color' => '#334155',
                'bg' => '#f1f5f9',
                'icon' => 'mdi-account-arrow-right-outline',
                'dot_class' => 'status-dot-resigned',
            ],
            self::STATUS_TERMINATED => [
                'label' => 'Terminated',
                'class' => 'badge-soft-dark',
                'border' => '#1e293b',
                'color' => '#0f172a',
                'bg' => '#e2e8f0',
                'icon' => 'mdi-close-octagon-outline',
                'dot_class' => 'status-dot-terminated',
            ],
            default => [
                'label' => $this->status ?: 'Inactive',
                'class' => 'badge-soft-secondary',
                'border' => '#94a3b8',
                'color' => '#475569',
                'bg' => '#f8fafc',
                'icon' => 'mdi-alert-circle-outline',
                'dot_class' => 'status-dot-inactive',
            ],
        };
    }

    public function getNextAnniversaryCarbonAttribute()
    {
        return $this->attributes['next_anniversary_carbon'] ?? $this->next_date ?? null;
    }

    public function getNextBirthdayCarbonAttribute()
    {
        return $this->attributes['next_birthday_carbon'] ?? $this->next_date ?? null;
    }

    public function getDaysUntilBirthdayAttribute()
    {
        return $this->attributes['days_until_birthday'] ?? $this->days_away ?? 0;
    }

    /**
     * Get HTML string badge for this employee's status.
     */
    public function getStatusBadgeHtmlAttribute(): string
    {
        $b = $this->status_badge;
        return '<span class="status-badge ' . e($b['class']) . '" style="border: 1px solid ' . e($b['border']) . '; color: ' . e($b['color']) . '; background: ' . e($b['bg']) . '; border-radius: 20px; padding: 3px 10px; font-weight: 600; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">'
            . '<i class="mdi ' . e($b['icon']) . '"></i> '
            . e($b['label'])
            . '</span>';
    }
}
