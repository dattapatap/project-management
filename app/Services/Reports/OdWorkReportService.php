<?php

namespace App\Services\Reports;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use App\Models\DepartmentProjects;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OdWorkReportService
{
    /**
     * @return array<string, int|float>
     */
    public function summaryForUser(int $userId, Carbon $from, Carbon $to): array
    {
        $logsQuery = $this->logsInRange($userId, $from, $to);

        $completedTasks = Task::where('assigned_to', $userId)
            ->whereRaw("LOWER(status) = 'completed'")
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $activeTasks = Task::where('assigned_to', $userId)
            ->whereRaw("LOWER(status) IN ('inprogress', 'todo')")
            ->count();

        // 1. Total task hours from TaskLog (excluding Sundays)
        $totalHours = round((float) (clone $logsQuery)->sum('time_spend'), 2);
        $logEntries = (clone $logsQuery)->count();

        // 2. Shift Days: Count days where employee had an active shift clocked in (GlobalAttendanceLog)
        $shiftDaysWorked = \App\Models\GlobalAttendanceLog::where('userid', $userId)
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->whereRaw('DAYOFWEEK(log_date) != 1')
            ->distinct('log_date')
            ->count('log_date');

        if ($shiftDaysWorked === 0) {
            $shiftDaysWorked = (clone $logsQuery)->distinct('log_date')->count('log_date');
        }

        // 3. Average daily task hours based on shift days worked
        $avgHoursPerDay = $shiftDaysWorked > 0 ? round($totalHours / $shiftDaysWorked, 2) : 0;

        return [
            'completed_tasks' => $completedTasks,
            'active_tasks' => $activeTasks,
            'total_hours' => $totalHours,
            'total_hours_formatted' => self::formatToTimingHours($totalHours),
            'log_entries' => $logEntries,
            'days_worked' => $shiftDaysWorked,
            'avg_hours_per_day' => $avgHoursPerDay,
            'avg_hours_formatted' => self::formatToTimingHours($avgHoursPerDay),
        ];
    }

    public function dailyBreakdown(int $userId, Carbon $from, Carbon $to): Collection
    {
        $logs = $this->logsInRange($userId, $from, $to)
            ->with(['task.project.clients'])
            ->orderBy('log_date')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($log) => Carbon::parse($log->log_date)->format('Y-m-d'));

        $completedByDay = Task::where('assigned_to', $userId)
            ->where('status', 'Completed')
            ->whereBetween('updated_at', [$from, $to])
            ->get()
            ->groupBy(fn ($task) => Carbon::parse($task->updated_at)->format('Y-m-d'));

        $days = collect();
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m-d');
            $dayLogs = $logs->get($key, collect());
            $taskHours = $dayLogs->groupBy('taskid')->map(function ($entries) {
                $first = $entries->first();
                $task = $first?->task;

                return (object) [
                    'task_id' => $first?->taskid,
                    'task_title' => $task?->title ?? 'Task',
                    'project_name' => $task?->project?->project_name ?? ($task?->project?->clients?->name ?? 'Internal'),
                    'hours' => round($entries->sum('time_spend'), 2),
                    'log_count' => $entries->count(),
                    'status' => $task?->status,
                ];
            })->values();

            $days->push((object) [
                'date' => $key,
                'label' => $cursor->format('d M, Y (D)'),
                'completed_tasks' => $completedByDay->get($key, collect())->count(),
                'total_hours' => round($dayLogs->sum('time_spend'), 2),
                'log_entries' => $dayLogs->count(),
                'tasks' => $taskHours,
            ]);

