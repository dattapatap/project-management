<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;

class TaskController extends Controller
{

    public function index(Request $request)
    {

        $project_id = base64_decode($request->project);
        $project  = DepartmentProjects::findOrFail($project_id);

        $completed  = Task::where('status', 'Completed')->get();
        $todo       = Task::where('status', 'ToDo')->get();
        $inprocess  = Task::where('status', 'InProgress')->get();

        return view('components.projects.tasksbar', compact('completed', 'todo', 'inprocess', 'project'));

    }

    public function create(Task $task)
    {

    }

    public function addtask(TaskRequest $request) //
    {
        $user = Auth::user();
        $project = DepartmentProjects::find($request->task_projectid);
        try{
            //Add Task, add users assigned, send notifications to assigned user
            DB::beginTransaction();

            $task  = new Task();
            $task->projectid            =   $request->project;
            $task->created_by           =   $user->id;
            $task->title                =   $request->task_title;
            $task->description          =   $request->task_description;
            $task->status               =   "ToDo";
            $task->priority             =   $request->task_priority;
            $task->startdate            =   $request->task_est_start_date;
            $task->enddate              =   $request->task_est_end_date;
            $task->assigned_to          =   $request->task_user;
            $task->save();

            $comment = "New Task Created by ".$user->name;
            DepartmentProjectHistoryController::store($task, $comment , $request->task_user);

            $currUser = User::find($request->task_user);
            $currUser->notify((new TaskAssigned($task, $category="New Task"))->delay(now()->addSeconds(5)));

            DB::commit();

            return response()->json(['code'=>200, "success"=>true, 'message'=> "New Task Created" ], 200);

        }catch(Exception $ex){
            DB::rollBack();
            Log::error("Task Creation Error : ".$ex->getMessage());
            return response()->json(['code'=>200, "success"=>false, 'message'=> "Task not created, please try again!" ], 200);
        }
    }


    public function show(Task $task)
    {

    }


    public function edit(Task $task)
    {
        return response()->json(['success'=>true, 'task' => $task ]);
    }

    public function update(Request $request, Task $task)
    {
        //
    }


    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['success'=>true, 'message' => "Task Deleted" ]);
    }
}
