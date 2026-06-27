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
use App\Services\Commercial\ClientEngagementService;
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
            $proforma = 'nullable|max:2000|mimes:jpeg,jpg,png,gif,pdf';
            $payment_type = 'nullable|string';
            $category = 'nullable|numeric';
            $sub_category = 'nullable|numeric';
            $amount = 'nullable|numeric|gte:100|lte:package';
            $package = 'nullable|numeric|gte:100';
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
                    // We only save the DSR history and update the client status.
                    // The actual project, package, and payments are set up via the Handoff Wizard.
                    $client->status = 'Matured';
                    $client->updated_by = $userid;
                    $client->save();

                    DB::commit();
                    return response()->json([
                        'code' => 200, 
                        'status' => true, 
                        'message' => 'Lead matured! Loading handoff wizard...', 
                        'handoff_required' => true, 
                        'client_id' => $client->id
                    ], 200);
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
