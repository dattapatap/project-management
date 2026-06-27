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

        // Fetch list of sales reps if user is TL or Admin
        $subordinates = [];
        if ($user->hasRole(['Admin', 'Branch-Manager'])) {
            $subordinates = User::role('Sales-Executive')->where('status', 'Active')->get();
        } elseif ($user->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $subordinates = User::whereHas('teamMember', function ($q) use ($teams) {
                $q->whereIn('team', $teams);
            })
            ->role('Sales-Executive')
            ->where('status', 'Active')
            ->get();
        }

        return view('sales.targets.index', compact(
            'targets',
            'leaderboard',
            'subordinates',
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

        // Authorization check: TL can only set targets for their team members
        $user = Auth::user();
        if (!$user->hasRole(['Admin', 'Branch-Manager', 'Team-Leader'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to set targets.'], 403);
        }

        if ($user->hasRole('Team-Leader') && !$user->hasRole(['Admin', 'Branch-Manager'])) {
            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $isSubordinate = TeamMembers::whereIn('team', $teams)
                ->where('user', $request->user_id)
                ->where('status', true)
                ->exists();

            if (!$isSubordinate && $request->user_id != $user->id) {
                return response()->json(['success' => false, 'message' => 'Cannot set target for users outside your team.'], 403);
            }
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
