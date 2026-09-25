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
    public function startGlobalTimer(User $user, ?string $workLocation = null, ?string $workLocationNotes = null): array
    {
        if (!$user->isWorking()) {
            return ['success' => false, 'message' => "Cannot start shift timer for {$user->status} account."];
        }

        // First clean up any unclosed orphan logs from previous days
        $this->cleanupPriorDaysOrphanLogs($user);

        $activeLog = $user->activeGlobalTimer();
        if ($activeLog) {
            return ['success' => false, 'message' => 'Global timer is already running.'];
        }

        return DB::transaction(function () use ($user, $workLocation, $workLocationNotes) {
            $now = Carbon::now();
            $todayDate = $now->format('Y-m-d');

            // If work location is not provided (e.g. resuming from break), inherit from today's initial log or default to Office
            $resolvedLocation = $workLocation;
            $resolvedNotes = $workLocationNotes;
            if (empty($resolvedLocation)) {
                $todayFirstLog = GlobalAttendanceLog::where('userid', $user->id)
                    ->where('log_date', $todayDate)
                    ->whereNotNull('work_location')
                    ->orderBy('id', 'asc')
                    ->first();
                $resolvedLocation = $todayFirstLog?->work_location ?? GlobalAttendanceLog::LOCATION_OFFICE;
                if (empty($resolvedNotes)) {
                    $resolvedNotes = $todayFirstLog?->work_location_notes;
                }
            }

            $log = new GlobalAttendanceLog();
            $log->userid = $user->id;
            $log->log_date = $todayDate;
            $log->starttime = $now->format('H:i:s');
            $log->endtime = null;
            $log->time_spend = null;
            $log->status = 'active';
            $log->work_location = $resolvedLocation;
            $log->work_location_notes = $resolvedNotes;
            $log->save();

            // Auto-resume the last worked task if any exists in InProgress status
            $this->autoResumeLastTask($user);

            return ['success' => true, 'message' => "Global shift timer started ({$resolvedLocation}).", 'log' => $log];
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
    public function resumeGlobalTimer(User $user, ?string $workLocation = null, ?string $workLocationNotes = null): array
    {
        return $this->startGlobalTimer($user, $workLocation, $workLocationNotes);
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
     * Helper to auto-resume any InProgress task for a user when shift starts.
     */
    private function autoResumeLastTask(User $user): void
    {
        // 1. Check if user has an InProgress task assigned to them
        $inProgressTask = Task::where('assigned_to', $user->id)
            ->where('status', 'InProgress')
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($inProgressTask) {
            $activeTimer = $inProgressTask->activeTimerForUser($user->id);
            if (!$activeTimer) {
                app(TaskService::class)->startTimer($inProgressTask, $user);
            }
            return;
        }

        // 2. Otherwise fallback to the last worked task if InProgress
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
     * Finalize the global attendance log (capping at 11:00 PM).
     */
    private function finalizeGlobalLog(GlobalAttendanceLog $log, string $status = 'paused'): void
    {
        $now = Carbon::now();
        $startedAt = Carbon::parse($log->log_date . ' ' . $log->starttime);
        $capTime = Carbon::parse($log->log_date . ' 23:00:00');

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
     * Get total break hours for a user on a given date.
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
            $capTime = Carbon::parse($date . ' 23:00:00');
            
            if ($now->gt($capTime)) {
                $now = $capTime;
            }

            if ($now->gt($lastEnd)) {
                $breakSeconds += $lastEnd->diffInSeconds($now);
            }
        }

        return round($breakSeconds / 3600, 2);
    }

    /**
     * Cleanly close any orphan unclosed logs from prior days without logging artificial hours.
     */
    public function cleanupPriorDaysOrphanLogs(User $user): void
    {
        $todayStr = Carbon::today()->format('Y-m-d');

        // 1. Close unfinalized GlobalAttendanceLogs from prior days (0 hours on unclosed segment)
        $orphanGlobalLogs = GlobalAttendanceLog::where('userid', $user->id)
            ->where('log_date', '<', $todayStr)
            ->whereNull('endtime')
            ->get();

        foreach ($orphanGlobalLogs as $gLog) {
            $gLog->endtime = $gLog->starttime;
            $gLog->time_spend = 0.0;
            $gLog->status = 'unclosed_missed';
            $gLog->save();
        }

        // 2. Close unfinalized TaskLogs from prior days (0 hours on unclosed timer)
        $orphanTaskLogs = TaskLog::where('userid', $user->id)
            ->where('log_date', '<', $todayStr)
            ->whereNull('endtime')
            ->get();

        foreach ($orphanTaskLogs as $tLog) {
            $tLog->endtime = $tLog->starttime;
            $tLog->time_spend = 0.0;
            $tLog->log_description = $tLog->log_description ?: 'Unclosed task timer - zeroed because shift was not closed';
            $tLog->save();
        }
    }

    /**
     * Reverse geocode coordinates to a precise full address (house/building, road, landmark, area, city, pincode, state).
     */
    public function reverseGeocode(float $lat, float $lon): array
    {
        $cacheKey = 'rev_geo_full_' . round($lat, 4) . '_' . round($lon, 4);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($lat, $lon) {
            // 1. Primary: OpenStreetMap Nominatim with zoom=18 for exact building, street, and address details
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'WMS-ERP-Digitalnock/1.0 (contact@digitalnock.net)',
                    'Accept' => 'application/json',
                ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lon}&zoom=18&addressdetails=1");

                if ($response->successful()) {
                    $data = $response->json();
                    $address = $data['address'] ?? [];
                    $displayName = $data['display_name'] ?? null;

                    $parts = [];

                    // 1. House / Building / Flat / Shop / Amenity (e.g. Near Bhanu Nursing Home)
                    $premise = [];
                    if (!empty($address['house_number'])) {
                        $premise[] = '#' . ltrim($address['house_number'], '#');
                    }
                    if (!empty($address['building'])) {
                        $premise[] = $address['building'];
                    }
                    if (!empty($address['amenity'])) {
                        $premise[] = 'Near ' . $address['amenity'];
                    }
                    if (!empty($address['office'])) {
                        $premise[] = $address['office'];
                    }
                    if (!empty($address['shop'])) {
                        $premise[] = $address['shop'];
                    }
                    if (!empty($premise)) {
                        $parts[] = implode(', ', array_unique($premise));
                    }

                    // 2. Road / Street / Lane
                    if (!empty($address['road'])) {
                        $parts[] = $address['road'];
                    }

                    // 3. Neighbourhood / Block / Sector / Cross
                    if (!empty($address['neighbourhood'])) {
                        $parts[] = $address['neighbourhood'];
                    }

                    // 4. Suburb / Area (e.g. Bommanahalli, Koramangala)
                    if (!empty($address['suburb']) && !in_array($address['suburb'], $parts)) {
                        $parts[] = $address['suburb'];
                    }

                    // 5. Residential / Layout / Colony
                    if (!empty($address['residential']) && !in_array($address['residential'], $parts)) {
                        $parts[] = $address['residential'];
                    }

                    // 6. City / Town + Pincode
                    $city = $address['city'] ?? $address['town'] ?? $address['municipality'] ?? $address['village'] ?? null;
                    $postcode = $address['postcode'] ?? null;
                    $state = $address['state'] ?? null;

                    if (!empty($city)) {
                        if (!empty($postcode)) {
                            $parts[] = $city . ' - ' . $postcode;
                        } else {
                            $parts[] = $city;
                        }
                    } elseif (!empty($postcode)) {
                        $parts[] = $postcode;
                    }

                    // 7. State
                    if (!empty($state)) {
                        $parts[] = $state;
                    }

                    $fullAddress = count($parts) >= 2 ? implode(', ', $parts) : ($displayName ?? '');

                    if (!empty($fullAddress)) {
                        return [
                            'success' => true,
                            'location' => $fullAddress,
                            'display_name' => $displayName,
                            'source' => 'nominatim',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Nominatim reverse geocode attempt failed: " . $e->getMessage());
            }

            // 2. Secondary Fallback: BigDataCloud
            try {
                $bdcResponse = \Illuminate\Support\Facades\Http::timeout(4)
                    ->get("https://api.bigdatacloud.net/data/reverse-geocode-client?latitude={$lat}&longitude={$lon}&localityLanguage=en");

                if ($bdcResponse->successful()) {
                    $bdcData = $bdcResponse->json();
                    $parts = [];
                    if (!empty($bdcData['locality']) && $bdcData['locality'] !== ($bdcData['city'] ?? '')) {
                        $parts[] = $bdcData['locality'];
                    }
                    if (!empty($bdcData['city'])) {
                        $parts[] = $bdcData['city'];
                    }
                    if (!empty($bdcData['postcode'])) {
                        $parts[] = $bdcData['postcode'];
                    }
                    if (!empty($bdcData['principalSubdivision'])) {
                        $parts[] = $bdcData['principalSubdivision'];
                    }

                    if (!empty($parts)) {
                        return [
                            'success' => true,
                            'location' => implode(', ', $parts),
                            'source' => 'bigdatacloud',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("BigDataCloud fallback reverse geocode failed: " . $e->getMessage());
            }

            // 3. Fallback coordinates
            return [
                'success' => true,
                'location' => 'Lat: ' . round($lat, 4) . ', Lon: ' . round($lon, 4),
                'source' => 'coordinates',
            ];
        });
    }
}
