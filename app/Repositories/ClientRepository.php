<?php

namespace App\Repositories;

use App\Models\Clients;
use App\Models\ClientHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository extends BaseRepository
{
    public function __construct(Clients $model)
    {
        parent::__construct($model);
    }

    /* ------------------------------------------------------------------
     *  QUERY SCOPES
     * ------------------------------------------------------------------ */

    /**
     * Clients belonging to a set of users (by ref_user).
     */
    public function forUsers(array $userIds): Builder
    {
        return $this->query()->whereIn('ref_user', $userIds);
    }

    /**
     * Clients with a specific status.
     */
    public function withStatus(string $status): Builder
    {
        return $this->query()->where('status', $status);
    }

    /**
     * Clients NOT in given statuses.
     */
    public function excludeStatuses(array $statuses): Builder
    {
        return $this->query()->whereNotIn('status', $statuses);
    }

    /* ------------------------------------------------------------------
     *  PIPELINE / KANBAN HELPERS
     * ------------------------------------------------------------------ */

    /**
     * Get clients grouped by status for a pipeline view.
     */
    public function getPipelineCards(array $userIds, array $stages): array
    {
        $pipeline = [];
        foreach ($stages as $stage) {
            $query = $this->forUsers($userIds)
                ->where('status', $stage)
                ->with(['referral', 'telereferral', 'history' => function ($q) {
                    $q->latest()->limit(1);
                }])
                ->latest();

            // Limit card count to prevent PHP memory limit exhaustion
            if (in_array($stage, ['Matured', 'Not Interested'])) {
                $query->limit(50);
            } else {
                $query->limit(150);
            }

            $pipeline[$stage] = $query->get();
        }
        return $pipeline;
    }

    /**
     * Move a client to a new pipeline stage and log history.
     */
    public function moveToStage(Clients $client, string $newStatus, int $userId, ?string $tbro = null, ?string $time = null, ?string $remarks = null): ClientHistory
    {
        $oldStatus = $client->status;
        $client->status = $newStatus;
        $client->updated_by = $userId;
        $client->save();

        return $client->histories()->create([
            'category'  => 'STS',
            'status'    => $newStatus,
            'remarks'   => $remarks ?? "Pipeline stage moved from {$oldStatus} to {$newStatus}",
            'tbro'      => $tbro,
            'time'      => $time ?? Carbon::now()->format('H:i:s'),
            'created'   => $userId,
        ]);
    }

    /* ------------------------------------------------------------------
     *  METRICS
     * ------------------------------------------------------------------ */

    /**
     * Count clients by status for a set of users, optionally filtered by year.
     */
    public function countByStatus(array $userIds, string $status, ?int $year = null, string $dateField = 'created_at'): int
    {
        $query = $this->forUsers($userIds)->where('status', $status);
        if ($year) {
            $query->whereYear($dateField, $year);
        }
        return $query->count();
    }

    /**
     * Status distribution map for charts.
     */
    public function statusDistribution(array $userIds, int $year): array
    {
        return [
            'Fresh'          => $this->countByStatus($userIds, 'Fresh', $year),
            'Followup'       => $this->countByStatus($userIds, 'Followup', $year),
            'Meeting Fixed'  => $this->countByStatus($userIds, 'Meeting Fixed', $year),
            'Matured'        => $this->countByStatus($userIds, 'Matured', $year, 'updated_at'),
            'Not Interested' => $this->countByStatus($userIds, 'Not Interested', $year, 'updated_at'),
        ];
    }

    /**
     * Count clients with TBRO scheduled for a specific date.
     */
    public function countTbrosForDate(array $userIds, string $date): int
    {
        return $this->excludeStatuses(['Fresh', 'Matured', 'Not Interested'])
            ->whereIn('ref_user', $userIds)
            ->whereHas('histories', function ($q) use ($userIds, $date) {
                $q->where('tbro', $date)->whereIn('created', $userIds);
            })
            ->count();
    }

    /**
     * Count overdue TBROs for a set of users.
     */
    public function countOverdueTbros(array $userIds): int
    {
        return $this->forUsers($userIds)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->whereHas('histories', function ($q) use ($userIds) {
                $q->where('tbro', '<', Carbon::today()->toDateString())
                  ->whereIn('created', $userIds);
            })
            ->count();
    }

    /**
     * Count active leads (excluding Fresh, Matured, Not Interested).
     */
    public function countActiveLeads(array $userIds): int
    {
        return $this->forUsers($userIds)
            ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
            ->count();
    }

    /* ------------------------------------------------------------------
     *  DATA LOADERS
     * ------------------------------------------------------------------ */

    /**
     * Get today's scheduled callbacks for a set of users.
     */
    public function todaysCallbacks(array $userIds): Collection
    {
        return $this->forUsers($userIds)
            ->whereHas('histories', function ($q) use ($userIds) {
                $q->where('tbro', Carbon::today()->toDateString())
                  ->whereIn('created', $userIds);
            })
            ->with(['telereferral', 'referral', 'histories' => fn($q) => $q->orderBy('id', 'desc')])
            ->get();
    }

    /**
     * Get calendar events from ClientHistory TBRO data.
     */
    public function getCalendarEvents(array $userIds, string $startDate, string $endDate): Collection
    {
        return ClientHistory::whereIn('created', $userIds)
            ->whereNotNull('tbro')
            ->whereBetween('tbro', [$startDate, $endDate])
            ->with([
                'clientNotif' => fn($q) => $q->select('id', 'name', 'status', 'cont_person'),
                'referel:id,name'
            ])
            ->orderBy('tbro')
            ->get();
    }
}
