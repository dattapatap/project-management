<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EmptyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:empty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Empty NOtification More Then 15 Days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        DB::table('notifications')
            ->where('created_at', '<', $now->subDays(15))
             ->delete();
    }
}
