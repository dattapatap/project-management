<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use App\Models\TeamMembers;
use App\Models\User;
use App\Repositories\ClientRepository;
use Auth;
use DB;
use Illuminate\Http\Request;

class SalesPipelineController extends Controller
{
    private array $stages = [
        'Fresh',
        'Followup',
        'Meeting Fixed',
        'Hot Perspective',
        'Warm Perspective'
    ];

    public function __construct(
        private ClientRepository $clientRepo
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $userIds = $this->getSalesUserIds($user);

        // Get clients grouped by stage
        $pipeline = $this->clientRepo->getPipelineCards($userIds, $this->stages);

        // Get list of sales executives for filtering/assignment (if Admin/PM/TL)
        $executives = [];
        if ($user->hasRole(['Admin', 'Branch-Manager', 'Team-Leader'])) {
            $executives = User::role(['Sales-Executive', 'Team-Leader'])->where('status', 'Active')->get();
        }

        return view('sales.pipeline', compact('pipeline', 'executives'));
    }

    public function moveCard(Request $request)
    {
        $request->validate([
            'client_id'  => 'required|integer',
            'new_status' => 'required|string',
            'tbro'       => 'nullable|date',
            'time'       => 'nullable',
            'remarks'    => 'nullable|string',
        ]);

        $client = Clients::findOrFail($request->client_id);
        $user = Auth::user();

        // Security check: ensure user has access to this client
        $userIds = $this->getSalesUserIds($user);
        if (!in_array($client->ref_user, $userIds) && !$user->hasRole(['Admin', 'Branch-Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access to this client.'], 403);
        }

        // Validate target status
        if (!in_array($request->new_status, $this->stages)) {
            return response()->json(['success' => false, 'message' => 'Invalid status stage.'], 400);
        }

        try {
            DB::beginTransaction();

            $history = $this->clientRepo->moveToStage(
                $client, 
                $request->new_status, 
                $user->id,
                $request->tbro,
                $request->time,
                $request->remarks
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully moved client status to {$request->new_status}",
                'history' => $history
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to move card: ' . $e->getMessage()], 500);
        }
    }

    private function getSalesUserIds(User $user): array
    {
        if ($user->hasRole(['Admin', 'Branch-Manager'])) {
            return User::pluck('id')->toArray();
        }

        if ($user->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $allMembers = TeamMembers::whereIn('team', $teams)
                ->where('status', true)
                ->pluck('user')
                ->toArray();

            if (!in_array($user->id, $allMembers)) {
                $allMembers[] = $user->id;
            }
            return $allMembers;
        }

        return [$user->id];
    }
}
