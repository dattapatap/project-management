<?php

namespace App\Services\Csd;

use App\Models\Clients;
use App\Models\CsdClientAssignment;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Notifications\ProjectHandoffToCsd;
use App\Services\BranchScopeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CsdHandoffService
{
    public function __construct(private BranchScopeService $branchScope)
    {
    }

    public function getBranchStakeholdersForClient(Clients $client): Collection
    {
        return $this->resolveBranchStakeholders($client);
    }

    public function handoffFromCompletedProject(DepartmentProjects $project): ?CsdClientAssignment
    {
        if ($project->status !== 'Completed') {
            return null;
        }

        $existing = CsdClientAssignment::where('project_id', $project->id)->first();
        if ($existing) {
            return $existing;
        }

        $client = Clients::find($project->client);
        $defaultAssignee = $this->resolveDefaultAssignee($client);

        $assignment = CsdClientAssignment::create([
            'client' => $project->client,
            'project_id' => $project->id,
            'assigned_to' => $defaultAssignee,
            'handoff_date' => now()->toDateString(),
            'health_status' => 'healthy',
            'status' => 'active',
            'created_by' => Auth::id() ?? $project->assigned_by ?? 1,
            'notes' => "Auto handoff after project '{$project->project_name}' was completed.",
        ]);

        $stakeholders = $this->resolveBranchStakeholders($client);
        if ($stakeholders->isNotEmpty()) {
            Notification::send($stakeholders, new ProjectHandoffToCsd($project, $assignment));
        }

        return $assignment;
    }

    public function migrateClientFromMaturedProject(DepartmentProjects $project): ?CsdClientAssignment
    {
        if (CsdClientAssignment::where('client', $project->client)->where('status', 'active')->exists()) {
            return null;
        }

        $project->status = 'Completed';

        return $this->handoffFromCompletedProject($project);
    }

    private function resolveDefaultAssignee(?Clients $client): ?int
    {
        if (!$client) {
            return null;
        }

        $branchId = $this->resolveClientBranchId($client);
        if (!$branchId) {
            return null;
        }

        $csdUserIds = User::whereIn('id', function ($q) use ($branchId) {
            $q->select('user')->from('user_branches')->where('branch', $branchId);
        })
            ->whereHas('departments', fn ($q) => $q->where('department', BranchScopeService::DEPT_CSD))
            ->whereHas('roles', fn ($q) => $q->where('name', 'CSD-Executive'))
            ->value('id');

        return $csdUserIds;
    }

    private function resolveBranchStakeholders(?Clients $client): Collection
    {
        $branchId = $client ? $this->resolveClientBranchId($client) : null;

        if (!$branchId) {
            return User::role('Admin')->get();
        }

        $branchUserIds = DB::table('user_branches')
            ->where('branch', $branchId)
            ->pluck('user')
            ->toArray();

        return User::whereIn('id', $branchUserIds)
            ->where(function ($query) {
                $query->whereHas('roles', fn ($q) => $q->where('name', 'Branch-Manager'))
                    ->orWhere(function ($inner) {
                        $inner->whereHas('roles', fn ($q) => $q->whereIn('name', ['Team-Leader', 'CSD-Executive']))
                            ->whereHas('departments', fn ($d) => $d->where('department', BranchScopeService::DEPT_CSD));
                    });
            })
            ->get();
    }

    private function resolveClientBranchId(Clients $client): ?int
    {
        $salesUserId = $client->ref_user ?: $client->tele_ref_user;
        if (!$salesUserId) {
            return null;
        }

        $branchId = DB::table('user_branches')->where('user', $salesUserId)->value('branch');

        return $branchId ? (int) $branchId : null;
    }
}
