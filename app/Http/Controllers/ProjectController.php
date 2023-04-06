<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\DepartmentProjectHistory;
use App\Models\DepartmentProjects;
use App\Models\TeamMembers;
use App\Models\TeamProject;
use App\Models\Teams;
use App\Models\User;
use App\Notifications\ProjectAssigned;
use App\Notifications\ProjectUpdate;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Response;
use Validator;

class ProjectController extends Controller
{
    public function index(Request $request){
        $projects = DepartmentProjects::with('tasks')->get();

        return view('components.projects.index', compact('projects'));

    }

    public function assignToTeam(Request $request){
        $rules = array(
            'project' => 'required|numeric',
            'team'      => 'required|numeric',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                DB::beginTransaction();

                // Check weather project already assigned or not
                $isAssigned = TeamProject::where('teamid', $request->post('team'))->where('projectid', $request->post('project') )->first();
                if($isAssigned){
                    return response()->json(['code'=>200, "success"=>false, 'message'=> "Project Assigned Already" ], 200);
                }

                $user = Auth::user();
                $project        = DepartmentProjects::where('id', $request->post('project') )->first();
                $teamMem     = Teams::where('id', $request->post('team') )->with('teammembers')->first();

                //Assign to team
                $teamproj = new TeamProject();
                $teamproj->projectid        = $request->post('project');
                $teamproj->teamid           = $request->post('team');
                $teamproj->assigned_by      = $user->id;
                $teamproj->assigned_date    = Carbon::now();
                $teamproj->save();

                // Update Project status
                $project->status = 'TODO';
                $project->save();

                // send notifications to team member
                if($teamMem->teammembers->count() > 0){
                    $teamLeads = $teamMem->teammembers;
                    $length = count($teamLeads);
                    for ($ctr=0; $ctr < $length ; $ctr++) {
                        $currUser = User::where('id', $teamLeads[$ctr]->user)
                                            ->whereHas('roles', function($query){
                                                $query->where('name', 'Team-Leader');
                                            })->first();
                        if($currUser){
                            $currUser->notify((new ProjectAssigned($project, $category="New Project"))->delay(now()->addSeconds(5)));
                        }
                    }
                }


                DB::commit();
                return response()->json(['code'=>200, "success"=>true, 'message'=> "Assigned" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'success'=>false, 'message'=>$ex->getMessage().' : Line - '.$ex->getLine() ], 200);
            }
        }
    }

    public function edit(Request $request){
        $projectid = $request->project;
        $project  = DepartmentProjects::with('client')->where('id', $projectid)->first();
        if($project)
             return response()->json(['success'=>true, 'project'=> $project ]);
        else
             return response()->json(['success'=>false, 'message'=> "Opps, project not found!" ]);

    }

    public function update(Request $request){
        $rules = array(
            'project_name'      => 'required|string',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date',
            'act_start_date'    => 'required|date',
            'description'       => 'required|string'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{

            try{
                $userid = Auth::user()->id;
                $project = DepartmentProjects::where('id',$request->post('project-id'))->first();
                if(!$project){
                    return response()->json(['code'=>200, "success"=>false, 'message'=> "Project Not Found" ], 200);
                }

                DB::beginTransaction();

                $project->project_name     =   $request->project_name;
                $project->start_date       =   Carbon::parse($request->start_date)->format('Y-m-d h:i');
                $project->end_date         =   Carbon::parse($request->end_date)->format('Y-m-d h:i');
                $project->act_start_date   =   Carbon::parse($request->act_start_date)->format('Y-m-d h:i');
                $project->description      =   $request->description;
                $project->save();

                DepartmentProjectHistoryController::store($project, "Project Updated", $userid);


                // Get Department Members and filter by role
                $productManager = User::whereHas('roles', function($q){  $q->where('name', 'Project-Manager' ); })->where('status', 'Active')->get();
                for($ctr=0; $ctr < count($productManager); $ctr++ ){
                    $currUser = $productManager[$ctr];
                    $currUser->notify((new ProjectUpdate($project,  $category="Project Update"))->delay(now()->addSeconds(5)));
                }

                DB::commit();
                return response()->json(['code'=>200, "success"=>true, 'message'=> "Project Updated" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'success'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }

    }

    //Add History
    public function projectupdate(Request $request){
        $rules = array(
            'remarks'=> 'required|string',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                $userid = Auth::user()->id;
                $project = DepartmentProjects::find($request->projectid);

                //Create Client Package
                $history                   = new DepartmentProjectHistory();
                $history->histories()->associate($project);
                $history->comments        = $request->remarks;
                $history->date            = Carbon::now();
                $history->addedby         = $userid;
                $history->save();

                // Get Department Members and filter by role
                $productManager = User::whereHas('roles', function($q){  $q->where('name', 'Project-Manager' ); })->where('status', 'Active')->get();
                for($ctr=0; $ctr < count($productManager); $ctr++ ){
                    $currUser = $productManager[$ctr];
                    $currUser->notify((new ProjectUpdate($project,  $category="Project Update"))->delay(now()->addSeconds(5)));
                }
                return response()->json(['code'=>200, "success"=>true, 'message'=> "Project Updated" ], 200);

            }catch(Exception $ex){
                return response()->json(['code'=>201, 'success'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }


}
