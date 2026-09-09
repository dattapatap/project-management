<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Services\BranchScopeService;
use App\Services\UserPerformanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeStatusDashboardService
{
    public function __construct(
        private BranchScopeService $branchScope,
        private UserPerformanceService $performanceService
    ) {}

    public function getEmployeeTodayStatus(User $currentUser): array
    {
        // 1. Resolve which employees are in scope of the current user
        $employeesQuery = User::where('status', 'Active')
            ->where('id', '!=', $currentUser->id);

        if ($currentUser->isGlobalAdmin()) {
            // Admin sees all employees in departments NSD (1), OD (2), CSD (3)
            $employeesQuery->whereHas('departments', function ($q) {
                $q->whereIn('department', [1, 2, 3]);
            });
        } elseif ($currentUser->isBranchManager()) {
            // Branch manager sees all employees in their branch and departments NSD, OD, CSD
            $branchUserIds = $this->branchScope->getBranchUserIds($currentUser);
            $employeesQuery->whereIn('id', $branchUserIds)
                ->whereHas('departments', function ($q) {
                    $q->whereIn('department', [1, 2, 3]);
                });
        } elseif ($currentUser->hasRole('Team-Leader')) {
            // Team leader sees members of their team(s)
            $teams = DB::table('team_members')
                ->where('user', $currentUser->id)
                ->where('status', true)
                ->pluck('team')
                ->toArray();

            $memberIds = DB::table('team_members')
                ->whereIn('team', $teams)
                ->where('status', true)
                ->where('user', '!=', $currentUser->id)
                ->pluck('user')
                ->toArray();

            $employeesQuery->whereIn('id', $memberIds);
        } else {
            // Standard executive roles don't see this widget
            return [];
        }

        $employees = $employeesQuery->with(['roles', 'departments'])->get();
        if ($employees->isEmpty()) {
            return [];
        }

        $employeeIds = $employees->pluck('id')->toArray();

        // 2. Fetch all InProgress tasks for OD employees (Department = 2) with active timers
        $tasks = Task::whereIn('assigned_to', $employeeIds)
            ->where('status', 'InProgress')
            ->whereHas('logs', function ($q) {
                $q->whereColumn('userid', 'tasks.assigned_to')
                  ->whereNull('endtime');
            })
            ->with(['project', 'logs'])
            ->get()
            ->groupBy('assigned_to');

        // 3. Fetch today's STS & DSR counts for NSD employees (Department = 1)
        $salesUpdates = DB::table('client_histories')
            ->whereIn('created', $employeeIds)
            ->whereDate('created_at', Carbon::today())
            ->select('created', 'category', DB::raw('count(*) as total'))
            ->groupBy('created', 'category')
            ->get()
            ->groupBy('created');

        // 4. Fetch today's CSD communications counts for CSD employees (Department = 3)
        $csdUpdates = DB::table('csd_communications')
            ->whereIn('created_by', $employeeIds)
            ->whereDate('created_at', Carbon::today())
            ->select('created_by', DB::raw('count(*) as total'))
            ->groupBy('created_by')
            ->get()
            ->keyBy('created_by');

        $statusData = [];
        foreach ($employees as $emp) {
            $deptType = $this->performanceService->departmentType($emp); // 'nsd', 'csd', 'od'
            $roleName = $emp->getRoleNames()->first() ?? '-';
            $details = [];

            if ($deptType === 'od') {
                $empTasks = $tasks->get($emp->id) ?? collect();
                $tasksList = [];
                foreach ($empTasks as $task) {
                    $totalTime = $task->total_time; // float hours
                    $formattedTime = $this->formatHours($totalTime);
                    $projName = $task->project->project_name ?? 'No Project';
                    // output format: "Task Title (Project Name) [Time Spent]"
                    $tasksList[] = htmlspecialchars($task->title) . ' <span class="text-muted">(' . htmlspecialchars($projName) . ')</span> <span class="badge badge-soft-primary px-1.5 py-0.5 ml-1">' . $formattedTime . '</span>';
                }
                $details = [
                    'type' => 'od',
                    'tasks' => $tasksList,
                ];
            } elseif ($deptType === 'nsd') {
                $userSales = $salesUpdates->get($emp->id) ?? collect();
                $stsCount = $userSales->where('category', 'STS')->first()->total ?? 0;
                $dsrCount = $userSales->where('category', 'DSR')->first()->total ?? 0;

                $details = [
                    'type' => 'nsd',
                    'sts' => $stsCount,
                    'dsr' => $dsrCount,
                ];
            } elseif ($deptType === 'csd') {
                $commsCount = $csdUpdates->get($emp->id)->total ?? 0;

                $details = [
                    'type' => 'csd',
                    'comms' => $commsCount,
                ];
            }

            if (!empty($details)) {
                $statusData[] = [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'dept' => strtoupper($deptType),
                    'role' => $roleName,
                    'details' => $details
                ];
            }
        }

        return $statusData;
    }

    private function formatHours($hoursVal)
    {
        if (is_null($hoursVal)) {
            return '00:00 min';
        }
        $min = round((float)$hoursVal * 60);
        $h = floor($min / 60);
        $m = $min % 60;
        return $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
    }
}
