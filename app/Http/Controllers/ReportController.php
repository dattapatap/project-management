<?php

namespace App\Http\Controllers;

use App\Exports\StsExport;
use App\Models\Clients;
use App\Models\TeamMembers;
use App\Models\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{

    public function index(){
        $clients = Clients::where('id', 0)->paginate(100);
        return view('components.clients.filters.sts', compact('clients'))->with('search', '');
    }

    public function index_dsr(Request $request){
        $clients = Clients::where('id', 0)->paginate(25);
        return view('components.clients.filters.dsr', compact('clients'))->with('search', '');
    }

    public function sales_reports(Request $request){
        return view('components.clients.filters.sales');
    }

    public function sales_reports_get(Request $request){
        $user = Auth::user();


        $data = Clients::with('telereferral')->where('is_active', true)->where('status', 'Matured')->latest('active_from');
        if($user->hasRole('Sales-Executive')){
            $data->where('ref_user', $user->id);
        }else if($user->hasRole('Team-Leader')){
            $teams  =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $allmem =  TeamMembers::with('users.roles')
                                    ->whereHas('users.roles', function($query){
                                        $query->where('name', 'Sales-Executive');
                                    })
                                    ->whereIn('team', $teams)->where('status', true)->pluck('user')->toArray();

            array_push($allmem, $user->id);

            $data->whereIn('tele_ref_user', $allmem);
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $actionBtn = '<a type="button" class="btn btn-outline-success btn-sm"  href="'. env('APP_URL').'/clients/'.base64_encode($row->id).'/'.'sts' .'"
                                >   <i class="mdi mdi-eye-outline"></i>
                            </a>';
                return $actionBtn;
            })
            ->editColumn('contactinfo', function ($data) { return $data->cont_person .'('. $data->designation.')'; })
            ->editColumn('name', function ($data) { return $data->name; })
            ->editColumn('category', function ($data) { return $data->category; })
            ->editColumn('mobile', function ($data) { return $data->mobile; })
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
            ->editColumn('status', function ($data) {
                return '<span class="text-success">'.$data->status.'</span>';
            })
            ->rawColumns(['action', 'status',])
            ->make(true);

    }



    public function searchSTS(Request $request){
        $clients = '';
        $dt = $request->from_date;$dates  = explode(" - ", $dt);$from   = $dates[0];$to     = $dates[1];


        $category  = $request->category;

        $frms = Carbon::createFromFormat('d/m/Y',$from)->format('Y-m-d');
        $toos   = Carbon::createFromFormat('d/m/Y', $to)->format('Y-m-d');
        $todt = Carbon::parse($toos)->addDays(1);

        $user  = Auth::user();
        if($request->employee == 'All'){

            if($user->hasRole('Team-Leader')){
                $teams  =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
                $allmem =  TeamMembers::with('users.roles')
                                        ->whereHas('users.roles', function($query){
                                            $query->where('name', 'Sales-Executive');
                                        })
                                        ->whereIn('team', $teams)->where('status', true)->pluck('user')->toArray();

                array_push($allmem, $user->id);

                $eloquent = Clients::whereHas('history', function($query) use($allmem,  $frms, $todt, $category){
                                            $query->whereIn('created',  $allmem);
                                            $query->where('category',  'STS');
                                            $query->filterStatus($category, 'STS');
                                            $query->whereBetween("created_at",[ $frms, $todt]);
                                        })
                                        ->with(['history' => function($query) use($allmem,  $frms, $todt, $category){
                                                $query->whereIn('created',   $allmem);
                                                $query->where('category',  'STS');
                                                $query->filterStatus($category, 'STS');
                                                $query->whereBetween("created_at",[ $frms, $todt]);
                                        }])
                                        ->whereIn('tele_ref_user', $allmem);

                $clients = $eloquent->filterStatus($category, 'STS')->paginate(50)->appends(request()->query());
            }

            if($user->hasRole('Admin')){
                $eloquent = Clients::whereHas('history', function($query) use( $frms, $todt, $category){
                                $query->where('category',  'STS');
                                $query->filterStatus($category, 'STS');
                                $query->whereBetween("created_at",[ $frms, $todt]);
                            })
                            ->with(['history' => function($query) use(  $frms, $todt, $category){
                                    $query->where('category',  'STS');
                                    $query->filterStatus($category, 'STS');
                                    $query->whereBetween("created_at",[ $frms, $todt]);
                            }]);
                $clients = $eloquent->filterStatus($category, 'STS')->paginate(50)->appends(request()->query());
            }

        }else{
            $employeeId = $request->employee;
            if($user->hasRole('Team-Leader') || $user->hasRole('Admin')){
                $eloquent = Clients::whereHas('history', function($query) use($employeeId,  $frms, $todt, $category){
                                        $query->where('created',  $employeeId);
                                        $query->where('category',  'STS');
                                        $query->filterStatus($category, 'STS');
                                        $query->whereBetween("created_at",[ $frms, $todt]);
                                    })
                                    ->with(['history' => function($query) use($employeeId,  $frms, $todt, $category){
                                            $query->where('created',  $employeeId);
                                            $query->where('category',  'STS');
                                            $query->filterStatus($category, 'STS');
                                            $query->whereBetween("created_at",[ $frms, $todt]);
                                    }])
                                    ->where(function ($query) use($employeeId) {
                                            $query->where('tele_ref_user', $employeeId);
                                    });
            }else{
                $eloquent = Clients::whereHas('history', function($query) use($employeeId,  $frms, $todt, $category){
                                                $query->where('created',  $employeeId);
                                                $query->where('category',  'STS');
                                                $query->filterStatus($category, 'STS');
                                                $query->whereBetween("created_at",[ $frms, $todt]);
                                            })
                                            ->with(['history' => function($query) use($employeeId,  $frms, $todt, $category){
                                                    $query->where('created',  $employeeId);
                                                    $query->where('category',  'STS');
                                                    $query->filterStatus($category, 'STS');
                                                    $query->whereBetween("created_at",[ $frms, $todt]);
                                            }])
                                            ->where('ref_user', $employeeId);
            }
            $clients = $eloquent->filterStatus($category, 'STS')->paginate(50)->appends(request()->query());
        }
        return view('components.clients.filters.sts', compact('clients'))->with('search', $request->input());
    }


    public function searchDSR(Request $request){
        $clients = '';
        $dt = $request->from_date;$dates  = explode(" - ", $dt);$from   = $dates[0];$to     = $dates[1];


        $category  = $request->category;

        $frms = Carbon::createFromFormat('d/m/Y',$from)->format('Y-m-d');
        $toos   = Carbon::createFromFormat('d/m/Y', $to)->format('Y-m-d');
        $todt = Carbon::parse($toos)->addDays(1);
        $user  = Auth::user();

        if($request->employee == 'All'){

            if($user->hasRole('Team-Leader')){
                $teams  =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
                $allmem =  TeamMembers::with('users.roles')
                                        ->whereHas('users.roles', function($query){
                                            $query->where('name', 'Sales-Executive');
                                        })
                                        ->whereIn('team', $teams)->where('status', true)->pluck('user')->toArray();

                array_push($allmem, $user->id);

                $eloquent = Clients::whereHas('history', function($query) use($allmem,  $frms, $todt, $category){
                                        $query->whereIn('created',  $allmem);
                                        $query->where('category',  'DSR');
                                        $query->filterStatus($category, 'DSR');
                                        $query->whereBetween("created_at",[ $frms, $todt]);
                                    })
                                    ->with(['history' => function($query) use($allmem,  $frms, $todt, $category){
                                            $query->whereIn('created',   $allmem);
                                            $query->where('category',  'DSR');
                                            $query->filterStatus($category, 'DSR');
                                            $query->whereBetween("created_at",[ $frms, $todt]);
                                    }])
                                    ->where(function ($query) use($allmem, $user) {
                                            $query->whereIn('ref_user', $allmem);
                                            $query->orWhere('tele_ref_user', $user->id);
                                    });

                $clients = $eloquent->filterStatus($category, 'DSR')->paginate(50)->appends(request()->query());
            }

            if($user->hasRole('Admin')){
                $eloquent = Clients::whereHas('history', function($query) use( $frms, $todt, $category){
                                $query->where('category',  'DSR');
                                $query->filterStatus($category, 'DSR');
                                $query->whereBetween("created_at",[ $frms, $todt]);
                            })
                            ->with(['history' => function($query) use(  $frms, $todt, $category){
                                    $query->where('category',  'DSR');
                                    $query->filterStatus($category, 'DSR');
                                    $query->whereBetween("created_at",[ $frms, $todt]);
                            }]);

                $clients = $eloquent->filterStatus($category, 'DSR')->paginate(50)->appends(request()->query());
            }
        }else{
            $employeeId = $request->employee;
            if($user->hasRole('Team-Leader') || $user->hasRole('Admin')){
                $eloquent = Clients::whereHas('history', function($query) use($employeeId,  $frms, $todt, $category){
                    $query->where('created',  $employeeId);
                    $query->where('category',  'DSR');
                    $query->filterStatus($category, 'DSR');
                    $query->whereBetween("created_at",[ $frms, $todt]);
                })
                ->with(['history' => function($query) use($employeeId,  $frms, $todt, $category){
                        $query->where('created',  $employeeId);
                        $query->where('category',  'DSR');
                        $query->filterStatus($category, 'DSR');
                        $query->whereBetween("created_at",[ $frms, $todt]);
                }])
                ->where(function ($query) use($employeeId) {
                        $query->where('ref_user', $employeeId);
                        $query->orWhere('tele_ref_user', $employeeId);
                });
            }else{
                $eloquent = Clients::whereHas('history', function($query) use($employeeId,  $frms, $todt, $category){
                                    $query->where('created',  $employeeId);
                                    $query->where('category',  'DSR');
                                    $query->filterStatus($category, 'DSR');
                                    $query->whereBetween("created_at",[ $frms, $todt]);
                                })
                                ->with(['history' => function($query) use($employeeId,  $frms, $todt, $category){
                                        $query->where('created',  $employeeId);
                                        $query->where('category',  'DSR');
                                        $query->filterStatus($category, 'DSR');
                                        $query->whereBetween("created_at",[ $frms, $todt]);
                                }])
                                ->where('ref_user', $employeeId);
            }

            $clients = $eloquent->filterStatus($category, 'DSR')->paginate(50)->appends(request()->query());

        }

        return view('components.clients.filters.dsr', compact('clients'))->with('search', $request->input());
    }

    public function exportStsReports(Request $request){
        $response = Excel::download(new StsExport($request), Carbon::today()->toDateString().'_sts_list.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        if (ob_get_contents()) ob_end_clean();
        return $response;
    }


    public function getCountMySts(Request $request){
        $user =  Auth::user();
        $status = array();

        if($user->hasRole('Team-Leader'))
            $userType = 'tele_ref_user';
        else
            $userType = 'ref_user';


        $status['sts']              = Clients::searchCategory($userType, $user->id)->where('status', '!=', 'Not Interested')->count();

        $status['unTouch']          = Clients::searchCategory($userType, $user->id)->status(['Fresh'])->count();

        $status['touch']            = $status['sts'] - $status['unTouch'];

        //Not Met
        $status['stsNotMet']        = Clients::searchCategory($userType, $user->id)->status(['Fresh','Followup', 'Meeting Fixed'])->count();
        $status['stsMeetingFixed']  = Clients::searchCategory($userType, $user->id)->status(['Meeting Fixed'])->count();

        $status['stsTBRO']          = Clients::whereIn('status', ['Followup', 'Meeting Fixed'])
                                                ->whereHas('history', function($query) use($user){
                                                    $query->tbro( 'STS', $user->id);
                                                })
                                                ->with(['history' => function($query) use($user,){
                                                    $query->tbro( 'STS', $user->id);
                                                }])->count();


        $status['stsReminder']      = Clients::status(['Fresh'])->searchCategory($userType, $user->id)
                                                ->whereHas('history', function($query) use($user){
                                                    $query->whereIn('status', ['Fresh']);
                                                    $query->reminder('STS', $user->id);
                                                })
                                                ->with(['history' => function($query) use($user){
                                                    $query->whereIn('status', ['Fresh']);
                                                    $query->reminder('STS', $user->id);
                                                }])->count();



        // Met
        $status['dsrMet']           = Clients::searchCategory($userType, $user->id)
                                                ->status(['Warm Prespective','Hot Prespective', 'Matured'])->count();

        $status['dsrMatured']       = Clients::searchCategory($userType, $user->id)->status(['Matured'])->count();

        $status['dsrTbro']          = Clients::status(['Warm Prespective','Hot Prespective'])->searchCategory($userType, $user->id)
                                                ->whereHas('history', function($query) use($user){
                                                    $query->tbro('DSR', $user->id);
                                                })
                                                ->with(['history' => function($query) use($user){
                                                    $query->tbro('DSR', $user->id);
                                                }])->count();

        $status['dsrReminder']      = Clients::whereIn('status', ['Warm Prespective','Hot Prespective'])->searchCategory($userType, $user->id)
                                                ->whereHas('history', function($query) use($user){
                                                    $query->reminder('DSR', $user->id);
                                                })
                                                ->with(['history' => function($query) use($user){
                                                    $query->reminder('DSR', $user->id);
                                                }])->count();

        return response()->json(['status' =>true, 'data' => $status, 'user' => $user], 200);

    }

    public function getCountMyStsByCategory(Request $request)
    {
        if ($request->ajax()) {
            $category = $request->category;
            $usercode     = $request->usercode;

            $user = User::find($usercode);

            if($user->hasRole('Sales-Executive') ){
                $data = Clients::where('ref_user', $user->id)->where('status' , '!=', 'Not Interested')
                                ->whereHas('history', function($q) use($user){
                                    $q->where('created', $user->id); })
                                ->with(['history' => function($query) use($user){ $query->where('created', $user->id); }])
                                ->orderBy('name', 'asc');
            }else if( $user->hasRole('Team-Leader') ){
                $data = Clients::with('history')->where('status' ,'!=', 'Not Interested')
                                ->where('tele_ref_user', $user->id)->orderBy('name', 'asc');
            }


            if($category == 'untouch'){
                $data->status(['Fresh']);
            }

            if($category == 'touch'){
                $data->statusNotIn(['Fresh','Not Interested']);
            }

            // DSR
            if($category == 'dsrMet'){
                $data->status(['Warm Prespective','Hot Prespective', 'Matured']);
            }

            if($category == 'dsrMatured'){
                $data->status(['Matured']);
            }
            if($category == 'dsrTbro'){
                $data->status(['Warm Prespective','Hot Prespective'])
                        ->whereHas('history', function($query) use($user){
                            $query->tbro('DSR', $user->id);
                        })
                        ->with(['history' => function($query) use($user){
                            $query->tbro('DSR', $user->id);
                        }]);
            }
            if($category == 'dsrReminder'){
                $data->status(['Warm Prespective','Hot Prespective'])
                            ->whereHas('history', function($query) use($user){
                                $query->reminder('DSR', $user->id);
                            })
                            ->with(['history' => function($query) use($user){
                                $query->reminder('DSR', $user->id);
                            }]);
            }

            // STS
            if($category == 'stsNotMet'){
                $data->status(['Fresh','Followup', 'Meeting Fixed']);
            }
            if($category == 'stsTbro'){
                $data->status(['Followup'])
                        ->whereHas('history', function($query) use($user){
                            $query->orderBy('id','desc');
                            $query->tbro('STS', $user->id);
                        })
                        ->with(['history' => function($query) use($user){
                            $query->orderBy('id','desc');
                            $query->tbro('STS', $user->id);
                        }]);
            }
            if($category == 'stsReminder'){
                $data->status(['Fresh'])
                        ->whereHas('history', function($query) use($user){
                            $query->orderBy('id','desc');
                            $query->reminder('STS', $user->id);
                        })
                        ->with(['history' => function($query) use($user){
                            $query->orderBy('id','desc');
                            $query->reminder('STS', $user->id);
                        }]);
            }
            if($category == 'stsMeetFixed'){
                $data->status(['Meeting Fixed'])
                        ->whereHas('history', function($query) {
                            $query->where('status' , 'Meeting Fixed');
                        })
                        ->with(['history' => function($query) {
                            $query->where('status' , 'Meeting Fixed');
                        }]);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<a type="button" class="btn btn-outline-success btn-sm" href="'. env('APP_URL').'/clients/'.base64_encode($row->id).'/'.'sts' .'"  target="_blank"
                                        data-toggle="tooltip" data-placement="bottom" aria-label="TS" data-bs-original-title="STS">
                                            <i class="mdi mdi-eye-outline"></i>
                                </a>';

                    return $actionBtn;
                })
                ->editColumn('contactinfo', function ($data) { return $data->cont_person .'('. $data->designation.')'; })
                ->editColumn('name', function ($data) { return $data->name; })
                ->editColumn('mobile', function ($data) { return $data->mobile; })
                ->editColumn('tbro', function ($data) {
                    if($data->history->tbro){
                        return Carbon::parse($data->history->tbro)->format('d M Y');
                    }else{
                        return '';
                    }
                })
                ->editColumn('status', function ($data) {
                    return '<span class="text-success">'.$data->status.'</span>';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
    }

}
