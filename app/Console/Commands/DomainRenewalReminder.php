<?php

namespace App\Console\Commands;

use App\Models\ClientDomains;
use App\Models\User;
use App\Notifications\DomainRenewalNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DomainRenewalReminder extends Command
{
    protected $signature = 'domain:reminders';
    protected $description = 'Send domain renewal reminders for 15, 7, 3, 1, and 0 days before expiration';

    public function handle()
    {
        $checkDays = [15, 7, 3, 1, 0];


        foreach ($checkDays as $days) {
            $targetDate = Carbon::today()->addDays($days)->toDateString();

            $domains = ClientDomains::where('expiry_dt', $targetDate)
                ->get();

            foreach ($domains as $domain) {
                // Notify the creator
                $user = User::find($domain->created_by);
                if ($user) {
                    $user->notify(new DomainRenewalNotification($domain, $days));
                }

                // Also notify Admins
                $admins = User::role('Admin')->get();
                foreach ($admins as $admin) {
                    if ($admin->id !== $domain->created_by) {
                        $admin->notify(new DomainRenewalNotification($domain, $days));
                    }
                }
            }
        }

        $this->info('Domain renewal reminders processed.');
    }
}
