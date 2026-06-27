<?php

namespace App\Services\Reports;

use App\Models\CsdClientAssignment;
use App\Models\CsdCommunication;
use App\Models\CsdCollectionFollowup;
use App\Models\CsdSupportTicket;
use App\Models\CsdOpportunity;
use App\Models\CsdChangeRequest;
use App\Models\CsdRenewal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CsdWorkReportService
{
    /**
     * Get summary stats for a CSD employee in a date range
     */
    public function summaryForUser(int $userId, Carbon $from, Carbon $to): array
    {
        // Active Clients (under care)
        $activeClientsCount = CsdClientAssignment::where('assigned_to', $userId)
            ->where('status', 'active')
            ->count();

        // Communications logged in range
        $commsCount = CsdCommunication::where('created_by', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Collections paid in range
        $collectionsPaidCount = CsdCollectionFollowup::where('assigned_to', $userId)
            ->where('status', 'paid')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        // Tickets resolved in range
        $ticketsResolvedCount = CsdSupportTicket::where('assigned_to', $userId)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        // Tickets opened/in progress currently
        $openTicketsCount = CsdSupportTicket::where('assigned_to', $userId)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        // Opportunities won in range
        $oppsWonCount = CsdOpportunity::where('assigned_to', $userId)
            ->where('status', 'won')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        // Change Requests completed in range
        $crCompletedCount = CsdChangeRequest::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        // Renewals completed in range
        $renewalsCompletedCount = CsdRenewal::where('created_by', $userId)
            ->where('status', 'renewed')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        // Days worked (days with at least one communication log, support ticket action, or collection followup update)
        $daysComm = CsdCommunication::where('created_by', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('DATE(created_at) as date'));
            
        $daysTicket = CsdSupportTicket::where('assigned_to', $userId)
            ->whereBetween('updated_at', [$from, $to])
            ->select(DB::raw('DATE(updated_at) as date'));
            
        $daysCollection = CsdCollectionFollowup::where('assigned_to', $userId)
            ->whereBetween('updated_at', [$from, $to])
            ->select(DB::raw('DATE(updated_at) as date'));

        $unionQuery = $daysComm->union($daysTicket)->union($daysCollection);
        $daysWorked = DB::query()->fromSub($unionQuery, 'all_days')->distinct()->count('date');

        return [
            'active_clients' => $activeClientsCount,
            'comms_count' => $commsCount,
            'collections_paid' => $collectionsPaidCount,
            'tickets_resolved' => $ticketsResolvedCount,
            'open_tickets' => $openTicketsCount,
            'opportunities_won' => $oppsWonCount,
            'change_requests_completed' => $crCompletedCount,
            'renewals_completed' => $renewalsCompletedCount,
            'days_worked' => $daysWorked,
            'avg_comms_per_day' => $daysWorked > 0 ? round($commsCount / $daysWorked, 2) : 0,
        ];
    }

    /**
     * Get day-by-day breakdown for a CSD employee in a date range
     */
    public function dailyBreakdown(int $userId, Carbon $from, Carbon $to): Collection
    {
        $comms = CsdCommunication::with('client')
            ->where('created_by', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(fn ($c) => Carbon::parse($c->created_at)->format('Y-m-d'));

        $tickets = CsdSupportTicket::with('client')
            ->where('assigned_to', $userId)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereBetween('updated_at', [$from, $to])
            ->get()
            ->groupBy(fn ($t) => Carbon::parse($t->updated_at)->format('Y-m-d'));

        $days = collect();
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m-d');
            $dayComms = $comms->get($key, collect());
            $dayTickets = $tickets->get($key, collect());

            $days->push((object) [
                'date' => $key,
                'label' => $cursor->format('d M, Y (D)'),
                'completed_tasks' => $dayTickets->count(),
                'log_entries' => $dayComms->count() + $dayTickets->count(),
                'total_hours' => $dayComms->count() * 0.5 + $dayTickets->count() * 1.0,
                'tasks' => $dayComms->map(fn ($c) => (object) [
                    'task_title' => 'Comm: ' . Str::limit($c->subject ?? $c->remarks ?? 'Communication Note', 45),
                    'project_name' => $c->client->name ?? 'Client',
                    'hours' => 0.5,
                    'status' => 'Logged',
                ])->concat($dayTickets->map(fn ($t) => (object) [
                    'task_title' => 'Ticket Resolved: ' . Str::limit($t->subject ?? $t->ticket_no, 45),
                    'project_name' => $t->client->name ?? 'Client',
                    'hours' => 1.0,
                    'status' => 'Resolved',
                ]))->values(),
            ]);

            $cursor->addDay();
        }

        return $days->reverse()->values();
    }

    /**
     * Get timeline history for a CSD employee in a date range
     */
    public function historyTimeline(int $userId, Carbon $from, Carbon $to): Collection
    {
        return CsdCommunication::with('client')
            ->where('created_by', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get current active client assignments, support tickets, and change requests
     */
    public function currentProjects(int $userId): Collection
    {
        $assignments = CsdClientAssignment::with('client')
            ->where('assigned_to', $userId)
            ->where('status', 'active')
            ->get()
            ->map(fn ($a) => (object) [
                'id' => $a->client,
                'name' => $a->client->name ?? 'Client',
                'status' => 'Under Active Care',
                'updated_at' => $a->updated_at,
            ]);

        $tickets = CsdSupportTicket::with('client')
            ->where('assigned_to', $userId)
            ->whereIn('status', ['open', 'in_progress'])
            ->get()
            ->map(fn ($t) => (object) [
                'id' => $t->id,
                'name' => 'Ticket #' . $t->ticket_no . ': ' . $t->subject,
                'status' => 'Open Ticket (' . $t->status . ')',
                'updated_at' => $t->updated_at,
            ]);

        return $assignments->concat($tickets)->sortByDesc('updated_at')->values();
    }

    /**
     * Enrich user model for the reports list Datatable
     */
    public function enrichEmployeeRow(User $employee, Carbon $from, Carbon $to): User
    {
        $summary = $this->summaryForUser($employee->id, $from, $to);

        $employee->active_clients = $summary['active_clients'];
        $employee->comms_count = $summary['comms_count'];
        $employee->collections_paid = $summary['collections_paid'];
        $employee->tickets_resolved = $summary['tickets_resolved'];
        $employee->open_tickets = $summary['open_tickets'];
        $employee->opportunities_won = $summary['opportunities_won'];
        $employee->change_requests_completed = $summary['change_requests_completed'];
        $employee->renewals_completed = $summary['renewals_completed'];
        $employee->days_worked = $summary['days_worked'];
        $employee->avg_comms_per_day = $summary['avg_comms_per_day'];
        $employee->completed_tasks = $summary['tickets_resolved'] + $summary['change_requests_completed'];
        $employee->total_hours = $summary['comms_count'];
        $employee->log_entries = $summary['comms_count'];
        $employee->avg_hours_per_day = $summary['avg_comms_per_day'];

        // Compute CSD specific productivity index
        $score = $summary['active_clients'] * 3
            + $summary['comms_count'] * 2
            + $summary['tickets_resolved'] * 5
            + $summary['collections_paid'] * 4
            + $summary['opportunities_won'] * 10
            + $summary['change_requests_completed'] * 6;

        $employee->productivity = min(100, (int) $score);

        return $employee;
    }
}
