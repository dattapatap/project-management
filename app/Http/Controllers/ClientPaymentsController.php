<?php

namespace App\Http\Controllers;

use App\Models\ClientHistory;
use App\Models\ClientPackages;
use App\Models\ClientPayments;
use App\Models\Clients;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use \NumberFormatter;
use Yajra\DataTables\Facades\DataTables;

class ClientPaymentsController extends Controller
{
    public function index()
    {
        return view('components.payments.index');
    }


    public function getallpayments(Request $request){
        if ($request->ajax()) {

            $data = ClientPackages::with('clients', 'projects', 'addedby')
                                ->orderBy('balance', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    $actionBtnEdt = '';
                    if($data->balance > 0 ){
                        $actionBtnEdt.= '<div class="btn-group client-action-btn">
                                        <a type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-settings-transfer-outline"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item payHistory" packageid="'.$data->id.'"  href="javascript:void(0);">History</a>';

                        $actionBtnEdt.= '<a class="dropdown-item addNewEntry" clientid="'.$data->client.'"  projectid="'.$data->project_id.'"
                                                 packageid="'.$data->id.'" href="javascript:void(0);">Add Entry</a>
                                                </div> </div>';
                    }else{
                        $actionBtnEdt.= '<div class="btn-group client-action-btn">
                                            <a type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <i class="mdi mdi-settings-transfer-outline"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item payHistory" packageid="'.$data->id.'" href="javascript:void(0);">History</a
                                            </div>
                                        </div>';
                    }
                    return $actionBtnEdt;

                })
                ->editColumn('clients', function ($data) { return $data->clients->name; })
                ->editColumn('projects', function ($data) { return $data->projects->project_name; })
                ->editColumn('package', function ($data) {
                    return  '₹ ' . number_format($data->package, 2);
                })
                ->editColumn('paid', function ($data) {
                        return  '₹ ' . number_format($data->package - $data->balance, 2);
                })
                ->editColumn('balance', function ($data)  {
                     return  '₹ ' . number_format($data->balance, 2);
                })
                ->editColumn('addedby', function ($data) {
                    return $data->addedby->name;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

    }

    public function getPaymentsByPackage(Request $request){
        $packageid = $request->packageid;

        $data = ClientPayments::with('addedBy')
                    ->where('package_id', $packageid)->orderBy('id', 'desc')->get()->toArray();
        if($data)
            return response()->json(['status'=>true, 'payments'=>$data], 200);
        else
            return response()->json(['status'=>false], 200);
    }




    public function getPaymentByClient(Request $request){
        if ($request->ajax()) {
            $client = $request->client;
            $data = ClientPayments::with('addedBy')->with('packages')->where('client', $client)->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('project', function ($data) {
                    $projname = DB::table('client_packages')
                                ->join('department_projects as dp', 'dp.id', '=' , 'client_packages.project_id' )
                                ->where('client_packages.id', $data->package_id)->value('dp.project_name');

                    return $projname;
                })
                ->editColumn('packages.package', function ($data) {
                    return $data->packages->package;
                })
                ->editColumn('paid_date', function ($data) {
                        return Carbon::parse($data->paid_date)->format('d M Y');
                })
                ->editColumn('referance', function ($data) {
                    if($data->file){

                     return '<a  href="'.asset('storage/'.$data->file. '').'" target="_blank"
                       class="p-1 view-visiting-card gallery-popup">
                          <img  src="'.asset('storage/'.$data->file. '').'" style="height:45px;"  />
                       </a>';
                    }else{
                        return $data->transactioinid;
                    }
                })
                ->editColumn('addedBy.name', function ($data) {
                        return $data->addedBy->name;
                })
                ->rawColumns(['referance'])
                ->make(true);


        }
    }

    public function getPaymentByProject(Request $request){
        $projectid = $request->project;

        $cliPayment = DB::table('client_packages')->select('balance')
                    ->where('project_id', $projectid)->first();
        return response()->json($cliPayment);
    }

    public function addPayment(Request $request){

        $cliPackage = ClientPackages::where('project_id', $request->project_type)->first();

        if($request->payment_type == 'Cheque'){
            $payment_cheque_receipt = 'required|max:2000|mimes:jpeg,jpg,png,gif';
            $payment_cash_receipt   = 'nullable';
            $transactionid          =  'nullable';
        }else if($request->payment_type == 'Online'){
            $payment_cheque_receipt = 'nullable';
            $payment_cash_receipt   = 'nullable';
            $transactionid          =  'required|numeric';
        }else if($request->payment_type == 'Cash'){
            $payment_cheque_receipt = 'nullable';
            $payment_cash_receipt   = 'required|max:2000|mimes:jpeg,jpg,png,gif';
            $transactionid          =  'nullable';
        }else{
            $payment_cheque_receipt = 'required';
            $payment_cash_receipt   = 'required';
            $transactionid          =  'required';
        }

        $rules = array(
            'client'   => 'required',
            'project_type'    => 'required|numeric',
            'amount'     => 'required|lte:'. $cliPackage->balance.'',
            'payment_type'     => 'required',

            'payment_cheque_receipt' => $payment_cheque_receipt,
            'payment_cash_receipt' => $payment_cash_receipt,
            'transactionid' => $transactionid,
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array( 'status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                $userid = Auth::user()->id;

                DB::beginTransaction();


                $clidocs1 = new ClientPayments();
                $clidocs1->client           = $cliPackage->client;
                $clidocs1->package_id       = $cliPackage->id;
                $clidocs1->paid_date        = Carbon::now();
                $clidocs1->amount           = $request->amount;
                $clidocs1->remains          = ($cliPackage->balance - $request->amount);
                $clidocs1->payment_type     = $request->payment_type;
                $clidocs1->created_by       = $userid;

                //Add Payment with type(Cash/Cheque/Online)
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
                    $path = $request->file('payment_cash_receipt')->storeAs('clients', $name, 'public');

                    $clidocs1->file   = $dbname2;
                }

                $clidocs1->save();

                $cliPackage->balance    = ($cliPackage->balance - $request->amount);
                $cliPackage->updated_by = $userid;
                $cliPackage->save();


                $cliHistory = new ClientHistory();
                $cliHistory->client     = $cliPackage->client;
                $cliHistory->category   = 'DSR';
                $cliHistory->status     = "Payment";
                $cliHistory->remarks    = "Payment Collection Update";

                $cliHistory->tbro_type  = "Payment";
                $cliHistory->time       = Carbon::now()->format('H:i:s');;
                $cliHistory->created     = $userid;
                $cliHistory->save();

                DB::commit();
                return response()->json(['code'=>200, "status"=>true, 'message'=> "Payment Added" ], 200);

            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }

    }


}
