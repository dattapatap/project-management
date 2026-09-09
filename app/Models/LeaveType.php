<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'code',
        'days_allowed_per_year',
        'monthly_limit',
        'is_monthly_accrual',
        'is_paid',
        'status',
        'description',
    ];

    protected $casts = [
        'days_allowed_per_year' => 'integer',
        'monthly_limit' => 'float',
        'is_monthly_accrual' => 'boolean',
        'is_paid' => 'boolean',
        'status' => 'boolean',
    ];

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class, 'leave_type_id');
    }
}
