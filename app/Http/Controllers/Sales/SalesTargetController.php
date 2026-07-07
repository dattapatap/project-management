<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\TargetService;
use App\Models\User;
use App\Models\TeamMembers;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;

class SalesTargetController extends Controller
{
    public function __construct(
        private TargetService $targetService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = (int) $request->get('month', date('n'));
        $selectedYear = (int) $request->get('year', date('Y'));

        // Fetch user's own targets for the year
        $targets = $this->targetService->getTargetsForUser($user->id, $selectedYear);

        // Leaderboard for the selected month/year
        $leaderboard = $this->targetService->getLeaderboardData($selectedMonth, $selectedYear);

        // Fetch list of targetable staff if user is Admin or Branch Manager
        $subordinates = collect();
        if ($user->hasRole(['Admin', 'Branch-Manager'])) {
            // NSD: Sales Executives + NSD Team Leaders (dept=1)
            $nsdUsers = User::where('status', 'Active')
                ->where('id', '!=', $user->id)
                ->where(function ($q) {
                    $q->whereHas('roles', function ($r) {
                        $r->where('name', 'Sales-Executive');
                    })
                        ->orWhere(function ($q2) {
                            $q2->whereHas('roles', function ($r) {
                                $r->where('name', 'Team-Leader');
                            })->whereHas('departments', function ($d) {
                                $d->where('department', 1); // NSD
                            });
                        });
                })
                ->get();

            // CSD: CSD Executives + CSD Team Leaders (dept=3)
            $csdUsers = User::where('status', 'Active')
                ->where('id', '!=', $user->id)
                ->where(function ($q) {
                    $q->whereHas('roles', function ($r) {
                        $r->where('name', 'CSD-Executive');
                    })
                        ->orWhere(function ($q2) {
                            $q2->whereHas('roles', function ($r) {
                                $r->where('name', 'Team-Leader');
                            })->whereHas('departments', function ($d) {
                                $d->where('department', 3); // CSD
                            });
                        });
                })
                ->get();

            $subordinates = $nsdUsers->merge($csdUsers)->unique('id')->values();
        }

        $subordinateTargets = [];
        if ($subordinates->isNotEmpty()) {
            $subordinateIds = $subordinates->pluck('id')->toArray();
            $subordinateTargetsRaw = \App\Models\SalesTarget::with('user')->whereIn('user_id', $subordinateIds)
                ->where('period_year', $selectedYear)
                ->orderBy('period_month', 'desc')
                ->get();

            foreach ($subordinateTargetsRaw as $tgt) {
                $tgt->achieved_value = $this->targetService->calculateAchievedValue(
                    $tgt->user_id,
                    $tgt->target_type,
                    $tgt->period_month,
                    $tgt->period_year
                );
                $tgt->save();
            }
            $subordinateTargets = $subordinateTargetsRaw;
        }

        return view('sales.targets.index', compact(
            'targets',
            'leaderboard',
            'subordinates',
            'subordinateTargets',
            'selectedMonth',
            'selectedYear'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'user_id'      => 'required|integer|exists:users,id',
            'target_type'  => 'required|string|in:revenue,conversions,meetings',
            'target_value' => 'required|numeric|min:1',
            'period_month' => 'required|integer|between:1,12',
            'period_year'  => 'required|integer|min:2020',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 400);
        }

        // Only Admin and Branch Manager can set targets
        $user = Auth::user();
        if (!$user->hasRole(['Admin', 'Branch-Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Admin and Branch Manager can set targets.'], 403);
        }

        try {
            $target = $this->targetService->setTarget($request->all(), $user);
            return response()->json([
                'success' => true,
                'message' => 'Sales target configured successfully!',
                'target'  => $target
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function leaderboard(Request $request)
    {
        $month = (int) $request->get('month', date('n'));
        $year = (int) $request->get('year', date('Y'));

        try {
            $leaderboard = $this->targetService->getLeaderboardData($month, $year);
            return response()->json([
                'success' => true,
                'data'    => $leaderboard
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
