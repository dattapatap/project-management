<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientEngagement extends Model
{
    public const TYPE_INITIAL = 'initial';

    public const TYPE_UPSELL = 'upsell';

    public const TYPE_CROSS_SELL = 'cross_sell';

    public const TYPE_AMENDMENT = 'amendment';

    public const STATUS_IDENTIFIED = 'identified';

    public const STATUS_PROPOSED = 'proposed';

    public const STATUS_WON_PENDING_COMMERCIAL = 'won_pending_commercial';

    public const STATUS_COMMERCIAL_IN_PROGRESS = 'commercial_in_progress';

    public const STATUS_COMMERCIAL_CLOSED = 'commercial_closed';

    public const STATUS_IN_DELIVERY = 'in_delivery';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_LOST = 'lost';

    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = [];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'closed_value' => 'decimal:2',
        'won_at' => 'datetime',
        'commercial_closed_at' => 'datetime',
        'delivery_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function clients(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_engagement_id');
    }

    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_engagement_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_engagement_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(CsdOpportunity::class, 'opportunity_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(DepartmentProjects::class, 'project_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ClientPackages::class, 'package_id');
    }

    public function salesOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_owner_id');
    }

    public function csdOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'csd_owner_id');
    }

    public function csdTeamLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'csd_team_leader_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ClientEngagementEvent::class, 'engagement_id')->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->status));
    }
}
