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

        // Auto-seed dummy tasks for demonstration if they don't exist yet
        $hasDummy = Task::where('assigned_to', 29)->where('title', 'Optimize Core UI Components')->exists();
        if (!$hasDummy) {
            $user29 = User::find(29);
            if ($user29) {
                // Find Team Leader of User 29
                $teamIds = DB::table('team_members')
                    ->where('user', 29)
                    ->where('status', true)
                    ->pluck('team')
                    ->toArray();

                $tlUser = null;
                if (!empty($teamIds)) {
                    $tlUser = User::whereHas('roles', function($q) {
                        $q->where('name', 'Team-Leader');
                    })->whereHas('teamMember', function($q) use ($teamIds) {
                        $q->whereIn('team', $teamIds)->where('status', true);
                    })->first();
                }
                
                if (!$tlUser) {
                    $tlUser = User::role('Team-Leader')->first();
                }

                if ($tlUser) {
                    $project = DepartmentProjects::first();
                    if (!$project) {
                        $project = DepartmentProjects::create([
                            'project_name' => 'EOD & Targets Integration Project',
                            'status' => 'InProgress',
                            'created_by' => $tlUser->id
                        ]);
                    }

                    $today = Carbon::today();
                    $dummyTasks = [
                        [
                            'title' => 'Optimize Core UI Components',
                            'assigned_to' => 29,
                            'startdate' => $today->copy()->subDays(2)->toDateString(),
                            'enddate' => $today->copy()->addDays(2)->toDateString(),
                            'status' => 'InProgress',
                            'priority' => 'High',
                        ],
                        [
                            'title' => 'Integrate Operations Task Calendar API',
                            'assigned_to' => 29,
                            'startdate' => $today->copy()->addDays(3)->toDateString(),
                            'enddate' => $today->copy()->addDays(7)->toDateString(),
                            'status' => 'ToDo',
                            'priority' => 'Medium',
                        ],
                        [
                            'title' => 'Review Code Quality & Pull Request',
                            'assigned_to' => $tlUser->id,
                            'startdate' => $today->copy()->toDateString(),
                            'enddate' => $today->copy()->addDays(1)->toDateString(),
                            'status' => 'InProgress',
                            'priority' => 'Urgent',
                        ],
                        [
                            'title' => 'Resolve SQL Query Performance Bottlenecks',
                            'assigned_to' => 29,
                            'startdate' => $today->copy()->subDays(5)->toDateString(),
                            'enddate' => $today->copy()->subDays(3)->toDateString(),
                            'status' => 'Completed',
                            'priority' => 'High',
                        ],
                        [
                            'title' => 'Verify Leave Recording Multi-select logic',
                            'assigned_to' => $tlUser->id,
                            'startdate' => $today->copy()->subDays(1)->toDateString(),
                            'enddate' => $today->copy()->addDays(3)->toDateString(),
                            'status' => 'ToDo',
                            'priority' => 'Medium',
                        ]
                    ];

                    foreach ($dummyTasks as $taskData) {
                        Task::create([
                            'title' => $taskData['title'],
                            'projectid' => $project->id,
                            'assigned_to' => $taskData['assigned_to'],
                            'startdate' => $taskData['startdate'],
                            'enddate' => $taskData['enddate'],
                            'status' => $taskData['status'],
                            'priority' => $taskData['priority'],
                            'created_by' => $tlUser->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }
        }

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
