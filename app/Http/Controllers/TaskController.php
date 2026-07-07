<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Requests\TaskUpdate;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\Od\TaskService;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private TaskRepository $taskRepo,
    ) {}

    public function index(Request $request)
    {
        $project_id = base64_decode($request->project);
        $project    = DepartmentProjects::findOrFail($project_id);
        $user       = Auth::user();

        // Scope tasks to user if they are a regular employee
        $userId = $user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant']) && !$user->hasRole('Team-Leader')
            ? $user->id
            : null;

        $kanban = $this->taskRepo->getKanbanColumns($project_id, $userId);

        return view('components.projects.tasksbar', [
            'completed'  => $kanban['completed'],
            'todo'       => $kanban['todo'],
            'inprocess'  => $kanban['inprogress'],
            'project'    => $project,
        ]);
    }

    public function create(Task $task) {}

    public function addtask(TaskRequest $request)
    {
        try {
            $result = $this->taskService->createTask([
                'project_id'  => $request->project,
                'title'       => $request->task_title,
                'description' => $request->task_description,
                'priority'    => $request->task_priority,
                'start_date'  => $request->task_est_start_date,
                'end_date'    => $request->task_est_end_date,
                'assigned_to' => $request->task_user,
                'documents'   => $request->file('task_documents') ?? [],
            ], Auth::user());

            return response()->json(['code' => 200, 'success' => $result['success'], 'message' => $result['message']], 200);
        } catch (Exception $ex) {
            Log::error("Task Creation Error : " . $ex->getMessage());
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Task not created, please try again!'], 200);
        }
    }

    public function show(Request $request)
    {
        $taskid = base64_decode($request->taskid);
        $task = Task::with(['logs.user', 'comments.user', 'project', 'user', 'createdby', 'histories.user'])->find($taskid);
        return view('components.projects.taskdetails', compact('task'));
    }

    public function edit(Task $task)
    {
        if ($task) {
            $isAssignedToTl = $task->user ? $task->user->hasRole('Team-Leader') : false;
            return response()->json([
                'success' => true, 
                'task' => $task,
                'is_assigned_to_tl' => $isAssignedToTl
            ]);
        } else {
            return response()->json(['success' => false, 'message' => "Task not exist"]);
        }
    }

    public function update(TaskUpdate $taskUpdate, Task $task)
    {
        $task = Task::find($taskUpdate->task_id);
        if (!$task) {
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Task not found, please try again!'], 200);
        }

        try {
            $result = $this->taskService->updateTask($task, [
                'title'       => $taskUpdate->txt_task_title,
                'description' => $taskUpdate->txt_task_description,
                'priority'    => $taskUpdate->txt_task_priority,
                'start_date'  => $taskUpdate->txt_task_est_start_date,
                'end_date'    => $taskUpdate->txt_task_est_end_date,
                'assigned_to' => $taskUpdate->txt_task_user,
            ], Auth::user());

            return response()->json(['code' => 200, 'success' => $result['success'], 'message' => $result['message']], 200);
        } catch (Exception $ex) {
            Log::error("Task Updation Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Task not updated, please try again!'], 200);
        }
    }

    public function startTimer(Task $task)
    {
        try {
            $result = $this->taskService->startTimer($task, Auth::user());
            $response = ['code' => 200, 'success' => $result['success'], 'message' => $result['message']];

            if (isset($result['timer'])) {
                $response['timer'] = [
                    'id'        => $result['timer']->id,
                    'starttime' => $result['timer']->starttime,
                    'log_date'  => $result['timer']->log_date,
                ];
            }

            return response()->json($response, 200);
        } catch (Exception $ex) {
            Log::error("Timer Start Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Failed to start timer: ' . $ex->getMessage()], 200);
        }
    }

    public function pauseTimer(Task $task)
    {
        try {
            $result = $this->taskService->pauseTimer($task, Auth::user());
            return response()->json(['code' => 200, 'success' => $result['success'], 'message' => $result['message']], 200);
        } catch (Exception $ex) {
            Log::error("Timer Pause Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Failed to pause timer: ' . $ex->getMessage()], 200);
        }
    }

    public function changestatus(Request $request)
    {
        $rules = ['status' => 'required|string'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(['status' => 400, 'errors' => $validator->getMessageBag()->toArray()], 400);
        }

        $task = Task::find($request->taskid);
        if (!$task) {
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Task not found, please try again!'], 200);
        }

        try {
            $result = $this->taskService->changeStatus($task, Auth::user(), $request->status, null);
            return response()->json(['code' => 200, 'success' => $result['success'], 'message' => $result['message']], 200);
        } catch (Exception $ex) {
            Log::error("Task Updation Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Task status update failed, please try again!'], 200);
        }
    }

    public function updateProgress(Request $request)
    {
        $task = Task::find($request->task_id);
        if (!$task) {
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Task not found, please try again!'], 200);
        }

        try {
            $result = $this->taskService->updateProgress($task, $request->progerss, Auth::user());
            return response()->json(['code' => 200, 'success' => $result['success'], 'message' => $result['message']], 200);
        } catch (Exception $ex) {
            Log::error("Task Updation Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
            return response()->json(['code' => 200, 'success' => false, 'message' => 'Task progress update failed, please try again!'], 200);
        }
    }

    public function moveTask(Request $request)
    {
        $task = Task::find($request->task_id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        try {
            $result = $this->taskService->moveTask($task, Auth::user(), $request->status);
            return response()->json(['success' => $result['success'], 'message' => $result['message']]);
        } catch (Exception $ex) {
            Log::error("Task Move Error : " . $ex->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to move task'], 500);
        }
    }

    public function addComment(Request $request)
    {
        $user   = Auth::user();
        $taskid = $request->task_id;
        $comment = $request->task_comment;

        try {
            $taskComment = new \App\Models\TaskComment();
            $taskComment->taskid  = $taskid;
            $taskComment->userid  = $user->id;
            $taskComment->parent  = '0';
            $taskComment->comment = $comment;
            $taskComment->save();

            $task = Task::find($taskid);
            \App\Models\UserActivity::log('Task Comment Added', "Posted a new comment on task '{$task->title}'");

            \App\Services\ProjectNotificationService::notifyTask($task, [
                'category' => 'Comment',
                'header'   => 'New Task Comment',
                'body'     => "{$user->name} commented on '{$task->title}': " . substr($comment, 0, 50) . "...",
                'link'     => url('/') . "/projects/taskboard/" . base64_encode($task->projectid) . "?task_id=" . $task->id,
            ], true);

            return redirect()->back()->with('success', 'comment posted');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'comment not posted, please try again')->withInput();
        }
    }

    public function nudge(Task $task)
    {
        $result = $this->taskService->nudge($task, Auth::user());
        return response()->json($result);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted successfully');
    }
}
