<?php

namespace App\Repositories;

use App\Models\DepartmentProjects;
use App\Models\TeamMembers;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository extends BaseRepository
{
    public function __construct(DepartmentProjects $model)
    {
        parent::__construct($model);
    }

    /* ------------------------------------------------------------------
     *  SCOPED QUERIES
     * ------------------------------------------------------------------ */

    /**
     * Base query with standard eager-loaded relations.
     */
    public function withStandardRelations(): Builder
    {
        return $this->query()->with([
            'tasks.user',
            'project_team.team.teammembers',
            'clients',
            'projectCategory',
        ]);
    }

    /**
     * Scope projects visible to a specific employee (by task assignment).
     */
    public function scopeForEmployee(Builder $query, int $userId): Builder
    {
        return $query->whereHas('tasks', fn($q) => $q->where('assigned_to', $userId));
    }

    public function scopeForTeamLeader(Builder $query, User $user): Builder
    {
        $teamMember = TeamMembers::where('user', $user->id)->where('status', true)->first();
        $teamId = $teamMember?->team;

        return $query->where(function ($q) use ($user, $teamId) {
            $q->where('assigned_to', $user->id);
            if ($teamId) {
                $q->orWhereHas('project_team', fn($sq) => $sq->where('teamid', $teamId));
            }
            // Expand project visibility to projects with tasks assigned to the TL or their team members
            $q->orWhereHas('tasks', function ($sq) use ($user, $teamId) {
                $sq->where('assigned_to', $user->id);
                if ($teamId) {
                    $sq->orWhereHas('user.teamMember', fn($ssq) => $ssq->where('team', $teamId));
                }
            });
        });
    }

    public function buildIndexQuery(User $user, ?string $status = null, ?int $department = null): Builder
    {
        $query = $this->withStandardRelations();

        if ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant'])) {
            $this->scopeForEmployee($query, $user->id);
            if ($status === 'Completed') {
                $query->whereHas('tasks', fn($q) => $q->where('assigned_to', $user->id)->where('status', 'Completed'));
            } elseif ($status === 'Pending') {
                $query->whereHas('tasks', fn($q) => $q->where('assigned_to', $user->id)->whereIn('status', ['ToDo', 'InProgress']));
            }
        } elseif ($user->hasRole('Team-Leader')) {
            $this->scopeForTeamLeader($query, $user);
            if ($status === 'Pending') {
                $query->whereIn('status', ['ToDo', 'InProgress']);
            } elseif ($status && $status !== 'all') {
                $query->where('status', $status);
            }
        } else {
            if ($status === 'Pending') {
                $query->whereIn('status', ['ToDo', 'InProgress']);
            } elseif ($status && $status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($department) {
            $query->whereHas('projectCategory', function ($q) use ($department) {
                $q->where('dept_id', $department);
            });
        }

        return $query->latest();
    }

    /* ------------------------------------------------------------------
     *  STATISTICS
     * ------------------------------------------------------------------ */

    /**
     * Aggregate project statistics (total, near_deadline, not_started, etc.).
     */
    public function computeStats(?Builder $baseQuery = null): array
    {
        $query = $baseQuery ? clone $baseQuery : $this->query();
        $nearDeadline = Carbon::now()->addDays(7);

        $base = $query->toBase();
        $base->columns = []; // Clear standard selects to prevent ONLY_FULL_GROUP_BY errors
        $base->orders = [];  // Clear ORDER BY clause to prevent ONLY_FULL_GROUP_BY errors

        $row = $base->selectRaw("
            count(*) as total,
            count(case when status != 'Completed' and end_date <= ? then 1 end) as near_deadline,
            count(case when status = 'ToDo' then 1 end) as not_started,
            count(case when status = 'InProgress' then 1 end) as in_progress,
            count(case when status != 'Completed' then 1 end) as pending,
            count(case when status = 'Completed' then 1 end) as completed
        ", [$nearDeadline])->first();

        return (array) $row;
    }

    /**
     * Get portfolio-level stats (used in admin dashboards).
     */
    public function getPortfolioStats(): array
    {
        return $this->computeStats();
    }

    /* ------------------------------------------------------------------
     *  PROJECT LOOKUPS
     * ------------------------------------------------------------------ */

    /**
     * Near-deadline projects (within 7 days, not completed).
     */
    public function nearDeadline(int $limit = 5): Collection
    {
        return $this->query()
            ->with('clients')
            ->where('status', '!=', 'Completed')
            ->where('end_date', '<=', Carbon::now()->addDays(7))
            ->orderBy('end_date', 'asc')
            ->take($limit)
            ->get();
    }

    /**
     * Projects for a Gantt timeline view.
     */
    public function getTimelineProjects(?User $user = null): Collection
    {
        $query = $this->query()
            ->with(['clients', 'projectCategory', 'tasks' => function ($q) {
                $q->select('id', 'projectid', 'title', 'status', 'startdate', 'enddate', 'act_startdate', 'act_enddate', 'assigned_to')
                  ->with('user:id,name');
            }])
            ->withCount(['tasks', 'completedTask']);

        if ($user && $user->hasRole('Team-Leader')) {
            $this->scopeForTeamLeader($query, $user);
        }

        return $query->orderBy('start_date', 'asc')->get();
    }

    /**
     * Resource allocation matrix: users × projects with task counts.
     */
    public function getResourceAllocation(?int $teamId = null): Collection
    {
        $query = $this->query()
            ->with(['tasks' => function ($q) {
                $q->select('id', 'projectid', 'assigned_to', 'status')
                  ->with('user:id,name');
            }, 'clients:id,name', 'projectCategory'])
            ->where('status', '!=', 'Completed');

        if ($teamId) {
            $query->whereHas('project_team', fn($q) => $q->where('teamid', $teamId));
        }

        return $query->orderBy('end_date', 'asc')->get();
    }

    /**
     * Compute workload per team member.
     */
    public function getWorkloadByTeamMembers(array $memberIds): Collection
    {
        return User::whereIn('id', $memberIds)
            ->withCount([
                'tasks as todo_count' => fn($q) => $q->where('status', 'ToDo'),
                'tasks as inprogress_count' => fn($q) => $q->where('status', 'InProgress'),
                'tasks as completed_count' => fn($q) => $q->where('status', 'Completed'),
                'tasks as overdue_count' => fn($q) => $q->where('status', '!=', 'Completed')
                    ->where('enddate', '<', Carbon::now()),
            ])
            ->withSum('taskLogs as total_hours', 'time_spend')
            ->get();
    }
}
