<?php

namespace App\Services\Csd;

use App\Models\CsdChangeRequest;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use App\Services\ProjectNotificationService;
use Illuminate\Database\Eloquent\Builder;

class CsdChangeRequestService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $resolver
    ) {
    }

    public function listQuery(User $user): Builder
    {
        $query = CsdChangeRequest::with(['client', 'project', 'assignee'])->latest();
        $this->scope->applyAssigneeScope($query, $user);

        return $query;
    }

    public function create(array $data, User $user): CsdChangeRequest
    {
        $this->assertClientAccess($user, (int) $data['client']);

        return CsdChangeRequest::create([
            ...$data,
            'status' => 'submitted',
            'created_by' => $user->id,
            'assigned_to' => $user->id,
        ]);
    }

    public function update(CsdChangeRequest $changeRequest, array $data): CsdChangeRequest
    {
        if ($data['status'] === 'approved' && !$changeRequest->approved_at) {
            $data['approved_at'] = now();
        }

        $changeRequest->update($data);

        return $changeRequest->fresh();
    }

    public function transferToOd(CsdChangeRequest $changeRequest, User $user): DepartmentProjects
    {
        if ($changeRequest->status !== 'approved') {
            throw new \InvalidArgumentException('Only approved requests can be transferred to OD.');
        }

        $sourceProject = $changeRequest->project;

        $odProject = DepartmentProjects::create([
            'client' => $changeRequest->client,
            'project_name' => 'CR: ' . $changeRequest->title,
            'category' => $sourceProject?->category ?? 1,
            'sub_category' => $sourceProject?->sub_category ?? 1,
            'department' => 2,
            'status' => 'ToDo',
            'assigned_by' => $user->id,
            'created_date' => now(),
            'start_date' => now(),
            'description' => $changeRequest->description,
        ]);

        $changeRequest->update([
            'status' => 'transferred_to_od',
            'od_project_id' => $odProject->id,
        ]);

        ProjectNotificationService::notifyProject($odProject, [
            'category' => 'Project',
            'header' => 'Change Request Transferred to OD',
            'data' => "CSD change request \"{$changeRequest->title}\" has been transferred as a new OD project.",
            'link' => url('/projects/' . $odProject->id),
        ]);

        return $odProject;
    }

    private function assertClientAccess(User $user, int $clientId): void
    {
        if (!$this->resolver->userCanAccessClient($user, $clientId)) {
            throw new \InvalidArgumentException('You cannot access this client.');
        }
    }
}
