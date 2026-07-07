<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }
    public function createdby()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function project()
    {
        return $this->belongsTo(DepartmentProjects::class, 'projectid', 'id');
    }

    public function logs()
    {
        return $this->hasMany(TaskLog::class, 'taskid', 'id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'taskid', 'id');
    }

    public function histories()
    {
        return $this->morphMany(DepartmentProjectHistory::class, 'histories');
    }

    public function getTotalTimeAttribute()
    {
        if ($this->relationLoaded('logs')) {
            return $this->logs->whereNotNull('time_spend')->sum('time_spend');
        }

        return $this->logs()->whereNotNull('time_spend')->sum('time_spend');
    }

    /**
     * Get the active (running) timer for the authenticated user.
     */
    public function getActiveTimerAttribute()
    {
        return $this->logs()
            ->where('userid', auth()->id())
            ->whereNull('endtime')
            ->first();
    }

    /**
     * Get the active (running) timer for a specific user.
     */
    public function activeTimerForUser(int $userId): ?TaskLog
    {
        return $this->logs()
            ->where('userid', $userId)
            ->whereNull('endtime')
            ->first();
    }
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

}
