<?php

namespace App\Services\Od;

use App\Http\Controllers\DepartmentProjectHistoryController;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TeamMembers;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\Csd\CsdHandoffService;
use App\Services\Commercial\ClientEngagementService;
use App\Services\ProjectNotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ProjectService
{
    public function __construct(
        private CsdHandoffService $handoff,
        private ClientEngagementService $engagements
    ) {
    }

    public function buildIndexQuery(User $user, ?string $status = null): Builder
    {
        $query = DepartmentProjects::with(['tasks.user', 'project_team.team.teammembers', 'clients', 'category']);

        if ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant'])) {
            $query->whereHas('tasks', function ($q) use ($user, $status) {
                $q->where('assigned_to', $user->id);
                if ($status === 'Completed') {
                    $q->where('status', 'Completed');
                } elseif ($status === 'Pending') {
                    $q->whereIn('status', ['ToDo', 'InProgress']);
                }
            });
        } elseif ($user->hasRole('Team-Leader')) {
            $teamMember = TeamMembers::where('user', $user->id)->where('status', true)->first();
            $teamId = $teamMember?->team;

            $query->where(function ($q) use ($user, $teamId) {
                $q->where('assigned_to', $user->id)
                    ->orWhereHas('project_team', fn ($sq) => $sq->where('teamid', $teamId));
            });
        }

        if ($status && !$user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant'])) {
            $query->where('status', $status);
        }

        return $query->latest();
    }

    public function getPortfolioStats(): array
    {
        $nearDeadlineDate = Carbon::now()->addDays(7);

        return DepartmentProjects::selectRaw("
            count(*) as total,
            count(case when status != 'Completed' and end_date <= ? then 1 end) as near_deadline,
            count(case when status != 'Completed' then 1 end) as pending,
            count(case when status = 'Completed' then 1 end) as completed
        ", [$nearDeadlineDate])->first()->toArray();
    }

    public function updateStatus(DepartmentProjects $project, User $user, string $status, ?string $actStartDate = null): array
    {
        if ($status === 'Completed') {
            if ($project->status === 'Completed') {
                return ['success' => false, 'message' => 'Project is already completed!'];
            }

            $incompleteTasks = $project->tasks()->where('status', '!=', 'Completed')->count();
            if ($incompleteTasks > 0) {
                return ['success' => false, 'message' => "Cannot complete project. There are {$incompleteTasks} incomplete task(s)."];
            }
        }

        if ($status === 'Completed') {
            $project->status = $status;
            $project->act_end_date = Carbon::now()->format('Y-m-d H:i');
        } elseif ($status === 'InProgress') {
            $project->status = $status;
            if ($actStartDate) {
                $project->act_start_date = Carbon::createFromFormat('d/m/Y h:i A', $actStartDate)->format('Y-m-d H:i:s');
            } elseif (!$project->act_start_date) {
                $project->act_start_date = Carbon::now()->format('Y-m-d H:i:s');
            }
        } else {
            $project->status = $status;
        }

        $project->save();

        UserActivity::log('Project Status Changed', "Changed status of project '{$project->project_name}' to '{$project->status}'");

        $comment = 'Project status updated as ' . $project->status . ' by ' . $user->name;
        DepartmentProjectHistoryController::store($project, $comment, $user->id);

        ProjectNotificationService::notifyProject($project, [
            'category' => 'Project',
            'header' => 'Project Status Updated',
            'body' => "Project '{$project->project_name}' is now {$project->status}",
            'link' => url('/') . '/projects/' . base64_encode($project->id) . '/history',
        ]);

        if ($project->status === 'Completed') {
            $this->engagements->markDeliveryCompleted($project);
            $this->handoff->handoffFromCompletedProject($project);
        }

        return ['success' => true, 'message' => 'Project Status Updated'];
    }

    public function countIncompleteTasks(DepartmentProjects $project): int
    {
        return $project->tasks()->where('status', '!=', 'Completed')->count();
    }
}
