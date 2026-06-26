<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\TeamMembers;
use App\Services\BranchScopeService;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class DashboardController extends Controller
{



    public function chartdata(Request $request)
    {
        $user = Auth::user();
        $sales = [];

        if ($user->hasRole('Sales-Executive') || $request->has('personal')) {
            $sales  = DB::select(
                'SELECT DATE_FORMAT(date, "%b") AS month, IFNULL( COUNT(DISTINCT ch.client), 0) as total
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
                            LEFT JOIN client_histories as ch
                            ON (ch.created_at >= date AND ch.created_at < date + INTERVAL 1 MONTH AND ch.status="Matured" AND ch.created=' . $user->id . ')
                            GROUP BY date'
            );
        } else if ($user->hasRole('Team-Leader')) {
            $allmem =  DB::table('department_members')->where('parent_leader', $user->id)->pluck('user')->toArray();
            array_push($allmem, $user->id);
            $allmemStr = implode(',', array_map('intval', $allmem));

            $sales  = DB::select(
                'SELECT DATE_FORMAT(date, "%b") AS month, IFNULL( COUNT(DISTINCT ch.client), 0) as total
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
                            LEFT JOIN client_histories as ch
                            ON (ch.created_at >= date AND ch.created_at < date + INTERVAL 1 MONTH AND ch.status="Matured" AND ch.created IN (' . $allmemStr . '))
                            GROUP BY date'
            );
        } else if ($user->isBranchManager()) {
            $branchScope = app(BranchScopeService::class);
            $salesUserIds = $branchScope->getBranchSalesUserIds($user);
            $allmemStr = !empty($salesUserIds) ? implode(',', array_map('intval', $salesUserIds)) : '0';

            $sales  = DB::select(
                'SELECT DATE_FORMAT(date, "%b") AS month, IFNULL( COUNT(DISTINCT ch.client), 0) as total
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
                            LEFT JOIN client_histories as ch
                            ON (ch.created_at >= date AND ch.created_at < date + INTERVAL 1 MONTH AND ch.status="Matured" AND ch.created IN (' . $allmemStr . '))
                            GROUP BY date'
            );
        } else if ($user->hasRole('Admin')) {
            $sales  = DB::select(
                'SELECT DATE_FORMAT(date, "%b") AS month, IFNULL( COUNT(DISTINCT ch.client), 0) as total
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
                            LEFT JOIN client_histories as ch
                            ON (ch.created_at >= date AND ch.created_at < date + INTERVAL 1 MONTH AND ch.status="Matured")
                            GROUP BY date'
            );
        }

        $counts = [];
        $months = [];
        if (!empty($sales) && is_array($sales)) {
            foreach ($sales as $sale) {
                $counts[] = (int)($sale->total ?? 0);
                $months[] = $sale->month ?? '';
            }
        }

        return response()->json([
            'code' => 200,
            'status' => true,
            'sales' => $sales,
            'counts' => $counts,
            'months' => $months
        ], 200);
    }


    public function getTodaysTbros(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Sales-Executive') || $request->has('personal')) {
            $data = Clients::with(['referral', 'telereferral'])
                ->whereNotIn('status', ['Fresh', 'Not Interested'])
                ->whereHas('history', function ($q) use ($user) {
                    $q->where('tbro', '=',  Carbon::today()->toDateString());
                    $q->where('created', $user->id);
                })
                ->with(['history' => function ($query) use ($user) {
                    $query->where('tbro', '=',  Carbon::today()->toDateString());
                    $query->where('created', $user->id);
                }]);
        } else if ($user->hasRole('Team-Leader')) {
            $teams  =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $allmem =  TeamMembers::with('users.roles')
                ->whereHas('users.roles', function ($query) {
                    $query->where('name', 'Sales-Executive');
                })
                ->whereIn('team', $teams)->where('status', true)->pluck('user')->toArray();

            array_push($allmem, $user->id);

            $data = Clients::with('telereferral')
                ->whereNotIn('status', ['Fresh', 'Not Interested'])
                ->whereHas('history', function ($q) use ($allmem) {
                    $q->where('tbro', '=',  Carbon::today()->toDateString());
                    $q->whereIn('created', $allmem);
                })
                ->with(['history' => function ($query) use ($allmem) {
                    $query->where('tbro', '=',  Carbon::today()->toDateString());
                    $query->whereIn('created', $allmem);
                }]);
        }

        return FacadesDataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = '<div class="d-flex" style="gap: 8px;">
                                <button type="button" class="btn btn-sm btn-outline-primary update-sts-btn"
                                        data-client-id="' . $row->id . '"
                                        data-client-name="' . htmlspecialchars($row->name, ENT_QUOTES) . '"
                                        style="border-radius: 8px; font-size: 11px; padding: 5px 10px; font-weight: 600;">
                                    <i class="mdi mdi-clock-outline mr-1"></i> Update STS
                                </button>
                                <button type="button" class="btn btn-sm btn-primary update-dsr-btn"
                                        data-client-id="' . $row->id . '"
                                        data-client-name="' . htmlspecialchars($row->name, ENT_QUOTES) . '"
                                        style="border-radius: 8px; font-size: 11px; padding: 5px 10px; font-weight: 600; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none;">
                                    <i class="mdi mdi-file-document-outline mr-1"></i> Update DSR
                                </button>
                              </div>';
                return $actionBtn;
            })
            // ->editColumn('contactinfo', function ($data) { return $data->cont_person .'('. $data->designation.')'; })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->editColumn('mobile', function ($data) {
                return $data->mobile;
            })
            ->editColumn('category', function ($data) {
                return $data->history->category;
            })
            ->editColumn('tbro', function ($data) {
                return Carbon::parse($data->history->tbro)->format('d M Y');
            })
            ->editColumn('remarks', function ($data) {
                return $data->history->remarks;
            })
            ->editColumn('telereferral', function ($data) {
                return $data->telereferral->name ?? '-';
            })
            ->editColumn('status', function ($data) {
                return '<span class="text-success">' . $data->status . '</span>';
            })
            ->rawColumns(['action', 'status',])
            ->make(true);
    }
}
