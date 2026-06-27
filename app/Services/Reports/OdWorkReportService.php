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
            ->where('status', 'Completed')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $activeTasks = Task::where('assigned_to', $userId)
            ->whereIn('status', ['InProgress', 'ToDo'])
            ->count();

        $totalHours = round((float) (clone $logsQuery)->sum('time_spend'), 2);
        $logEntries = (clone $logsQuery)->count();
        $daysWorked = (clone $logsQuery)->distinct('log_date')->count('log_date');

        return [
            'completed_tasks' => $completedTasks,
            'active_tasks' => $activeTasks,
            'total_hours' => $totalHours,
            'log_entries' => $logEntries,
            'days_worked' => $daysWorked,
            'avg_hours_per_day' => $daysWorked > 0 ? round($totalHours / $daysWorked, 2) : 0,
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

            return (object) [
                'task_id' => $row->taskid,
                'task_title' => $task?->title ?? 'Task #' . $row->taskid,
                'project_name' => $task?->project?->project_name ?? ($task?->project?->clients?->name ?? 'Internal'),
                'status' => $task?->status ?? '—',
                'total_hours' => round((float) $row->total_hours, 2),
                'log_count' => (int) $row->log_count,
                'first_log' => $row->first_log,
                'last_log' => $row->last_log,
            ];
        });
    }

    public function enrichEmployeeRow(User $employee, Carbon $from, Carbon $to): User
    {
        $summary = $this->summaryForUser($employee->id, $from, $to);

        $employee->active_tasks = $summary['active_tasks'];
        $employee->completed_tasks = $summary['completed_tasks'];
        $employee->total_hours = $summary['total_hours'];
        $employee->days_worked = $summary['days_worked'];
        $employee->log_entries = $summary['log_entries'];
        $employee->avg_hours_per_day = $summary['avg_hours_per_day'];

        $targetHours = max(1, $from->diffInDays($to) + 1) * 8;
        $employee->productivity = min(100, (int) round(($summary['total_hours'] / $targetHours) * 100));

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
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()]);
    }
}
