<?php

namespace App\Services\Sales;

use App\Models\SalesTarget;
use App\Models\Clients;
use App\Models\ClientHistory;
use App\Models\User;
use App\Models\ClientPayments;
use Carbon\Carbon;
use DB;

class TargetService
{
    /**
     * Get targets for a user in a specific year, dynamically computing achieved values.
     */
    public function getTargetsForUser(int $userId, int $year): array
    {
        $targets = SalesTarget::where('user_id', $userId)
            ->where('period_year', $year)
            ->orderBy('period_month', 'asc')
            ->get();

        foreach ($targets as $target) {
            $target->achieved_value = $this->calculateAchievedValue(
                $userId,
                $target->target_type,
                $target->period_month,
                $target->period_year
            );
            $target->save(); // Cache it back
        }

        return $targets->toArray();
    }

    /**
     * Set or update target for a user.
     */
    public function setTarget(array $data, User $creator): SalesTarget
    {
        return SalesTarget::updateOrCreate(
            [
                'user_id'      => $data['user_id'],
                'target_type'  => $data['target_type'],
                'period_month' => $data['period_month'],
                'period_year'  => $data['period_year'],
            ],
            [
                'target_value' => $data['target_value'],
                'created_by'   => $creator->id,
            ]
        );
    }

    /**
     * Get leaderboard ranking for a given month and year.
     */
    public function getLeaderboardData(int $month, int $year): array
    {
        // Get all active sales executives and team leaders in department 1
        $salesReps = User::role(['Sales-Executive', 'Team-Leader'])
            ->whereHas('departments', function ($q) {
                $q->where('department', 1);
            })
            ->where('status', 'Active')
            ->get();

        $leaderboard = [];

        foreach ($salesReps as $rep) {
            // Conversions (Matured leads in this month/year)
            $conversions = Clients::where('ref_user', $rep->id)
                ->where('status', 'Matured')
                ->whereMonth('updated_at', $month)
                ->whereYear('updated_at', $year)
                ->count();

            // Revenue generated (Payments collected in this month/year for this rep's clients)
            $revenue = ClientPayments::whereHas('clients', function ($q) use ($rep) {
                $q->where('ref_user', $rep->id);
            })
            ->whereMonth('paid_date', $month)
            ->whereYear('paid_date', $year)
            ->sum('amount');

            // Meetings fixed in this month
            $meetings = ClientHistory::where('created', $rep->id)
                ->where('status', 'Meeting Fixed')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            // Target Progress percentage if target set
            $target = SalesTarget::where('user_id', $rep->id)
                ->where('target_type', 'revenue')
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->first();

            $targetValue = $target ? $target->target_value : 0;
            $progressPercent = $targetValue > 0 ? min(100, round(($revenue / $targetValue) * 100)) : 0;

            $leaderboard[] = [
                'rep_id'           => $rep->id,
                'name'             => $rep->name,
                'avatar'           => $rep->image ?? null,
                'conversions'      => $conversions,
                'revenue'          => round($revenue, 2),
                'meetings'         => $meetings,
                'target'           => $targetValue,
                'progress_percent' => $progressPercent
            ];
        }

        // Sort by conversions desc, then revenue desc
        usort($leaderboard, function ($a, $b) {
            if ($a['conversions'] === $b['conversions']) {
                return $b['revenue'] <=> $a['revenue'];
            }
            return $b['conversions'] <=> $a['conversions'];
        });

        return $leaderboard;
    }

    /**
     * Compute real-time achieved values based on database records.
     */
    public function calculateAchievedValue(int $userId, string $type, int $month, int $year): float
    {
        if ($type === 'revenue') {
            return (float) ClientPayments::whereHas('clients', function ($q) use ($userId) {
                $q->where('ref_user', $userId);
            })
            ->whereMonth('paid_date', $month)
            ->whereYear('paid_date', $year)
            ->sum('amount');
        }

        if ($type === 'conversions') {
            return (float) Clients::where('ref_user', $userId)
                ->where('status', 'Matured')
                ->whereMonth('updated_at', $month)
                ->whereYear('updated_at', $year)
                ->count();
        }

        if ($type === 'meetings') {
            return (float) ClientHistory::where('created', $userId)
                ->where('status', 'Meeting Fixed')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();
        }

        return 0.0;
    }
}