            $cursor->addDay();
        }

        return $days->reverse()->values();
    }

    public function taskBreakdown(int $userId, Carbon $from, Carbon $to): Collection
    {
        $aggregates = $this->logsInRange($userId, $from, $to)
            ->select(
                'taskid',
                DB::raw('SUM(time_spend) as total_hours'),
                DB::raw('COUNT(*) as log_count'),
                DB::raw('MIN(log_date) as first_log'),
                DB::raw('MAX(log_date) as last_log')
            )
            ->groupBy('taskid')
            ->orderByDesc('total_hours')
            ->get();

        $taskIds = $aggregates->pluck('taskid')->filter()->all();
        $tasks = Task::with('project.clients')->whereIn('id', $taskIds)->get()->keyBy('id');

        return $aggregates->map(function ($row) use ($tasks) {
            $task = $tasks->get($row->taskid);
            $actualHours = round((float) $row->total_hours, 2);

            $startDate = $task?->startdate ? Carbon::parse($task->startdate) : null;
            $endDate = $task?->enddate ? Carbon::parse($task->enddate) : null;

            $allocatedHours = self::calculateTaskAllocatedHours($startDate, $endDate);
            $isOverdue = $actualHours > $allocatedHours;
            $varianceHours = round($actualHours - $allocatedHours, 2);

            $timeFrameLabel = 'Not Set';
            if ($startDate && $endDate) {
                if ($startDate->isSameDay($endDate)) {
                    $timeFrameLabel = $startDate->format('d M') . ' (' . $startDate->format('h:i A') . ' - ' . $endDate->format('h:i A') . ')';
                } else {
                    $timeFrameLabel = $startDate->format('d M, h:i A') . ' – ' . $endDate->format('d M, h:i A');
                }
            } elseif ($startDate) {
                $timeFrameLabel = 'From ' . $startDate->format('d M, h:i A');
            }

            return (object) [
                'task_id' => $row->taskid,
                'task_title' => $task?->title ?? 'Task #' . $row->taskid,
                'project_name' => $task?->project?->project_name ?? ($task?->project?->clients?->name ?? 'Internal'),
                'status' => $task?->status ?? '—',
                'total_hours' => $actualHours,
                'total_hours_formatted' => self::formatToTimingHours($actualHours),
                'allocated_hours' => $allocatedHours,
                'allocated_hours_formatted' => self::formatToTimingHours($allocatedHours),
                'is_overdue' => $isOverdue,
                'variance_hours' => $varianceHours,
                'variance_hours_formatted' => self::formatToTimingHours(abs($varianceHours)),
                'time_frame_label' => $timeFrameLabel,
                'startdate' => $startDate?->format('d M, Y h:i A'),
                'enddate' => $endDate?->format('d M, Y h:i A'),
                'log_count' => (int) $row->log_count,
                'first_log' => $row->first_log,
                'last_log' => $row->last_log,
            ];
        });
    }

    /**
     * Format decimal hours into time-based format (e.g. 15.75 decimal hrs -> "15.45" hrs, representing 15 hours and 45 minutes)
     */
    public static function formatToTimingHours(float|int|null $decimalHours): string
    {
        if (!$decimalHours || $decimalHours <= 0) {
            return '0.00';
        }

        $hours = floor($decimalHours);
        $minutes = (int) round(($decimalHours - $hours) * 60);

        if ($minutes >= 60) {
            $hours += 1;
            $minutes = 0;
        }

        return sprintf('%d.%02d', $hours, $minutes);
    }

    /**
     * Calculate expected working shift hours (10:00 AM to 6:30 PM = 8.5 hrs/day, skipping Sundays)
     */
    public static function calculateTaskAllocatedHours(?Carbon $startDate, ?Carbon $endDate): float
    {
        if (!$startDate || !$endDate) {
            return 8.5; // Default 1 working day allocation
        }

        if ($startDate->gt($endDate)) {
            return 8.5;
        }

        $totalMinutes = 0;
        $cursor = $startDate->copy()->startOfDay();
        $endDay = $endDate->copy()->startOfDay();

        while ($cursor->lte($endDay)) {
            // Skip Sundays
            if ($cursor->isSunday()) {
                $cursor->addDay();
                continue;
            }

            // Standard office shift: 10:00 AM (600 mins) to 6:30 PM (1110 mins) = 510 mins (8.5 hrs)
            $shiftStart = $cursor->copy()->setTime(10, 0, 0);
            $shiftEnd = $cursor->copy()->setTime(18, 30, 0);

            $effectiveStart = $shiftStart->copy();
            $effectiveEnd = $shiftEnd->copy();

            // First day start bounds
            if ($cursor->isSameDay($startDate) && $startDate->gt($shiftStart)) {
                $effectiveStart = $startDate->copy();
            }

            // Last day end bounds
            if ($cursor->isSameDay($endDate) && $endDate->lt($shiftEnd)) {
                $effectiveEnd = $endDate->copy();
            }

            if ($effectiveEnd->gt($effectiveStart)) {
                $mins = $effectiveStart->diffInMinutes($effectiveEnd);
                $mins = min(510, $mins);
                $totalMinutes += $mins;
            }

            $cursor->addDay();
        }

        $hours = round($totalMinutes / 60, 2);
        return $hours > 0 ? $hours : 8.5;
    }

    public function enrichEmployeeRow(User $employee, Carbon $from, Carbon $to): User
    {
        $summary = $this->summaryForUser($employee->id, $from, $to);

        $employee->active_tasks = $summary['active_tasks'];
        $employee->completed_tasks = $summary['completed_tasks'];
        $employee->total_hours = $summary['total_hours'];
        $employee->total_hours_formatted = $summary['total_hours_formatted'];
        $employee->days_worked = $summary['days_worked'];
        $employee->log_entries = $summary['log_entries'];
        $employee->avg_hours_per_day = $summary['avg_hours_per_day'];
        $employee->avg_hours_formatted = $summary['avg_hours_formatted'];

        $expectedHours = max(1, $summary['days_worked'] > 0 ? ($summary['days_worked'] * 6.5) : 8.0);
        $employee->productivity = min(100, (int) round(($summary['total_hours'] / $expectedHours) * 100));

        return $employee;
    }

    public function currentProjects(int $userId): Collection
    {
        return DepartmentProjects::whereHas('tasks', function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->whereIn('status', ['InProgress', 'ToDo']);
            })
            ->with(['clients'])
            ->get()
            ->map(fn ($proj) => (object) [
                'id' => $proj->id,
                'name' => $proj->project_name ?? ($proj->clients->name ?? 'Internal Project'),
                'status' => 'WMS Project (' . $proj->status . ')',
                'updated_at' => $proj->updated_at,
            ]);
    }

    private function logsInRange(int $userId, Carbon $from, Carbon $to)
    {
        return TaskLog::where('userid', $userId)
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->whereRaw('DAYOFWEEK(log_date) != 1'); // Exclude Sundays
    }
}
