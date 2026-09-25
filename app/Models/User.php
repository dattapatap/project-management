<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    use SoftDeletes;

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

    protected $with = ['departments'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'code',
        'mobile',
        'designation',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];


    public function emp()
    {
        return $this->hasOne(Employees::class, 'user', 'id');
    }

    public function departments()
    {
        return $this->hasOne(UserDepartment::class, 'user', 'id');
    }

    public function branch()
    {
        return $this->hasOne(UserBranch::class, 'user', 'id');
    }

    public function userBranch()
    {
        return $this->hasOne(UserBranch::class, 'user', 'id');
    }



    public function receivesBroadcastNotificationsOn()
    {
        return 'post_like.' . $this->id;
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to', 'id');
    }

    public function completedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to', 'id')->where('status', 'Completed');
    }

    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class, 'userid', 'id');
    }

    public function clients()
    {
        return $this->hasMany(Clients::class, 'ref_user', 'id');
    }

    public function teamMember()
    {
        return $this->hasOne(TeamMembers::class, 'user', 'id');
    }

    public function csdAssignments()
    {
        return $this->hasMany(CsdClientAssignment::class, 'assigned_to', 'id');
    }

    public function dayClosings()
    {
        return $this->hasMany(DayClosing::class, 'user_id', 'id');
    }

    public function isGlobalAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isBranchManager(): bool
    {
        return $this->hasRole('Branch-Manager');
    }

    public function hasBranchWideAccess(): bool
    {
        return $this->hasRole(['Admin', 'Branch-Manager']);
    }

    /**
     * Only Admin and Branch Manager can set sales targets.
     */
    public function canAssignTarget(): bool
    {
        return $this->hasRole(['Admin', 'Branch-Manager']);
    }

    public function branchId(): ?int
    {
        return app(\App\Services\BranchScopeService::class)->getBranchId($this);
    }

    public function globalAttendanceLogs()
    {
        return $this->hasMany(GlobalAttendanceLog::class, 'userid', 'id');
    }

    public function activeGlobalTimer()
    {
        return $this->globalAttendanceLogs()
            ->whereNull('endtime')
            ->first();
    }

    /**
     * Scope to working staff (can log in, participate in operations, targets, celebrations)
     */
    public function scopeWorking($query)
    {
        return $query->whereIn('status', self::WORKING_STATUSES);
    }

    /**
     * Check if user is in an active working state.
     */
    public function isWorking(): bool
    {
        return in_array($this->status, self::WORKING_STATUSES, true);
    }

    /**
     * Check if user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if user is separated / ex-staff.
     */
    public function isSeparated(): bool
    {
        return in_array($this->status, self::SEPARATED_STATUSES, true);
    }

    /**
     * Get UI badge metadata for this user's status.
     */
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

    /**
     * Get HTML string badge for this user's status.
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

