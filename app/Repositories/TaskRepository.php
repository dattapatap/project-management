<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository extends BaseRepository
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    public function forProject(int $projectId, ?int $userId = null): Builder
    {
        $query = $this->query()
            ->with(['user', 'logs.user', 'histories.user'])
            ->where('projectid', $projectId)
            ->orderBy('id', 'desc');

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query;
    }

    /**
     * Get tasks grouped by status for Kanban columns.
     */
    public function getKanbanColumns(int $projectId, ?int $userId = null): array
    {
        $query = $this->forProject($projectId, $userId);

        return [
            'todo'       => (clone $query)->where('status', 'ToDo')->orderBy('id', 'desc')->get(),
            'inprogress' => (clone $query)->where('status', 'InProgress')->orderBy('id', 'desc')->get(),
            'completed'  => (clone $query)->where('status', 'Completed')->orderBy('id', 'desc')->get(),
        ];
    }

    /**
     * Tasks assigned to a user, optionally filtered by status.
     */
    public function forUser(int $userId, ?string $status = null): Builder
    {
        $query = $this->query()->where('assigned_to', $userId);
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }

    /* ------------------------------------------------------------------
     *  EMPLOYEE DASHBOARD DATA
     * ------------------------------------------------------------------ */

    /**
     * Get task statistics for a specific user in a given year.
     */
    public function getUserTaskStats(int $userId, int $year): array
    {
        return [
            'total_assigned'   => $this->forUser($userId)->whereYear('created_at', $year)->count(),
            'completed'        => $this->forUser($userId, 'Completed')->whereYear('updated_at', $year)->count(),
            'in_progress'      => $this->forUser($userId, 'InProgress')->count(),
            'todo'             => $this->forUser($userId, 'ToDo')->count(),
            'pending'          => $this->forUser($userId)->whereIn('status', ['ToDo', 'InProgress'])->count(),
        ];
    }

    /**
     * Get total hours logged by a user in a given year.
     */
    public function getUserHours(int $userId, int $year): float
    {
        return round(TaskLog::where('userid', $userId)
            ->whereYear('created_at', $year)
            ->sum('time_spend'), 1);
    }

    /**
     * Average task duration for completed tasks.
     */
    public function getAvgTaskDuration(int $userId, int $year): float
    {
        $completedCount = $this->forUser($userId, 'Completed')->whereYear('created_at', $year)->count();
        if ($completedCount === 0) return 0;

        $totalHours = Task::where('assigned_to', $userId)
            ->where('status', 'Completed')
            ->whereYear('created_at', $year)
            ->withSum('logs as total_hours', 'time_spend')
            ->get()
            ->sum('total_hours');

        return round($totalHours / $completedCount, 1);
    }

    /**
     * Monthly completion trend for a user.
     */
    public function getMonthlyCompletionTrend(int $userId, int $year): \Illuminate\Support\Collection
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $monthlyData = Task::where('assigned_to', $userId)
            ->where('status', 'Completed')
            ->whereYear('updated_at', $year)
            ->selectRaw("count(*) as count, DATE_FORMAT(updated_at, '%b') as month")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        return collect($months)->map(fn($m) => (object)[
            'month' => $m,
            'count' => $monthlyData->has($m) ? $monthlyData->get($m)->count : 0,
        ]);
    }

    /**
     * Current active tasks with project context for a user.
     */
    public function getActiveTasksWithProjects(int $userId): Collection
    {
        return $this->query()
            ->with(['project.projectCategory', 'project.clients'])
            ->where('assigned_to', $userId)
            ->whereIn('status', ['ToDo', 'InProgress'])
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Recently completed tasks for a user.
     */
    public function getRecentlyCompleted(int $userId, int $limit = 10): Collection
    {
        return $this->query()
            ->with(['project.projectCategory', 'project.clients'])
            ->where('assigned_to', $userId)
            ->where('status', 'Completed')
            ->orderBy('updated_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Daily pulse metrics (today only).
     */
    public function getDailyPulse(int $userId): array
    {
        $startOfToday = Carbon::now()->startOfDay();

        return [
            'tasks_completed_today' => Task::where('assigned_to', $userId)
                ->where('status', 'Completed')
                ->where('updated_at', '>=', $startOfToday)
                ->count(),
            'hours_logged_today' => round(TaskLog::where('userid', $userId)
                ->where('created_at', '>=', $startOfToday)
                ->sum('time_spend'), 1),
        ];
    }

    /**
     * Count incomplete tasks on a project.
     */
    public function countIncompleteTasks(int $projectId): int
    {
        return $this->query()
            ->where('projectid', $projectId)
            ->where('status', '!=', 'Completed')
            ->count();
    }

    /**
     * Build base query for task listing with standard relations.
     */
    public function buildTaskListingQuery(User $user, array $filters = []): Builder
    {
        $query = $this->query()
            ->with([
                'user',
                'project.clients',
                'project.projectCategory',
                'project.project_team.team',
                'logs',
            ]);

        // Branch scope for Branch Manager
        if ($user->hasRole('Branch-Manager') && !$user->isGlobalAdmin()) {
            $branchUserIds = app(\App\Services\BranchScopeService::class)->getBranchUserIds($user);
            $query->whereIn('assigned_to', $branchUserIds);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('project', function ($pq) use ($search) {
                        $pq->where('project_name', 'like', "%{$search}%")
                            ->orWhereHas('clients', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
                    })
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        // Team filter
        if (!empty($filters['team_id'])) {
            $teamId = $filters['team_id'];
            $teamMemberIds = \App\Models\TeamMembers::where('team', $teamId)->where('status', true)->pluck('user')->toArray();
            $query->where(function ($q) use ($teamId, $teamMemberIds) {
                $q->whereHas('project.project_team', fn($pq) => $pq->where('teamid', $teamId));
                if (!empty($teamMemberIds)) {
                    $q->orWhereIn('assigned_to', $teamMemberIds);
                }
            });
        }

        // Assignee / User filter
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        // Priority filter
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Date range filters (checks overlap with task lifecycle)
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->where(function($q) use ($filters) {
                $q->whereBetween('startdate', [$filters['start_date'], $filters['end_date']])
                  ->orWhereBetween('enddate', [$filters['start_date'], $filters['end_date']])
                  ->orWhere(function($sq) use ($filters) {
                      $sq->where('startdate', '<=', $filters['start_date'])
                         ->where('enddate', '>=', $filters['end_date']);
                  });
            });
        } elseif (!empty($filters['start_date'])) {
            $query->whereDate('startdate', '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $query->whereDate('enddate', '<=', $filters['end_date']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Active') {
                $query->whereIn('status', ['ToDo', 'InProgress']);
            } elseif ($filters['status'] === 'Completed') {
                $query->where('status', 'Completed');
            } elseif (in_array($filters['status'], ['ToDo', 'InProgress'])) {
                $query->where('status', $filters['status']);
            }
        }

        return $query;
    }

    /**
     * Compute statistics for task listing based on active filters.
     */
    public function computeTaskListingStats(Builder $baseQuery): array
    {
        $statsQuery = clone $baseQuery;
        $taskIds = (clone $statsQuery)->pluck('id')->toArray();
        
        $totalHours = 0.0;
        if (!empty($taskIds)) {
            $totalHours = (float) round(TaskLog::whereIn('taskid', $taskIds)->whereNotNull('time_spend')->sum('time_spend'), 1);
        }

        return [
            'total'       => (clone $statsQuery)->count(),
            'active'      => (clone $statsQuery)->whereIn('status', ['ToDo', 'InProgress'])->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'InProgress')->count(),
            'todo'        => (clone $statsQuery)->where('status', 'ToDo')->count(),
            'completed'   => (clone $statsQuery)->where('status', 'Completed')->count(),
            'total_hours' => $totalHours,
        ];
    }
}
