<?php

namespace App\Http\Controllers;

use App\Models\ClientDocs;
use App\Models\ClientHistory;
use App\Models\ClientPackages;
use App\Models\ClientPayments;
use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Notifications\ClientMatured;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientHistoryController extends Controller
{

    public function createSts(Request $request)
    {
        if($request->attachment_type !=''){
            $file = 'required|max:2000|mimes:jpeg,jpg,png,gif,pdf';
        }else{
            $file = 'nullable';
        }


        $rules = array(
            'sts_remarks' => 'required|string',
            'sts_status' => 'required|string',
            'tbro_date' => 'nullable|date',
            'tbro_time' => 'required|date_format:h:i A',
            'tbro_type' => 'required',
            'attachment' => $file,
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                $userid = Auth::user()->id;
                $client = Clients::findOrFail($request->client_id);

                DB::beginTransaction();

                $cliHistory = new ClientHistory();
                $cliHistory->client     = $request->client_id;
                $cliHistory->category   = 'STS';
                $cliHistory->status     = $request->sts_status;
                $cliHistory->remarks    = $request->sts_remarks;

                $cliHistory->tbro_type  = $request->tbro_type;
                $cliHistory->time       = Carbon::parse($request->tbro_time)->format('H:i:s');;
                $cliHistory->tbro       = Carbon::parse($request->tbro_date)->format('Y-m-d');

                $cliHistory->created  = $userid;
                $cliHistory->save();

                if($request->attachment_type !=''){
                    $attachment = $request->file('attachment');
                    $name = 'docs/'.time().'.'.$attachment->getClientOriginalExtension();
                    $path = $request->file('attachment')->storeAs('clients', $name, 'public');

                    $clidocs = new ClientDocs();
                    $clidocs->client     = $request->client_id;
                    $clidocs->history    = $cliHistory->id;
                    $clidocs->category   = 'STS';
                    $clidocs->doc_type   = $request->attachment_type;
                    $clidocs->files      = $name;
                    $clidocs->uploaded   = Carbon::now();
                    $clidocs->created    = $userid;
                    $clidocs->save();
                }

                $client->status = $request->sts_status;
                $client->updated_by = $userid;
                $client->save();

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "STS Updated" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }

    public function createDsr(Request $request){

        if($request->dsr_status =='Matured'){
            $proforma = 'required|max:2000|mimes:jpeg,jpg,png,gif,pdf';
            $payment_type = 'required|string';
            $category = 'required|numeric';
            $sub_category = 'required|numeric';
            $amount = 'required|numeric|gte:100|lte:package';
            $package = 'required|numeric|gte:100';
        }else{
            $proforma = 'nullable';
            $payment_type = 'nullable';
            $amount  = 'nullable';
            $package  = 'nullable';
            $category = 'nullable';
            $sub_category = 'nullable';
        }

        if($request->payment_type == 'Cheque'){
            $payment_cheque_receipt = 'required|max:2000|mimes:jpeg,jpg,png,gif,pdf';
            $payment_cash_receipt   = 'nullable';
            $transactionid          =  'nullable';
        }else if($request->payment_type == 'Online'){
            $payment_cheque_receipt = 'nullable';
            $payment_cash_receipt   = 'nullable';
            $transactionid          =  'required|numeric';
        }else if($request->payment_type == 'Cash'){
            $payment_cheque_receipt = 'nullable';
            $payment_cash_receipt   = 'required|max:2000|mimes:jpeg,jpg,png,gif,pdf';
            $transactionid          =  'nullable';
        }else{
            $payment_cheque_receipt = 'nullable';
            $payment_cash_receipt   = 'nullable';
            $transactionid          =  'nullable';
        }

        $rules = array(
            'dsr_remarks'   => 'required|string',
            'dsr_status'    => 'required|string',
            'tbro_date'     => 'nullable|date',
            'tbro_time'     => 'required|date_format:h:i A',
            'tbro_type'     => 'required',

            'proforma'      => $proforma,
            'payment_type'  => $payment_type,
            'advance'       => $amount,
            'package'       => $package,

            'payment_cheque_receipt' => $payment_cheque_receipt,
            'payment_cash_receipt' => $payment_cash_receipt,
            'transactionid' => $transactionid,

            'category'      => $category,
            'sub_category'  => $sub_category,

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                $userid = Auth::user()->id;
                $client = Clients::findOrFail($request->client_id);

                DB::beginTransaction();

                $cliHistory = new ClientHistory();
                $cliHistory->client     = $request->client_id;
                $cliHistory->category   = 'DSR';
                $cliHistory->status     = $request->dsr_status;
                $cliHistory->remarks    = $request->dsr_remarks;

                $cliHistory->tbro_type  = $request->tbro_type;
                $cliHistory->time       = Carbon::parse($request->tbro_time)->format('H:i:s');;
                $cliHistory->tbro       = Carbon::parse($request->tbro_date)->format('Y-m-d');

                $cliHistory->created  = $userid;
                $cliHistory->save();

                if($request->dsr_status =='Matured'){

                    $projectCat   = DB::table('project_category')->where('id', $request->category )->first();
                    $projectnm   = DB::table('project_sub_categories')->where('id', $request->sub_category )->first();

                    // Add proforma
                    if($request->has('proforma')){
                        $attachment = $request->file('proforma');
                        $name = 'docs/'.time().'.'.$attachment->getClientOriginalExtension();
                        $dbname = 'clients/'.$name;
                        $request->file('proforma')->storeAs('clients', $name, 'public');

                        $clidocs = new ClientDocs();
                        $clidocs->client     = $request->client_id;
                        $clidocs->history    = $cliHistory->id;
                        $clidocs->category   = 'DSR';
                        $clidocs->doc_type   = "Proforma";
                        $clidocs->files      = $dbname;
                        $clidocs->uploaded   = Carbon::now();
                        $clidocs->created    = $userid;
                        $clidocs->save();
                    }

                    // Assign Project to Department
                    $dept   = new DepartmentProjects();
                    $dept->client           =   $request->client_id;
                    $dept->department       =   $projectCat->dept_id;
                    $dept->category         =   $request->category;
                    $dept->sub_category     =   $request->sub_category;
                    $dept->assigned_by      =   $userid;
                    $dept->created_date     =   Carbon::now();
                    $dept->project_name     =   $projectnm->name;
                    $dept->start_date       =   Carbon::now();
                    $dept->status           =   "NOT ASSIGNED";
                    $dept->save();


                    //Create Client Package
                    $clipack = new ClientPackages();
                    $clipack->client           = $request->client_id;
                    $clipack->project_id       = $dept->id;
                    $clipack->package          = $request->package;
                    $clipack->balance          = round($request->package - $request->advance);
                    $clipack->created_by       = $userid;
                    $clipack->updated_by       = $userid;
                    $clipack->save();

                    //Add Payment with type(Cash/Cheque/Online)
                    $clidocs1 = new ClientPayments();
                    $clidocs1->client           = $request->client_id;
                    $clidocs1->package_id       = $clipack->id;
                    $clidocs1->paid_date        = Carbon::now();
                    $clidocs1->amount           = $request->advance;
                    $clidocs1->remains          = round($request->package - $request->advance);
                    $clidocs1->payment_type     = $request->payment_type;
                    $clidocs1->created_by       = $userid;

                    if($request->payment_type == 'Cheque'){
                        $attachment = $request->file('payment_cheque_receipt');
                        $name = 'payments/'.time().'.'.$attachment->getClientOriginalExtension();
                        $dbname1 = 'clients/'.$name;
                        $path = $request->file('payment_cheque_receipt')->storeAs('clients', $name, 'public');

                        $clidocs1->file             = $dbname1;

                    }else if($request->payment_type == 'Online'){
                        $clidocs1->transactioinid = $request->transactionid;
                    }else{
                        $attachment = $request->file('payment_cash_receipt');
                        $name = 'payments/'.time().'.'.$attachment->getClientOriginalExtension();
                        $dbname2 = 'clients/'.$name;
                        $request->file('payment_cash_receipt')->storeAs('clients', $name, 'public');

                        $clidocs1->file   = $dbname2;
                    }

                    $clidocs1->save();


                    // Get Department Members and filter by role
                   $productManager = User::whereHas('roles', function($q){  $q->where('name', 'Project-Manager' ); })
                                                        ->where('status', 'Active')->get();
                   for($ctr=0; $ctr < count($productManager); $ctr++ ){
                        $currUser = $productManager[$ctr];
                        $currUser->notify((new ClientMatured($client,  $dept, $category=$projectnm->name))->delay(now()->addSeconds(5)));
                    }

                    $client->is_active = true;
                    $client->active_from = Carbon::now();

                }

                $client->status = $request->dsr_status;
                $client->updated_by = $userid;
                $client->save();

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "DSR Updated" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }


    public function updateSts(Request $request){
        $rules = array(
            'sts_remarks' => 'required|string',
            'tbro_time' => 'required|date_format:h:i A',
            'tbro_type' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                $userid = Auth::user()->id;
                DB::beginTransaction();

                $cliHistory = new ClientHistory();
                $cliHistory->client     = $request->client_id;
                $cliHistory->category   = 'STS';
                $cliHistory->status     = "STS UPDATE";
                $cliHistory->remarks    = $request->sts_remarks;

                $cliHistory->tbro_type  = $request->tbro_type;
                $cliHistory->time       = Carbon::parse($request->tbro_time)->format('H:i:s');
                $cliHistory->created  = $userid;
                $cliHistory->save();

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "STS Updated" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }

    public function updateDsr(Request $request){
        $rules = array(
            'dsr_remarks'   => 'required|string',
            'tbro_time'     => 'required|date_format:h:i A',
            'tbro_type'     => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                $userid = Auth::user()->id;
                DB::beginTransaction();

                $cliHistory = new ClientHistory();
                $cliHistory->client     = $request->client_id;
                $cliHistory->category   = 'DSR';
                $cliHistory->status     = "DSR UPDATE";
                $cliHistory->remarks    = $request->dsr_remarks;
                $cliHistory->tbro_type  = $request->tbro_type;
                $cliHistory->time       = Carbon::parse($request->tbro_time)->format('H:i:s');
                $cliHistory->created    = $userid;
                $cliHistory->save();

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "DSR Updated" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }


    public function addVisitingCard(Request $request){
        $rules = array(
            'visiting_card' => 'required|max:2000|mimes:jpeg,jpg,png',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                $userid = Auth::user()->id;
                $client = Clients::findOrFail($request->client);

                DB::beginTransaction();

                $attachment = $request->file('visiting_card');
                $name = 'docs/'.time().'.'.$attachment->getClientOriginalExtension();
                $path = $request->file('visiting_card')->storeAs('clients', $name, 'public');

                $clidocs = new ClientDocs();
                $clidocs->client     = $client->id;
                $clidocs->category   = 'STS';
                $clidocs->doc_type   = "Visiting Card";
                $clidocs->files      = 'clients/'.$name;
                $clidocs->uploaded   = Carbon::now();
                $clidocs->created    = $userid;
                $clidocs->save();

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "Visiting Card Added" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }

    }


    public function getclienthistory(Request $request){

        $clientid = $request->client;
        $category = $request->category;

        $history = '';
        if($category == 'STS' || $category == 'DSR' ){
            $history  = ClientHistory::with('referel')->where('client',  $clientid)
                            ->where('category', $category)
                            ->orderBy('id','desc')->get()->toArray();
        }

        if($history){
            return response()->json(['status'=>true, 'data'=>$history]);
        }else{
            return response()->json(['status'=>false]);
        }

    }



}
