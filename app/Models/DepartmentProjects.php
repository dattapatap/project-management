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

    public function projectCategory()
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

    public function getWorkingDevsAttribute()
    {
        $devs = collect();
        if ($this->relationLoaded('tasks')) {
            foreach ($this->tasks as $task) {
                if ($task->user) {
                    $devs->push($task->user);
                }
                if ($task->relationLoaded('logs')) {
                    foreach ($task->logs as $log) {
                        if ($log->user) {
                            $devs->push($log->user);
                        }
                    }
                }
            }
        } else {
            $tasks = $this->tasks()->with(['user.roles', 'logs.user.roles'])->get();
            foreach ($tasks as $task) {
                if ($task->user) {
                    $devs->push($task->user);
                }
                foreach ($task->logs as $log) {
                    if ($log->user) {
                        $devs->push($log->user);
                    }
                }
            }
        }
        $uniqueDevs = new \Illuminate\Database\Eloquent\Collection($devs->unique('id')->values());
        $uniqueDevs->loadMissing('roles');
        return $uniqueDevs;
    }

    public function getDeveloperStatsAttribute()
    {
        $stats = collect();
        $tasks = $this->relationLoaded('tasks') ? $this->tasks : $this->tasks()->with(['user', 'logs.user'])->get();
        
        $devs = $this->working_devs;
        foreach ($devs as $dev) {
            $userTasks = $tasks->where('assigned_to', $dev->id);
            $totalUserTasks = $userTasks->count();
            $completedUserTasks = $userTasks->where('status', 'Completed')->count();
            
            $userLogs = $tasks->flatMap(fn($t) => $t->logs ?? collect())->where('user_id', $dev->id);
            $userTimeSpend = $userLogs->whereNotNull('time_spend')->sum('time_spend');
            
            $totalMins = round($userTimeSpend * 60);
            $h = floor($totalMins / 60);
            $m = $totalMins % 60;
            $formattedTime = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
            
            $hasActiveTimer = $userLogs->contains(fn($l) => is_null($l->endtime) && !is_null($l->starttime));
            
            $stats->push([
                'user'                  => $dev,
                'tasks_count'           => $totalUserTasks,
                'completed_tasks_count' => $completedUserTasks,
                'time_spend'            => $userTimeSpend,
                'time_formatted'        => $formattedTime,
                'is_active_now'         => $hasActiveTimer,
                'progress'              => $totalUserTasks > 0 ? round(($completedUserTasks / $totalUserTasks) * 100) : 0,
            ]);
        }
        
        return $stats->sortByDesc('time_spend')->values();
    }

    public function getTotalTimeSpentFormattedAttribute()
    {
        $totalHours = 0;
        if ($this->relationLoaded('tasks')) {
            foreach ($this->tasks as $task) {
                if ($task->relationLoaded('logs')) {
                    $totalHours += $task->logs->sum('time_spend');
                } else {
                    $totalHours += $task->logs()->sum('time_spend');
                }
            }
        } else {
            $totalHours = $this->total_working_hours;
        }
        $totalMinutes = round($totalHours * 60);
        $h = floor($totalMinutes / 60);
        $m = $totalMinutes % 60;
        return $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
    }

    public function getTimelinePerformanceAttribute()
    {
        if (!$this->end_date) {
            return [
                'type'       => 'no_deadline',
                'status'     => 'No Deadline',
                'label'      => 'No Deadline Set',
                'badge'      => 'badge-soft-secondary',
                'bg_class'   => 'bg-soft-secondary',
                'text_color' => 'text-secondary',
                'icon'       => 'mdi-calendar-blank-outline',
                'detail'     => 'No deadline date configured',
                'is_over'    => false,
                'days'       => 0
            ];
        }

        $deadline = \Carbon\Carbon::parse($this->end_date);

        if ($this->status === 'Completed') {
            $completedDate = $this->act_end_date ? \Carbon\Carbon::parse($this->act_end_date) : ($this->updated_at ?? \Carbon\Carbon::now());
            
            if ($completedDate->lte($deadline->copy()->endOfDay())) {
                $daysAhead = floor($completedDate->diffInDays($deadline, false));
                $detail = $daysAhead > 0 
                    ? "Completed {$daysAhead} day(s) ahead of deadline" 
                    : "Completed right on deadline";
                return [
                    'type'       => 'under_timeline',
                    'status'     => 'Under Timeline',
                    'label'      => 'Completed Under Timeline',
                    'badge'      => 'badge-soft-success',
                    'bg_class'   => 'bg-soft-success',
                    'text_color' => 'text-success',
                    'icon'       => 'mdi-check-decagram',
                    'detail'     => $detail,
                    'is_over'    => false,
                    'days'       => $daysAhead
                ];
            } else {
                $daysOver = ceil($deadline->diffInDays($completedDate, false));
                return [
                    'type'       => 'over_timeline',
                    'status'     => 'Over Timeline',
                    'label'      => 'Completed Over Timeline',
                    'badge'      => 'badge-soft-danger',
                    'bg_class'   => 'bg-soft-danger',
                    'text_color' => 'text-danger',
                    'icon'       => 'mdi-alert-circle',
                    'detail'     => "Delayed by {$daysOver} day(s) past deadline",
                    'is_over'    => true,
                    'days'       => $daysOver
                ];
            }
        } else {
            $now = \Carbon\Carbon::now();
            if ($now->lte($deadline->copy()->endOfDay())) {
                $daysLeft = ceil($now->diffInDays($deadline, false));
                $detail = $daysLeft == 0 
                    ? "Due today" 
                    : ($daysLeft == 1 ? "Due tomorrow (1 day left)" : "{$daysLeft} days remaining");
                return [
                    'type'       => 'on_track',
                    'status'     => 'Within Timeline',
                    'label'      => 'Within Timeline (On Track)',
                    'badge'      => 'badge-soft-primary',
                    'bg_class'   => 'bg-soft-primary',
                    'text_color' => 'text-primary',
                    'icon'       => 'mdi-clock-check-outline',
                    'detail'     => $detail,
                    'is_over'    => false,
                    'days'       => $daysLeft
                ];
            } else {
                $daysOverdue = ceil($deadline->diffInDays($now, false));
                return [
                    'type'       => 'overdue',
                    'status'     => 'Over Timeline',
                    'label'      => 'Over Timeline (Overdue)',
                    'badge'      => 'badge-soft-danger',
                    'bg_class'   => 'bg-soft-danger',
                    'text_color' => 'text-danger',
                    'icon'       => 'mdi-alert-decagram',
                    'detail'     => "Overdue by {$daysOverdue} day(s)",
                    'is_over'    => true,
                    'days'       => $daysOverdue
                ];
            }
        }
    }
}
