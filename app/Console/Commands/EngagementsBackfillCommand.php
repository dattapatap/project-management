<?php

namespace App\Console\Commands;

use App\Models\ClientEngagement;
use App\Models\ClientPackages;
use App\Models\Clients;
use App\Models\DepartmentProjects;
use App\Services\Commercial\ClientEngagementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EngagementsBackfillCommand extends Command
{
    protected $signature = 'engagements:backfill {--dry-run : Count only}';

    protected $description = 'Create client_engagements records for existing matured clients and projects';

    public function handle(ClientEngagementService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        $clients = Clients::where('status', 'Matured')->get();

        foreach ($clients as $client) {
            $projects = DepartmentProjects::where('client', $client->id)->orderBy('id')->get();

            if ($projects->isEmpty()) {
                $skipped++;
                continue;
            }

            $rootId = null;

            foreach ($projects as $index => $project) {
                if (ClientEngagement::where('project_id', $project->id)->exists()) {
                    $skipped++;
                    $rootId = $rootId ?? ClientEngagement::where('client_id', $client->id)->orderBy('id')->value('id');
                    continue;
                }

                $package = ClientPackages::where('project_id', $project->id)->first();

                if ($dryRun) {
                    $created++;
                    continue;
                }

                DB::transaction(function () use ($client, $project, $package, $index, &$rootId, &$created, $service) {
                    if ($index === 0 || !$rootId) {
                        if ($package) {
                            $eng = $service->recordInitialFromMaturity($client, $project, $package, $project->assigned_by ?? 1);
                            $rootId = $eng->id;
                        } else {
                            $eng = ClientEngagement::create([
                                'engagement_no' => 'ENG-BF-' . $project->id,
                                'client_id' => $client->id,
                                'engagement_type' => ClientEngagement::TYPE_INITIAL,
                                'title' => $project->project_name ?? 'Initial Project',
                                'status' => $project->status === 'Completed'
                                    ? ClientEngagement::STATUS_COMPLETED
                                    : ClientEngagement::STATUS_IN_DELIVERY,
                                'project_id' => $project->id,
                                'sales_owner_id' => $client->ref_user,
                                'created_by' => $project->assigned_by ?? 1,
                                'commercial_closed_at' => $project->created_date,
                                'delivery_started_at' => $project->created_date,
                                'completed_at' => $project->status === 'Completed' ? now() : null,
                            ]);
                            $eng->update(['root_engagement_id' => $eng->id]);
                            $project->update(['engagement_id' => $eng->id]);
                            $rootId = $eng->id;
                        }
                    } else {
                        $eng = ClientEngagement::create([
                            'engagement_no' => 'ENG-BF-' . $project->id,
                            'client_id' => $client->id,
                            'parent_engagement_id' => $rootId,
                            'root_engagement_id' => $rootId,
                            'engagement_type' => ClientEngagement::TYPE_UPSELL,
                            'title' => $project->project_name ?? 'Additional Project',
                            'status' => $project->status === 'Completed'
                                ? ClientEngagement::STATUS_COMPLETED
                                : ClientEngagement::STATUS_IN_DELIVERY,
                            'project_id' => $project->id,
                            'package_id' => $package?->id,
                            'closed_value' => $package?->package,
                            'sales_owner_id' => $client->ref_user,
                            'commercial_closed_at' => $project->created_date,
                            'delivery_started_at' => $project->created_date,
                            'completed_at' => $project->status === 'Completed' ? now() : null,
                            'created_by' => $project->assigned_by ?? 1,
                        ]);
                        $project->update(['engagement_id' => $eng->id]);
                        if ($package) {
                            $package->update(['engagement_id' => $eng->id]);
                        }
                    }

                    $created++;
                });
            }
        }

        $this->info("Backfill complete. Created: {$created}, Skipped: {$skipped}" . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
