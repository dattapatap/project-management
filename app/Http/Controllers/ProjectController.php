<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Services\Od\ProjectService;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->status;
        $department = $request->query('department');

        if (is_null($status)) {
            $status = 'Pending';
        }

        if (!$user->hasRole(['Admin', 'Branch-Manager'])) {
            $department = $user->departments->department ?? null;
        }

        $result = $this->projectService->getProjectIndexData($user, $status, $department);
        $projects = $result['projects'];
        $stats = $result['stats'];

        if ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant'])) {
            $allTasks = $user->tasks()->with(['project.projectCategory', 'project.clients'])->orderBy('created_at', 'desc')->get();
            $activeTasks = $allTasks->whereIn('status', ['ToDo', 'InProgress']);
            $completedTasks = $allTasks->where('status', 'Completed');
            return view('components.projects.employee_index', compact('projects', 'stats', 'activeTasks', 'completedTasks', 'department'));
        }

        return view('components.projects.index', compact('projects', 'stats', 'department'))->with('search', '');
    }

    public function search(Request $request)
    {
        $filter = $request->query('search');
        $department = $request->query('department');
        $user = Auth::user();

        if (!$user->hasRole(['Admin', 'Branch-Manager'])) {
            $department = $user->departments->department ?? null;
        }

        $result = $this->projectService->searchProjects($user, $filter, 50, $department);
        $projects = $result['projects'];
        $stats = $result['stats'];

        return view('components.projects.index', compact('projects', 'stats', 'department'))->with('search', $filter);
    }

    public function create()
    {
        $clients = Clients::where('status', 'Matured')->orderBy('name', 'asc')->get();
        return view('components.projects.create', compact('clients'));
    }

    public function assignToTeam(Request $request)
    {
        $rules = [
            'project' => 'required|numeric',
            'team'    => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(['status' => 400, 'errors' => $validator->getMessageBag()->toArray()], 400);
        }

        try {
            $result = $this->projectService->assignToTeam(
                $request->post('project'),
                $request->post('team'),
                Auth::user()
            );

            return response()->json([
                'code'    => 200,
                'success' => $result['success'],
                'message' => $result['message']
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                'code'    => 201,
                'success' => false,
                'message' => $ex->getMessage() . ' : Line - ' . $ex->getLine()
            ], 200);
        }
    }

    public function edit(Request $request)
    {
        $projectid = $request->project;
        $project = DepartmentProjects::with('clients')->where('id', $projectid)->first();
        if ($project) {
            return response()->json(['success' => true, 'project' => $project]);
        } else {
            return response()->json(['success' => false, 'message' => "Opps, project not found!"]);
        }
    }

    public function update(Request $request)
    {
        $rules = [
            'project_name'   => 'required|string',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date',
            'act_start_date' => 'required|date',
            'description'    => 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(['status' => 400, 'errors' => $validator->getMessageBag()->toArray()], 400);
        }

        try {
            $result = $this->projectService->updateProjectDetails(
                $request->post('project-id'),
                $request->only(['project_name', 'start_date', 'end_date', 'act_start_date', 'description']),
                Auth::user()
            );

            return response()->json(['code' => 200, 'success' => $result['success'], 'message' => $result['message']], 200);
        } catch (Exception $ex) {
            return response()->json(['code' => 201, 'success' => false, 'message' => $ex->getMessage()], 200);
        }
    }

    public function projectupdate(Request $request)
    {
        $rules = [
            'remarks' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(['status' => 400, 'errors' => $validator->getMessageBag()->toArray()], 400);
        }

        try {
            $result = $this->projectService->addProjectHistoryRemark(
                $request->projectid,
                $request->remarks,
                Auth::user()
            );

            return response()->json(['code' => 200, 'success' => true, 'message' => $result['message']], 200);
        } catch (Exception $ex) {
            return response()->json(['code' => 201, 'success' => false, 'message' => $ex->getMessage()], 200);
        }
    }

    public function status(Request $request)
    {
        $rules = [
            'status'         => 'required|string',
            'act_start_date' => 'nullable',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(['status' => 400, 'errors' => $validator->getMessageBag()->toArray()], 400);
        }

        $project = DepartmentProjects::find($request->projectid);
        if (!$project) {
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Project not found, please try again!'], 200);
        }

        try {
            $result = $this->projectService->updateStatus(
                $project,
                Auth::user(),
                $request->status,
                $request->act_start_date
            );

            return response()->json([
                'code'    => 200,
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
        } catch (Exception $e) {
            $id = $projectid;
        }

        $project = DepartmentProjects::with(['histories.user', 'tasks.user', 'tasks.logs.user', 'tasks.histories.user', 'tasks.documents.user', 'clients', 'projectCategory', 'documents.user'])->find($id);

        if ($project) {
            return view('projects.history', compact('project'));
        }

        return redirect()->back()->with('error', 'Project not found');
    }

    /**
     * Project Gantt / Timeline view.
     */
    public function timeline(Request $request)
    {
        $user = Auth::user();
        $projects = $this->projectService->getTimelineProjects($user);

        return view('components.projects.timeline', compact('projects'));
    }

    public function getTeamLeadersByCategory(Request $request)
    {
        $category_id = $request->category_id;
        $category = \DB::table('project_category')->where('id', $category_id)->first();

        if (!$category) return response()->json(['status' => false, 'data' => []]);

        $categoryName = $category->category;
        $user = Auth::user();

        // If the logged-in user is a Team Leader, they should only see themselves
        if ($user->hasRole('Team-Leader')) {
            $teamLeaders = [['id' => $user->id, 'name' => $user->name]];
        } else {
            // Managers and Admins see TLs belonging to the team that matches the category name
            $teamLeaders = \App\Models\User::role('Team-Leader')
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
        try {
            $result = $this->projectService->assignToTL(
                $request->projectid,
                $request->assigned_to,
                Auth::user()
            );

            return response()->json(['status' => $result['success'], 'message' => $result['message']]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getEmployeesByProject(Request $request)
    {
        Log::info("getEmployeesByProject called with parameters:", $request->all());
        try {
            $interTeam = $request->query('inter_team') == '1';
            $employees = $this->projectService->getEmployeesForProject(
                $request->project_id,
                Auth::user(),
                $interTeam
            );
            Log::info("getEmployeesByProject success, count: " . count($employees));
            return response()->json(['status' => true, 'data' => $employees]);
        } catch (Exception $e) {
            Log::error("getEmployeesByProject error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function activeProjectsList()
    {
        $user = Auth::user();
        $query = DepartmentProjects::query();

        if ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant']) && !$user->hasRole('Team-Leader')) {
            $query->whereHas('tasks', fn($q) => $q->where('assigned_to', $user->id));
        } elseif ($user->hasRole('Team-Leader')) {
            $teamMember = \App\Models\TeamMembers::where('user', $user->id)->where('status', true)->first();
            $teamId = $teamMember?->team;

            $query->where(function ($q) use ($user, $teamId) {
                $q->where('assigned_to', $user->id);
                if ($teamId) {
                    $q->orWhereHas('project_team', fn($sq) => $sq->where('teamid', $teamId));
                }
                $q->orWhereHas('tasks', function ($sq) use ($user, $teamId) {
                    $sq->where('assigned_to', $user->id);
                    if ($teamId) {
                        $sq->orWhereHas('user.teamMember', fn($ssq) => $ssq->where('team', $teamId));
                    }
                });
            });
        }

        $projects = $query->with(['clients:id,name', 'projectCategory:id,category'])
            ->orderBy('project_name', 'asc')
            ->get();

        $data = [];
        foreach ($projects as $proj) {
            $clientName = $proj->clients?->name ?? 'No Client';
            $categoryName = $proj->projectCategory?->category ?? '';

            // Format: "Client Name | Project Name (Category)"
            $displayName = "{$clientName} | {$proj->project_name}";
            if ($categoryName && strtolower($categoryName) !== strtolower($proj->project_name)) {
                $displayName .= " ({$categoryName})";
            }

            $data[] = [
                'id' => $proj->id,
                'project_name' => $displayName,
                'status' => $proj->status
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
