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

        // 1. Assignee
        $assigneeId = $task->assigned_to;
        if ($toAssignee && $assigneeId) {
            $assignee = $task->user ?: User::find($assigneeId);
            if ($assignee) $recipients->push($assignee);
        }

        // 2. Project Owner (who created/assigned the project)
        if ($task->projectid) {
            $project = $task->project ?: DepartmentProjects::find($task->projectid);
            if ($project && $project->assigned_by) {
                $owner = User::find($project->assigned_by);
                if ($owner) $recipients->push($owner);
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
                })->get();
                $recipients = $recipients->concat($tls);
            }
        }

        // 4. All Project Managers
        $pms = User::whereHas('roles', function ($q) {
            $q->where('name', 'Project-Manager');
        })->get();
        $recipients = $recipients->concat($pms);

        self::dispatch($recipients, $details);
    }

    /**
     * Notify relevant stakeholders for project-related events
     */
    public static function notifyProject(DepartmentProjects $project, array $details)
    {
        $recipients = collect();

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
            })->get();
            $recipients = $recipients->concat($tls);
        }

        // 3. Project Owner and all PMs
        if ($project->assigned_by) {
            $owner = User::find($project->assigned_by);
            if ($owner) $recipients->push($owner);
        }

        $pms = User::whereHas('roles', function ($q) {
            $q->where('name', 'Project-Manager');
        })->get();
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
