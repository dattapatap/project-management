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

    protected $with = ['departments'];

    protected $fillable = [
        'name',
        'email',
        'password',
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
        return $this->belongsTo(UserBranch::class, 'user', 'id');
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
}
