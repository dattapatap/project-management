<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\TeamMembers;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{



    public function chartdata(){
        $user =Auth::user();

        if($user->hasRole('Sales-Executive')){
            $sales  = DB::select('SELECT DATE_FORMAT(date, "%b") AS month, IFNULL( COUNT(DISTINCT ch.client), 0) as total
                            FROM (
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 1 MONTH AS date UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 2 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 3 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 4 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 5 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 6 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 7 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 8 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 9 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 10 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 11 MONTH UNION ALL
                                SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 12 MONTH
                            ) AS dates
                            JOIN clients as c ON(c.ref_user='.$user->id.' AND c.status="Matured")
                            LEFT JOIN client_histories as ch
                            ON (ch.created_at >= date AND ch.created_at < date + INTERVAL 1 MONTH AND ch.status="Matured" AND ch.created='.$user->id.')
                            GROUP BY date'
                );
        }else if($user->hasRole('Team-Leader')){
            $allmem =  DB::table('department_members')->where('parent_leader', $user->id)->pluck('user')->toArray();
            array_push($allmem, $user->id);
            // $sales  = DB::select('SELECT DATE_FORMAT(date, "%b") AS month,  IFNULL( COUNT(DISTINCT name), 0) as total
            //                 FROM (
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 1 MONTH AS date UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 2 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 3 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 4 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 5 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 6 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 7 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 8 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 9 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 10 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 11 MONTH UNION ALL
            //                     SELECT LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 12 MONTH
            //                 ) AS dates
            //                 LEFT JOIN clients ON( ( active_from >= date AND active_from < date + INTERVAL 1 MONTH )
            //                                         AND is_active=true AND status="Matured" AND ref_user IN($allmem) )
            //                 GROUP BY date'
            //     );


        }
        return response()->json(['code'=>200,'status'=>true, 'sales'=> $sales ], 200);
    }


    public function getTodaysTbros(Request $request){
        $user = Auth::user();

        if($user->hasRole('Sales-Executive')){
            $data = Clients::with('referral')
                            ->whereNotIn('status', ['Fresh','Not Interested'])
                            ->whereHas('history', function($q) use($user){
                                $q->where('tbro' , '=',  Carbon::today()->toDateString());
                                $q->where('created', $user->id);
                            })
                            ->with(['history' => function($query) use($user){
                                $query->where('tbro' , '=',  Carbon::today()->toDateString());
                                $query->where('created', $user->id);
                            }]);

        }else if($user->hasRole('Team-Leader')){
            $teams  =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $allmem =  TeamMembers::with('users.roles')
                                    ->whereHas('users.roles', function($query){
                                        $query->where('name', 'Sales-Executive');
                                    })
                                    ->whereIn('team', $teams)->where('status', true)->pluck('user')->toArray();

            array_push($allmem, $user->id);

            $data = Clients::with('telereferral')
                                    ->whereNotIn('status', ['Fresh','Not Interested'])
                                    ->whereHas('history', function($q) use($allmem){
                                        $q->where('tbro' , '=',  Carbon::today()->toDateString());
                                        $q->whereIn('created', $allmem);
                                    })
                                    ->with(['history' => function($query) use($allmem){
                                        $query->where('tbro' , '=',  Carbon::today()->toDateString());
                                        $query->whereIn('created', $allmem);
                                    }]);

        }

        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $actionBtn = '<a type="button" class="btn btn-outline-success btn-sm" target="_blank" href="'. env('APP_URL').'/clients/'.base64_encode($row->id).'/'.'sts' .'"
                                        >   <i class="mdi mdi-eye-outline"></i>
                                    </a>';
                        return $actionBtn;
                    })
                    // ->editColumn('contactinfo', function ($data) { return $data->cont_person .'('. $data->designation.')'; })
                    ->editColumn('name', function ($data) { return $data->name; })
                    ->editColumn('mobile', function ($data) { return $data->mobile; })
                    ->editColumn('category', function ($data) {
                            return $data->history->category;
                    })
                    ->editColumn('tbro', function ($data) {
                            return Carbon::parse($data->history->tbro)->format('d M Y');
                    })
                    ->editColumn('remarks', function ($data) {
                            return $data->history->remarks;
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



}

