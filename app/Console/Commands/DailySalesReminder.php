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
        $clientHistories = ClientHistory::with('clientNotif')

                                    ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                                    ->where('tbro', Carbon::today()->toDateString())
                                    ->get();

        foreach($clientHistories as $items){
            $user = User::where('id', $items->created)->first();
            $user->notify(new DailySalesReminderNot($items, $items->clientNotif, $cat="TBRO Reminder"));
        }

    }
}


