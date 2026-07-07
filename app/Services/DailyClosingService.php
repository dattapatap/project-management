<?php

namespace App\Services;

use App\Models\ClientHistory;
use App\Models\CsdCommunication;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\DailyTarget;
use App\Models\DayClosing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyClosingService
{
    /**
     * Compute the prefilled metrics for a user on a given date.
     */
    public function getTodayMetrics(User $user, string $date): array
    {
        $userId = $user->id;
        $userPerformance = new UserPerformanceService();
        $deptType = $userPerformance->departmentType($user);

        // Fetch global shift & break hours for everyone
        $globalHours = (float) \App\Models\GlobalAttendanceLog::where('userid', $userId)
            ->whereDate('log_date', $date)
            ->sum('time_spend');

        $activeLog = \App\Models\GlobalAttendanceLog::where('userid', $userId)
            ->whereDate('log_date', $date)
            ->whereNull('endtime')
            ->first();

        if ($activeLog) {
            $now = Carbon::now();
            $startedAt = Carbon::parse($activeLog->log_date . ' ' . $activeLog->starttime);
            $capTime = Carbon::parse($activeLog->log_date . ' 21:00:00');

            if ($now->gt($capTime)) {
                $endTime = $capTime;
            } else {
                $endTime = $now;
            }

            if ($startedAt->gt($endTime)) {
                $endTime = $startedAt;
            }

            $durationSeconds = $startedAt->diffInSeconds($endTime);
            $durationHours = $durationSeconds / 3600;
            $globalHours += $durationHours;
        }

        $breakHours = app(\App\Services\Od\GlobalTimerService::class)->getBreakHoursForDate($user, $date);

        if ($deptType === 'nsd') {
            $stsCount = ClientHistory::where('created', $userId)
                ->where('category', 'STS')
                ->whereDate('created_at', $date)
                ->count();

            $dsrCount = ClientHistory::where('created', $userId)
                ->where('category', 'DSR')
                ->whereDate('created_at', $date)
                ->count();

            return [
                'sts' => $stsCount,
                'dsr' => $dsrCount,
                'global_hours' => round((float) $globalHours, 2),
                'break_hours' => round((float) $breakHours, 2),
            ];
        }

        if ($deptType === 'csd') {
            $commsCount = CsdCommunication::where('created_by', $userId)
                ->whereDate('created_at', $date)
                ->count();

            return [
                'communications' => $commsCount,
                'global_hours' => round((float) $globalHours, 2),
                'break_hours' => round((float) $breakHours, 2),
            ];
        }

        // Default: OD
        $hoursLogged = TaskLog::where('userid', $userId)
            ->whereDate('created_at', $date)
            ->sum('time_spend');

        $tasksCompleted = Task::where('assigned_to', $userId)
            ->where('status', 'Completed')
            ->whereDate('updated_at', $date)
            ->count();

        return [
            'hours' => round((float) $hoursLogged, 2),
            'global_hours' => round((float) $globalHours, 2),
            'break_hours' => round((float) $breakHours, 2),
            'tasks' => $tasksCompleted,
        ];
    }

    /**
     * Get target values for a user.
     */
    public function getDailyTargets(User $user): array
    {
        $userId = $user->id;
        $userPerformance = new UserPerformanceService();
        $deptType = $userPerformance->departmentType($user);

        // Pre-configure targets in the database if missing
        $defaultTargets = [];
        if ($deptType === 'nsd') {
            $defaultTargets = [
                'global_hours' => 7,
                'sts_updates' => 45,
                'dsr_updates' => 2,
            ];
        } elseif ($deptType === 'csd') {
            $defaultTargets = [
                'global_hours' => 7,
                'communications' => 15,
            ];
        } elseif ($deptType === 'od') {
            $defaultTargets = [
                'global_hours' => 7,
                'hours_logged' => 6,
                'tasks_completed' => 1,
            ];
        }

        foreach ($defaultTargets as $type => $val) {
            $exists = \App\Models\DailyTarget::where('user_id', $userId)
                ->where('target_type', $type)
                ->exists();
            if (!$exists) {
                \App\Models\DailyTarget::create([
                    'user_id' => $userId,
                    'target_type' => $type,
                    'target_value' => $val,
                    'created_by' => auth()->id() ?: 1,
                ]);
            }
        }

        return \App\Models\DailyTarget::where('user_id', $userId)->pluck('target_value', 'target_type')->toArray();
    }

    /**
     * Determine if today's achieved metrics meet the target.
     */
    public function resolveTargetStatus(User $user, array $achieved): string
    {
        if ($user->hasRole('Branch-Manager')) {
            return 'Met';
        }

        $targets = $this->getDailyTargets($user);
        $userPerformance = new UserPerformanceService();
        $deptType = $userPerformance->departmentType($user);

        if ($deptType === 'nsd') {
            $actualSts = $achieved['sts'] ?? 0;
            $actualDsr = $achieved['dsr'] ?? 0;
            $targetSts = $targets['sts_updates'];
            $targetDsr = $targets['dsr_updates'];

            return ($actualSts >= $targetSts || $actualDsr >= $targetDsr) ? 'Met' : 'Not Met';
        }

        $actualGlobal = $achieved['global_hours'] ?? 0;
        $targetGlobal = $targets['global_hours'];

        if ($deptType === 'csd') {
            $actualComms = $achieved['communications'] ?? 0;
            $targetComms = $targets['communications'];

            return ($actualGlobal >= $targetGlobal && $actualComms >= $targetComms) ? 'Met' : 'Not Met';
        }

        // OD
        $actualHours = $achieved['hours'] ?? 0; // Task hours
        $actualTasks = $achieved['tasks'] ?? 0;
        $targetHours = $targets['hours_logged'];
        $targetTasks = $targets['tasks_completed'];

        return ($actualGlobal >= $targetGlobal && ($actualHours >= $targetHours || $actualTasks >= $targetTasks)) ? 'Met' : 'Not Met';
    }

    /**
     * Submit day closing for a user.
     */
    public function submitClosing(User $user, ?string $remarks, bool $onLeave = false): DayClosing
    {
        $todayDate = Carbon::today()->format('Y-m-d');

        // Check if already submitted
        $existing = DayClosing::where('user_id', $user->id)
            ->where('closing_date', $todayDate)
            ->first();

        if ($existing) {
            throw new \Exception("You have already submitted your day-closing report for today.");
        }

        $userPerformance = new UserPerformanceService();
        $deptType = strtoupper($userPerformance->departmentType($user)); // NSD, CSD, OD

        // Auto-stop global timer before retrieving metrics
        if (in_array($deptType, ['OD', 'CSD'])) {
            app(\App\Services\Od\GlobalTimerService::class)->stopGlobalTimer($user);
        }

        $metrics = $this->getTodayMetrics($user, $todayDate);
        $targetStatus = $onLeave ? 'On Leave' : $this->resolveTargetStatus($user, $metrics);

        return DayClosing::create([
            'user_id' => $user->id,
            'closing_date' => $todayDate,
            'department' => $deptType,
            'achieved_metrics' => $metrics,
            'target_status' => $targetStatus,
            'executive_remarks' => $remarks,
            'status' => 'Pending',
        ]);
    }
}
