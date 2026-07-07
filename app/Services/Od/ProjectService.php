<?php

namespace App\Services\Od;

use App\Http\Controllers\DepartmentProjectHistoryController;
use App\Models\DepartmentProjects;
use App\Models\TeamMembers;
use App\Models\TeamProject;
use App\Models\Teams;
use App\Models\User;
use App\Models\UserActivity;
use App\Repositories\ProjectRepository;
use App\Services\Csd\CsdHandoffService;
use App\Services\Commercial\ClientEngagementService;
use App\Services\ProjectNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectUpdate;

class ProjectService
{
    public function __construct(
        private ProjectRepository $projectRepo,
        private CsdHandoffService $handoff,
        private ClientEngagementService $engagements
    ) {
    }

    public function getProjectIndexData(User $user, ?string $status = null, ?int $department = null, int $perPage = 50): array
    {
        $query = $this->projectRepo->buildIndexQuery($user, $status, $department);
        $projects = $this->projectRepo->paginate($query, $perPage);

        // Compute stats using same user scope and department filter
        $statsQuery = $this->projectRepo->buildIndexQuery($user, null, $department);
        $stats = $this->projectRepo->computeStats($statsQuery);

        return [
            'projects' => $projects,
            'stats'    => $stats
        ];
    }

    /**
     * Search projects by query and compute stats.
     */
    public function searchProjects(User $user, ?string $filter, int $perPage = 50, ?int $department = null): array
    {
        $query = $this->projectRepo->withStandardRelations();

        if ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant'])) {
            $this->projectRepo->scopeForEmployee($query, $user->id);
        } elseif ($user->hasRole('Team-Leader')) {
            $this->projectRepo->scopeForTeamLeader($query, $user);
        }

        if ($department) {
            $query->whereHas('projectCategory', function ($q) use ($department) {
                $q->where('dept_id', $department);
            });
        }

        if (!empty($filter)) {
            if ($filter == 'Near Deadline') {
                $nearDeadlineDate = Carbon::now()->addDays(7);
                $query->where('status', '!=', 'Completed')
                    ->where('end_date', '<=', $nearDeadlineDate);
            } else {
                $query->where(function ($q) use ($filter) {
                    $q->where('project_name', 'like', '%' . $filter . '%')
                        ->orWhere('status', 'like', '%' . $filter . '%')
                        ->orWhereHas('clients', fn($sq) => $sq->where('name', 'like', '%' . $filter . '%'))
                        ->orWhereHas('projectCategory', fn($sq) => $sq->where('category', 'like', '%' . $filter . '%'))
                        ->orWhereHas('sub_categories', fn($sq) => $sq->where('name', 'like', '%' . $filter . '%'));
                });
            }
        }

        $projects = $this->projectRepo->paginate($query->latest(), $perPage);
        $stats = $this->projectRepo->computeStats(clone $query);

