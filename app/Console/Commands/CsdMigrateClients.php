<?php

namespace App\Console\Commands;

use App\Services\Csd\CsdMigrationService;
use Illuminate\Console\Command;

class CsdMigrateClients extends Command
{
    protected $signature = 'csd:migrate-clients {--dry-run : Preview without creating assignments}';

    protected $description = 'Create CSD assignments for matured clients with completed projects';

    public function handle(CsdMigrationService $migration): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $stats = $migration->migrateExistingClients($dryRun);

        $this->table(['Metric', 'Count'], collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->all());

        $this->info($dryRun ? 'Dry run complete.' : 'CSD client migration complete.');

        return self::SUCCESS;
    }
}
