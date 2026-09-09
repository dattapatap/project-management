<?php

namespace App\Exports;

use App\Models\User;
use App\Models\DayClosing;
use App\Services\BranchScopeService;
use App\Services\DailyClosingService;
use App\Services\UserPerformanceService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyTargetsExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private $employeeId;
    private $startDateStr;
    private $endDateStr;
    private $user;

    public function __construct($employeeId, $startDateStr, $endDateStr, $user)
    {
        $this->employeeId = $employeeId;
        $this->startDateStr = $startDateStr;
        $this->endDateStr = $endDateStr;
        $this->user = $user;
    }

    public function array(): array
    {
        $branchScope = app(BranchScopeService::class);
        $closingService = app(DailyClosingService::class);
        $performanceService = app(UserPerformanceService::class);

        $startDateStr = $this->startDateStr;
        $endDateStr = $this->endDateStr;

        if (!$startDateStr || !$endDateStr) {
            $startDateStr = Carbon::today()->subDays(6)->format('Y-m-d');
            $endDateStr = Carbon::today()->format('Y-m-d');
        }

        $startDate = Carbon::parse($startDateStr);
        $endDate = Carbon::parse($endDateStr);

        // Cap range to 31 days
        if ($startDate->diffInDays($endDate) > 31) {
            $endDate = $startDate->copy()->addDays(31);
        }

        // Generate date list desc, excluding Sundays
        $dateList = [];
        for ($d = $endDate->copy(); $d->gte($startDate); $d->subDay()) {
            if ($d->isSunday()) {
                continue;
            }
            $dateList[] = $d->format('Y-m-d');
        }

        // Fetch Employees
        if ($this->user->isGlobalAdmin()) {
            $employeesQuery = User::where('status', 'Active')
                ->where('id', '!=', $this->user->id)
                ->with(['roles', 'departments']);
        } else {
            $branchUserIds = $branchScope->getBranchUserIds($this->user);
            $employeesQuery = User::whereIn('id', $branchUserIds)
                ->where('status', 'Active')
                ->where('id', '!=', $this->user->id)
                ->with(['roles', 'departments']);
        }

        if ($this->employeeId) {
            $employeesQuery->where('id', $this->employeeId);
        }

        $allEmployees = $employeesQuery->get()->filter(function ($u) use ($performanceService) {
            $u->dept_type = $performanceService->departmentType($u);
            return in_array($u->dept_type, ['nsd', 'csd', 'od'], true);
        })->values();

        $employeeIds = $allEmployees->pluck('id')->toArray();
        $dayClosings = DayClosing::whereIn('user_id', $employeeIds)
            ->whereBetween('closing_date', [$startDateStr, $endDateStr])
            ->with('approver')
            ->get()
            ->groupBy(function ($dc) {
                $date = $dc->closing_date instanceof Carbon ? $dc->closing_date->format('Y-m-d') : Carbon::parse($dc->closing_date)->format('Y-m-d');
                return $dc->user_id . '_' . $date;
            });

        $exportData = [];
        foreach ($dateList as $dateStr) {
            foreach ($allEmployees as $emp) {
                $key = $emp->id . '_' . $dateStr;
                $dc = isset($dayClosings[$key]) ? $dayClosings[$key]->first() : null;

                $roleName = $emp->getRoleNames()->first() ?? '-';
                $deptUpper = strtoupper($emp->dept_type);

                if ($dc) {
                    $metrics = $dc->achieved_metrics ?? [];
                    $remarks = $dc->executive_remarks ?? '-';
                    $approvedDate = $dc->approved_at ? Carbon::parse($dc->approved_at)->format('d-M-Y H:i') : '-';
                    $approvedBy = $dc->approver ? $dc->approver->name : '-';
                    $targetStatus = $dc->target_status;
                } else {
                    $metrics = $closingService->getTodayMetrics($emp, $dateStr);
                    $remarks = '-';
                    $approvedDate = '-';
                    $approvedBy = '-';
                    if ($dateStr === Carbon::today()->format('Y-m-d')) {
                        $targetStatus = 'Pending';
                    } else {
                        $targetStatus = 'Not Met';
                    }
                }

                // Format timing metrics
                $shiftTiming = $this->formatHours($metrics['global_hours'] ?? null);
                $breakTime = $this->formatHours($metrics['break_hours'] ?? null);

                // Format department-specific metrics
                $taskLoggedTime = 'n/a';
                $totalTaskCompleted = '0';

                if ($deptUpper === 'NSD') {
                    $sts = $metrics['sts'] ?? 0;
                    $dsr = $metrics['dsr'] ?? 0;
                    $totalTaskCompleted = "STS: {$sts}, DSR: {$dsr}";
                } elseif ($deptUpper === 'CSD') {
                    $comms = $metrics['communications'] ?? 0;
                    $totalTaskCompleted = "Comms: {$comms}";
                } elseif ($deptUpper === 'OD') {
                    $taskLoggedTime = $this->formatHours($metrics['hours'] ?? null);
                    $tasks = $metrics['tasks'] ?? 0;
                    $totalTaskCompleted = (string)$tasks;
                }

                $dateCarbon = Carbon::parse($dateStr);
                $exportData[] = [
                    'date' => $dateCarbon->format('d-M-Y') . ' (' . $dateCarbon->format('l') . ')',
                    'employee' => $emp->name,
                    'department' => $deptUpper,
                    'role' => $roleName,
                    'shift_timing' => $shiftTiming,
                    'break_time' => $breakTime,
                    'task_logged_time' => $taskLoggedTime,
                    'total_task_completed' => $totalTaskCompleted,
                    'target_status' => $targetStatus,
                    'submission_remark' => $remarks,
                    'approved_date' => $approvedDate,
                    'approved_by' => $approvedBy,
                ];
            }
        }

        return $exportData;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Employee',
            'Department',
            'Role',
            'Shift Timing',
            'Break Time',
            'Task Logged Time',
            'Total Task Completed',
            'Target Status',
            'Submission Remark',
            'Approved Date',
            'Approved By',
        ];
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
