<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\ClientHistory;
use App\Models\TeamMembers;
use App\Models\User;
use App\Repositories\ClientRepository;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class SalesActivityController extends Controller
{
    public function __construct(
        private ClientRepository $clientRepo
    ) {}

    public function index()
    {
        return view('sales.activity_calendar');
    }

    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date',
        ]);

        $user = Auth::user();
        $userIds = $this->getSalesUserIds($user);

        // Fetch histories with scheduled TBRO callbacks in range
        $histories = $this->clientRepo->getCalendarEvents($userIds, $request->start, $request->end);

        $events = [];
        $colorMap = [
            'Fresh'             => '#3b82f6',
            'Followup'          => '#06b6d4',
            'Meeting Fixed'     => '#a855f7',
            'Hot Perspective'   => '#ef4444',
            'Warm Perspective'  => '#f59e0b',
            'Matured'           => '#10b981',
            'Not Interested'    => '#6b7280',
        ];

        foreach ($histories as $history) {
            $client = $history->clientNotif;
            if (!$client) continue;

            $status = $client->status;
            $color = $colorMap[$status] ?? '#3b82f6';

            // Flex start time
            $time = $history->time ?? '10:00:00';
            $startDateTime = $history->tbro . 'T' . $time;

            $events[] = [
                'id'              => $history->id,
                'title'           => "{$client->name} - {$history->tbro_type} ({$status})",
                'start'           => $startDateTime,
                'url'             => url("/clients/" . base64_encode($client->id) . "/sts"),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'contact_person'=> $client->cont_person,
                    'remarks'       => $history->remarks,
                    'executive'     => $history->referel->name ?? 'Unassigned'
                ]
            ];
        }

        return response()->json($events);
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
