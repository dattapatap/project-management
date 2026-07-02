<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentProjects extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'projectid');
    }

    public function completedTask()
    {
        return $this->hasMany(Task::class, 'projectid')->where('status', 'Completed');
    }

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'category', 'id');
    }

    public function project_team()
    {
        return $this->hasOne(TeamProject::class, 'projectid', 'id');
    }

    public function sub_categories()
    {
        return $this->belongsTo(ProjectSubCategory::class, 'sub_category', 'id');
    }



    public function clients()
    {
        return $this->belongsTo(Clients::class, 'client', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }

    public function histories()
    {
        return $this->morphMany(DepartmentProjectHistory::class, 'histories');
    }

    public function getTotalWorkingHoursAttribute()
    {
        return $this->tasks()->with('logs')->get()->sum(function($task) {
            return $task->logs->sum('time_spend');
        });
    }
    public function getProgressAttribute()
    {
        if (isset($this->attributes['tasks_count'])) {
            $total = $this->attributes['tasks_count'];
        } elseif ($this->relationLoaded('tasks')) {
            $total = $this->tasks->count();
        } else {
            $total = $this->tasks()->count();
        }

        if ($total > 0) {
            if (isset($this->attributes['completed_task_count'])) {
                $completed = $this->attributes['completed_task_count'];
            } elseif ($this->relationLoaded('completedTask')) {
                $completed = $this->completedTask->count();
            } else {
                $completed = $this->completedTask()->count();
            }
            return round(($completed / $total) * 100);
        }
        return 0;
    }

    public function getIsOverdueAttribute()
    {
        if ($this->status != 'Completed' && $this->end_date) {
            return \Carbon\Carbon::parse($this->end_date)->isPast();
        }
        return false;
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
