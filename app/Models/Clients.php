<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clients extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    //Scopes
    public function scopeStatus(Builder $query, $status): void
    {
        $query->whereIn('status', $status);
    }
    public function scopeStatusNotIn(Builder $query, $status): void
    {
        $query->whereNotIn('status', $status);
    }

    public function scopeSearchCategory(Builder $query, $field, $value): void
    {
        $query->where($field, $value);
    }

    public function scopeFilterStatus(Builder $query, $category, $historyType): void
    {
        if ($category == 'All') {
            if ($historyType == 'STS')
                $query->whereIn('status', ['Fresh', 'Followup', 'Meeting Fixed', 'Not Interested']);
            else
                $query->whereIn('status', ['Hot Prespective', 'Warm Prespective', 'Matured', 'Not Interested']);
        } else {
            $query->where('status',  $category);
        }
    }


    //End Scopes


    public function referral(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'ref_user');
    }

    public function creator(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by')->withTrashed();
    }

    public function telereferral(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'tele_ref_user')->withTrashed();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ClientHistory::class, 'client', 'id');
    }

    public function history(): HasOne
    {
        return $this->hasOne(ClientHistory::class, 'client', 'id');
    }

    public function docs(): HasMany
    {
        return $this->hasMany(ClientDocs::class, 'client', 'id')->withTrashed();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(DepartmentProjects::class, 'client', 'id');
    }

    public function package(): HasMany
    {
        return $this->hasMany(ClientPackages::class, 'client', 'id');
    }

    public function csdAssignment(): HasMany
    {
        return $this->hasMany(CsdClientAssignment::class, 'client', 'id');
    }

    public function csdContacts(): HasMany
    {
        return $this->hasMany(CsdContactPerson::class, 'client', 'id');
    }
}
