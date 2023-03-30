<?php

namespace App\Http\Controllers;

use App\Models\DepartmentProjects;
use App\Models\TeamMembers;
use App\Models\TeamProject;
use App\Models\Teams;
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

                $user = Auth::user();
                $project        = DepartmentProjects::where('id', $request->post('project') )->first();
                $teamleader     = TeamMembers::where('team', $request->post('team') )
                                        ->whereHas('users', function($query){
                                            $query->where('name', 'Team-Leader');
                                        })->with('users')->first();

                $teamproj = new TeamProject();
                $teamproj->projectid        = $request->post('project');
                $teamproj->teamid           = $request->post('team');
                $teamproj->assigned_by      = $user->id;
                $teamproj->assigned_date    = Carbon::now();
                $teamproj->save();

                if($teamleader->users->count() > 0){


                }


                DB::commit();

                return response()->json(['code'=>200, "status"=>true, 'message'=> "Assigned" ], 200);


            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }

}
