<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\TeamMembers;
use App\Models\Teams;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Response;
use Validator;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $department = Department::where('name', $request->name)->firstOrFail();
        $teams = Teams::with('teammembers.users:name,id,profile')
                        ->where('department', $department->id)->get();
        return view('components.department.teams', compact('teams', 'department'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'name' => 'required|string',
            'department' => 'required|numeric',
            'description' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                if($request->team_id == '-1'){
                    $oldTeam= Teams::where('name', $request->post('name') )->where('department',  $request->post('department'))
                                            ->where('deleted_at', null)->first();
                    if($oldTeam){
                        return response()->json(['code'=>200, "status"=>false, 'message'=> "Team name already exist, Please try new name!" ], 200);
                    }else{

                        $teams = new Teams();
                        $teams->name = $request->post('name');
                        $teams->department = $request->post('department');
                        $teams->description = $request->post('description');
                        $teams->status = true;
                        $teams->save();
                        return response()->json(['code'=>200, "status"=>true, 'message'=> "Team Created" ], 200);
                    }
                }else{
                    $oldTeam = Teams::where('name', $request->post('name') )->where('department',  $request->post('department'))
                                    ->where('deleted_at', null)->where('id', '!=',  $request->team_id )->first();
                    if($oldTeam){
                        return response()->json(['code'=>200, "status"=>false, 'message'=> "Team name already exist, Please try new name!" ], 200);
                    }else{
                        $teams = Teams::where('id', $request->team_id)->first();
                        $teams->name = $request->post('name');
                        $teams->department = $request->post('department');
                        $teams->description = $request->post('description');
                        $teams->save();
                        return response()->json(['code'=>200, "status"=>true, 'message'=> "Team Updated" ], 200);
                    }
                }
                exit();
            }catch(Exception $ex){
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Teams  $teams
     * @return \Illuminate\Http\Response
     */
    public function show(Teams $teams)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Teams  $teams
     * @return \Illuminate\Http\Response
     */
    public function edit(Teams $team)
    {
       return response()->json([ 'status'=>true, 'data'=>$team ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Teams  $teams
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Teams $teams)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Teams  $teams
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teams $teams)
    {
        //
    }



    public function teammembers(Request $request){
        $department  = $request->department;
        $teamid  = $request->teamid;

        //get All user under team leader
        $deptMems = TeamMembers::with('users.roles:name')->where('team', $teamid)->where('status', true)->get();

        // get All users under department excludiing assigned
        $arrUsr = $deptMems->pluck('user')->toArray();
        $unsignedUsers = User::with('roles:name')->where('status', 'Active')
                                ->whereHas('departments', function($query) use($department){
                                    $query->where('department', $department);
                                })
                                ->whereHas('roles', function($role) {
                                    $role->where('name', '!=' ,'Project-Manager');
                                })
                                ->whereNotIn('id', $arrUsr)
                                ->orderBy('name', 'asc')->get()->toArray();

        return response()->json(['status'=>true, 'unsignedUsers'=>$unsignedUsers, 'signedUsers'=>$deptMems->toArray()], 200);
    }

    public function addMember(Request $request){
        $departmentId  = $request->deptid;
        $userid      = $request->userid;
        $teamid      = $request->teamid;

        $member = TeamMembers::where('user', $userid)
                                    ->where('team', $teamid)
                                    ->where('deleted_at', null)->first();
        if(!$member){
            $deptMem = new TeamMembers();
            $deptMem->user              = $userid;
            $deptMem->department        = $departmentId;
            $deptMem->team              = $teamid;
            $deptMem->from_date         = Carbon::now();
            $deptMem->status            = true;
            $deptMem->save();
            return response()->json(['code'=>200, "status"=>true, 'data'=> $deptMem ], 200);
        }else{
            $member->from_date         = Carbon::now();
            $member->save();
            return response()->json(['code'=>200, "status"=>true, 'data'=> $member ], 200);
        }
    }

    public function removeMember(Request $request){
        $memberid = $request->memberid;
        $deptMember = TeamMembers::where('id', $memberid)->first();
        if($deptMember){
            $deptMember->to_date = Carbon::now();
            $deptMember->delete();
        }

    }


}
