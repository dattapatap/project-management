<?php

namespace App\Http\Controllers;

use App\Services\Od\GlobalTimerService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalTimerController extends Controller
{
    public function __construct(
        private GlobalTimerService $globalTimerService
    ) {}

    /**
     * Get the current global timer status for the authenticated user.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $activeLog = $user->activeGlobalTimer();
        $activeTaskLog = \App\Models\TaskLog::where('userid', $user->id)
            ->whereNull('endtime')
            ->first();

        $activeTaskTitle = null;
        if ($activeTaskLog) {
            $task = \App\Models\Task::find($activeTaskLog->taskid);
            $activeTaskTitle = $task ? $task->title : null;
        }

        // Calculate today's total accumulated global hours so far
        $todayDate = now()->format('Y-m-d');
        
        $hasTodayEntry = \App\Models\GlobalAttendanceLog::where('userid', $user->id)
            ->where('log_date', $todayDate)
            ->exists();

        $lastLog = \App\Models\GlobalAttendanceLog::where('userid', $user->id)
            ->where('log_date', $todayDate)
            ->orderBy('id', 'desc')
            ->first();
        $isPaused = $lastLog ? ($lastLog->status === 'paused') : false;

        $accumulatedHours = \App\Models\GlobalAttendanceLog::where('userid', $user->id)
            ->where('log_date', $todayDate)
            ->whereNotNull('time_spend')
            ->sum('time_spend');

        $hasSubmittedClosing = \App\Models\DayClosing::where('user_id', $user->id)
            ->where('closing_date', $todayDate)
            ->exists();

        $workLocation = $activeLog?->work_location;
        $workLocationNotes = $activeLog?->work_location_notes;
        if (!$workLocation && $hasTodayEntry) {
            $firstTodayLog = \App\Models\GlobalAttendanceLog::where('userid', $user->id)
                ->where('log_date', $todayDate)
                ->whereNotNull('work_location')
                ->first();
            $workLocation = $firstTodayLog?->work_location;
            $workLocationNotes = $firstTodayLog?->work_location_notes;
        }

        return response()->json([
            'success' => true,
            'is_running' => !is_null($activeLog),
            'log_date' => $activeLog ? $activeLog->log_date : null,
            'starttime' => $activeLog ? $activeLog->starttime : null,
            'accumulated_hours' => round($accumulatedHours, 2),
            'active_task_title' => $activeTaskTitle,
            'has_today_entry' => $hasTodayEntry,
            'is_paused' => $isPaused,
            'has_submitted_closing' => $hasSubmittedClosing,
            'work_location' => $workLocation,
            'work_location_notes' => $workLocationNotes,
        ]);
    }

    /**
     * Start the global shift timer.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'work_location' => 'nullable|string|max:50',
            'work_location_notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $workLocation = $request->input('work_location');
        $workLocationNotes = $request->input('work_location_notes');

        $result = $this->globalTimerService->startGlobalTimer($user, $workLocation, $workLocationNotes);
        return response()->json($result);
    }

    /**
     * Pause the global shift timer.
     */
    public function pause(): JsonResponse
    {
        $user = Auth::user();
        $result = $this->globalTimerService->pauseGlobalTimer($user);
        return response()->json($result);
    }

    /**
     * Stop the global shift timer.
     */
    public function stop(): JsonResponse
    {
        $user = Auth::user();
        $result = $this->globalTimerService->stopGlobalTimer($user);
        return response()->json($result);
    }

    /**
     * Reverse geocode coordinates to a precise locality/suburb/city place name.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        $lat = (float) $request->input('lat');
        $lon = (float) $request->input('lon');

        $result = $this->globalTimerService->reverseGeocode($lat, $lon);
        return response()->json($result);
    }
}
