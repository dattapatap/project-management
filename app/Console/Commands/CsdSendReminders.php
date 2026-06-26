<?php

namespace App\Console\Commands;

use App\Services\Csd\CsdReminderService;
use Illuminate\Console\Command;

class CsdSendReminders extends Command
{
    protected $signature = 'csd:reminders';

    protected $description = 'Send CSD reminders for follow-ups, collections, SLA, AMC, and renewals';

    public function handle(CsdReminderService $reminders): int
    {
        $results = $reminders->sendAll();

        foreach ($results as $type => $count) {
            $this->line("{$type}: {$count}");
        }

        $this->info('CSD reminders processed.');

        return self::SUCCESS;
    }
}
