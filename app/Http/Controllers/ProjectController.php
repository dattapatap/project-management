<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\DepartmentProjectHistory;
use App\Models\DepartmentProjects;
use App\Models\TeamMembers;
use App\Models\TeamProject;
use App\Models\Teams;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\ProjectNotificationService;
use App\Services\Csd\CsdHandoffService;
use App\Services\Od\ProjectService;
use App\Notifications\ProjectAssigned;
use App\Notifications\ProjectUpdate;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base query with relations
        $query = DepartmentProjects::with(['tasks.user', 'project_team.team.teammembers', 'clients', 'category']);

        // Handle regular OD employees (Developer, Designer, etc.)
        if ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant'])) {
            $scopedQuery = DepartmentProjects::whereHas('tasks', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
            $stats = $this->computeProjectStats($scopedQuery);

            $query->whereHas('tasks', function ($q) use ($user, $request) {
                $q->where('assigned_to', $user->id);
                if ($request->has('status')) {
                    if ($request->status == 'Completed') {
                        $q->where('status', 'Completed');
                    } elseif ($request->status == 'Pending') {
                        $q->whereIn('status', ['ToDo', 'InProgress']);
                    }
                }
            });
            if ($request->has('status') && in_array($request->status, ['ToDo', 'InProgress', 'Completed'], true)) {
                $query->where('status', $request->status);
            }
            $projects = $query->latest()->paginate(50);
            return view('components.projects.employee_index', compact('projects', 'stats'));
        }

        $scopedQuery = DepartmentProjects::query();
        if ($user->hasRole('Team-Leader')) {
            $scopedQuery = $this->applyTeamLeaderProjectScope($scopedQuery, $user);
            $query = $this->applyTeamLeaderProjectScope($query, $user);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $stats = $this->computeProjectStats($scopedQuery);

        $projects = $query->latest()->paginate(50);

        return view('components.projects.index', compact('projects', 'stats'))->with('search', '');
    }


    public function search(Request $request)
    {
        $filter = $request->query('search');
        $query = DepartmentProjects::with(['tasks.user', 'project_team.team.teammembers', 'clients']);

        if (!empty($filter)) {
            $query->where(function ($q) use ($filter) {
                if ($filter == 'Near Deadline') {
                    $nearDeadlineDate = Carbon::now()->addDays(7);
                    $q->where('status', '!=', 'Completed')
                        ->where('end_date', '<=', $nearDeadlineDate);
                } else {
                    $q->where('project_name', 'like', '%' . $filter . '%')
                        ->orWhere('status', 'like', '%' . $filter . '%')
                        ->orWhereHas('clients', fn($q) => $q->where('name', 'like', '%' . $filter . '%'))
                        ->orWhereHas('category', fn($q) => $q->where('category', 'like', '%' . $filter . '%'))
                        ->orWhereHas('sub_categories', fn($q) => $q->where('name', 'like', '%' . $filter . '%'));
                }
            });
        }

        $stats = $this->computeProjectStats(DepartmentProjects::query());

        $projects = $query->latest()->paginate(50);
        return view('components.projects.index', compact('projects', 'stats'))->with('search', $filter);
    }

    private function applyTeamLeaderProjectScope($query, $user)
    {
        $teamMember = TeamMembers::where('user', $user->id)->where('status', true)->first();
        $teamId = $teamMember ? $teamMember->team : null;

        return $query->where(function ($q) use ($user, $teamId) {
            $q->where('assigned_to', $user->id)
                ->orWhereHas('project_team', function ($sq) use ($teamId) {
                    $sq->where('teamid', $teamId);
                });
        });
    }

    private function computeProjectStats($query)
    {
        $nearDeadlineDate = Carbon::now()->addDays(7);

        return (clone $query)->selectRaw("
            count(*) as total,
            count(case when status != 'Completed' and end_date <= ? then 1 end) as near_deadline,
            count(case when status = 'ToDo' then 1 end) as not_started,
            count(case when status = 'InProgress' then 1 end) as in_progress,
            count(case when status != 'Completed' then 1 end) as pending,
            count(case when status = 'Completed' then 1 end) as completed
        ", [$nearDeadlineDate])->first()->toArray();
    }

    public function create()
    {
        $clients = Clients::where('status', 'Matured')->orderBy('name', 'asc')->get();
        return view('components.projects.create', compact('clients'));
    }


    public function assignToTeam(Request $request)
    {
        $rules = array(
            'project' => 'required|numeric',
            'team'      => 'required|numeric',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400, 'errors' => $validator->getMessageBag()->toArray()), 400);
        } else {
            try {
                DB::beginTransaction();

                // Check weather project already assigned or not
                $isAssigned = TeamProject::where('teamid', $request->post('team'))->where('projectid', $request->post('project'))->first();
                if ($isAssigned) {
                    return response()->json(['code' => 200, "success" => false, 'message' => "Project Assigned Already"], 200);
                }

                $user = Auth::user();
                $project        = DepartmentProjects::where('id', $request->post('project'))->first();
                $teamMem     = Teams::where('id', $request->post('team'))->with('teammembers')->first();

                //Assign to team
                $teamproj = new TeamProject();
                $teamproj->projectid        = $request->post('project');
                $teamproj->teamid           = $request->post('team');
                $teamproj->assigned_by      = $user->id;
                $teamproj->assigned_date    = Carbon::now();
                $teamproj->save();

                // Update Project status
                $project->status = 'ToDo';
                $project->save();

                // Bulk notify Team Leaders
                if ($teamMem->teammembers->count() > 0) {
                    $teamLeads = User::whereIn('id', $teamMem->teammembers->pluck('user'))
                        ->role('Team-Leader')
                        ->get();

                    if ($teamLeads->count() > 0) {
                        $details = [
                            'category' => 'Project',
                            'header'   => 'New Project Assigned to Team',
                            'body'     => "Project '{$project->project_name}' has been assigned to your team.",
                            'link'     => url('/') . "/projects/" . base64_encode($project->id) . "/history"
                        ];
                        // Notify all team members if it's a team assignment
                        ProjectNotificationService::notifyProject($project, $details);
                    }
                }


                DB::commit();
                return response()->json(['code' => 200, "success" => true, 'message' => "Assigned"], 200);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['code' => 201, 'success' => false, 'message' => $ex->getMessage() . ' : Line - ' . $ex->getLine()], 200);
            }
        }
    }

    public function edit(Request $request)
    {
        $projectid = $request->project;
        $project  = DepartmentProjects::with('clients')->where('id', $projectid)->first();
        if ($project)
            return response()->json(['success' => true, 'project' => $project]);
        else
            return response()->json(['success' => false, 'message' => "Opps, project not found!"]);
    }

    public function update(Request $request)
    {
        $rules = array(
            'project_name'      => 'required|string',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date',
            'act_start_date'    => 'required|date',
            'description'       => 'required|string'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400, 'errors' => $validator->getMessageBag()->toArray()), 400);
        } else {

            try {
                $userid = Auth::user()->id;
                $project = DepartmentProjects::where('id', $request->post('project-id'))->first();
                if (!$project) {
                    return response()->json(['code' => 200, "success" => false, 'message' => "Project Not Found"], 200);
                }

                DB::beginTransaction();

                $project->project_name     =   $request->project_name;
                $project->start_date       =   Carbon::parse($request->start_date)->format('Y-m-d h:i');
                $project->end_date         =   Carbon::parse($request->end_date)->format('Y-m-d h:i');
                $project->act_start_date   =   Carbon::parse($request->act_start_date)->format('Y-m-d h:i');
                $project->description      =   $request->description;
                $project->save();

                UserActivity::log('Project Updated', "Updated basic details of project '{$project->project_name}'");

                DepartmentProjectHistoryController::store($project, "Project Updated", $userid);


                // Get Department Members and filter by role
                // Bulk notify Product Managers
                ProjectNotificationService::notifyProject($project, [
                    'category' => 'Project',
                    'header'   => 'Project Details Updated',
                    'body'     => "Details for project '{$project->project_name}' have been updated by " . Auth::user()->name,
                    'link'     => url('/') . "/projects/" . base64_encode($project->id) . "/history"
                ]);

                DB::commit();
                return response()->json(['code' => 200, "success" => true, 'message' => "Project Updated"], 200);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['code' => 201, 'success' => false, 'message' => $ex->getMessage()], 200);
            }
        }
    }

    //Add History
    public function projectupdate(Request $request)
    {
        $rules = array(
            'remarks' => 'required|string',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400, 'errors' => $validator->getMessageBag()->toArray()), 400);
        } else {
            try {
                $userid = Auth::user()->id;
                $project = DepartmentProjects::find($request->projectid);

                //Create Client Package
                $history                   = new DepartmentProjectHistory();
                $history->histories()->associate($project);
                $history->comments        = $request->remarks;
                $history->date            = Carbon::now();
                $history->addedby         = $userid;
                $history->save();

                UserActivity::log('Project Update', "Added progress remark for project '{$project->project_name}': {$request->remarks}");

                // Get Department Members and filter by role
                // Bulk notify Product Managers
                $productManagers = User::role('Project-Manager')->where('status', 'Active')->get();
                if ($productManagers->count() > 0) {
                    \Notification::send($productManagers, (new ProjectUpdate($project, "Project Update"))->delay(now()->addSeconds(5)));
                }
                return response()->json(['code' => 200, "success" => true, 'message' => "Project Updated"], 200);
            } catch (Exception $ex) {
                return response()->json(['code' => 201, 'success' => false, 'message' => $ex->getMessage()], 200);
            }
        }
    }

    public function status(Request $request, ProjectService $projectService)
    {
        $actStartDt = 'nullable';

        $rules = array(
            'status'         => 'required|string',
            'act_start_date' =>  $actStartDt,
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400, 'errors' => $validator->getMessageBag()->toArray()), 400);
        }

        $user = Auth::user();
        $project = DepartmentProjects::find($request->projectid);
        if (!$project) {
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Project not found, please try again!'], 200);
        }

        try {
            $result = $projectService->updateStatus(
                $project,
                $user,
                $request->status,
                $request->act_start_date
            );

            return response()->json([
                'code' => 200,
                'success' => $result['success'],
                'message' => $result['message'],
            ], 200);
        } catch (Exception $ex) {
            Log::error('Project Status Error: ' . $ex->getMessage() . ' @ Line - ' . $ex->getLine());

            return response()->json(['code' => 200, 'success' => false, 'message' => 'Project status update failed, please try again!'], 200);
        }
    }

    public function history($projectid)
    {
        try {
            $id = base64_decode($projectid);
        } catch (\Exception $e) {
            $id = $projectid;
        }

        $project = DepartmentProjects::with(['histories.user', 'tasks.user', 'tasks.logs.user', 'tasks.histories.user', 'tasks.documents.user', 'clients', 'category', 'documents.user'])->find($id);

        if ($project) {
            return view('projects.history', compact('project'));
        }

        return redirect()->back()->with('error', 'Project not found');
    }

    public function getTeamLeadersByCategory(Request $request)
    {
        $category_id = $request->category_id;
        $category = DB::table('project_category')->where('id', $category_id)->first();

        if (!$category) return response()->json(['status' => false, 'data' => []]);

        $categoryName = $category->category;
        $user = Auth::user();

        // If the logged-in user is a Team Leader, they should only see themselves
        if ($user->hasRole('Team-Leader')) {
            $teamLeaders = [['id' => $user->id, 'name' => $user->name]];
        } else {
            // Managers and Admins see TLs belonging to the team that matches the category name
            $teamLeaders = User::role('Team-Leader')
                ->where('status', 'Active')
                ->whereHas('teamMember.team', function ($q) use ($categoryName) {
                    $q->where('name', 'like', '%' . $categoryName . '%');
                })
                ->select('id', 'name')
                ->get();
        }

        return response()->json(['status' => true, 'data' => $teamLeaders]);
    }

    public function assignToTL(Request $request)
    {
        $project = DepartmentProjects::find($request->projectid);
        if (!$project) return response()->json(['status' => false, 'message' => 'Project not found']);

        if ($project->status == 'Completed') {
            return response()->json(['status' => false, 'message' => 'Completed projects cannot be reassigned.']);
        }

        if ($project->assigned_to) {
            return response()->json(['status' => false, 'message' => 'Project is already assigned to a Team Leader.']);
        }
        $assign_to = $request->assigned_to;

        // If TL is assigning to themselves
        if ($user->hasRole('Team-Leader') && !$assign_to) {
            $assign_to = $user->id;
        }

        if (!$assign_to) return response()->json(['status' => false, 'message' => 'Target Team Leader not specified']);

        try {
            $project->assigned_to = $assign_to;
            $project->status = 'InProgress'; // Change to InProgress once assigned to a specific TL
            $project->save();

            // Link to the TL's team if not already linked
            $tlTeam = TeamMembers::where('user', $assign_to)->where('status', true)->first();
            if ($tlTeam) {
                \App\Models\TeamProject::updateOrCreate(
                    ['projectid' => $project->id],
                    [
                        'teamid' => $tlTeam->team,
                        'assigned_by' => $user->id,
                        'assigned_date' => Carbon::now()
                    ]
                );
            }

            $targetUser = User::find($assign_to);
            DepartmentProjectHistoryController::store($project, "Project assigned to Team Leader: " . $targetUser->name, $user->id);

            ProjectNotificationService::notifyProject($project, [
                'category' => 'Project',
                'header'   => 'Project Assigned to TL',
                'body'     => "Project '{$project->project_name}' has been assigned to Team Leader {$targetUser->name}",
                'link'     => url('/') . "/projects/" . base64_encode($project->id) . "/history"
            ]);

            return response()->json(['status' => true, 'message' => 'Project successfully assigned']);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getEmployeesByProject(Request $request)
    {
        $projectId = $request->project_id;
        $project = DepartmentProjects::with('project_team.team.teammembers')->find($projectId);
        if (!$project) return response()->json(['status' => false, 'message' => 'Project not found']);

        $user = Auth::user();

        $query = User::where('status', 'Active');

        if ($project->project_team && $project->project_team->team) {
            // Show only members of the assigned team
            $teamUserIds = $project->project_team->team->teammembers->pluck('user');
            $query->whereIn('id', $teamUserIds);
        } else {
            // Fallback: If no team assigned, show department members
            $deptId = $project->category;
            $query->whereHas('teamMember', function ($q) use ($deptId) {
                $q->where('department', $deptId);
            });
        }

        // If Team Leader, only show employees in their team (additional filter for safety)
        if ($user->hasRole('Team-Leader')) {
            $teamMember = TeamMembers::where('user', $user->id)->where('status', true)->first();
            if ($teamMember) {
                $query->whereHas('teamMember', function ($q) use ($teamMember) {
                    $q->where('team', $teamMember->team);
                });
            }
        }

        $employees = $query->select('id', 'name')->orderBy('name')->get();

        $data = [];
        foreach ($employees as $emp) {
            $name = ($emp->id == $user->id) ? $emp->name . ' (Assign to me)' : $emp->name;
            $data[] = ['id' => $emp->id, 'name' => $name];
        }

        // Always ensure the current user is an option if they are not already in the list
        $hasCurrentUser = collect($data)->contains('id', $user->id);
        if (!$hasCurrentUser) {
            array_unshift($data, ['id' => $user->id, 'name' => $user->name . ' (Assign to me)']);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }
}
