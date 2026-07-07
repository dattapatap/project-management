<?php

namespace App\Services\Od;

use App\Models\GlobalAttendanceLog;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GlobalTimerService
{
    /**
     * Start the global attendance timer.
     */
    public function startGlobalTimer(User $user): array
    {
        $activeLog = $user->activeGlobalTimer();
        if ($activeLog) {
            return ['success' => false, 'message' => 'Global timer is already running.'];
        }

        return DB::transaction(function () use ($user) {
            $now = Carbon::now();

            $log = new GlobalAttendanceLog();
            $log->userid = $user->id;
            $log->log_date = $now->format('Y-m-d');
            $log->starttime = $now->format('H:i:s');
            $log->endtime = null;
            $log->time_spend = null;
            $log->status = 'active';
            $log->save();

            // Auto-resume the last worked task if any exists in InProgress status
            $this->autoResumeLastTask($user);

            return ['success' => true, 'message' => 'Global shift timer started.', 'log' => $log];
        });
    }

    /**
     * Pause the global attendance timer.
     */
    public function pauseGlobalTimer(User $user): array
    {
        $activeLog = $user->activeGlobalTimer();
        if (!$activeLog) {
            return ['success' => false, 'message' => 'Global timer is not running.'];
        }

        return DB::transaction(function () use ($user, $activeLog) {
            // Stop any active task first
            $this->pauseActiveTaskTimer($user);

            $this->finalizeGlobalLog($activeLog);

            return ['success' => true, 'message' => 'Global shift timer paused.'];
        });
    }

    /**
     * Resume the global attendance timer.
     */
    public function resumeGlobalTimer(User $user): array
    {
        return $this->startGlobalTimer($user);
    }

    /**
     * Stop the global attendance timer.
     */
    public function stopGlobalTimer(User $user): array
    {
        $todayDate = Carbon::today()->format('Y-m-d');
        
        $hasLogsToday = GlobalAttendanceLog::where('userid', $user->id)
            ->where('log_date', $todayDate)
            ->exists();

        if (!$hasLogsToday) {
            return ['success' => false, 'message' => 'Shift has not been started today.'];
        }

        return DB::transaction(function () use ($user, $todayDate) {
            // Stop any active task first
            $this->pauseActiveTaskTimer($user);

            // Finalize active global timer if running
            $activeLog = $user->activeGlobalTimer();
            if ($activeLog) {
                $this->finalizeGlobalLog($activeLog, 'completed');
            } else {
                // If currently paused, find the last log and mark it completed
                $lastLog = GlobalAttendanceLog::where('userid', $user->id)
                    ->where('log_date', $todayDate)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($lastLog && $lastLog->status !== 'completed') {
                    $lastLog->status = 'completed';
                    $lastLog->save();
                }
            }

            return ['success' => true, 'message' => 'Global shift timer stopped.'];
        });
    }

    /**
     * Ensure the global timer is running for the user.
     * Call this when starting a task timer to ensure shift has started.
     */
    public function ensureGlobalTimerIsRunning(User $user): void
    {
        $activeLog = $user->activeGlobalTimer();
        if (!$activeLog) {
            $this->startGlobalTimer($user);
        }
    }

    /**
     * Helper to pause the currently active task timer.
     */
    public function pauseActiveTaskTimer(User $user): void
    {
        $activeTaskLog = TaskLog::where('userid', $user->id)
            ->whereNull('endtime')
            ->first();

        if ($activeTaskLog) {
            $task = Task::find($activeTaskLog->taskid);
            if ($task) {
                app(TaskService::class)->pauseTimer($task, $user, 'Auto-paused on Global Timer Pause/Stop');
            }
        }
    }

    /**
     * Helper to auto-resume the last worked task for a user.
     */
    private function autoResumeLastTask(User $user): void
    {
        // Find the last paused task log for this user
        $lastLog = TaskLog::where('userid', $user->id)
            ->whereNotNull('endtime')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLog) {
            $task = Task::find($lastLog->taskid);
            if ($task && $task->status === 'InProgress' && $task->assigned_to == $user->id) {
                app(TaskService::class)->startTimer($task, $user);
            }
        }
    }

    /**
     * Finalize the global attendance log (capping at 9:00 PM).
     */
    private function finalizeGlobalLog(GlobalAttendanceLog $log, string $status = 'paused'): void
    {
        $now = Carbon::now();
        $startedAt = Carbon::parse($log->log_date . ' ' . $log->starttime);
        $capTime = Carbon::parse($log->log_date . ' 21:00:00');

        if ($now->gt($capTime)) {
            $endTime = $capTime;
        } else {
            $endTime = $now;
        }

        if ($startedAt->gt($endTime)) {
            $endTime = $startedAt;
        }

        $durationSeconds = $startedAt->diffInSeconds($endTime);
        $durationHours = round($durationSeconds / 3600, 2);
        if ($durationHours < 0.01 && $durationSeconds > 0) {
            $durationHours = 0.01;
        }

        $log->endtime = $endTime->format('H:i:s');
        $log->time_spend = $durationHours;
        $log->status = $status;
        $log->save();
    }

    /**
     * Calculate total break hours for a user on a given date.
     */
    public function getBreakHoursForDate(User $user, string $date): float
    {
        $logs = GlobalAttendanceLog::where('userid', $user->id)
            ->where('log_date', $date)
            ->orderBy('starttime', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return 0.0;
        }

        $breakSeconds = 0;

        // 1. Calculate gaps between completed segments
        for ($i = 0; $i < $logs->count() - 1; $i++) {
            $prevLog = $logs[$i];
            $nextLog = $logs[$i + 1];

            if ($prevLog->endtime) {
                $prevEnd = Carbon::parse($date . ' ' . $prevLog->endtime);
                $nextStart = Carbon::parse($date . ' ' . $nextLog->starttime);

                if ($nextStart->gt($prevEnd)) {
                    $breakSeconds += $prevEnd->diffInSeconds($nextStart);
                }
            }
        }

        // 2. If currently paused, calculate break time elapsed since the last endtime up to now
        $lastLog = $logs->last();
        $isCurrentlyPaused = ($lastLog->status === 'paused' && $lastLog->endtime);
        if ($isCurrentlyPaused && $date === Carbon::today()->format('Y-m-d')) {
            $lastEnd = Carbon::parse($date . ' ' . $lastLog->endtime);
            $now = Carbon::now();
            $capTime = Carbon::parse($date . ' 21:00:00');
            
            if ($now->gt($capTime)) {
                $now = $capTime;
            }

            if ($now->gt($lastEnd)) {
                $breakSeconds += $lastEnd->diffInSeconds($now);
            }
        }

        return round($breakSeconds / 3600, 2);
    }
}
