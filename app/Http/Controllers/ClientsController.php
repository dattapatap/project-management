<?php

namespace App\Http\Controllers;

use App\Services\ClientServices;

use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdate;
use App\Models\ClientHistory;
use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Notifications\AssignToExecutive;
use Carbon\Carbon;
use DataTables;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;

class ClientsController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }


    public function index(Request $request, ClientServices $clientService)
    {

        $category = $request->category;
        $data = $clientService->clients($category, $this->user);
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($client){
                    $btns = '';
                    if($this->user->hasRole(["Admin","Team-Leader"]) && $client->status != 'Matured' ){
                        $btns .= '<a class="dropdown-item assignToUser" client="'.$client->id.'" href="javascript:void(0);">Assign To</a>';
                    }

                    if($client->status != 'Not Interested'){
                        $btns .= '<a class="dropdown-item" href="'. env('APP_URL').'/clients/'.base64_encode($client->id).'/'.'sts' .'">Update STS</a>'
                                .'<a class="dropdown-item" href="'. env('APP_URL').'/clients/'.base64_encode($client->id).'/'.'dsr' .'">Update DSR</a>';
                    }

                    if($this->user->hasRole(["Admin","Team-Leader", 'Branch-Manager'])){
                        if($client->status == 'Matured'){
                            $btns .= '<a class="dropdown-item createNewProject" client="'. $client->id.'" clientnm="'.$client->name.'"'
                                            .' href="javascript:void(0)" >Add Projects '
                                    .'</a>'
                                    .'<a class="dropdown-item" href="'. env('APP_URL').'/clients/'.base64_encode($client->id).'/'.'payment' .'" >Add Payment</a>'
                                    .'<a class="dropdown-item createNewDomain" client="'. $client->id.'" clientnm="'.$client->name.'"  href="javascript:void(0)" >'
                                        .' Add Domain '
                                    .'</a>'
                                    .'<a class="dropdown-item" href="'. env('APP_URL').'/clients/'.base64_encode($client->id).'/'.'docs' .'"> Add Documents </a>';
                        }

                    }

                    $action =  '<a type="button" class="btn btn-outline-success btn-sm m-1" target="_blank" href="'. env('APP_URL').'/clients/'.base64_encode($client->id).'/'.'sts' .'"'
                                    .'data-toggle="tooltip" data-placement="bottom" title="Update STS">'
                                    .'<i class="mdi mdi-eye-outline"></i>'
                                .'</a>'
                                .'<a type="button" class="btn btn-outline-warning btn-sm m-1" href="'. env('APP_URL').'/clients/'.$client->id.'/edit'.'"'
                                    .'data-toggle="tooltip" data-placement="bottom" title="Edit Client">'
                                    .'<i class="mdi mdi-square-edit-outline"></i>'
                                .'</a>'
                                .'<div class="btn-group client-action-btn m-1">'
                                    .'<a type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                                        .'<i class="mdi mdi-settings-transfer-outline"></i>'
                                    .'</a>.'
                                    .'<div class="dropdown-menu dropdown-menu-right">'.$btns.'</div>'
                                .'</div>';

                return $action;

            })
            ->editColumn('contactinfo', function ($data) { return $data->cont_person .'('. $data->designation.')'; })
            ->editColumn('active_from', function ($data) {
                if($data->active_from){
                    return Carbon::parse($data->active_from)->format('d M Y');
                }else{
                    return '';
                }
            })
            ->editColumn('telereferral', function ($data){
                 return $data->telereferral->name;
            })
            ->addColumn('created_at', function ($data){
                 return Carbon::parse($data->created_at)->format('d M Y');
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(created_at,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->editColumn('status', function ($data) {
                return '<span class="text-success">'.$data->status.'</span>';
            })
            ->rawColumns(['action', 'status',])
            ->make(true);



    }

    public function clientsbycategory(Request $request)
    {
       return view('components.clients.index');
    }

    public function create()
    {
        $users = User::where('deleted_at', null)->where('status', 'Active' )
                        ->whereHas('roles', function($q){
                            $q->whereIn('name', ['Sales-Executive', 'Team-Leader'] );
                        })->get();
        return view('components.clients.create', compact('users'));
    }

    public function store(ClientStoreRequest $request)
    {
        try{
            $userid = Auth::user()->id;

            DB::beginTransaction();

            $client = new Clients();
            $client->name          = ucfirst($request->post('name'));
            $client->category      = ucfirst($request->post('category'));

            $client->cont_person   = ucfirst($request->post('contact_person'));
            $client->designation   = ucfirst($request->post('designation'));
            $client->email         = $request->post('email');
            $client->mobile        = $request->post('mobile');

            $client->city          = ucfirst($request->post('city'));
            $client->website_link  = $request->post('website_link');
            $client->ref_user      = $request->post('referral');

            $client->address       = $request->post('address');
            $client->description   = $request->post('remarks');

            $client->status       = $request->status;
            $client->is_active    = false;

            $client->created_by    = $userid;
            $client->tele_ref_user = $userid;
            $client->updated_by    = $userid;
            $client->save();

            $cliHistory           = new ClientHistory();
            $cliHistory->client   = $client->id;
            $cliHistory->category = 'STS';
            $cliHistory->status   = $request->status;
            $cliHistory->tbro_type= $request->type;
            $cliHistory->time     = Carbon::parse($request->time)->format('H:i:s');;
            $cliHistory->tbro     = Carbon::parse($request->tbro_date)->format('Y-m-d');
            $cliHistory->remarks  = $request->post('remarks');
            $cliHistory->created  = $userid;

            $cliHistory->save();

            DB::commit();
            return redirect()->route('clients.category', 'Fresh')->with('success', 'Client Added successfully');

        }catch(Exception $ex){
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage())->withInput();
            Log::error($ex->getMessage());
        }

    }

    public function show(Clients $client)
    {
        if($client){
            $client  = Clients::with('referral')->where('id', $client->id)->where('deleted_at', null)->first();
            return view('components.clients.history.show', compact('client'));
        }
        abort(404);
    }


    public function showClient(Request $request, Clients $client)
    {
        $urlParams = $request->urlname;
        $client_id = base64_decode($request->id);

        if($client_id){

            if($urlParams == 'contacts'){
                $client  = Clients::with('referral', 'telereferral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.contacts', compact('client'));

            }

            if($urlParams == 'sts'){
                $client  = Clients::with('referral', 'telereferral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.sts', compact('client'));

            }

            if($urlParams == 'dsr'){
                $client  = Clients::with('referral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.dsr', compact('client'));

            }


            if($urlParams == 'development'){
                $client  = Clients::with('referral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.development', compact('client'));

            }

            if($urlParams == 'designing'){
                $client  = Clients::with('referral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.designing', compact('client'));

            }

            if($urlParams == 'seo'){
                $client  = Clients::with('referral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.seo', compact('client'));

            }

            if($urlParams == 'history'){
                $client  = Clients::with('referral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.history', compact('client'));

            }

            if($urlParams == 'docs'){
                $client  = Clients::with('referral')->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.docs', compact('client'));

            }

            if($urlParams == 'payment'){
                $client  = Clients::with('referral')->with('package')
                                        ->where('id', $client_id)->where('deleted_at', null)->first();
                return view('components.clients.history.payments', compact('client'));

            }


        }
        abort(404);
    }

    public function edit(Request $request)
    {
        $client  = Clients::with('referral')->where('id', $request->id)->where('deleted_at', null)->first();
        if($client)
             return view('components.clients.edit', compact('client'));
        else
            abort(404);
    }

    public function update(ClientUpdate $request, Clients $client)
    {
        $client = Clients::findOrFail($client->id);

        try{
            DB::beginTransaction();
            $client->name          = ucfirst($request->post('name'));
            $client->category      = ucfirst($request->post('category'));

            $client->cont_person   = ucfirst($request->post('contact_person'));
            $client->designation   = ucfirst($request->post('designation'));
            $client->email         = $request->post('email');
            $client->alt_email     = $request->post('alternate_email');
            $client->mobile        = $request->post('mobile');
            $client->alt_mobile    = $request->post('alternate_mobile');
            $client->telephone     = $request->post('telephone');
            $client->alt_telephone = $request->post('alternate_telephone');

            $client->city          = ucfirst($request->post('city'));
            $client->website_link  = $request->post('website_link');

            $client->address       = $request->post('address1');
            $client->alt_address   = $request->post('address2');

            $client->updated_by= Auth::user()->id;
            $client->save();
            DB::commit();
            return redirect()->route('client.detail', [ base64_encode($client->id) , 'contacts'])->with('success', 'Client updated successfully');

        }catch(Exception $ex){
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage())->withInput();
            dd($ex->getMessage());

        }
    }

    public function destroy(Clients $client)
    {
        if($client){
            $client->updated_by = Auth::user()->id;
            $client->save();
            $client->delete();
            return redirect()->back()->with('message', 'Client Deleted');
        }
    }


    public function assignToExecutive(Request $request){
        $user = Auth::user();

        $clientid  = $request->clientid;
        $executive = $request->executive;

        if($clientid && $executive){
            $client = Clients::where('id', $clientid)->first();
            $assignUser = User::find($executive);
            $clientUser = User::find($client->ref_user);
            try{
                DB::beginTransaction();

                $history = new ClientHistory();
                $history->category    = 'STS';
                $history->client      = $client->id;
                $history->remarks     = " Client has been assigned from $clientUser->name To $assignUser->name ";
                $history->status      = 'Fresh';
                $history->time        = Carbon::now()->format('H:i');
                $history->created     = $user->id;
                $history->save();

                $client->status = 'Fresh';

                if($assignUser->hasRole('Team-Leader')){
                    $client->ref_user = $assignUser->id;
                    $client->tele_ref_user = $assignUser->id;
                }else{
                    $client->ref_user = $assignUser->id;
                }

                $client->save();

                DB::commit();

                $assignUser->notify((new AssignToExecutive($client,  $category="Client"))->delay(now()->addSeconds(5)));

                return response()->json(['status'=>true, 'message'=> 'Client Assigned successfully']);

            }catch(Exception $ex){
                DB::rollBack();
                Log::error($ex->getMessage());
                return response()->json(['status'=>false, 'message'=> 'Opps! somthing went wrong, please try again']);
            }
        }


    }



}


