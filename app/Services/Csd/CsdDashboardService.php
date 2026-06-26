<?php

namespace App\Services\Csd;

use App\Models\ClientPackages;
use App\Models\CsdAmcContract;
use App\Models\CsdChangeRequest;
use App\Models\CsdClientAssignment;
use App\Models\CsdCollectionFollowup;
use App\Models\CsdCommunication;
use App\Models\CsdRenewal;
use App\Models\CsdSupportTicket;
use App\Models\User;
use App\Services\CsdTeamScopeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CsdDashboardService
{
    public function __construct(
        private CsdTeamScopeService $scope,
        private CsdClientResolverService $clientResolver
    ) {
    }

    public function getBranchManagerData(User $user): array
    {
        $memberIds = $this->scope->getBranchCsdUserIds($user);
        $data = $this->buildMetrics($memberIds, $user, true, (int) date('Y'));
        $data['team_performance_matrix'] = $this->teamPerformanceMatrix($memberIds);
        $data['unassigned_handoffs'] = $this->unassignedHandoffs($memberIds);

        return $data;
    }

    public function getTeamLeaderData(User $user, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $memberIds = $this->scope->getTeamMemberIds($user);
        $data = $this->buildMetrics($memberIds, $user, true, $year);
        $data['team_performance_matrix'] = $this->teamPerformanceMatrix($memberIds);
        $data['unassigned_handoffs'] = $this->unassignedHandoffs($memberIds);
        $data['allocatable_team_members'] = User::whereIn('id', $memberIds)
            ->where('id', '!=', $user->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'CSD-Executive'))
            ->get();
        $data['selected_year'] = $year;
        $data['available_years'] = range((int) date('Y'), (int) date('Y') - 5);

        return $data;
    }

    public function getExecutiveData(User $user, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $data = $this->buildMetrics([$user->id], $user, false, $year);
        $data['selected_year'] = $year;
        $data['available_years'] = range((int) date('Y'), (int) date('Y') - 5);

        return $data;
    }

    private function teamPerformanceMatrix(array $memberIds)
    {
        return User::whereIn('id', $memberIds)
            ->withCount(['csdAssignments as active_clients_count' => fn ($q) => $q->where('status', 'active')])
            ->withCount(['csdAssignments as at_risk_count' => fn ($q) => $q->where('health_status', 'at_risk')])
            ->get();
    }

    private function unassignedHandoffs(array $memberIds)
    {
        return CsdClientAssignment::with(['client', 'project'])
            ->whereNull('assigned_to')
            ->when(!empty($memberIds), fn ($q) => $q->where(function ($inner) use ($memberIds) {
                $inner->whereIn('assigned_to', $memberIds)->orWhereNull('assigned_to');
            }))
            ->latest()
            ->take(10)
            ->get();
    }

    private function buildMetrics(array $memberIds, User $user, bool $includeUnassigned, int $year): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($year, 12, 31)->endOfDay();
        $today = Carbon::today();

        $assignmentScope = function ($q) use ($memberIds, $includeUnassigned) {
            if ($includeUnassigned) {
                $q->where(function ($inner) use ($memberIds) {
                    $inner->whereIn('assigned_to', $memberIds)->orWhereNull('assigned_to');
                });
            } else {
                $q->whereIn('assigned_to', $memberIds);
            }
        };

        $clientIds = CsdClientAssignment::where($assignmentScope)->pluck('client');

        $activeClients = CsdClientAssignment::where('status', 'active')->where($assignmentScope)->count();

        $newClientsThisMonth = CsdClientAssignment::where('status', 'active')
            ->where($assignmentScope)
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->count();

        $paymentsDue = ClientPackages::where('balance', '>', 0)
            ->when($clientIds->isNotEmpty(), fn ($q) => $q->whereIn('client', $clientIds), fn ($q) => $q->whereRaw('1=0'))
            ->count();

        $outstandingAmount = ClientPackages::when($clientIds->isNotEmpty(), fn ($q) => $q->whereIn('client', $clientIds), fn ($q) => $q->whereRaw('1=0'))
            ->sum('balance');

        $collectionsThisMonth = DB::table('client_payments')
            ->when($clientIds->isNotEmpty(), fn ($q) => $q->whereIn('client', $clientIds), fn ($q) => $q->whereRaw('1=0'))
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->sum('amount');

        $scopedChange = CsdChangeRequest::query();
        $this->scope->applyAssigneeScope($scopedChange, $user);

        $scopedRenewals = CsdRenewal::query();
        $this->clientResolver->applyAccessibleClientScope($scopedRenewals, $user);

        $scopedTickets = CsdSupportTicket::query();
        $this->scope->applyAssigneeScope($scopedTickets, $user);

        $scopedCollections = CsdCollectionFollowup::query();
        $this->scope->applyAssigneeScope($scopedCollections, $user);

        $scopedAmc = CsdAmcContract::query();
        $this->scope->applyClientScope($scopedAmc, $user);

        $scopedComms = CsdCommunication::query();
        $this->scope->applyClientScope($scopedComms, $user);

        return [
            'active_clients' => $activeClients,
            'new_clients_this_month' => $newClientsThisMonth,
            'clients_requiring_followup' => (clone $scopedComms)
                ->whereNotNull('next_followup')
                ->where('next_followup', '<=', $today)
                ->distinct('client')
                ->count('client'),
            'payments_due' => $paymentsDue,
            'outstanding_amount' => $outstandingAmount,
            'collections_this_month' => $collectionsThisMonth,
            'pending_change_requests' => (clone $scopedChange)->whereNotIn('status', ['completed', 'rejected'])->count(),
            'approved_change_requests' => (clone $scopedChange)->where('status', 'approved')->count(),
            'in_progress_change_requests' => (clone $scopedChange)->whereIn('status', ['estimating', 'transferred_to_od'])->count(),
            'completed_change_requests' => (clone $scopedChange)->where('status', 'completed')->count(),
            'renewal_due_clients' => (clone $scopedRenewals)->whereIn('status', ['upcoming', 'due'])
                ->where('due_date', '<=', $today->copy()->addDays(30))->count(),
            'renewals_due_this_month' => (clone $scopedRenewals)->whereIn('status', ['upcoming', 'due'])
                ->whereBetween('due_date', [$startOfMonth, $endOfMonth])->count(),
            'satisfaction_score' => ($avg = CsdClientAssignment::whereNotNull('satisfaction_score')->where($assignmentScope)->avg('satisfaction_score'))
                ? round($avg, 1) : null,
            'at_risk_clients' => CsdClientAssignment::where('health_status', 'at_risk')->where($assignmentScope)->count(),
            'high_risk_clients' => CsdClientAssignment::where('health_status', 'churning')->where($assignmentScope)->count(),
            'open_tickets' => (clone $scopedTickets)->whereIn('status', ['open', 'in_progress'])->count(),
            'closed_tickets' => (clone $scopedTickets)->whereIn('status', ['resolved', 'closed'])->count(),
            'escalated_tickets' => (clone $scopedTickets)->where('type', 'escalation')->whereIn('status', ['open', 'in_progress'])->count(),
            'sla_breaches' => (clone $scopedTickets)->whereIn('status', ['open', 'in_progress'])
                ->whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->count(),
            'overdue_collections' => (clone $scopedCollections)->where('status', 'overdue')->count(),
            'pending_collections' => (clone $scopedCollections)->whereIn('status', ['pending', 'partial'])->count(),
            'expiring_amc' => (clone $scopedAmc)->where('status', 'active')
                ->where(function ($q) use ($today) {
                    $q->where(function ($inner) use ($today) {
                        $inner->where('billing_cycle', 'monthly')
                            ->whereBetween('end_date', [$today, $today->copy()->addDays(CsdAmcContract::REMINDER_DAYS['monthly'])]);
                    })->orWhere(function ($inner) use ($today) {
                        $inner->where('billing_cycle', 'yearly')
                            ->whereBetween('end_date', [$today, $today->copy()->addDays(CsdAmcContract::REMINDER_DAYS['yearly'])]);
                    })->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('billing_cycle')
                            ->whereBetween('end_date', [$today, $today->copy()->addDays(30)]);
                    });
                })->count(),
            'expired_services' => (clone $scopedAmc)->where('status', 'expired')->count(),
            'recent_handoffs' => CsdClientAssignment::with(['client', 'project', 'assignee'])
                ->where($assignmentScope)
                ->whereBetween('created_at', [$startOfYear, $endOfYear])
                ->latest()->take(5)->get(),
            'upcoming_followups' => CsdCommunication::with('client')
                ->whereIn('client', $clientIds->isNotEmpty() ? $clientIds : [0])
                ->whereNotNull('next_followup')
                ->whereBetween('next_followup', [$today, $today->copy()->addDays(7)])
                ->orderBy('next_followup')->take(5)->get(),
            'matured_clients' => $clientIds->count(),
            'is_team_view' => $includeUnassigned,
        ];
    }
}
