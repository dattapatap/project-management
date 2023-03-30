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

    public function index()
    {
        $tasks = Task::with('project')->all();
        return view('components.projects.tasks', compact('tasks'));
    }

    public function create(Task $task)
    {

    }

    public function store(TaskRequest $request)
    {
        $user = Auth::user();
        $project = DepartmentProjects::find($request->project);

        try{
            //Add Task, add users assigned, send notifications to assigned user
            DB::beginTransaction();

            $task  = new Task();
            $task->projectid            =   $request->project;
            $task->created_by           =   $user->id;
            $task->title                =   $request->title;
            $task->description          =   $request->description;
            $task->status               =   $request->status;
            $task->priority             =   $request->priority;
            $task->startdate            =   $request->startdate;
            $task->enddate              =   $request->enddate;
            $task->save();

            foreach($request->taskUsers as $usr){
                $taskUser           = new TaskUser();
                $taskUser->taskid   = $task->id;
                $taskUser->userid   = $usr;
                $taskUser->assigned_dt   = Carbon::now();
                $taskUser->save();

                $currUser = User::find($usr);
                $currUser->notify((new TaskAssigned($task, $category="New Task"))->delay(now()->addSeconds(5)));

            }
            DB::commit();

        }catch(Exception $ex){
            DB::rollBack();
            Log::error("Task Creation Error : ".$ex->getMessage());
            return redirect()->back()->with('error', $ex->getMessage())->withInput();
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
