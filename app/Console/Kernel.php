<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        // Hourly Heartbeat to verify cron daemon execution in storage/logs/laravel.log
        $schedule->call(function () {
            \Illuminate\Support\Facades\Log::info('[CRON HEARTBEAT] Laravel scheduler is actively running. Server time: ' . \Carbon\Carbon::now()->toDateTimeString() . ' (' . config('app.timezone') . ')');
        })->hourly()->name('cron-heartbeat');

        $schedule->command('notification:empty')->dailyAt('14:29');
        $schedule->command('sales:dailysalesreminder')->dailyAt('01:00');
        $schedule->command('csd:reminders')->dailyAt('09:30');
        $schedule->command('attendance:nightly-closing-audit')
            ->dailyAt('23:00')
            ->before(function () {
                \Illuminate\Support\Facades\Log::info('[CRON SCHEDULE] Scheduler triggered attendance:nightly-closing-audit at ' . \Carbon\Carbon::now()->toDateTimeString());
            })
            ->after(function () {
                \Illuminate\Support\Facades\Log::info('[CRON SCHEDULE] Scheduler finished attendance:nightly-closing-audit at ' . \Carbon\Carbon::now()->toDateTimeString());
            });
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return config('app.timezone', 'Asia/Calcutta');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
