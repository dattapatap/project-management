<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClearProjectDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $now = Carbon::now();

            // 1. Update all tasks to 'Completed' and restore if soft-deleted
            DB::table('tasks')
                ->update([
                    'status' => 'Completed',
                    'act_enddate' => $now,
                    'deleted_at' => null,
                ]);

            // 2. Update all projects to 'Completed', assign to user 4, restore if soft-deleted, and set actual end date
            DB::table('department_projects')
                ->update([
                    'status' => 'Completed',
                    'assigned_to' => 4,
                    'act_end_date' => $now,
                    'deleted_at' => null,
                ]);

            $this->command->info("Successfully marked all projects and their tasks as Completed and assigned to user 4.");
        });
    }
}
