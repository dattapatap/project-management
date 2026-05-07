<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLiveData extends Command
{
    protected $signature = 'db:sync-live-data';
    protected $description = 'Safely copy records from old-data-erp into erp-digitalnock active schema';

    public function handle()
    {
        $sourceDb = 'old-data-erp';
        $targetDb = env('DB_DATABASE', 'erp-digitalnock');

        $this->info("=== STARTING DATABASE RECORD SYNCHRONIZATION ===");
        $this->info("Source Database (Live Data): {$sourceDb}");
        $this->info("Target Database (Your Code): {$targetDb}");
        $this->line("");

        // 1. Verify source database exists
        $dbCheck = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$sourceDb}'");
        if (empty($dbCheck)) {
            $this->error("Temporary source database '{$sourceDb}' does not exist! Please ensure it is created and loaded with the live dump.");
            return Command::FAILURE;
        }

        // 2. Disable Foreign Key Checks on target DB
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 3. Get list of tables in the live source database
        $tables = DB::select("SHOW TABLES IN `{$sourceDb}`");
        $copiedTablesCount = 0;

        foreach ($tables as $table) {
            $tableName = current((array)$table);

            // Skip Laravel migration records so your local development migration history remains intact
            if ($tableName === 'migrations') {
                continue;
            }

            // 4. Verify table exists in local target database
            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                $this->warn("  [!] Table '{$tableName}' does not exist in your active local database. Skipping.");
                continue;
            }

            $this->comment("Syncing table: {$tableName}");

            // Fetch columns that exist in the local target table
            $targetColumns = array_flip(DB::getSchemaBuilder()->getColumnListing($tableName));

            // Truncate the local active table first to ensure a clean wipe of local data
            DB::statement("TRUNCATE TABLE `{$targetDb}`.`{$tableName}`");

            // Copy rows in high-performance chunks
            $chunkSize = 1000;
            $offset = 0;
            $totalCopied = 0;

            while (true) {
                // Fetch data from source table
                $rows = DB::select("SELECT * FROM `{$sourceDb}`.`{$tableName}` LIMIT {$chunkSize} OFFSET {$offset}");

                if (empty($rows)) {
                    break;
                }

                // Map query objects to associative arrays, keeping ONLY columns that exist locally
                $insertData = array_map(function ($row) use ($targetColumns) {
                    $array = (array)$row;
                    return array_intersect_key($array, $targetColumns);
                }, $rows);

                // Insert directly into active working table
                DB::table($tableName)->insert($insertData);

                $totalCopied += count($rows);
                $offset += $chunkSize;
            }

            $this->info("  -> Successfully copied {$totalCopied} rows into local {$tableName}");
            $copiedTablesCount++;
        }

        // 4. Re-enable Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->line("");
        $this->info("=== SYNCHRONIZATION COMPLETED ===");
        $this->info("Successfully synchronized {$copiedTablesCount} tables from '{$sourceDb}' into your active database '{$targetDb}'!");
        $this->info("Your newly added tables and columns are perfectly safe and preserved!");
        
        return Command::SUCCESS;
    }
}
