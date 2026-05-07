<?php

namespace App\Console\Commands;

use App\Models\ClientHistory;
use App\Notifications\DailySalesReminderNot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailySalesReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:dailysalesreminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminders of sales TBRO';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->hasRole('Sales-Executive') || $user->hasRole('Team-Leader')) {
                // Count callbacks scheduled for today for this user
                $count = \App\Models\Clients::where('ref_user', $user->id)
                    ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                    ->whereHas('histories', function ($q) {
                        $q->where('tbro', Carbon::today()->toDateString());
                    })
                    ->count();

                if ($count > 0) {
                    $user->notify(new \App\Notifications\DailySummaryFollowupNotification($count));
                }
            }
        }
    }
}


