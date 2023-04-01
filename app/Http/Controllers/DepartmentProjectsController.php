<?php

namespace App\Http\Controllers;

use App\Models\ClientHistory;
use App\Models\ClientPackages;
use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Notifications\ClientMatured;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Response;
use Validator;

class DepartmentProjectsController extends Controller
{
    public function createNewProject(Request $request){

        $rules = array(
            'client_name'   => 'required|string',
            'department'    => 'required|numeric',
            'category'      => 'required|numeric',
            'package'       => 'required|numeric|gt:0',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'description'   => 'required|string'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{

            try{
                $userid = Auth::user()->id;
                $client = Clients::findOrFail($request->post('client-id'));

                DB::beginTransaction();

                $projectnm   = DB::table('department_roles')->where('id', $request->category )->first();

                // Assign Project to Department
                $dept   = new DepartmentProjects();
                $dept->client           =   $request->post('client-id');
                $dept->department       =   $request->department;
                $dept->category         =   $request->category;
                $dept->assigned_by      =   $userid;
                $dept->created_date     =   Carbon::now();
                $dept->project_name     =   $projectnm->dept_role_name;
                $dept->start_date       =   Carbon::parse($request->start_date)->format('Y-m-d h:i');
                $dept->end_date         =   Carbon::parse($request->end_date)->format('Y-m-d h:i');
                $dept->status           =   "NOT Assigned";
                $dept->description      =   $request->description;
                $dept->save();


                //Create Client Package
                $clipack = new ClientPackages();
                $clipack->client           = $request->post('client-id');
                $clipack->project_id       = $dept->id;
                $clipack->package          = $request->package;
                $clipack->balance          = $request->package;
                $clipack->created_by       = $userid;
                $clipack->updated_by       = $userid;
                $clipack->save();

                // Get Department Members and filter by role
                $productManager = User::whereHas('roles', function($q){  $q->where('name', 'Project-Manager' ); })->where('status', 'Active')->get();
                for($ctr=0; $ctr < count($productManager); $ctr++ ){
                    $currUser = $productManager[$ctr];
                    $currUser->notify((new ClientMatured($client,  $dept, $category=$projectnm->name))->delay(now()->addSeconds(5)));
                }

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "Project Created" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }

    }


}
