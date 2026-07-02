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

        // Determine team scope & department scope
        $teamId = null;
        $selectedDeptId = 2; // Strictly OD (Operations)

        if ($user->hasRole('Team-Leader')) {
            $member = TeamMembers::where('user', $user->id)->where('status', true)->first();
            $teamId = $member?->team;
        } else {
            if ($request->has('team') && $request->get('team') !== '') {
                $teamId = (int) $request->get('team');
            }
        }

        // Get all active projects with task & member data
        $projects = $this->projectRepo->getResourceAllocation($teamId);

        // Build member list based on selected scope
        if ($teamId) {
            $memberIds = TeamMembers::where('team', $teamId)
                ->where('status', true)
                ->pluck('user')
                ->unique()
                ->filter()
                ->toArray();
        } else {
            // Get active users belonging to the selected department
            $memberIds = User::whereHas('departments', function ($q) use ($selectedDeptId) {
                $q->where('department', $selectedDeptId);
            })->where('status', 'Active')->pluck('id')->toArray();
        }

        // Get workload stats per member
        $workload = $memberIds
            ? $this->projectRepo->getWorkloadByTeamMembers($memberIds)
            : collect();

        // Build list of teams for filter (Admin/PM/BM only)
        $teams = [];
        if ($user->hasRole(['Admin', 'Project-Manager', 'Branch-Manager'])) {
            $teams = Teams::with('teammembers.member')->get();
        }

        return view('components.projects.resources', compact(
            'projects',
            'workload',
            'memberIds',
            'teams',
            'teamId',
            'selectedDeptId'
        ));
    }
}
