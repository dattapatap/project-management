<?php

namespace App\Services\Reports;

use App\Models\Clients;
use App\Models\ClientHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NsdWorkReportService
{
    /**
     * Get summary stats for a Sales employee in a date range
     */
    public function summaryForUser(int $userId, Carbon $from, Carbon $to): array
    {
        // Leads assigned/created in the range
        $leadsCount = Clients::where(function ($q) use ($userId) {
                $q->where('ref_user', $userId)
                  ->orWhere('tele_ref_user', $userId);
            })
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Matured in the range
        $maturedCount = Clients::where(function ($q) use ($userId) {
                $q->where('ref_user', $userId)
                  ->orWhere('tele_ref_user', $userId);
            })
            ->where('status', 'Matured')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        // Active followups (currently Followup or Meeting Fixed)
        $followupsCount = Clients::where(function ($q) use ($userId) {
                $q->where('ref_user', $userId)
                  ->orWhere('tele_ref_user', $userId);
            })
            ->whereIn('status', ['Followup', 'Meeting Fixed'])
            ->count();

        // Sales amount (from client_payments table created by user)
        $salesAmount = DB::table('client_payments')
            ->where('created_by', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        // Callback logs count (ClientHistory logs created by user in range)
        $callbackLogsCount = ClientHistory::where('created', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Days worked (days with at least one client history log)
        $daysWorked = ClientHistory::where('created', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->distinct()
            ->count(DB::raw('DATE(created_at)'));

        return [
            'leads_count' => $leadsCount,
            'matured_count' => $maturedCount,
            'followups_count' => $followupsCount,
            'sales_amount' => round((float) $salesAmount, 2),
            'callback_logs_count' => $callbackLogsCount,
            'days_worked' => $daysWorked,
            'avg_callbacks_per_day' => $daysWorked > 0 ? round($callbackLogsCount / $daysWorked, 2) : 0,
        ];
    }

    /**
     * Get day-by-day breakdown for a Sales employee in a date range
     */
    public function dailyBreakdown(int $userId, Carbon $from, Carbon $to): Collection
    {
        $logs = ClientHistory::where('created', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($log) => Carbon::parse($log->created_at)->format('Y-m-d'));

        $maturedByDay = Clients::where(function ($q) use ($userId) {
                $q->where('ref_user', $userId)
                  ->orWhere('tele_ref_user', $userId);
            })
            ->where('status', 'Matured')
            ->whereBetween('updated_at', [$from, $to])
            ->get()
            ->groupBy(fn ($c) => Carbon::parse($c->updated_at)->format('Y-m-d'));

        $days = collect();
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m-d');
            $dayLogs = $logs->get($key, collect());

            $days->push((object) [
                'date' => $key,
                'label' => $cursor->format('d M, Y (D)'),
                'completed_tasks' => $maturedByDay->get($key, collect())->count(),
                'log_entries' => $dayLogs->count(),
                'total_hours' => $dayLogs->count() * 0.5,
                'tasks' => $dayLogs->map(fn ($l) => (object) [
                    'task_title' => 'Callback: ' . Str::limit($l->remarks ?? 'No remarks', 45),
                    'project_name' => $l->client->name ?? 'Client',
                    'hours' => 0.5,
                    'status' => $l->status,
                ])->values(),
            ]);

            $cursor->addDay();
        }

        return $days->reverse()->values();
    }

    /**
     * Get timeline history for a Sales employee in a date range
     */
    public function historyTimeline(int $userId, Carbon $from, Carbon $to): Collection
    {
        return ClientHistory::with('client')
            ->where('created', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get current active "projects" (which translates to active leads/followups)
     */
    public function currentProjects(int $userId): Collection
    {
        return Clients::where(function ($q) use ($userId) {
                $q->where('ref_user', $userId)
                  ->orWhere('tele_ref_user', $userId);
            })
            ->whereIn('status', ['Followup', 'Meeting Fixed'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($client) => (object) [
                'id' => $client->id,
                'name' => $client->name,
                'status' => $client->status,
                'updated_at' => $client->updated_at,
            ]);
    }

    /**
     * Enrich user model for the reports list Datatable
     */
    public function enrichEmployeeRow(User $employee, Carbon $from, Carbon $to): User
    {
        $summary = $this->summaryForUser($employee->id, $from, $to);

        $employee->leads_count = $summary['leads_count'];
        $employee->matured_count = $summary['matured_count'];
        $employee->followups_count = $summary['followups_count'];
        $employee->sales_amount = $summary['sales_amount'];
        $employee->callback_logs_count = $summary['callback_logs_count'];
        $employee->days_worked = $summary['days_worked'];
        $employee->avg_callbacks_per_day = $summary['avg_callbacks_per_day'];
        $employee->completed_tasks = $summary['matured_count'];
        $employee->total_hours = $summary['callback_logs_count'];
        $employee->log_entries = $summary['callback_logs_count'];
        $employee->avg_hours_per_day = $summary['avg_callbacks_per_day'];

        // Compute productivity
        $targetMatured = 5;
        $daysDiff = max(1, $from->diffInDays($to) + 1);
        if ($daysDiff < 7) {
            $targetMatured = 1;
        } elseif ($daysDiff <= 31) {
            $targetMatured = 5;
        } else {
            $targetMatured = ceil($daysDiff / 30) * 5;
        }
        $employee->productivity = min(100, (int) round(($summary['matured_count'] / $targetMatured) * 100));

        return $employee;
    }
}
