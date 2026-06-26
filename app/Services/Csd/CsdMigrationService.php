<?php

namespace App\Services\Csd;

use App\Models\Clients;
use App\Models\CsdClientAssignment;
use App\Models\DepartmentProjects;
use Illuminate\Support\Facades\DB;

class CsdMigrationService
{
    public function __construct(private CsdHandoffService $handoff)
    {
    }

    /**
     * Create CSD assignments for matured clients that have a completed project but no assignment.
     */
    public function migrateExistingClients(bool $dryRun = false): array
    {
        $stats = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        $clients = Clients::where('status', 'Matured')
            ->whereNotIn('id', CsdClientAssignment::where('status', 'active')->pluck('client'))
            ->get();

        foreach ($clients as $client) {
            $project = DepartmentProjects::where('client', $client->id)
                ->where('status', 'Completed')
                ->latest('id')
                ->first();

            if (!$project) {
                $stats['skipped']++;
                continue;
            }

            try {
                if ($dryRun) {
                    $stats['created']++;
                    continue;
                }

                DB::transaction(function () use ($project, &$stats) {
                    $assignment = $this->handoff->migrateClientFromMaturedProject($project);
                    if ($assignment) {
                        $stats['created']++;
                    } else {
                        $stats['skipped']++;
                    }
                });
            } catch (\Throwable) {
                $stats['errors']++;
            }
        }

        return $stats;
    }
}
