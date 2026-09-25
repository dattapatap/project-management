<?php

namespace App\Console\Commands;

use App\Models\GlobalAttendanceLog;
use App\Models\TaskLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        $nowStr = Carbon::now()->toDateTimeString();
        $tz = config('app.timezone', 'Asia/Calcutta');

        $this->info("Running Nightly 11:00 PM Attendance & Task Timer Audit for date <= {$todayStr} at {$nowStr} ({$tz})...");
        Log::info("[CRON AUDIT START] Nightly Attendance & Task Timer Closing Audit started at {$nowStr} ({$tz}) for target date <= {$todayStr}.");

        try {
            // 1. Find and close any open global shift timers (today or prior days left unclosed)
            $openShiftLogs = GlobalAttendanceLog::where('log_date', '<=', $todayStr)
                ->whereNull('endtime')
                ->get();

            $shiftsCount = $openShiftLogs->count();
            foreach ($openShiftLogs as $shiftLog) {
                $shiftLog->endtime = $shiftLog->starttime;
                $shiftLog->time_spend = 0.0;
                $shiftLog->status = 'unclosed_missed';
                $shiftLog->save();

                Log::warning("[CRON AUDIT SHIFT] Auto-closed unclosed shift #{$shiftLog->id} for User ID #{$shiftLog->userid} (Log Date: {$shiftLog->log_date}, Started: {$shiftLog->starttime}). Set status='unclosed_missed', duration=0.0h.");
            }

            // 2. Find and nullify any open running task timers (today or prior days left running)
            $openTaskLogs = TaskLog::where('log_date', '<=', $todayStr)
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

                Log::warning("[CRON AUDIT TASK] Auto-nullified active task timer #{$taskLog->id} for User ID #{$taskLog->userid}, Task ID #{$taskLog->taskid} (Log Date: {$taskLog->log_date}, Started: {$taskLog->starttime}). Set duration=0.0h.");
            }

            $completeMsg = "Audit completed successfully at " . Carbon::now()->toDateTimeString() . ". Processed {$shiftsCount} unclosed shifts and nullified {$tasksCount} active task timers.";
            $this->info($completeMsg);
            Log::info("[CRON AUDIT COMPLETE] {$completeMsg}");

            return 0;
        } catch (\Throwable $e) {
            $errorMsg = "Nightly Attendance Closing Audit encountered an error: " . $e->getMessage();
            $this->error($errorMsg);
            Log::error("[CRON AUDIT ERROR] {$errorMsg}", [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