        return [
            'projects' => $projects,
            'stats'    => $stats
        ];
    }

    /**
     * Assign a project to a team.
     */
    public function assignToTeam(int $projectId, int $teamId, User $user): array
    {
        return DB::transaction(function () use ($projectId, $teamId, $user) {
            $isAssigned = TeamProject::where('teamid', $teamId)->where('projectid', $projectId)->first();
            if ($isAssigned) {
                return ['success' => false, 'message' => 'Project Assigned Already'];
            }

            $project = $this->projectRepo->findOrFail($projectId);
            $team = Teams::where('id', $teamId)->with('teammembers')->firstOrFail();

            // Assign to team
            $teamproj = new TeamProject();
            $teamproj->projectid     = $projectId;
            $teamproj->teamid        = $teamId;
            $teamproj->assigned_by   = $user->id;
            $teamproj->assigned_date = Carbon::now();
            $teamproj->save();

            // Update Project status to ToDo
            $project->status = 'ToDo';
            $project->save();

            // Notify Team Leaders
            if ($team->teammembers->count() > 0) {
                $teamLeads = User::whereIn('id', $team->teammembers->pluck('user'))
                    ->role('Team-Leader')
                    ->get();

                if ($teamLeads->count() > 0) {
                    $details = [
                        'category' => 'Project',
                        'header'   => 'New Project Assigned to Team',
                        'body'     => "Project '{$project->project_name}' has been assigned to your team.",
                        'link'     => url('/') . "/projects/" . base64_encode($project->id) . "/history"
                    ];
                    ProjectNotificationService::notifyProject($project, $details);
                }
            }

            return ['success' => true, 'message' => 'Assigned'];
        });
    }

    /**
     * Update project basic details.
     */
    public function updateProjectDetails(int $projectId, array $data, User $user): array
    {
        return DB::transaction(function () use ($projectId, $data, $user) {
            $project = $this->projectRepo->findOrFail($projectId);

            $project->project_name   = $data['project_name'];
            $project->start_date     = Carbon::parse($data['start_date'])->format('Y-m-d H:i');
            $project->end_date       = Carbon::parse($data['end_date'])->format('Y-m-d H:i');
            $project->act_start_date = Carbon::parse($data['act_start_date'])->format('Y-m-d H:i');
            $project->description    = $data['description'];
            $project->save();

            UserActivity::log('Project Updated', "Updated basic details of project '{$project->project_name}'");
            DepartmentProjectHistoryController::store($project, 'Project Updated', $user->id);

            ProjectNotificationService::notifyProject($project, [
                'category' => 'Project',
                'header'   => 'Project Details Updated',
                'body'     => "Details for project '{$project->project_name}' have been updated by " . $user->name,
                'link'     => url('/') . "/projects/" . base64_encode($project->id) . "/history"
            ]);

            return ['success' => true, 'message' => 'Project Updated'];
        });
    }

    /**
     * Add a progress update history remark for a project.
     */
    public function addProjectHistoryRemark(int $projectId, string $remarks, User $user): array
    {
        $project = $this->projectRepo->findOrFail($projectId);

        $history = new \App\Models\DepartmentProjectHistory();
        $history->histories()->associate($project);
        $history->comments = $remarks;
        $history->date     = Carbon::now();
        $history->addedby  = $user->id;
        $history->save();

        UserActivity::log('Project Update', "Added progress remark for project '{$project->project_name}': {$remarks}");

        // Bulk notify Project Managers
        $productManagers = User::role('Project-Manager')->where('status', 'Active')->get();
        if ($productManagers->count() > 0) {
            Notification::send($productManagers, (new ProjectUpdate($project, "Project Update"))->delay(now()->addSeconds(5)));
        }

        return ['success' => true, 'message' => 'Project Updated'];
    }

    /**
     * Assign project to a Team Leader.
     */
    public function assignToTL(int $projectId, ?int $assignedToId, User $user): array
    {
        $project = $this->projectRepo->findOrFail($projectId);

        if ($project->status == 'Completed') {
            return ['success' => false, 'message' => 'Completed projects cannot be reassigned.'];
        }

        if ($project->assigned_to) {
            return ['success' => false, 'message' => 'Project is already assigned to a Team Leader.'];
        }

        // Default to self if current user is TL
        if ($user->hasRole('Team-Leader') && !$assignedToId) {
            $assignedToId = $user->id;
        }

        if (!$assignedToId) {
            return ['success' => false, 'message' => 'Target Team Leader not specified'];
        }

        return DB::transaction(function () use ($project, $assignedToId, $user) {
            $project->assigned_to = $assignedToId;
            $project->status = 'InProgress';
            $project->save();

            // Link TL's team to project
            $tlTeam = TeamMembers::where('user', $assignedToId)->where('status', true)->first();
            if ($tlTeam) {
                TeamProject::updateOrCreate(
                    ['projectid' => $project->id],
                    [
                        'teamid'        => $tlTeam->team,
                        'assigned_by'   => $user->id,
                        'assigned_date' => Carbon::now()
                    ]
                );
            }

            $targetUser = User::findOrFail($assignedToId);
            DepartmentProjectHistoryController::store($project, "Project assigned to Team Leader: " . $targetUser->name, $user->id);

            ProjectNotificationService::notifyProject($project, [
                'category' => 'Project',
                'header'   => 'Project Assigned to TL',
                'body'     => "Project '{$project->project_name}' has been assigned to Team Leader {$targetUser->name}",
                'link'     => url('/') . "/projects/" . base64_encode($project->id) . "/history"
            ]);

            return ['success' => true, 'message' => 'Project successfully assigned'];
        });
    }

    public function getEmployeesForProject(int $projectId, User $user, bool $interTeam = false): array
    {
        $project = $this->projectRepo->withStandardRelations()->findOrFail($projectId);

        if ($user->hasRole('Team-Leader')) {
            if ($interTeam) {
                // Show all active Team Leaders in the TL's own department
                $userDeptId = $user->departments?->department ?? $user->teamMember?->department ?? 2;
                $otherTls = User::role('Team-Leader')
                    ->where('status', 'Active')
                    ->where('id', '!=', $user->id)
                    ->whereHas('departments', function($q) use ($userDeptId) {
                        $q->where('department', $userDeptId);
                    })
                    ->with(['teamMember.team'])
                    ->get();

                $data = [];
                foreach ($otherTls as $tl) {
                    $teamName = $tl->teamMember?->team?->name ?? 'No Team';
                    $data[] = [
                        'id' => $tl->id,
                        'name' => "{$tl->name} (TL - {$teamName})"
                    ];
                }
                return $data;
            }

            // Normal task creation: show all active users under his team
            $teamMember = TeamMembers::where('user', $user->id)->where('status', true)->first();
            $tlTeamId = $teamMember?->team;

            $query = User::where('status', 'Active');
            if ($tlTeamId) {
                $query->whereHas('teamMember', function ($q) use ($tlTeamId) {
                    $q->where('team', $tlTeamId);
                });
            } else {
                $userDeptId = $user->departments?->department ?? $user->teamMember?->department ?? 2;
                $query->whereHas('teamMember', function ($q) use ($userDeptId) {
                    $q->where('department', $userDeptId);
                });
            }

            $employees = $query->select('id', 'name')->orderBy('name')->get();

            $data = [];
            foreach ($employees as $emp) {
                $name = ($emp->id == $user->id) ? $emp->name . ' (Assign to me)' : $emp->name;
                $data[] = ['id' => $emp->id, 'name' => $name];
            }

            // Ensure Team Leader is in the list
            $hasCurrentUser = collect($data)->contains('id', $user->id);
            if (!$hasCurrentUser) {
                array_unshift($data, ['id' => $user->id, 'name' => $user->name . ' (Assign to me)']);
            }

            return $data;
        }

        // For non-Team Leader roles: Admin, Branch-Manager, Project-Manager
        $query = User::where('status', 'Active')
            ->where(function ($q) {
                $q->whereHas('teamMember', function ($sub) {
                    $sub->where('department', 2);
                })->orWhereHas('departments', function ($sub) {
                    $sub->where('department', 2);
                });
            });

        if ($user->hasRole(['Admin', 'Branch-Manager'])) {
            // Exclude Branch-Manager and Admin roles from being assigned to
            $query->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Admin', 'Branch-Manager']);
            });
        }

        $employees = $query->select('id', 'name')->orderBy('name')->get();

        $data = [];
        foreach ($employees as $emp) {
            $name = ($emp->id == $user->id) ? $emp->name . ' (Assign to me)' : $emp->name;
            $data[] = ['id' => $emp->id, 'name' => $name];
        }

        // For Project-Manager (and other non-Admin/non-BM management), ensure they can assign to themselves
        if (!$user->hasRole(['Admin', 'Branch-Manager'])) {
            $hasCurrentUser = collect($data)->contains('id', $user->id);
            if (!$hasCurrentUser) {
                array_unshift($data, ['id' => $user->id, 'name' => $user->name . ' (Assign to me)']);
            }
        }

        return $data;
    }

    /**
     * Update project status.
     */
    public function updateStatus(DepartmentProjects $project, User $user, string $status, ?string $actStartDate = null): array
    {
        // Rule 1: Once started, cannot go back to ToDo
        if (in_array($project->status, ['InProgress', 'Completed']) && $status === 'ToDo') {
            return ['success' => false, 'message' => 'Project cannot be moved back to ToDo once it has started.'];
        }

        // Rule 2: Reopening Completed project is restricted to Admin, Branch-Manager & Team-Leader
        if ($project->status === 'Completed' && $status === 'InProgress') {
            if (!$user->hasRole(['Admin', 'Branch-Manager', 'Team-Leader'])) {
                return ['success' => false, 'message' => 'Only Admins, Branch Managers and Team Leaders can reopen completed projects.'];
            }
        }

        // Rule 3: Must be InProgress before Completed (cannot skip InProgress)
        if ($project->status === 'ToDo' && $status === 'Completed') {
            return ['success' => false, 'message' => 'Project must be started (In Progress) before it can be marked as Completed.'];
        }

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
                try {
                    $project->act_start_date = Carbon::parse($actStartDate)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $project->act_start_date = Carbon::createFromFormat('d/m/Y h:i A', $actStartDate)->format('Y-m-d H:i:s');
                }
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

    /**
     * Get projects with full task data for the Gantt timeline view.
     */
    public function getTimelineProjects(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->projectRepo->getTimelineProjects($user);
    }
}

