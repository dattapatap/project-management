<?php

namespace App\Console\Commands;

use App\Models\GlobalAttendanceLog;
use App\Models\TaskLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NightlyAttendanceClosingAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:nightly-closing-audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nightly 11:00 PM Audit: Check unclosed shifts, nullify running task timers, and mark unsubmitted shifts as missed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $todayStr = Carbon::today()->format('Y-m-d');
        $this->info("Running Nightly 11:00 PM Attendance & Task Timer Audit for {$todayStr}...");

        // 1. Find and close any open global shift timers for today
        $openShiftLogs = GlobalAttendanceLog::where('log_date', $todayStr)
            ->whereNull('endtime')
            ->get();

        $shiftsCount = $openShiftLogs->count();
        foreach ($openShiftLogs as $shiftLog) {
            $shiftLog->endtime = $shiftLog->starttime;
            $shiftLog->time_spend = 0.0;
            $shiftLog->status = 'unclosed_missed';
            $shiftLog->save();
        }

        // 2. Find and nullify any open running task timers for today
        $openTaskLogs = TaskLog::where('log_date', $todayStr)
            ->whereNull('endtime')
            ->get();

        $tasksCount = $openTaskLogs->count();
        foreach ($openTaskLogs as $taskLog) {
            $taskLog->endtime = $taskLog->starttime;
            $taskLog->time_spend = 0.0;
            $taskLog->log_description = $taskLog->log_description 
                ? $taskLog->log_description . ' [Nullified at 11:00 PM: Shift was not closed]' 
                : 'Task timer nullified at 11:00 PM: Shift/Day Closing was not submitted.';
            $taskLog->save();
        }

        $this->info("Audit completed successfully. Processed {$shiftsCount} unclosed shifts and nullified {$tasksCount} active task timers.");

        return 0;
    }
}
