<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\User;
use App\Services\Csd\CsdDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BranchManagerDashboardService
{
    public function __construct(
        private BranchScopeService $branchScope,
        private CsdDashboardService $csdDashboard
    ) {
    }

    public function build(User $user, int $selectedYear): array
    {
        $branchId = $this->branchScope->resolveBranchId($user);
        $branchName = $branchId
            ? (DB::table('branches')->where('id', $branchId)->value('name') ?? 'Branch')
            : 'Branch';

        $branchUserIds = $this->branchScope->getBranchUserIds($user);
        $salesUserIds = $this->branchScope->getBranchSalesUserIds($user);
        $odUserIds = $this->branchScope->getBranchOdUserIds($user);
        $csdUserIds = $this->branchScope->getBranchCsdUserIds($user);

        $branchClientIds = $this->branchClientIds($salesUserIds);
        $branchProjects = $this->branchProjectsQuery($branchClientIds, $odUserIds);
        $branchTasks = Task::whereIn('assigned_to', $odUserIds);

        $startOfMonth = Carbon::now()->startOfMonth();
        $clientsQuery = $this->branchClientsQuery($salesUserIds);

        $nsd = $this->buildNsdMetrics($salesUserIds, $clientsQuery, $selectedYear, $startOfMonth);
        $od = $this->buildOdMetrics($branchProjects, $branchTasks, $odUserIds);
        $csd = $this->csdDashboard->getBranchManagerData($user);

        return [
            'branch_id' => $branchId,
            'branch_name' => $branchName,
            'selected_year' => $selectedYear,
            'available_years' => $this->availableYears(),
            'total_users' => count($branchUserIds),
            'dept_headcount' => [
                'nsd' => count($salesUserIds),
                'csd' => count($csdUserIds),
                'od' => count($odUserIds),
            ],
            'total_clients' => (clone $clientsQuery)->count(),
            'matured_clients' => (clone $clientsQuery)->where('status', 'Matured')->count(),
            'total_projects' => (clone $branchProjects)->count(),
            'active_tasks' => (clone $branchTasks)->whereIn('status', ['ToDo', 'InProgress'])->count(),
            'nsd' => $nsd,
            'csd' => $csd,
            'od' => $od,
            'sales_performance' => $nsd['team_performance_matrix'],
            'monthly_matured_trend' => $this->monthlyMaturedTrend($salesUserIds, $selectedYear),
            'department_overview' => [
                'labels' => ['NSD (Sales)', 'CSD', 'OD (Projects)'],
                'staff' => [count($salesUserIds), count($csdUserIds), count($odUserIds)],
                'workload' => [
                    $nsd['total_active_leads'] ?? 0,
                    $csd['active_clients'] ?? 0,
                    (clone $branchProjects)->where('status', '!=', 'Completed')->count(),
                ],
            ],
        ];
    }

    private function availableYears(): array
    {
        $currentYear = (int) date('Y');
        $startYear = min(2023, $currentYear - 3);

        return range($currentYear, $startYear);
    }

    private function branchClientIds(array $salesUserIds)
    {
        return $this->branchClientsQuery($salesUserIds)->pluck('id');
    }

    private function branchClientsQuery(array $salesUserIds)
    {
        if (empty($salesUserIds)) {
            return Clients::whereRaw('1 = 0');
        }

        return Clients::where(function ($q) use ($salesUserIds) {
            $q->whereIn('ref_user', $salesUserIds)
                ->orWhereIn('tele_ref_user', $salesUserIds);
        });
    }

    private function branchProjectsQuery($branchClientIds, array $odUserIds)
    {
        return DepartmentProjects::where(function ($q) use ($branchClientIds, $odUserIds) {
            if ($branchClientIds->isNotEmpty()) {
                $q->whereIn('client', $branchClientIds);
            }
            if (!empty($odUserIds)) {
                $q->orWhereIn('assigned_to', $odUserIds)
                    ->orWhereHas('tasks', fn ($tq) => $tq->whereIn('assigned_to', $odUserIds));
            }
            if ($branchClientIds->isEmpty() && empty($odUserIds)) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    private function buildNsdMetrics(array $salesUserIds, $clientsQuery, int $selectedYear, Carbon $startOfMonth): array
    {
        $statusDistribution = [
            'Fresh' => (clone $clientsQuery)->where('status', 'Fresh')->whereYear('created_at', $selectedYear)->count(),
            'Followup' => (clone $clientsQuery)->where('status', 'Followup')->whereYear('created_at', $selectedYear)->count(),
            'Meeting Fixed' => (clone $clientsQuery)->where('status', 'Meeting Fixed')->whereYear('created_at', $selectedYear)->count(),
            'Matured' => (clone $clientsQuery)->where('status', 'Matured')->whereYear('updated_at', $selectedYear)->count(),
            'Not Interested' => (clone $clientsQuery)->where('status', 'Not Interested')->whereYear('updated_at', $selectedYear)->count(),
        ];

        $totalActiveLeads = (clone $clientsQuery)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->count();

        $todaysTbros = empty($salesUserIds) ? 0 : Clients::whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->where(function ($q) use ($salesUserIds) {
                $q->whereIn('ref_user', $salesUserIds)->orWhereIn('tele_ref_user', $salesUserIds);
            })
            ->whereHas('histories', function ($q) use ($salesUserIds) {
                $q->where('tbro', Carbon::today()->toDateString())
                    ->whereIn('created', $salesUserIds);
            })
            ->count();

        $overdueTbros = empty($salesUserIds) ? 0 : Clients::where(function ($q) use ($salesUserIds) {
            $q->whereIn('ref_user', $salesUserIds)->orWhereIn('tele_ref_user', $salesUserIds);
        })
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereHas('histories', function ($q) use ($salesUserIds) {
                $q->where('tbro', '<', Carbon::today()->toDateString())
                    ->whereIn('created', $salesUserIds);
            })
            ->count();

        $teamMatrix = empty($salesUserIds) ? collect() : User::whereIn('id', $salesUserIds)
            ->withCount(['clients as active_leads_count' => fn ($q) => $q->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])])
            ->withCount(['clients as matured_leads_count' => fn ($q) => $q->where('status', 'Matured')->whereYear('updated_at', $selectedYear)])
            ->withCount(['clients as today_callbacks_count' => fn ($q) => $q->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                ->whereHas('histories', fn ($sq) => $sq->where('tbro', Carbon::today()->toDateString()))])
            ->withCount(['clients as overdue_callbacks_count' => fn ($q) => $q->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                ->whereHas('histories', fn ($sq) => $sq->where('tbro', '<', Carbon::today()->toDateString()))])
            ->orderByDesc('matured_leads_count')
            ->get();

        return [
            'fresh_leads' => (clone $clientsQuery)->where('status', 'Fresh')->count(),
            'followups' => (clone $clientsQuery)->whereIn('status', ['Followup', 'Meeting Fixed'])->count(),
            'matured_this_month' => (clone $clientsQuery)->where('status', 'Matured')->where('updated_at', '>=', $startOfMonth)->count(),
            'matured_year' => (clone $clientsQuery)->where('status', 'Matured')->whereYear('updated_at', $selectedYear)->count(),
            'team_size' => count($salesUserIds),
            'total_active_leads' => $totalActiveLeads,
            'todays_tbros_count' => $todaysTbros,
            'overdue_tbros_count' => $overdueTbros,
            'status_distribution' => $statusDistribution,
            'team_performance_matrix' => $teamMatrix,
            'recent_matured' => (clone $clientsQuery)
                ->where('status', 'Matured')
                ->with('referral')
                ->whereYear('updated_at', $selectedYear)
                ->latest('updated_at')
                ->take(8)
                ->get(),
        ];
    }

    private function buildOdMetrics($branchProjects, $branchTasks, array $odUserIds): array
    {
        $teamMatrix = empty($odUserIds) ? collect() : User::whereIn('id', $odUserIds)
            ->withCount(['tasks as active_tasks' => fn ($q) => $q->whereIn('status', ['ToDo', 'InProgress'])])
            ->withCount(['tasks as completed_tasks' => fn ($q) => $q->where('status', 'Completed')])
            ->withSum('taskLogs as total_hours', 'time_spend')
            ->orderByDesc('active_tasks')
            ->get();

        return [
            'projects_todo' => (clone $branchProjects)->where('status', 'ToDo')->count(),
            'projects_in_progress' => (clone $branchProjects)->where('status', 'InProgress')->count(),
            'projects_completed' => (clone $branchProjects)->where('status', 'Completed')->count(),
            'tasks_in_progress' => (clone $branchTasks)->where('status', 'InProgress')->count(),
            'tasks_todo' => (clone $branchTasks)->where('status', 'ToDo')->count(),
            'tasks_completed' => (clone $branchTasks)->where('status', 'Completed')->count(),
            'team_size' => count($odUserIds),
            'team_performance_matrix' => $teamMatrix,
            'near_deadline_projects' => (clone $branchProjects)
                ->with('clients')
                ->where('status', '!=', 'Completed')
                ->where('end_date', '<=', Carbon::now()->addDays(7))
                ->orderBy('end_date')
                ->take(8)
                ->get(),
        ];
    }

    private function monthlyMaturedTrend(array $salesUserIds, int $selectedYear): array
    {
        $months = [];
        $counts = [];

        if (empty($salesUserIds)) {
            for ($m = 1; $m <= 12; $m++) {
                $months[] = Carbon::create($selectedYear, $m, 1)->format('M');
                $counts[] = 0;
            }

            return compact('months', 'counts');
        }

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($selectedYear, $m, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $months[] = $start->format('M');
            $counts[] = Clients::whereIn('ref_user', $salesUserIds)
                ->where('status', 'Matured')
                ->whereBetween('updated_at', [$start, $end])
                ->count();
        }

        return compact('months', 'counts');
    }
}
