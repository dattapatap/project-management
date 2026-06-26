<?php

namespace App\Services\Csd;

use App\Models\CsdAmcContract;
use App\Models\CsdChangeRequest;
use App\Models\CsdClientAssignment;
use App\Models\CsdCollectionFollowup;
use App\Models\CsdCommunication;
use App\Models\CsdOpportunity;
use App\Models\CsdRenewal;
use App\Models\CsdSupportTicket;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use App\Services\UserPerformanceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CsdTeamReportService
{
    private const MONTH_LABELS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    public function __construct(
        private CsdTeamScopeService $scope,
        private UserPerformanceService $performance
    ) {
    }

    public function getTeamMembers(User $actor): Collection
    {
        $memberIds = $this->resolveMemberIds($actor);

        return User::whereIn('id', $memberIds)
            ->with(['departments', 'roles'])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * @return array<string, mixed>
     */
    public function getReportData(User $actor, ?int $selectedUserId, int $year, string $month): array
    {
        $memberIds = $this->resolveMemberIds($actor);
        $members = $this->getTeamMembers($actor);

        if ($selectedUserId !== null && !in_array($selectedUserId, $memberIds, true)) {
            $selectedUserId = null;
        }

        $targetIds = $selectedUserId !== null ? [$selectedUserId] : $memberIds;
        $selectedUser = $selectedUserId !== null ? $members->firstWhere('id', $selectedUserId) : null;

        $metrics = $selectedUserId !== null
            ? array_merge($this->emptyMetrics(), $this->performance->buildMetrics($selectedUser, $year, $month))
            : $this->buildAggregatedMetrics($targetIds, $year, $month);

        $monthlyTrend = $selectedUserId !== null
            ? $this->buildUserMonthlyTrend($selectedUserId, $year)
            : $this->buildTeamMonthlyTrend($targetIds, $year);

        $performanceScore = $this->performance->performanceScore($metrics, 'csd');

        $executiveRows = $this->buildExecutiveRows($memberIds, $year, $month);

        $workloadMix = [
            ['label' => 'Open Tickets', 'value' => (int) ($metrics['open_tickets'] ?? 0)],
            ['label' => 'Pending Change Requests', 'value' => (int) ($metrics['change_requests_pending'] ?? 0)],
            ['label' => 'Open Opportunities', 'value' => (int) ($metrics['open_opportunities'] ?? 0)],
            ['label' => 'Overdue Collections', 'value' => (int) ($metrics['collections_overdue'] ?? 0)],
            ['label' => 'At-Risk Clients', 'value' => (int) ($metrics['at_risk_clients'] ?? 0)],
        ];

        return [
            'team_size' => count($memberIds),
            'selected_user_id' => $selectedUserId,
            'selected_user' => $selectedUser,
            'is_team_view' => $selectedUserId === null,
            'year' => $year,
            'month' => $month,
            'months' => array_merge(['All'], self::MONTH_LABELS),
            'available_years' => range((int) date('Y'), (int) date('Y') - 5),
            'members' => $members,
            'metrics' => $metrics,
            'performance_score' => $performanceScore,
            'monthly_trend' => $monthlyTrend,
            'executives' => $executiveRows,
            'workload_mix' => $workloadMix,
            'comparison' => $this->buildComparisonChartData($executiveRows),
            'period_label' => $this->periodLabel($year, $month),
            'snapshot' => $this->buildSnapshot($targetIds),
        ];
    }

    private function periodLabel(int $year, string $month): string
    {
        return $month === 'All' ? (string) $year : "{$month} {$year}";
    }

    /**
     * @param  array<int>  $memberIds
     * @return array<string, mixed>
     */
    private function buildAggregatedMetrics(array $memberIds, int $year, string $month): array
    {
        if (empty($memberIds)) {
            return $this->emptyMetrics();
        }

        $commsQ = CsdCommunication::whereIn('created_by', $memberIds);
        $paidQ = CsdCollectionFollowup::whereIn('assigned_to', $memberIds)->where('status', 'paid');
        $ticketsResolvedQ = CsdSupportTicket::whereIn('assigned_to', $memberIds)->whereIn('status', ['resolved', 'closed']);
        $openTicketsQ = CsdSupportTicket::whereIn('assigned_to', $memberIds)->whereIn('status', ['open', 'in_progress']);
        $wonQ = CsdOpportunity::whereIn('assigned_to', $memberIds)->where('status', 'won');
        $openOppQ = CsdOpportunity::whereIn('assigned_to', $memberIds)->whereIn('status', ['identified', 'proposed']);
        $crDoneQ = CsdChangeRequest::whereIn('assigned_to', $memberIds)->where('status', 'completed');
        $crPendingQ = CsdChangeRequest::whereIn('assigned_to', $memberIds)->whereNotIn('status', ['completed', 'rejected']);
        $renewedQ = CsdRenewal::whereIn('created_by', $memberIds)->where('status', 'renewed');

        $this->applyMonthFilter($commsQ, $year, $month);
        $this->applyMonthFilter($paidQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($ticketsResolvedQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($wonQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($crDoneQ, $year, $month, 'updated_at');
        $this->applyMonthFilter($renewedQ, $year, $month, 'updated_at');

        $clientIds = CsdClientAssignment::whereIn('assigned_to', $memberIds)->pluck('client');

        return [
            'active_clients' => CsdClientAssignment::whereIn('assigned_to', $memberIds)->where('status', 'active')->count(),
            'at_risk_clients' => CsdClientAssignment::whereIn('assigned_to', $memberIds)->where('health_status', 'at_risk')->count(),
            'communications' => $commsQ->count(),
            'collections_paid' => $paidQ->count(),
            'collections_overdue' => CsdCollectionFollowup::whereIn('assigned_to', $memberIds)->where('status', 'overdue')->count(),
            'tickets_resolved' => $ticketsResolvedQ->count(),
            'open_tickets' => $openTicketsQ->count(),
            'sla_breaches' => (clone $openTicketsQ)->whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->count(),
            'opportunities_won' => $wonQ->count(),
            'open_opportunities' => $openOppQ->count(),
            'change_requests_completed' => $crDoneQ->count(),
            'change_requests_pending' => $crPendingQ->count(),
            'renewals_completed' => $renewedQ->count(),
            'pending_renewals' => CsdRenewal::whereIn('status', ['upcoming', 'due'])
                ->whereIn('client', $clientIds)->count(),
            'expiring_amc' => CsdAmcContract::where('status', 'active')
                ->whereIn('client', $clientIds)
                ->get()
                ->filter(fn ($c) => $c->isExpiringSoon())
                ->count(),
            'unassigned_clients' => CsdClientAssignment::whereNull('assigned_to')->where('status', 'active')->count(),
        ];
    }

    /**
     * @param  array<int>  $memberIds
     */
    private function buildTeamMonthlyTrend(array $memberIds, int $year): Collection
    {
        if (empty($memberIds)) {
            return collect(self::MONTH_LABELS)->map(fn ($m) => (object) [
                'month' => $m,
                'communications' => 0,
                'tickets_resolved' => 0,
                'opportunities_won' => 0,
                'renewals_completed' => 0,
                'change_requests_completed' => 0,
            ]);
        }

        $comms = CsdCommunication::whereIn('created_by', $memberIds)->whereYear('created_at', $year)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(created_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $tickets = CsdSupportTicket::whereIn('assigned_to', $memberIds)
            ->whereIn('status', ['resolved', 'closed'])->whereYear('updated_at', $year)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $won = CsdOpportunity::whereIn('assigned_to', $memberIds)->where('status', 'won')->whereYear('updated_at', $year)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $renewals = CsdRenewal::whereIn('created_by', $memberIds)->where('status', 'renewed')->whereYear('updated_at', $year)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        $crs = CsdChangeRequest::whereIn('assigned_to', $memberIds)->where('status', 'completed')->whereYear('updated_at', $year)
            ->select(DB::raw('count(*) as count'), DB::raw("DATE_FORMAT(updated_at, '%b') as month"))
            ->groupBy('month')->get()->keyBy('month');

        return collect(self::MONTH_LABELS)->map(fn ($m) => (object) [
            'month' => $m,
            'communications' => $comms->has($m) ? $comms->get($m)->count : 0,
            'tickets_resolved' => $tickets->has($m) ? $tickets->get($m)->count : 0,
            'opportunities_won' => $won->has($m) ? $won->get($m)->count : 0,
            'renewals_completed' => $renewals->has($m) ? $renewals->get($m)->count : 0,
            'change_requests_completed' => $crs->has($m) ? $crs->get($m)->count : 0,
        ]);
    }

    private function buildUserMonthlyTrend(int $userId, int $year): Collection
    {
        return $this->buildTeamMonthlyTrend([$userId], $year);
    }

    /**
     * @param  array<int>  $memberIds
     */
    private function buildExecutiveRows(array $memberIds, int $year, string $month): Collection
    {
        return User::whereIn('id', $memberIds)
            ->with(['departments', 'roles'])
            ->orderBy('name')
            ->get()
            ->map(function (User $exec) use ($year, $month) {
                $stats = $this->performance->buildMetrics($exec, $year, $month);
                $exec->period_stats = $stats;
                $exec->performance_score = $this->performance->performanceScore($stats, 'csd');
                $exec->active_clients = $stats['active_clients'] ?? 0;
                $exec->at_risk_clients = $stats['at_risk_clients'] ?? 0;
                $exec->communications = $stats['communications'] ?? 0;
                $exec->open_tickets = $stats['open_tickets'] ?? 0;
                $exec->tickets_resolved = $stats['tickets_resolved'] ?? 0;
                $exec->overdue_collections = $stats['collections_overdue'] ?? 0;
                $exec->open_opportunities = $stats['open_opportunities'] ?? 0;
                $exec->opportunities_won = $stats['opportunities_won'] ?? 0;
                $exec->change_requests_pending = $stats['change_requests_pending'] ?? 0;
                $exec->renewals_completed = $stats['renewals_completed'] ?? 0;

                return $exec;
            });
    }

    /**
     * @param  array<int>  $memberIds
     * @return array<string, mixed>
     */
    private function buildSnapshot(array $memberIds): array
    {
        if (empty($memberIds)) {
            return [
                'open_tickets' => 0,
                'sla_breaches' => 0,
                'pending_collections' => 0,
                'expiring_amc' => 0,
            ];
        }

        $clientIds = CsdClientAssignment::whereIn('assigned_to', $memberIds)->pluck('client');
        $openTickets = CsdSupportTicket::whereIn('assigned_to', $memberIds)->whereIn('status', ['open', 'in_progress']);

        return [
            'open_tickets' => (clone $openTickets)->count(),
            'sla_breaches' => (clone $openTickets)->whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->count(),
            'pending_collections' => CsdCollectionFollowup::whereIn('assigned_to', $memberIds)
                ->whereIn('status', ['pending', 'partial'])->count(),
            'expiring_amc' => CsdAmcContract::where('status', 'active')
                ->whereIn('client', $clientIds)
                ->get()
                ->filter(fn ($c) => $c->isExpiringSoon())
                ->count(),
        ];
    }

    private function buildComparisonChartData(Collection $executives): array
    {
        return [
            'labels' => $executives->pluck('name')->values()->all(),
            'communications' => $executives->pluck('communications')->values()->all(),
            'tickets_resolved' => $executives->pluck('tickets_resolved')->values()->all(),
            'opportunities_won' => $executives->pluck('opportunities_won')->values()->all(),
        ];
    }

    private function applyMonthFilter($query, int $year, string $month, string $column = 'created_at'): void
    {
        $query->whereYear($column, $year);
        if ($month !== 'All') {
            $query->whereMonth($column, date('m', strtotime($month)));
        }
    }

    /**
     * @return array<string, int>
     */
    private function emptyMetrics(): array
    {
        return [
            'active_clients' => 0,
            'at_risk_clients' => 0,
            'communications' => 0,
            'collections_paid' => 0,
            'collections_overdue' => 0,
            'tickets_resolved' => 0,
            'open_tickets' => 0,
            'sla_breaches' => 0,
            'opportunities_won' => 0,
            'open_opportunities' => 0,
            'change_requests_completed' => 0,
            'change_requests_pending' => 0,
            'renewals_completed' => 0,
            'pending_renewals' => 0,
            'expiring_amc' => 0,
            'unassigned_clients' => 0,
        ];
    }

    private function resolveMemberIds(User $actor): array
    {
        if ($actor->isBranchManager()) {
            return $this->scope->getBranchCsdUserIds($actor);
        }

        if ($actor->hasRole('Team-Leader')) {
            return $this->scope->getTeamMemberIds($actor, true);
        }

        return [$actor->id];
    }
}
