<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdvancedReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Projects Report View
     */
    public function projectsReport(Request $request)
    {
        $user = Auth::user();
        
        // Basic metrics for cards
        $metrics = [
            'total' => DepartmentProjects::count(),
            'todo' => DepartmentProjects::where('status', 'ToDo')->count(),
            'in_progress' => DepartmentProjects::where('status', 'InProgress')->count(),
            'completed' => DepartmentProjects::where('status', 'Completed')->count(),
        ];

        // 1. Category Distribution (All Projects)
        $categoryData = DepartmentProjects::with('projectCategory')
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->projectCategory->category ?? 'Uncategorized',
                    'total' => $item->total
                ];
            });

        // 2. Delivery Velocity (Completed Projects - Last 12 Months - Filled)
        $selectedYear = $request->get('year', date('Y'));
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        $growthDataRaw = DepartmentProjects::select(
                DB::raw('count(*) as count'),
                DB::raw("DATE_FORMAT(act_end_date, '%b') as month")
            )
            ->where('status', 'Completed')
            ->whereYear('act_end_date', $selectedYear)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $growthData = collect($months)->map(function($month) use ($growthDataRaw) {
            return (object)[
                'month' => $month,
                'count' => $growthDataRaw->has($month) ? $growthDataRaw->get($month)->count : 0
            ];
        });

        // 3. Completed by Sub-Category (Design, Website, etc.)
        $completedCategories = DepartmentProjects::where('department_projects.status', 'Completed')
            ->whereYear('department_projects.act_end_date', $selectedYear)
            ->join('project_sub_categories', 'department_projects.sub_category', '=', 'project_sub_categories.id')
            ->select('project_sub_categories.name', DB::raw('count(*) as total'))
            ->groupBy('project_sub_categories.name')
            ->get();

        // 4. Avg Completion Time
        $completed = DepartmentProjects::where('status', 'Completed')
            ->whereNotNull('start_date')
            ->whereNotNull('act_end_date')
            ->get();
        
        $totalDays = $completed->reduce(function($carry, $item) {
            return $carry + Carbon::parse($item->start_date)->diffInDays(Carbon::parse($item->act_end_date));
        }, 0);

        $metrics['avg_days'] = $completed->count() > 0 ? round($totalDays / $completed->count()) : 0;
        $metrics['selected_year'] = $selectedYear;

        return view('components.reports.projects', compact('metrics', 'categoryData', 'growthData', 'completedCategories'));
    }

    /**
     * Projects DataTable Data
     */
    public function projectsData(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $view = $request->get('view', 'global');
        
        $data = DepartmentProjects::with(['clients', 'projectCategory', 'creator', 'tasks'])
            ->withCount([
                'tasks', 
                'completedTask',
                'tasks as members_count' => function($query) {
                    $query->select(DB::raw('count(distinct(assigned_to))'));
                }
            ])
            ->whereYear('created_date', $year);

        if ($view == 'critical') {
            $data->where('status', '!=', 'Completed')
                 ->where('end_date', '<=', Carbon::now()->addDays(7));
        }

        $data->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('task_yield', function($row) {
                $totalTasks = $row->tasks_count;
                $sumProgress = $row->tasks->sum('progress');
                $percent = $totalTasks > 0 ? round($sumProgress / $totalTasks) : 0;
                $completed = $row->completed_task_count;
                
                $colorClass = $percent > 70 ? 'bg-success' : ($percent > 30 ? 'bg-primary' : 'bg-danger');

                return '<div class="d-flex align-items-center">
                            <div class="progress-modern flex-grow-1 mr-2" style="width: 70px;">
                                <div class="progress-bar ' . $colorClass . '" style="width: ' . $percent . '%"></div>
                            </div>
                            <span class="font-weight-bold small text-dark">' . $percent . '%</span>
                        </div>';
            })
            ->addColumn('duration', function($row) {
                $start = Carbon::parse($row->start_date);
                $end = ($row->status == 'Completed' && $row->act_end_date) ? Carbon::parse($row->act_end_date) : Carbon::now();
                $days = round($start->diffInDays($end));
                return $days . ' Days';
            })
            ->addColumn('team_count', function($row) {
                $count = $row->members_count;
                return '<span class="creator-identity"><i class="mdi mdi-account-group-outline mr-2"></i>' . $count . ' Members</span>';
            })
            ->addColumn('client_name', function($row) {
                return $row->clients->name ?? 'N/A';
            })
            ->editColumn('status', function($row) {
                $class = 'badge-soft-secondary';
                if($row->status == 'InProgress') $class = 'badge-soft-warning';
                if($row->status == 'Completed') $class = 'badge-soft-success';
                return '<span class="badge ' . $class . ' font-size-12">' . $row->status . '</span>';
            })
            ->editColumn('end_date', function($row) {
                return $row->end_date ? Carbon::parse($row->end_date)->format('d M, Y') : 'N/A';
            })
            ->rawColumns(['status', 'task_yield', 'team_count'])
            ->make(true);
    }

}
