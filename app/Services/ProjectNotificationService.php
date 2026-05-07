<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\TeamMembers;
use App\Models\DepartmentProjects;
use App\Notifications\ProjectTaskNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectNotificationService
{
    /**
     * Notify relevant stakeholders for task-related events
     */
    public static function notifyTask(Task $task, array $details, bool $toAssignee = false)
    {
        $recipients = collect();

        // Determine the target department of the task/project
        $targetDeptId = null;
        $assigneeId = $task->assigned_to;

        if ($task->projectid) {
            $project = $task->project ?: DepartmentProjects::find($task->projectid);
            if ($project) {
                if ($project->category) {
                    $category = \DB::table('project_category')->where('id', $project->category)->first();
                    if ($category) {
                        $targetDeptId = $category->dept_id;
                    }
                }
            }
        }
        if (!$targetDeptId && $assigneeId) {
            $assignee = $task->user ?: User::find($assigneeId);
            if ($assignee && $assignee->departments) {
                $targetDeptId = $assignee->departments->department;
            }
        }

        // 1. Assignee
        if ($toAssignee && $assigneeId) {
            $assignee = $task->user ?: User::find($assigneeId);
            if ($assignee) $recipients->push($assignee);
        }

        // 2. Project Owner (who created/assigned the project)
        if ($task->projectid) {
            $project = $task->project ?: DepartmentProjects::find($task->projectid);
            if ($project && $project->assigned_by) {
                $owner = User::find($project->assigned_by);
                if ($owner) {
                    // Only include the project owner if they are not a Team Leader of a different department
                    $ownerDeptId = optional($owner->departments)->department;
                    $isTL = $owner->hasRole('Team-Leader');
                    if (!$isTL || !$targetDeptId || (int)$ownerDeptId === (int)$targetDeptId) {
                        $recipients->push($owner);
                    }
                }
            }
        }

        // 3. Team Leaders of the assignee's team
        if ($assigneeId) {
            $teamMember = TeamMembers::where('user', $assigneeId)->where('status', true)->first();
            if ($teamMember) {
                $teamId = $teamMember->team;
                $tls = User::whereHas('teamMember', function ($q) use ($teamId) {
                    $q->where('team', $teamId);
                })->whereHas('roles', function ($q) {
                    $q->where('name', 'Team-Leader');
                })
                ->when($targetDeptId, function ($q) use ($targetDeptId) {
                    $q->whereHas('departments', function ($dq) use ($targetDeptId) {
                        $dq->where('department', $targetDeptId);
                    });
                })
                ->get();
                $recipients = $recipients->concat($tls);
            }
        }

        // 4. All Project Managers of the same department
        $pms = User::whereHas('roles', function ($q) {
            $q->where('name', 'Project-Manager');
        })
        ->when($targetDeptId, function ($q) use ($targetDeptId) {
            $q->whereHas('departments', function ($dq) use ($targetDeptId) {
                $dq->where('department', $targetDeptId);
            });
        })
        ->get();
        $recipients = $recipients->concat($pms);

        self::dispatch($recipients, $details);
    }

    /**
     * Notify relevant stakeholders for project-related events
     */
    public static function notifyProject(DepartmentProjects $project, array $details)
    {
        $recipients = collect();

        // Determine target department of project
        $targetDeptId = null;
        if ($project->category) {
            $category = \DB::table('project_category')->where('id', $project->category)->first();
            if ($category) {
                $targetDeptId = $category->dept_id;
            }
        }

        // 1. All users currently assigned to tasks in this project
        $assignees = User::whereHas('tasks', function ($q) use ($project) {
            $q->where('projectid', $project->id);
        })->get();
        $recipients = $recipients->concat($assignees);

        // 2. Team Leaders of all involved teams
        $teamIds = TeamMembers::whereIn('user', $assignees->pluck('id'))->pluck('team')->unique();
        if ($teamIds->isNotEmpty()) {
            $tls = User::whereHas('teamMember', function ($q) use ($teamIds) {
                $q->whereIn('team', $teamIds);
            })->whereHas('roles', function ($q) {
                $q->where('name', 'Team-Leader');
            })
            ->when($targetDeptId, function ($q) use ($targetDeptId) {
                $q->whereHas('departments', function ($dq) use ($targetDeptId) {
                    $dq->where('department', $targetDeptId);
                });
            })
            ->get();
            $recipients = $recipients->concat($tls);
        }

        // 3. Project Owner (who created/assigned the project)
        if ($project->assigned_by) {
            $owner = User::find($project->assigned_by);
            if ($owner) {
                // Only include the owner if they are not a Team Leader of a different department
                $ownerDeptId = optional($owner->departments)->department;
                $isTL = $owner->hasRole('Team-Leader');
                if (!$isTL || !$targetDeptId || (int)$ownerDeptId === (int)$targetDeptId) {
                    $recipients->push($owner);
                }
            }
        }

        // 4. Assigned Team Leader (direct assignment to project)
        if ($project->assigned_to) {
            $assignedTL = User::find($project->assigned_to);
            if ($assignedTL) {
                $recipients->push($assignedTL);
            }
        }

        // 5. Team Leaders of the assigned team (via team_projects table)
        $teamProjects = \DB::table('team_projects')->where('projectid', $project->id)->pluck('teamid')->unique();
        if ($teamProjects->isNotEmpty()) {
            $teamTLs = User::whereHas('teamMember', function ($q) use ($teamProjects) {
                $q->whereIn('team', $teamProjects);
            })->whereHas('roles', function ($q) {
                $q->where('name', 'Team-Leader');
            })
            ->when($targetDeptId, function ($q) use ($targetDeptId) {
                $q->whereHas('departments', function ($dq) use ($targetDeptId) {
                    $dq->where('department', $targetDeptId);
                });
            })
            ->get();
            $recipients = $recipients->concat($teamTLs);
        }

        // 6. All Project Managers of the same department
        $pms = User::whereHas('roles', function ($q) {
            $q->where('name', 'Project-Manager');
        })
        ->when($targetDeptId, function ($q) use ($targetDeptId) {
            $q->whereHas('departments', function ($dq) use ($targetDeptId) {
                $dq->where('department', $targetDeptId);
            });
        })
        ->get();
        $recipients = $recipients->concat($pms);

        self::dispatch($recipients, $details);
    }

    /**
     * Dispatch notification to unique recipients, excluding the current user
     */
    private static function dispatch($recipients, array $details)
    {
        $currentUserId = Auth::id();
        $toNotify = $recipients->unique('id')->filter(function ($user) use ($currentUserId) {
            return $user && (int)$user->id !== (int)$currentUserId;
        });

        foreach ($toNotify as $user) {
            try {
                $user->notify(new ProjectTaskNotification($details));
            } catch (\Exception $e) {
                Log::error("Notification failed for user {$user->id}: " . $e->getMessage());
            }
        }
    }
}
