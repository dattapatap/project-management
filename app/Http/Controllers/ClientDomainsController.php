<?php

namespace App\Http\Controllers;

use App\Models\ClientDomains;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class ClientDomainsController extends Controller
{

    public function index()
    {
        $expired = ClientDomains::with('clients')
                                ->where('expiry_dt', '<=', Carbon::today() )
                                ->where('renewed', false)->count();

        return view('components.domains.index', compact('expired'));
    }

    public function getalldomains(){

        $from  = Carbon::today()->subDays(2);
        $to    = Carbon::today()->addDay(10);

        $currmonthDomains = ClientDomains::with('clients')
                            ->whereBetween('expiry_dt', [ $from, $to] )
                            ->where('renewed', false)
                            ->orderBy('expiry_dt', 'desc')->get();

        $allDomains = ClientDomains::with('clients')
                            ->whereNotBetween('expiry_dt', [ $from, $to] )
                            ->orderBy('expiry_dt', 'desc')->get();

        $data  = $currmonthDomains->merge($allDomains);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($data){
                $actionBtnEdt = '';
                if($data->renewed == false ){
                     $actionBtnEdt.= '<div class="btn-group client-action-btn">
                                    <a type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-settings-transfer-outline"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item editDomain" clientnm="'.$data->client_name.'" client="'.$data->client.'"
                                                domainid="'.$data->id.'" domainnm="'.$data->domain.'" href="javascript:void(0);">Edit</a>';

                    $actionBtnEdt.= '<a class="dropdown-item renewDomain" clientnm="'.$data->client_name.'" client="'.$data->client.'"
                                                domainid="'.$data->id.'" domainnm="'.$data->domain.'" href="javascript:void(0);">Renew</a>
                                            </div> </div>';
                }else{
                        $actionBtnEdt.= '<span class="badge badge-success fs-12"> <i class="mdi mdi-check-bold "></i> ('. $data->renewd_dt.')</span>';
                }
                return $actionBtnEdt;

            })
            ->editColumn('contactinfo', function ($data) { return $data->clients->cont_person .'('. $data->clients->designation.')'; })
            ->editColumn('name', function ($data) { return $data->clients->name; })
            ->editColumn('mobile', function ($data) { return $data->clients->mobile; })
            ->editColumn('registered_dt', function ($data) {
                    return Carbon::parse($data->registered_dt)->format('d M Y');
            })
            ->editColumn('expiry_dt', function ($data) {
                if($data->expiry_dt <= Carbon::today()->format('Y-m-d') && !$data->renewed){
                    return '<span class="text-danger">'.Carbon::parse($data->expiry_dt)->format('d M Y').'</span>';
                }else if($data->expiry_dt < Carbon::now()->addDays(15) && !$data->renewed){
                    return '<span class="text-warning">'.Carbon::parse($data->expiry_dt)->format('d M Y').'</span>';
                }else{
                    return Carbon::parse($data->expiry_dt)->format('d M Y');
                }
            })
            ->rawColumns(['action', 'status', 'expiry_dt'])
            ->make(true);

    }


    public function store(Request $request)
    {
        if($request->post('domain_id') == ''){
            $rules = array(
                'client_id' => 'required',
                'client_nm' => 'required|string',
                'domain'    => 'required|regex:/^(?:[a-z0-9](?:[a-z0-9-æøå]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/isu',
                'reg_date'  => 'required|date',
                'exp_date'  => 'required|date',
            );
        }else{
            $rules = array(
                'client_id' => 'required',
                'client_nm' => 'required|string',
                'domain'    => 'required|regex:/^(?:[a-z0-9](?:[a-z0-9-æøå]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/isu',
                'reg_date'  => 'required|date',
                'exp_date'  => 'required|date',

            );
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{
                if($request->post('domain_id') ==''){

                    $client = DB::table('clients')->where('id', $request->client_id )->first();

                    $domain = new ClientDomains();
                    $domain->client        = $request->post('client_id');
                    $domain->client_name   = $client->name;
                    $domain->domain        = $request->post('domain');
                    $domain->registered_dt = Carbon::parse($request->reg_date)->format('Y-m-d');
                    $domain->expiry_dt     = Carbon::parse($request->exp_date)->format('Y-m-d');
                    $domain->created_by    = Auth::user()->id;
                    $domain->save();

                    return response()->json(['code'=>200, "status"=>true, 'message'=> "Domain Added", 'data'=>$domain ], 200);
                }else{
                    $domain = ClientDomains::find($request->post('domain_id'));
                    $domain->domain        = $request->post('domain');
                    $domain->expiry_dt     = Carbon::parse($request->exp_date)->format('Y-m-d');
                    $domain->save();

                    return response()->json(['code'=>200, "status"=>true, 'message'=> "Domain Updated", 'data'=>$domain ], 200);
                }

            }catch(Exception $ex){
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }
    }

    public function renew(Request $request)
    {
        $rules = array(
            'client' => 'required',
            'domainid' => 'required',
            'clientnm' => 'required|string',
            'domain_nm' => 'required|regex:/^(?:[a-z0-9](?:[a-z0-9-æøå]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/isu',
            'renew_date'  => 'required|date',
            'expirydate'  => 'required|date',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(array('status' => 400,'errors' => $validator->getMessageBag()->toArray()), 400);
        }else{
            try{

                DB::beginTransaction();

                $exiDomain = ClientDomains::find($request->domainid);
                if($exiDomain){
                    $exiDomain->notified    = true;
                    $exiDomain->renewed     = true;
                    $exiDomain->renewd_dt   = Carbon::parse($request->renew_date)->format('Y-m-d');
                    $exiDomain->save();


                    $domain  = new ClientDomains();
                    $domain->client        = $request->post('client');
                    $domain->client_name   = $request->post('clientnm');
                    $domain->domain        = $request->post('domain_nm');
                    $domain->registered_dt = Carbon::parse($request->renew_date)->format('Y-m-d');
                    $domain->expiry_dt     = Carbon::parse($request->expirydate)->format('Y-m-d');
                    $domain->created_by    = Auth::user()->id;
                    $domain->save();

                    DB::commit();

                    return response()->json(['code'=>200, "status"=>true, 'message'=> "Domain Renewd", 'data'=>$domain ], 200);
                }else{
                    return response()->json(['code'=>200, "status"=>false, 'message'=> "Opps! Domain not renewd, please try again", ], 200);
                }
            }catch(Exception $ex){
                DB::rollBack();
                return response()->json(['code'=>201, 'status'=>false, 'message'=>$ex->getMessage() ], 200);
            }
        }

    }

    public function edit(Request $request )
    {
        $domainid =  $request->domainid;
        $domain = ClientDomains::where('id', $domainid)->first();
        if($domain){
            return response()->json(['status'=>true, 'client' => $domain->toArray() ], 200);
        }else{
            return response()->json(['status'=>false ], 200);
        }
    }

}
