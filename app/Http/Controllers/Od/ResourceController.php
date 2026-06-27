<?php

namespace App\Http\Controllers\Od;

use App\Http\Controllers\Controller;
use App\Models\Teams;
use App\Models\TeamMembers;
use App\Models\User;
use App\Repositories\ProjectRepository;
use Auth;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct(
        private ProjectRepository $projectRepo
    ) {}

    /**
     * Show the resource allocation matrix view.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Determine team scope
        $teamId = null;
        if ($user->hasRole('Team-Leader')) {
            $member = TeamMembers::where('user', $user->id)->where('status', true)->first();
            $teamId = $member?->team;
        }

        // Get all active projects with task & member data
        $projects = $this->projectRepo->getResourceAllocation($teamId);

        // Build member list: collect all users assigned to tasks across these projects
        $memberIds = $projects->flatMap(function ($project) {
            return $project->tasks->pluck('assigned_to');
        })->unique()->filter()->values()->toArray();

        // Get workload stats per member
        $workload = $memberIds
            ? $this->projectRepo->getWorkloadByTeamMembers($memberIds)
            : collect();

        // Build teams list for filter (Admin/PM only)
        $teams = [];
        if ($user->hasRole(['Admin', 'Project-Manager', 'Branch-Manager'])) {
            $teams = Teams::with('teammembers.member')->get();
        }

        return view('components.projects.resources', compact(
            'projects',
            'workload',
            'memberIds',
            'teams',
            'teamId'
        ));
    }
}
