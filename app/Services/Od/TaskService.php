<?php

namespace App\Services\Od;

use App\Http\Controllers\DepartmentProjectHistoryController;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use App\Models\UserActivity;
use App\Repositories\TaskRepository;
use App\Services\ProjectNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepo,
    ) {}

    /* ------------------------------------------------------------------
     *  TASK CREATION
     * ------------------------------------------------------------------ */

    /**
     * Create a new task within a project.
     *
     * @return array{success: bool, message: string, task?: Task}
     */
    public function createTask(array $data, User $creator): array
    {
        return DB::transaction(function () use ($data, $creator) {
            $project = DepartmentProjects::find($data['project_id']);

            $task = new Task();
            $task->projectid    = $data['project_id'];
            $task->created_by   = $creator->id;
            $task->title        = $data['title'];
            $task->description  = $data['description'] ?? null;
            $task->status       = 'ToDo';
            $task->priority     = $data['priority'] ?? 'Medium';
            $task->startdate    = Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
            $task->enddate      = Carbon::parse($data['end_date'])->format('Y-m-d H:i:s');
            $task->assigned_to  = $data['assigned_to'];
            $task->save();

            UserActivity::log('Task Created', "Added new task '{$task->title}' to project '{$project->project_name}'");

            // If project was completed, revert it to InProgress
            if ($project && $project->status === 'Completed') {
                $project->status = 'InProgress';
                $project->save();
                DepartmentProjectHistoryController::store(
                    $project,
                    "Project reopened automatically due to new task creation: {$task->title}",
                    $creator->id
                );
            }

            $comment = "New Task has been assigned: '{$task->title}' with deadline " . Carbon::parse($task->enddate)->format('d M, Y');
            DepartmentProjectHistoryController::store($task, $comment, $data['assigned_to']);

            ProjectNotificationService::notifyTask($task, [
                'category' => 'Task',
                'header'   => 'New Task Assigned',
                'body'     => $comment,
                'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
            ], true);

            // Handle document uploads
            $this->handleTaskDocuments($task, $data['documents'] ?? [], $creator);

            return ['success' => true, 'message' => 'New Task Created', 'task' => $task];
        });
    }

    /* ------------------------------------------------------------------
     *  TASK STATUS MANAGEMENT
     * ------------------------------------------------------------------ */

    /**
     * Change task status with authorization check.
     *
     * @return array{success: bool, message: string}
     */
    public function changeStatus(Task $task, User $user, string $newStatus, ?string $actStartDate = null): array
    {
        if (!$this->canModifyTask($task, $user)) {
            return ['success' => false, 'message' => 'Unauthorized! You can only update your own tasks.'];
        }

        // Rule 1: Once moved to InProgress or Completed, cannot go back to ToDo
        if (in_array($task->status, ['InProgress', 'Completed']) && $newStatus === 'ToDo') {
            return ['success' => false, 'message' => 'Task cannot be moved back to ToDo once it is in progress or completed.'];
        }

        // Rule 2: Only Admin, BM, PM, TL can move Completed back to InProgress
        if ($task->status === 'Completed' && $newStatus === 'InProgress') {
            if (!$user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader'])) {
                return ['success' => false, 'message' => 'Only administrators and managers can move completed tasks back to In Progress.'];
            }
        }

        // Rule 3: Before completing, must have at least one time log
        if ($newStatus === 'Completed') {
            if ($task->logs()->count() === 0) {
                return ['success' => false, 'message' => 'Cannot complete task. You must add at least one work time log before completing the task.'];
            }
        }

        // Rule 4: Cannot transition directly from ToDo to Completed
        if ($task->status === 'ToDo' && $newStatus === 'Completed') {
            return ['success' => false, 'message' => 'Task must be in In Progress status before it can be Completed.'];
        }

        $this->applyStatusTransition($task, $newStatus, $actStartDate);
        $task->save();

        UserActivity::log('Task Status Updated', "Changed status of task '{$task->title}' to '{$task->status}'");

        $comment = "Task status updated as {$task->status} by {$user->name}";
        DepartmentProjectHistoryController::store($task, $comment, $user->id);

        ProjectNotificationService::notifyTask($task, [
            'category' => 'Task',
            'header'   => $task->status === 'Completed' ? 'Task Completed' : 'Task Status Updated',
            'body'     => "Task '{$task->title}' is now {$task->status} (updated by {$user->name})",
            'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
        ], true);

        return ['success' => true, 'message' => 'Task Status Updated'];
    }

    /**
     * Move task between Kanban columns (drag-and-drop).
     *
     * @return array{success: bool, message: string}
     */
    public function moveTask(Task $task, User $user, string $newStatus): array
    {
        if (!$this->canModifyTask($task, $user)) {
            return ['success' => false, 'message' => 'Unauthorized! You can only move your own tasks.'];
        }

        // Rule 1: Once moved to InProgress or Completed, cannot go back to ToDo
        if (in_array($task->status, ['InProgress', 'Completed']) && $newStatus === 'ToDo') {
            return ['success' => false, 'message' => 'Task cannot be moved back to ToDo once it is in progress or completed.'];
        }

        // Rule 2: Only Admin, BM, PM, TL can move Completed back to InProgress
        if ($task->status === 'Completed' && $newStatus === 'InProgress') {
            if (!$user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader'])) {
                return ['success' => false, 'message' => 'Only administrators and managers can move completed tasks back to In Progress.'];
            }
        }

        // Rule 3: Before completing, must have at least one time log
        if ($newStatus === 'Completed') {
            if ($task->logs()->count() === 0) {
                return ['success' => false, 'message' => 'Cannot complete task. You must add at least one work time log before completing the task.'];
            }
        }

        // Rule 4: Cannot transition directly from ToDo to Completed
        if ($task->status === 'ToDo' && $newStatus === 'Completed') {
            return ['success' => false, 'message' => 'Task must be in In Progress status before it can be Completed.'];
        }

        return DB::transaction(function () use ($task, $user, $newStatus) {
            $oldStatus = $task->status;
            $this->applyStatusTransition($task, $newStatus);
            $task->save();

            UserActivity::log('Task Moved', "Moved task '{$task->title}' from '{$oldStatus}' to '{$newStatus}' via Kanban");

            $comment = "Task moved from {$oldStatus} to {$newStatus} by {$user->name}";
            DepartmentProjectHistoryController::store($task, $comment, $user->id);

            ProjectNotificationService::notifyTask($task, [
                'category' => 'Task',
                'header'   => 'Task Moved',
                'body'     => "Task '{$task->title}' moved to {$newStatus}",
                'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
            ], true);

            return ['success' => true, 'message' => "Task moved to {$newStatus}"];
        });
    }

    /* ------------------------------------------------------------------
     *  TASK UPDATES
     * ------------------------------------------------------------------ */

    /**
     * Update task details.
     *
     * @return array{success: bool, message: string}
     */
    public function updateTask(Task $task, array $data, User $user): array
    {
        return DB::transaction(function () use ($task, $data, $user) {
            $task->title       = $data['title'];
            $task->description = $data['description'] ?? $task->description;
            $task->priority    = $data['priority'] ?? $task->priority;
            $task->startdate   = Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
            $task->enddate     = Carbon::parse($data['end_date'])->format('Y-m-d H:i:s');
            $task->assigned_to = $data['assigned_to'];
            $task->save();

            DepartmentProjectHistoryController::store($task, "Task updated by {$user->name}", $data['assigned_to']);

            ProjectNotificationService::notifyTask($task, [
                'category' => 'Task',
                'header'   => 'Task Details Updated',
                'body'     => "Task '{$task->title}' has been updated by {$user->name}. Deadline: " . Carbon::parse($task->enddate)->format('d M, Y'),
                'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
            ], true);

            return ['success' => true, 'message' => 'Task Updated'];
        });
    }

    /**
     * Update task progress percentage.
     *
     * @return array{success: bool, message: string}
     */
    public function updateProgress(Task $task, int $progress, User $user): array
    {
        $task->progress = $progress;
        $task->save();

        UserActivity::log('Task Progress Updated', "Updated progress of task '{$task->title}' to {$progress}%");

        DepartmentProjectHistoryController::store($task, "Task progress updated to {$progress}% by {$user->name}", $user->id);

        ProjectNotificationService::notifyTask($task, [
            'category' => 'Task',
            'header'   => 'Task Progress Update',
            'body'     => "Task '{$task->title}' progress is now {$progress}%",
            'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
        ], true);

        return ['success' => true, 'message' => 'Task Progress Updated'];
    }

    /* ------------------------------------------------------------------
     *  WORK LOGGING
     * ------------------------------------------------------------------ */

    /**
     * Add a time-tracked work log entry for a task.
     *
     * @return array{success: bool, message: string}
     */
    public function addWorkLog(array $data, User $user): array
    {
        $taskLog = new TaskLog();
        $taskLog->taskid  = $data['task_id'];
        $taskLog->userid  = $user->id;
        $taskLog->log_date = Carbon::parse($data['log_date'])->format('Y-m-d');
        $taskLog->log_description = $data['description'];
        $taskLog->time_spend = $data['time_spend'];

        // Flexible time parsing
        try {
            $taskLog->starttime = Carbon::parse($data['start_time'])->format('H:i:s');
            $taskLog->endtime   = Carbon::parse($data['end_time'])->format('H:i:s');
        } catch (\Exception $e) {
            $taskLog->starttime = Carbon::createFromFormat('h:i A', $data['start_time'])->format('H:i:s');
            $taskLog->endtime   = Carbon::createFromFormat('h:i A', $data['end_time'])->format('H:i:s');
        }

        $taskLog->save();

        $task = Task::find($data['task_id']);
        UserActivity::log('Task Work Logged', "Logged {$taskLog->time_spend} hours for task '{$task->title}'");

        $comment = "Logged {$taskLog->time_spend} hours for task '{$task->title}' by {$user->name}";
        DepartmentProjectHistoryController::store($task, $comment, $user->id);

        ProjectNotificationService::notifyTask($task, [
            'category' => 'Log',
            'header'   => 'New Work Log',
            'body'     => "{$user->name} logged {$taskLog->time_spend}h on task '{$task->title}'",
            'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
        ]);

        return ['success' => true, 'message' => 'Task Log Added'];
    }

    /* ------------------------------------------------------------------
     *  NUDGE
     * ------------------------------------------------------------------ */

    /**
     * Send a progress nudge notification to the task assignee.
     *
     * @return array{success: bool, message: string}
     */
    public function nudge(Task $task, User $requestor): array
    {
        $assignedUser = $task->user;
        if (!$assignedUser) {
            return ['success' => false, 'message' => 'No user assigned to this task.'];
        }

        $assignedUser->notify(new \App\Notifications\TaskNudgeNotification($task, $requestor));

        $comment = "Progress update requested by {$requestor->name}";
        DepartmentProjectHistoryController::store($task, $comment, $requestor->id);

        return ['success' => true, 'message' => 'Nudge sent successfully!'];
    }

    /* ------------------------------------------------------------------
     *  PRIVATE HELPERS
     * ------------------------------------------------------------------ */

    /**
     * Check if a user is authorized to modify a task.
     */
    private function canModifyTask(Task $task, User $user): bool
    {
        $isAssignee   = $task->assigned_to == $user->id;
        $isManagement = $user->hasRole(['Team-Leader', 'Project-Manager', 'Admin', 'Branch-Manager']);
        return $isAssignee || $isManagement;
    }

    /**
     * Apply status transition rules (timestamps, progress).
     */
    private function applyStatusTransition(Task $task, string $newStatus, ?string $actStartDate = null): void
    {
        $task->status = $newStatus;

        if ($newStatus === 'Completed') {
            $task->progress    = 100;
            $task->act_enddate = Carbon::now()->format('Y-m-d H:i:s');
        } elseif ($newStatus === 'InProgress' && !$task->act_startdate) {
            if ($actStartDate) {
                try {
                    $task->act_startdate = Carbon::parse($actStartDate)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $task->act_startdate = Carbon::createFromFormat('d/m/Y h:i A', $actStartDate)->format('Y-m-d H:i:s');
                }
            } else {
                $task->act_startdate = Carbon::now()->format('Y-m-d H:i:s');
            }
        }
    }

    /**
     * Handle file uploads for a task.
     */
    private function handleTaskDocuments(Task $task, array $files, User $user): void
    {
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $fileName     = time() . '_' . $originalName;
            $path         = $file->storeAs('documents/tasks/' . $task->id, $fileName, 'local');

            \App\Models\Document::create([
                'documentable_id'   => $task->id,
                'documentable_type' => Task::class,
                'file_name'         => $fileName,
                'original_name'     => $originalName,
                'file_path'         => $path,
                'file_type'         => $file->getClientOriginalExtension(),
                'file_size'         => $file->getSize(),
                'user_id'           => $user->id,
            ]);
        }
    }
}
