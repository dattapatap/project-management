<?php

namespace App\Http\Controllers;

use App\Models\ClientHistory;
use App\Models\ClientPackages;
use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Services\ProjectNotificationService;
use App\Models\User;
use App\Notifications\ClientMatured;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Response;
use Validator;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DepartmentProjectsController extends Controller
{
    public function createNewProject(Request $request){

        $rules = array(
            'department'    => 'required|numeric',
            'category'      => 'required|numeric',
            'package'       => 'nullable|numeric',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'description'   => 'required|string',
            'documents.*'   => 'nullable|file|max:10240', // Max 10MB per file
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{

            try{
                $userid = Auth::user()->id;
                $client = Clients::findOrFail($request->post('clientsid'));

                DB::beginTransaction();

                if ($client->status !== 'Matured') {
                    $client->status = 'Matured';
                    $client->updated_by = $userid;
                    $client->save();

                    // Log history
                    $client->histories()->create([
                        'category' => 'STS',
                        'status'   => 'Matured',
                        'remarks'  => 'Client matured automatically upon project creation by ' . Auth::user()->name,
                        'created'  => $userid,
                    ]);
                }

                $projectnm   = DB::table('project_sub_categories')->where('id', $request->category )->first();

                // Assign Project to Department
                $dept   = new DepartmentProjects();
                $dept->client           =   $request->post('clientsid');
                $dept->department       =   1;
                $dept->category         =   $request->department;
                $dept->sub_category     =   $request->category;
                $dept->assigned_by      =   $userid;
                $dept->assigned_to      =   $request->team_leader;
                $dept->created_date     =   Carbon::now();
                $dept->project_name     =   $projectnm->name;
                $dept->start_date       =   Carbon::parse($request->start_date)->format('Y-m-d h:i');
                $dept->end_date         =   Carbon::parse($request->end_date)->format('Y-m-d h:i');
                $dept->status           =   "ToDo";
                $dept->description      =   $request->description;
                $dept->save();

                // Handle Document Uploads
                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        $originalName = $file->getClientOriginalName();
                        $fileName = time() . '_' . $originalName;
                        $path = $file->storeAs('documents/projects/' . $dept->id, $fileName, 'local');

                        Document::create([
                            'documentable_id'   => $dept->id,
                            'documentable_type' => DepartmentProjects::class,
                            'file_name'         => $fileName,
                            'original_name'     => $originalName,
                            'file_path'         => $path,
                            'file_type'         => $file->getClientOriginalExtension(),
                            'file_size'         => $file->getSize(),
                            'user_id'           => $userid
                        ]);
                    }
                }

                // If TL is assigned during creation, link to their team
                if ($request->team_leader) {
                    $tlTeam = \App\Models\TeamMembers::where('user', $request->team_leader)->where('status', true)->first();
                    if ($tlTeam) {
                        \App\Models\TeamProject::create([
                            'projectid' => $dept->id,
                            'teamid' => $tlTeam->team,
                            'assigned_by' => $userid,
                            'assigned_date' => Carbon::now()
                        ]);
                    }
                }


                //Create Client Package (Only if package is provided)
                if ($request->package) {
                    $clipack = new ClientPackages();
                    $clipack->client           = $request->post('clientsid');
                    $clipack->project_id       = $dept->id;
                    $clipack->package          = $request->package;
                    $clipack->balance          = $request->package;
                    $clipack->created_by       = $userid;
                    $clipack->updated_by       = $userid;
                    $clipack->save();
                }

                // Notify stakeholders about the new project
                ProjectNotificationService::notifyProject($dept, [
                    'category' => 'Project',
                    'header'   => 'New Project Created',
                    'body'     => "Project '{$dept->project_name}' has been created and assigned.",
                    'link'     => url('/') . "/projects/" . base64_encode($dept->id) . "/history"
                ]);

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "Project Created" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }

    }


}

