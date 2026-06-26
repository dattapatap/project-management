<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\CsdChangeRequest;
use App\Models\CsdClientAssignment;
use App\Models\CsdCollectionFollowup;
use App\Models\CsdCommunication;
use App\Models\CsdOpportunity;
use App\Models\CsdRenewal;
use App\Models\CsdSupportTicket;
use App\Models\DepartmentProjects;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserPerformanceService
{
    public const DEPT_NSD = 1;
    public const DEPT_OD = 2;
    public const DEPT_CSD = 3;

    public function departmentType(User $user): string
    {
        $user->loadMissing('departments');
        $deptId = (int) ($user->departments->department ?? 0);

        if ($user->hasRole('Sales-Executive') || ($deptId === self::DEPT_NSD && $user->hasRole('Team-Leader'))) {
            return 'nsd';
        }

        if ($user->hasRole('CSD-Executive') || ($deptId === self::DEPT_CSD && $user->hasRole(['Team-Leader', 'Branch-Manager']))) {
            return 'csd';
        }

        if ($deptId === self::DEPT_NSD) {
            return 'nsd';
        }

        if ($deptId === self::DEPT_CSD) {
            return 'csd';
        }

        return 'od';
    }

    public function isSalesUser(User $user): bool
    {
        return $this->departmentType($user) === 'nsd'
            && $user->hasRole(['Sales-Executive', 'Team-Leader']);
    }

    public function isCsdUser(User $user): bool
    {
        return $this->departmentType($user) === 'csd';
    }

    /**
     * @return array<string, mixed>
     */
    public function buildMetrics(User $user, int $year, string $month = 'All'): array
    {
        return match ($this->departmentType($user)) {
            'nsd' => $this->buildNsdMetrics($user, $year, $month),
            'csd' => $this->buildCsdMetrics($user, $year, $month),
            default => $this->buildOdMetrics($user, $year, $month),
        };
    }

    public function buildMonthlyTrend(User $user, int $year): Collection
    {
        $userId = $user->id;
        $trendMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $type = $this->departmentType($user);

        if ($type === 'nsd') {
            $monthlyClients = DB::table('clients')->where('ref_user', $userId)->whereYear('created_at', $year)
                ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
                ->groupBy('month')->get()->keyBy('month');

            $monthlyMatured = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured')->whereYear('created_at', $year)
                ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
                ->groupBy('month')->get()->keyBy('month');

            return collect($trendMonths)->map(fn ($m) => (object) [
                'month' => $m,
                'tasks' => 0,
                'clients' => $monthlyClients->has($m) ? $monthlyClients->get($m)->count : 0,
                'hours' => 0,
                'matured' => $monthlyMatured->has($m) ? $monthlyMatured->get($m)->count : 0,
                'communications' => 0,
                'opportunities_won' => 0,
            ]);
        }

        if ($type === 'csd') {
            $comms = CsdCommunication::where('created_by', $userId)->whereYear('created_at', $year)
                ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
                ->groupBy('month')->get()->keyBy('month');

            $won = CsdOpportunity::where('assigned_to', $userId)->where('status', 'won')->whereYear('updated_at', $year)
                ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
                ->groupBy('month')->get()->keyBy('month');

            return collect($trendMonths)->map(fn ($m) => (object) [
                'month' => $m,
                'tasks' => 0,
                'clients' => 0,
                'hours' => 0,
                'matured' => 0,
                'communications' => $comms->has($m) ? $comms->get($m)->count : 0,
                'opportunities_won' => $won->has($m) ? $won->get($m)->count : 0,
            ]);
        }

        $monthlyTasks = Task::where('assigned_to', $userId)->where('status', 'Completed')->whereYear('updated_at', $year)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $monthlyHours = TaskLog::where('userid', $userId)->whereYear('created_at', $year)
            ->select(DB::raw('SUM(time_spend) as minutes'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        return collect($trendMonths)->map(fn ($m) => (object) [
            'month' => $m,
            'tasks' => $monthlyTasks->has($m) ? $monthlyTasks->get($m)->count : 0,
            'clients' => 0,
            'hours' => $monthlyHours->has($m) ? round($monthlyHours->get($m)->minutes, 1) : 0,
            'matured' => 0,
            'communications' => 0,
            'opportunities_won' => 0,
        ]);
    }

    public function performanceScore(array $stats, string $departmentType): int
    {
        return match ($departmentType) {
            'nsd' => min(100, (int) (
                ($stats['matured'] ?? 0) * 15
                + ($stats['active_followups'] ?? 0) * 2
                + min(($stats['total_sales'] ?? 0) / 10000, 40)
            )),
            'csd' => min(100, (int) (
                ($stats['active_clients'] ?? 0) * 3
                + ($stats['communications'] ?? 0) * 2
                + ($stats['tickets_resolved'] ?? 0) * 5
                + ($stats['collections_paid'] ?? 0) * 4
                + ($stats['opportunities_won'] ?? 0) * 10
                + ($stats['change_requests_completed'] ?? 0) * 6
            )),
            default => min(100, (int) (
                ($stats['completed_tasks'] ?? 0) * 4
                + min(($stats['total_hours'] ?? 0) / 4, 40)
                + ($stats['completed_projects'] ?? 0) * 8
            )),
        };
    }

    private function applyMonthFilter($query, int $year, string $month, string $column = 'created_at'): void
    {
        $query->whereYear($column, $year);
        if ($month !== 'All') {
            $query->whereMonth($column, date('m', strtotime($month)));
        }
    }

    private function buildNsdMetrics(User $user, int $year, string $month): array
    {
        $userId = $user->id;

        $maturedQ = DB::table('clients')->where('ref_user', $userId)->where('status', 'Matured');
        $followupQ = DB::table('clients')->where('ref_user', $userId)->where('status', 'Followup');
        $salesQ = DB::table('client_payments')->where('created_by', $userId);
        $leadsQ = DB::table('clients')->where('ref_user', $userId);
        $dsrQ = DB::table('client_histories')->where('created', $userId)->where('category', 'DSR');

        $this->applyMonthFilter($maturedQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($followupQ, $year, $month, 'created_at');
        $this->applyMonthFilter($salesQ, $year, $month);
        $this->applyMonthFilter($leadsQ, $year, $month);
        $this->applyMonthFilter($dsrQ, $year, $month);

        $activeFollowups = Clients::where('ref_user', $userId)
            ->whereIn('status', ['Followup', 'Meeting Fixed'])->count();

        return [
            'total_leads' => $leadsQ->count(),
            'matured' => $maturedQ->count(),
            'followup' => $followupQ->count(),
            'active_followups' => $activeFollowups,
            'total_sales' => round((float) $salesQ->sum('amount'), 2),
            'dsr_logs' => $dsrQ->count(),
            'completed_tasks' => 0,
            'pending_tasks' => 0,
            'active_tasks' => 0,
            'completed_projects' => 0,
            'total_hours' => 0,
            'avg_daily_hours' => 0,
            'avg_task_delivery_time' => 0,
        ];
    }

    private function buildOdMetrics(User $user, int $year, string $month): array
    {
        $userId = $user->id;

        $compTasksQ = Task::where('assigned_to', $userId)->where('status', 'Completed');
        $pendingTasksQ = Task::where('assigned_to', $userId)->where('status', '!=', 'Completed');
        $logsQ = TaskLog::where('userid', $userId);
        $compProjsQ = DepartmentProjects::where('status', 'Completed')->whereHas('tasks', fn ($q) => $q->where('assigned_to', $userId));

        $this->applyMonthFilter($compTasksQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($pendingTasksQ, $year, $month, 'created_at');
        $this->applyMonthFilter($logsQ, $year, $month);
        $this->applyMonthFilter($compProjsQ, $year, $month, 'updated_at');

        $totalHours = round((float) $logsQ->sum('time_spend'), 1);
        $daysWorked = (clone $logsQ)->distinct()->count(DB::raw('DATE(created_at)'));
        $completedTasks = $compTasksQ->count();

        $totalHoursOnCompleted = Task::where('assigned_to', $userId)->where('status', 'Completed')
            ->whereYear('updated_at', $year)
            ->withSum('logs as total_hours', 'time_spend')
            ->get()->sum('total_hours');

        return [
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasksQ->count(),
            'active_tasks' => Task::where('assigned_to', $userId)->where('status', 'InProgress')->count(),
            'completed_projects' => $compProjsQ->count(),
            'total_hours' => $totalHours,
            'avg_daily_hours' => $daysWorked > 0 ? round($totalHours / $daysWorked, 1) : 0,
            'avg_task_delivery_time' => $completedTasks > 0 ? round($totalHoursOnCompleted / $completedTasks, 1) : 0,
            'total_leads' => 0,
            'matured' => 0,
            'followup' => 0,
            'active_followups' => 0,
            'total_sales' => 0,
            'dsr_logs' => 0,
        ];
    }

    private function buildCsdMetrics(User $user, int $year, string $month): array
    {
        $userId = $user->id;

        $commsQ = CsdCommunication::where('created_by', $userId);
        $paidQ = CsdCollectionFollowup::where('assigned_to', $userId)->where('status', 'paid');
        $ticketsResolvedQ = CsdSupportTicket::where('assigned_to', $userId)->whereIn('status', ['resolved', 'closed']);
        $openTicketsQ = CsdSupportTicket::where('assigned_to', $userId)->whereIn('status', ['open', 'in_progress']);
        $wonQ = CsdOpportunity::where('assigned_to', $userId)->where('status', 'won');
        $openOppQ = CsdOpportunity::where('assigned_to', $userId)->whereIn('status', ['identified', 'proposed']);
        $crDoneQ = CsdChangeRequest::where('assigned_to', $userId)->where('status', 'completed');
        $crPendingQ = CsdChangeRequest::where('assigned_to', $userId)->whereNotIn('status', ['completed', 'rejected']);
        $renewedQ = CsdRenewal::where('created_by', $userId)->where('status', 'renewed');

        $this->applyMonthFilter($commsQ, $year, $month);
        $this->applyMonthFilter($paidQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($ticketsResolvedQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($wonQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($crDoneQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($renewedQ, $year, $month, 'updated_at');

        return [
            'active_clients' => CsdClientAssignment::where('assigned_to', $userId)->where('status', 'active')->count(),
            'at_risk_clients' => CsdClientAssignment::where('assigned_to', $userId)->where('health_status', 'at_risk')->count(),
            'communications' => $commsQ->count(),
            'collections_paid' => $paidQ->count(),
            'collections_overdue' => CsdCollectionFollowup::where('assigned_to', $userId)->where('status', 'overdue')->count(),
            'tickets_resolved' => $ticketsResolvedQ->count(),
            'open_tickets' => $openTicketsQ->count(),
            'opportunities_won' => $wonQ->count(),
            'open_opportunities' => $openOppQ->count(),
            'change_requests_completed' => $crDoneQ->count(),
            'change_requests_pending' => $crPendingQ->count(),
            'renewals_completed' => $renewedQ->count(),
            'completed_tasks' => 0,
            'pending_tasks' => 0,
            'active_tasks' => 0,
            'completed_projects' => 0,
            'total_hours' => 0,
            'avg_daily_hours' => 0,
            'avg_task_delivery_time' => 0,
            'total_leads' => 0,
            'matured' => 0,
            'followup' => 0,
            'active_followups' => 0,
            'total_sales' => 0,
            'dsr_logs' => 0,
        ];
    }
}
