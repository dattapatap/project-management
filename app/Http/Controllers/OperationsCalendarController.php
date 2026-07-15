<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use App\Models\DepartmentProjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationsCalendarController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the Operations Task Calendar.
     */
    public function index()
    {
        $user = Auth::user();


        // Resolve subordinates for filtering (if leader/admin/PM/manager)
        $subordinates = collect();
        if ($user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager'])) {
            $subordinates = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Developer', 'Designer', 'Seo-Developer', 'Accountant']);
            })->orderBy('name')->get();
        } elseif ($user->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')
                ->where('user', $user->id)
                ->where('status', true)
                ->pluck('team')
                ->toArray();

            $subordinateIds = DB::table('team_members')
                ->whereIn('team', $teams)
                ->where('status', true)
                ->where('user', '!=', $user->id)
                ->pluck('user')
                ->toArray();

            $subordinates = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        }

        return view('operations.task_calendar', compact('subordinates'));
    }

    /**
     * Fetch calendar events.
     */
    public function events(Request $request)
    {
        $user = Auth::user();
        $start = $request->query('start');
        $end = $request->query('end');
        $filterUserId = $request->query('user_id');

        $userIds = [$user->id];

        if ($user->hasRole('Team-Leader')) {
            $teams = DB::table('team_members')
                ->where('user', $user->id)
                ->where('status', true)
                ->pluck('team')
                ->toArray();

            $teamUserIds = DB::table('team_members')
                ->whereIn('team', $teams)
                ->where('status', true)
                ->pluck('user')
                ->toArray();

            $userIds = array_unique(array_merge($userIds, $teamUserIds));
        } elseif ($user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager'])) {
            $userIds = null; // null means retrieve all tasks
        }

        // Apply drop down filter if set
        if ($filterUserId) {
            if ($userIds === null || in_array((int)$filterUserId, $userIds, true)) {
                $userIds = [(int)$filterUserId];
            } else {
                // If attempting to filter to someone they can't manage, force empty results
                $userIds = [-1];
            }
        }

        $query = Task::with(['project', 'user'])
            ->where(function($q) use ($start, $end) {
                $q->where(function($sq) use ($start, $end) {
                    $sq->whereNotNull('startdate')
                       ->whereNotNull('enddate')
                       ->where('startdate', '<=', $end)
                       ->where('enddate', '>=', $start);
                })
                ->orWhere(function($sq) use ($start, $end) {
                    $sq->whereNull('startdate')
                       ->whereNotNull('enddate')
                       ->whereBetween('enddate', [$start, $end]);
                });
            });

        if ($userIds !== null) {
            $query->whereIn('assigned_to', $userIds);
        }

        $tasks = $query->get();

        $events = [];
        foreach ($tasks as $task) {
            $color = '#3b82f6'; // default: Blue
            if ($task->status === 'Completed') {
                $color = '#10b981'; // Green
            } elseif ($task->status === 'InProgress') {
                $color = '#f59e0b'; // Orange
            } elseif ($task->priority === 'High' || $task->priority === 'Urgent') {
                $color = '#ef4444'; // Red
            }

            $events[] = [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->startdate ?: $task->enddate,
                'end' => $task->enddate ? Carbon::parse($task->enddate)->addDay()->toDateString() : ($task->startdate ?: null),
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'project' => $task->project->project_name ?? 'Internal Project',
                    'assignee' => $task->user->name ?? 'Unassigned',
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'start_date' => $task->startdate ? Carbon::parse($task->startdate)->format('d M Y') : 'N/A',
                    'end_date' => $task->enddate ? Carbon::parse($task->enddate)->format('d M Y') : 'N/A',
                    'project_id' => base64_encode($task->projectid),
                ]
            ];
        }

        return response()->json($events);
    }
}
