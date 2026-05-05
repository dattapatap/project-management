<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Requests\TaskUpdate;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskLog;
use App\Models\TaskUser;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class TaskController extends Controller
{

    public function index(Request $request)
    {

        $project_id = base64_decode($request->project);
        $project  = DepartmentProjects::findOrFail($project_id);
        $user = Auth::user();

        // Base query for tasks in this project
        $query = Task::with('user')->where('projectid', $project_id);

        // If regular employee (not Manager/Admin/TL), only show their assigned tasks
        if ($user->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant']) && !$user->hasRole('Team-Leader')) {
            $query->where('assigned_to', $user->id);
        }

        $completed  = (clone $query)->where('status', 'Completed')->get();
        $todo       = (clone $query)->where('status', 'ToDo')->get();
        $inprocess  = (clone $query)->where('status', 'InProgress')->get();

        return view('components.projects.tasksbar', compact('completed', 'todo', 'inprocess', 'project'));
    }

    public function create(Task $task) {}

    public function addtask(TaskRequest $request) //
    {
        $user = Auth::user();
        $project = DepartmentProjects::find($request->task_projectid);
        try {
            DB::beginTransaction();

            $task  = new Task();
            $task->projectid            =   $request->project;
            $task->created_by           =   $user->id;
            $task->title                =   $request->task_title;
            $task->description          =   $request->task_description;
            $task->status               =   "ToDo";
            $task->priority             =   $request->task_priority;
            $task->startdate            =   Carbon::parse($request->task_est_start_date)->format('Y-m-d H:i:s');
            $task->enddate              =   Carbon::parse($request->task_est_end_date)->format('Y-m-d H:i:s');
            $task->assigned_to          =   $request->task_user;
            $task->save();

            // If project was completed, revert it to InProgress because new work was added
            if ($project && $project->status == 'Completed') {
                $project->status = 'InProgress';
                $project->save();
                DepartmentProjectHistoryController::store($project, "Project reopened automatically due to new task creation: " . $task->title, $user->id);
            }

            $comment = "New Task Created by " . $user->name;
            DepartmentProjectHistoryController::store($task, $comment, $request->task_user);

            $currUser = User::find($request->task_user);
            $currUser->notify((new TaskAssigned($task, $category = "New Task"))->delay(now()->addSeconds(5)));

            // Handle Task Document Uploads
            if ($request->hasFile('task_documents')) {
                foreach ($request->file('task_documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = time() . '_' . $originalName;
                    $path = $file->storeAs('documents/tasks/' . $task->id, $fileName, 'local');

                    \App\Models\Document::create([
                        'documentable_id'   => $task->id,
                        'documentable_type' => \App\Models\Task::class,
                        'file_name'         => $fileName,
                        'original_name'     => $originalName,
                        'file_path'         => $path,
                        'file_type'         => $file->getClientOriginalExtension(),
                        'file_size'         => $file->getSize(),
                        'user_id'           => $user->id
                    ]);
                }
            }

            DB::commit();

            return response()->json(['code' => 200, "success" => true, 'message' => "New Task Created"], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error("Task Creation Error : " . $ex->getMessage());
            return response()->json(['code' => 200, "success" => false, 'message' => "Task not created, please try again!"], 200);
        }
    }


    public function show(Request $request)
    {
        $taskid = base64_decode($request->taskid);
        $task = Task::with(['logs.user', 'comments.user', 'project', 'user', 'createdby'])->find($taskid);
        return view('components.projects.taskdetails', compact('task'));
    }


    public function edit(Task $task)
    {
        if ($task)
            return response()->json(['success' => true, 'task' => $task]);
        else
            return response()->json(['success' => false, 'message' => "Task not exist"]);
    }

    public function update(TaskUpdate $taskUpdate, Task $task)
    {
        $user = Auth::user();
        $task  = Task::find($taskUpdate->task_id);
        if (!$task)
            return response()->json(['code' => 200, "success" => false, 'message' => "Task not found, please try again!"], 200);

        try {
            DB::beginTransaction();

            $task->title                =   $taskUpdate->txt_task_title;
            $task->description          =   $taskUpdate->txt_task_description;
            $task->priority             =   $taskUpdate->txt_task_priority;
            $task->startdate            =   Carbon::parse($taskUpdate->txt_task_est_start_date)->format('Y-m-d H:i:s');
            $task->enddate              =   Carbon::parse($taskUpdate->txt_task_est_end_date)->format('Y-m-d H:i:s');
            $task->assigned_to          =   $taskUpdate->txt_task_user;
            $task->save();

            $comment = "Task updated by " . $user->name;
            DepartmentProjectHistoryController::store($task, $comment, $taskUpdate->txt_task_user);

            DB::commit();
            return response()->json(['code' => 200, "success" => true, 'message' => "Task Updated"], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error("Task Updation Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
            return response()->json(['code' => 200, "success" => false, 'message' => "Task not updated, please try again!"], 200);
        }
    }


    public function addTaskLog(Request $request)
    {
        $rules = array(
            'log_date'             => 'required|date|date_format:d/m/Y',
            'log_start_time'       => 'required|date_format:h:i A',
            'log_end_time'         => 'required|date_format:h:i A',
            'log_time_spend'       => 'required',
            'log_description'      => 'required|string|min:15',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400, 'errors' => $validator->getMessageBag()->toArray()), 400);
        } else {

            $user = Auth::user();
            try {

                $taskLog                = new TaskLog();
                $taskLog->taskid        = $request->tasklog;
                $taskLog->userid        = $user->id;
                $taskLog->starttime     = Carbon::createFromFormat('h:i A', $request->log_start_time)->format('H:i:s');
                $taskLog->endtime       = Carbon::createFromFormat('h:i A', $request->log_end_time)->format('H:i:s');
                $taskLog->time_spend    = $request->log_time_spend;
                $taskLog->log_date      = Carbon::parse($request->log_date)->format('Y-m-d');
                $taskLog->log_description = $request->log_description;
                $taskLog->save();

                return response()->json(['code' => 200, "success" => true, 'message' => "Task Log Added"], 200);
            } catch (Exception $ex) {
                Log::error("Task Updation Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
                return response()->json(['code' => 200, "success" => false, 'message' => "Task log not updated, please try again!"], 200);
            }
        }
    }


    public function changestatus(Request $request)
    {
        if ($request->status == 'InProgress') {
            $actStartDt = 'required';
        } else {
            $actStartDt = 'nullable';
        }

        $rules = array(
            'status'         => 'required|string',
            'act_start_date' =>  $actStartDt,
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400, 'errors' => $validator->getMessageBag()->toArray()), 400);
        } else {
            $user = Auth::user();
            $task  = Task::find($request->taskid);
            if (!$task)
                return response()->json(['code' => 200, "success" => false, 'message' => "Task not found, please try again!"], 200);

            try {

                if ($request->status === 'Completed') {
                    $task->status = $request->status;
                    $task->progress = 100;
                    $task->act_enddate  = Carbon::now()->format('Y-m-d H:i');
                } else if ($request->status === 'InProgress') {
                    $task->status = $request->status;
                    $task->act_startdate = Carbon::parse($request->act_start_date)->format('Y-m-d H:i:s');
                } else {
                    $task->status = $request->status;
                }
                $task->save();

                $comment = 'Task status updated as ' . $task->status . ' by ' . $user->name;
                DepartmentProjectHistoryController::store($task, $comment, $user->id);

                return response()->json(['code' => 200, "success" => true, 'message' => "Task Status Updated"], 200);
            } catch (Exception $ex) {
                Log::error("Task Updation Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
                return response()->json(['code' => 200, "success" => false, 'message' => "Task status updated, please try again!"], 200);
            }
        }
    }


    public function updateProgress(Request $request)
    {

        $user = Auth::user();
        $task  = Task::find($request->task_id);
        if (!$task)
            return response()->json(['code' => 200, "success" => false, 'message' => "Task not found, please try again!"], 200);

        try {
            $task->progress = $request->progerss;
            $task->save();

            $comment = 'Task progress updated as ' . $request->progerss . '% completed by ' . $user->name;

            DepartmentProjectHistoryController::store($task, $comment, $user->id);
            return response()->json(['code' => 200, "success" => true, 'message' => "Task Progress Updated"], 200);
        } catch (Exception $ex) {
            Log::error("Task Updation Error : " . $ex->getMessage() . " @:@ Line - " . $ex->getLine());
            return response()->json(['code' => 200, "success" => false, 'message' => "Task progress updated, please try again!"], 200);
        }
    }

    public function addComment(Request  $request)
    {
        $user = Auth::user();
        $taskid = $request->task_id;
        $comment = $request->task_comment;

        try {
            $taskComment = new TaskComment();
            $taskComment->taskid  = $taskid;
            $taskComment->userid  = $user->id;
            $taskComment->parent  = '0';
            $taskComment->comment = $comment;

            $taskComment->save();

            return redirect()->back()->with('success', "comment posted");
        } catch (Exception $ex) {
            return redirect()->back()->with('error', "comment not posted, please try again")->withInput();
        }
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted successfully');
    }
}
