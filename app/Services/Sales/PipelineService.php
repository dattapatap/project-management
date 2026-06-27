<?php

namespace App\Services\Sales;

use App\Models\Clients;
use App\Models\ClientHistory;
use App\Repositories\ClientRepository;
use Carbon\Carbon;

class PipelineService
{
    public function __construct(
        private ClientRepository $clientRepo
    ) {}

    /**
     * Get pipeline summary stats (card counts per stage).
     */
    public function getPipelineStats(array $userIds): array
    {
        $stages = ['Fresh', 'Followup', 'Meeting Fixed', 'Hot Perspective', 'Warm Perspective', 'Matured', 'Not Interested'];
        $stats = [];

        foreach ($stages as $stage) {
            $stats[$stage] = Clients::whereIn('ref_user', $userIds)
                ->where('status', $stage)
                ->count();
        }

        return $stats;
    }

    /**
     * Get velocity metrics — average days a client stays in each stage.
     */
    public function getStageVelocity(array $userIds): array
    {
        $stages = ['Fresh', 'Followup', 'Meeting Fixed', 'Hot Perspective', 'Warm Perspective'];
        $velocity = [];

        foreach ($stages as $stage) {
            // Average age (in days) of clients currently in this stage
            $avg = Clients::whereIn('ref_user', $userIds)
                ->where('status', $stage)
                ->selectRaw('AVG(DATEDIFF(NOW(), updated_at)) as avg_days')
                ->value('avg_days');

            $velocity[$stage] = round($avg ?? 0, 1);
        }

        return $velocity;
    }

    /**
     * Get conversion funnel data for charting.
     */
    public function getConversionFunnel(array $userIds, int $year): array
    {
        return $this->clientRepo->statusDistribution($userIds, $year);
    }

    /**
     * Get overdue callbacks summary.
     */
    public function getOverdueCallbacks(array $userIds): int
    {
        return $this->clientRepo->countOverdueTbros($userIds);
    }

    /**
     * Get today's scheduled callbacks.
     */
    public function getTodayCallbacks(array $userIds): \Illuminate\Database\Eloquent\Collection
    {
        return $this->clientRepo->todaysCallbacks($userIds);
    }
}
