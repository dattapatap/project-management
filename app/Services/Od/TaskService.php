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

            // If project was Completed or ToDo, move it to InProgress automatically
            if ($project && $project->status !== 'InProgress') {
                $oldStatus = $project->status;
                $project->status = 'InProgress';
                $project->save();
                
                $reason = $oldStatus === 'Completed' 
                    ? "Project reopened automatically due to new task creation: {$task->title}"
                    : "Project automatically started due to new task creation: {$task->title}";

                DepartmentProjectHistoryController::store(
                    $project,
                    $reason,
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

        // Rule 5: Project must be started before task can be moved to In Progress
        if ($newStatus === 'InProgress') {
            $project = $task->project;
            if ($project && $project->status === 'ToDo') {
                if (!$user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader'])) {
                    return [
                        'success' => false,
                        'message' => 'The project is still not started. Please ask your Team Leader to move the project from ToDo to In Progress first.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'The project is still in ToDo state. Please move the project from ToDo to In Progress.'
                    ];
                }
            }
        }

        $this->applyStatusTransition($task, $newStatus, $actStartDate);
        $task->save();

        // Auto-start timer when moving to InProgress
        if ($newStatus === 'InProgress') {
            $activeTimer = $task->activeTimerForUser($user->id);
            if (!$activeTimer) {
                $this->startTimer($task, $user);
            }
        }

        // Auto-stop timer when completing
        if ($newStatus === 'Completed') {
            $activeTimer = $task->activeTimerForUser($user->id);
            if ($activeTimer) {
                $this->pauseTimer($task, $user, 'Auto-stopped on task completion');
            }
        }

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

        // Rule 5: Project must be started before task can be moved to In Progress
        if ($newStatus === 'InProgress') {
            $project = $task->project;
            if ($project && $project->status === 'ToDo') {
                if (!$user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader'])) {
                    return [
                        'success' => false,
                        'message' => 'The project is still not started. Please ask your Team Leader to move the project from ToDo to In Progress first.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'The project is still in ToDo state. Please move the project from ToDo to In Progress.'
                    ];
                }
            }
        }

        return DB::transaction(function () use ($task, $user, $newStatus) {
            $oldStatus = $task->status;
            $this->applyStatusTransition($task, $newStatus);
            $task->save();

            // Auto-start timer when moving to InProgress
            if ($newStatus === 'InProgress') {
                $activeTimer = $task->activeTimerForUser($user->id);
                if (!$activeTimer) {
                    $this->startTimer($task, $user);
                }
            }

            // Auto-stop timer when completing
            if ($newStatus === 'Completed') {
                $activeTimer = $task->activeTimerForUser($user->id);
                if ($activeTimer) {
                    $this->pauseTimer($task, $user, 'Auto-stopped on task completion via Kanban');
                }
            }

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
     *  TIMER MANAGEMENT
     * ------------------------------------------------------------------ */

    /**
     * Start a timer for a task.
     *
     * @return array{success: bool, message: string, timer?: TaskLog}
     */
    public function startTimer(Task $task, User $user): array
    {
        if (!$this->canModifyTask($task, $user)) {
            return ['success' => false, 'message' => 'Unauthorized! You can only start timers on your own tasks.'];
        }

        if ($task->status === 'Completed') {
            return ['success' => false, 'message' => 'Cannot start a timer on a completed task.'];
        }

        // Project must be started before starting the timer
        $project = $task->project;
        if ($project && $project->status === 'ToDo') {
            if (!$user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader'])) {
                return [
                    'success' => false,
                    'message' => 'The project is still not started. Please ask your Team Leader to move the project from ToDo to In Progress first.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'The project is still in ToDo state. Please move the project from ToDo to In Progress.'
                ];
            }
        }

        // Check if there is already an active timer for this user on this task
        $activeTimer = $task->activeTimerForUser($user->id);
        if ($activeTimer) {
            return ['success' => false, 'message' => 'Timer is already running for this task.'];
        }

        return DB::transaction(function () use ($task, $user) {
            // Ensure the Global Timer is running
            app(GlobalTimerService::class)->ensureGlobalTimerIsRunning($user);

            // Auto-pause any other active timer for the same user across all tasks
            $otherActiveTimer = TaskLog::where('userid', $user->id)
                ->whereNull('endtime')
                ->where('taskid', '!=', $task->id)
                ->first();

            if ($otherActiveTimer) {
                $otherTask = Task::find($otherActiveTimer->taskid);
                if ($otherTask) {
                    $this->pauseTimer($otherTask, $user, 'Auto-paused when another task timer was started');
                }
            }

            $now = Carbon::now();

            $taskLog = new TaskLog();
            $taskLog->taskid = $task->id;
            $taskLog->userid = $user->id;
            $taskLog->log_date = $now->format('Y-m-d');
            $taskLog->starttime = $now->format('H:i:s');
            $taskLog->endtime = null;
            $taskLog->time_spend = null;
            $taskLog->log_description = null;
            $taskLog->save();

            // Auto-transition task from ToDo to InProgress
            if ($task->status === 'ToDo') {
                $this->applyStatusTransition($task, 'InProgress');
                $task->save();

                UserActivity::log('Task Status Updated', "Changed status of task '{$task->title}' to 'InProgress' automatically via Timer Start");
                $autoComment = "Task status updated to InProgress automatically by starting timer by {$user->name}";
                DepartmentProjectHistoryController::store($task, $autoComment, $user->id);
            }

            UserActivity::log('Timer Started', "Started timer for task '{$task->title}'");
            DepartmentProjectHistoryController::store($task, "Timer started by {$user->name}", $user->id);

            return ['success' => true, 'message' => 'Timer started', 'timer' => $taskLog];
        });
    }

    /**
     * Pause/Stop a running timer for a task.
     *
     * @return array{success: bool, message: string}
     */
    public function pauseTimer(Task $task, User $user, ?string $description = null): array
    {
        $activeTimer = $task->activeTimerForUser($user->id);
        if (!$activeTimer) {
            return ['success' => false, 'message' => 'No active timer found for this task.'];
        }

        $now = Carbon::now();
        $startedAt = Carbon::parse($activeTimer->log_date . ' ' . $activeTimer->starttime);

        // 9:00 PM cap on the starting day
        $capTime = Carbon::parse($activeTimer->log_date . ' 21:00:00');

        if ($now->gt($capTime)) {
            $endTime = $capTime;
        } else {
            $endTime = $now;
        }

        // If the task was started after 9:00 PM, cap the end time to the start time (0 hours spend)
        if ($startedAt->gt($endTime)) {
            $endTime = $startedAt;
        }

        // Calculate duration in hours
        $durationSeconds = $startedAt->diffInSeconds($endTime);
        $durationHours = round($durationSeconds / 3600, 2);
        if ($durationHours < 0.01 && $durationSeconds > 0) {
            $durationHours = 0.01;
        }

        $activeTimer->endtime = $endTime->format('H:i:s');
        $activeTimer->time_spend = $durationHours;
        $activeTimer->log_description = $description;
        $activeTimer->save();

        UserActivity::log('Timer Paused', "Paused timer for task '{$task->title}'");
        DepartmentProjectHistoryController::store($task, "Timer paused by {$user->name}", $user->id);

        ProjectNotificationService::notifyTask($task, [
            'category' => 'Log',
            'header'   => 'Timer Paused',
            'body'     => "{$user->name} paused timer on task '{$task->title}'",
            'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
        ]);

        return ['success' => true, 'message' => "Timer paused. Logged {$durationHours} hours."];
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
