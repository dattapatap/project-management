<?php

namespace App\Exports;

use App\Models\DayClosing;
use App\Models\EmployeeLeave;
use App\Models\GlobalAttendanceLog;
use App\Models\Holiday;
use App\Models\TaskLog;
use App\Models\User;
use App\Services\Reports\OdWorkReportService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DateRangeAttendanceExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private string $startDateStr;
    private string $endDateStr;
    private ?string $departmentId;
    private ?int $userId;
    private ?string $statusFilter;
    private ?User $actingUser;
    private ?string $locationFilter;

    public function __construct(
        string $startDateStr,
        string $endDateStr,
        ?string $departmentId = null,
        ?int $userId = null,
        ?string $statusFilter = null,
        ?User $actingUser = null,
        ?string $locationFilter = null
    ) {
        $this->startDateStr = $startDateStr;
        $this->endDateStr = $endDateStr;
        $this->departmentId = $departmentId;
        $this->userId = $userId;
        $this->statusFilter = $statusFilter;
        $this->actingUser = $actingUser;
        $this->locationFilter = $locationFilter;
    }

    public function array(): array
    {
        $startDate = Carbon::parse($this->startDateStr)->startOfDay();
        $endDate = Carbon::parse($this->endDateStr)->startOfDay();
        $todayStr = Carbon::today()->format('Y-m-d');

        if ($startDate->gt($endDate)) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        // Cap to max 90 days to maintain high performance
        if ($startDate->diffInDays($endDate) > 90) {
            $endDate = $startDate->copy()->addDays(90);
        }

        // Build list of dates
        $dateList = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dateList[] = $d->format('Y-m-d');
        }

        // Query employees (excluding Admin and Client roles)
        $query = User::whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
            ->with(['roles', 'departments.dept', 'emp']);

        if ($this->userId) {
            $query->where('id', $this->userId);
        } else {
            $query->whereIn('status', User::WORKING_STATUSES);
        }

        if ($this->actingUser && $this->actingUser->isBranchManager() && !$this->actingUser->isGlobalAdmin()) {
            $branchId = app(\App\Services\BranchScopeService::class)->resolveBranchId($this->actingUser);
            if ($branchId) {
                $branchUserIds = app(\App\Services\BranchScopeService::class)->getBranchUserIds($this->actingUser);
                $query->whereIn('id', $branchUserIds);
            }
        }

        if ($this->departmentId) {
            $query->whereHas('departments', fn($q) => $q->where('department', $this->departmentId));
        }

        $employees = $query->get();
        $empIds = $employees->pluck('id')->toArray();

        // Eager load all attendance, task logs, closings, leaves, and holidays across the date range
        $allAttendances = GlobalAttendanceLog::whereIn('userid', $empIds)
            ->whereBetween('log_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy(fn($item) => $item->userid . '_' . Carbon::parse($item->log_date)->format('Y-m-d'));

        $allTaskLogs = TaskLog::whereIn('userid', $empIds)
            ->whereBetween('log_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['task.project.clients'])
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy(fn($item) => $item->userid . '_' . Carbon::parse($item->log_date)->format('Y-m-d'));

        $allClosings = DayClosing::whereIn('user_id', $empIds)
            ->whereBetween('closing_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy(fn($item) => $item->user_id . '_' . Carbon::parse($item->closing_date)->format('Y-m-d'));

        $allLeaves = EmployeeLeave::whereIn('user_id', $empIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $endDate->format('Y-m-d'))
            ->whereDate('end_date', '>=', $startDate->format('Y-m-d'))
            ->with('leaveType')
            ->get();

        $allHolidays = Holiday::whereBetween('holiday_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(fn($h) => Carbon::parse($h->holiday_date)->format('Y-m-d'));

        $rows = [];

        foreach ($employees as $emp) {
            $empCode = '#EMP-' . ($emp->id + 1000);

            foreach ($dateList as $dateStr) {
                $dateObj = Carbon::parse($dateStr);
                $isToday = ($dateStr === $todayStr);
                $isSunday = $dateObj->isSunday();
                $isPastDate = ($dateStr < $todayStr);
                $holidayObj = $allHolidays->get($dateStr);

                $key = $emp->id . '_' . $dateStr;
                $empAttendance = $allAttendances->get($key, collect());
                $empTaskLogs = $allTaskLogs->get($key, collect());
                $empClosing = $allClosings->get($key)?->first();

                // Check for approved leave on this date
                $approvedLeave = $allLeaves->first(function ($leave) use ($emp, $dateStr) {
                    $lStart = Carbon::parse($leave->start_date)->format('Y-m-d');
                    $lEnd = Carbon::parse($leave->end_date)->format('Y-m-d');
                    return (int)$leave->user_id === (int)$emp->id && $lStart <= $dateStr && $lEnd >= $dateStr;
                });

                $firstAttendance = $empAttendance->first();
                $lastAttendance = $empAttendance->last();
                $shiftStart = $firstAttendance && $firstAttendance->starttime ? Carbon::parse($firstAttendance->starttime)->format('h:i A') : '';
                $isShiftOpen = $empAttendance->contains(fn($l) => is_null($l->endtime));
                
                $shiftClose = '';
                if ($lastAttendance && $lastAttendance->endtime && !$isShiftOpen) {
                    $shiftClose = Carbon::parse($lastAttendance->endtime)->format('h:i A');
                }

                $rawShiftHours = (float) $empAttendance->sum('time_spend');
                $rawTaskHours = (float) $empTaskLogs->sum('time_spend');

                // In-memory Break Hours calculation
                $breakSeconds = 0;
                $logsCount = $empAttendance->count();
                for ($i = 0; $i < $logsCount - 1; $i++) {
                    $prevLog = $empAttendance[$i];
                    $nextLog = $empAttendance[$i + 1];

                    if ($prevLog->endtime && $nextLog->starttime) {
                        $prevEnd = Carbon::parse($dateStr . ' ' . $prevLog->endtime);
                        $nextStart = Carbon::parse($dateStr . ' ' . $nextLog->starttime);

                        if ($nextStart->gt($prevEnd)) {
                            $breakSeconds += $prevEnd->diffInSeconds($nextStart);
                        }
                    }
                }

                if ($lastAttendance && $lastAttendance->status === 'paused' && $lastAttendance->endtime && $isToday) {
                    $lastEnd = Carbon::parse($dateStr . ' ' . $lastAttendance->endtime);
                    $now = Carbon::now();
                    $capTime = Carbon::parse($dateStr . ' 23:00:00');
                    if ($now->gt($capTime)) $now = $capTime;
                    if ($now->gt($lastEnd)) {
                        $breakSeconds += $lastEnd->diffInSeconds($now);
                    }
                }
                $breakHours = round($breakSeconds / 3600, 2);

                $isPresent = $empAttendance->isNotEmpty() || !empty($shiftStart);
                $isMissedClosing = false;

                // ── Determine Day Closing Status ─────────────────────────────
                if ($empClosing) {
                    $closingStatus = $empClosing->status ? ucfirst($empClosing->status) : 'Submitted';
                } elseif ($isSunday) {
                    $closingStatus = $isPresent ? 'Sunday Work' : 'Weekend';
                } elseif ($holidayObj) {
                    $closingStatus = $isPresent ? 'Holiday Work' : 'Holiday';
                } elseif ($approvedLeave) {
                    $closingStatus = 'On Leave';
                } elseif ($isPresent) {
                    if ($isPastDate) {
                        $isMissedClosing = true;
                        $closingStatus = 'Missed Day Closing';
                    } else {
                        $closingStatus = $isShiftOpen ? 'Pending Today' : 'Pending Submission';
                    }
                } else {
                    if ($isPastDate) {
                        $closingStatus = 'Absent';
                    } else {
                        $closingStatus = 'Not Clocked In';
                    }
                }

                // ── Determine Attendance Status based on Shift Start & Close ─
                if ($isSunday) {
                    $attendanceStatus = $isPresent ? 'Present (Sunday Work)' : 'Sunday Weekend';
                } elseif ($holidayObj) {
                    $attendanceStatus = $isPresent ? 'Present (Holiday Work)' : ('Holiday (' . $holidayObj->name . ')');
                } elseif ($approvedLeave) {
                    if ($approvedLeave->is_half_day) {
                        $attendanceStatus = 'Half Day (' . ($approvedLeave->leaveType->name ?? 'Leave') . ')';
                    } else {
                        $attendanceStatus = 'Approved Leave (' . ($approvedLeave->leaveType->name ?? 'Leave') . ')';
                    }
                } elseif ($isPresent) {
                    if (!empty($shiftClose)) {
                        if ($rawShiftHours > 0 && $rawShiftHours < 4.5) {
                            $attendanceStatus = 'Half Day';
                        } else {
                            $attendanceStatus = 'Present';
                        }
                    } else {
                        $attendanceStatus = $isToday ? 'Present (In Progress)' : 'Present';
                    }
                } else {
                    $attendanceStatus = $isPastDate ? 'Absent' : 'Not Clocked In';
                }

                // ── Verified Hours Calculation ──────────────────────────────
                if ($isMissedClosing) {
                    $verifiedShiftHours = 0.0;
                    $verifiedTaskHours = round($rawTaskHours, 2);
                } else {
                    $verifiedShiftHours = round($rawShiftHours, 2);
                    $verifiedTaskHours = round($rawTaskHours, 2);
                }

                // ── Productivity / Efficiency Ratio ─────────────────────────
                $efficiencyRatio = 0;
                if ($verifiedShiftHours > 0) {
                    $efficiencyRatio = min(100, round(($verifiedTaskHours / $verifiedShiftHours) * 100));
                }

                // ── Projects & Tasks summary ────────────────────────────────
                $projectsSummary = $empTaskLogs->map(function ($l) {
                    $task = $l->task;
                    $project = $task?->project;
                    $client = $project?->clients;

                    $companyName = trim($client?->name ?? '');
                    $projectName = trim($project?->project_name ?? '');

                    if ($companyName && $projectName) {
                        if (strcasecmp($companyName, $projectName) === 0) {
                            return $projectName;
                        }
                        return "{$companyName} - {$projectName}";
                    } elseif ($companyName) {
                        return $companyName;
                    } elseif ($projectName) {
                        return $projectName;
                    }

                    return null;
                })->filter()->unique()->implode(', ') ?: '—';

                $tasksSummary = $empTaskLogs->map(fn($l) => $l->task?->title)->filter()->unique()->implode(', ') ?: '—';

                $firstAttendance = $empAttendance->first();
                $workLocation = $firstAttendance?->work_location ?: '—';

                // Optional work status and work location filter
                if ($this->statusFilter) {
                    if ($this->statusFilter === 'present' && !$isPresent) continue;
                    if ($this->statusFilter === 'absent' && $isPresent) continue;
                    if ($this->statusFilter === 'missed_closing' && !$isMissedClosing) continue;
                }

                if ($this->locationFilter) {
                    if ($this->locationFilter === 'Office' && $workLocation !== 'Office') continue;
                    if ($this->locationFilter === 'Work from Home' && $workLocation !== 'Work from Home') continue;
                    if ($this->locationFilter === 'Client Place' && $workLocation !== 'Client Place') continue;
                }

                $rows[] = [
                    $empCode,
                    $emp->name,
                    $dateStr,
                    $dateObj->format('l'),
                    $shiftStart ?: '',
                    $shiftClose ?: '',
                    OdWorkReportService::formatToTimingHours($verifiedShiftHours) . ' hrs',
                    OdWorkReportService::formatToTimingHours($verifiedTaskHours) . ' hrs',
                    OdWorkReportService::formatToTimingHours($breakHours) . ' hrs',
                    $efficiencyRatio . '%',
                    $closingStatus,
                    $attendanceStatus,
                    $workLocation,
                    $projectsSummary,
                    $tasksSummary,
                ];
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Employee Code',
            'Employee Name',
            'Date',
            'Day',
            'Shift Start',
            'Shift Close',
            'Verified Shift Hours',
            'Task Hours Logged',
            'Break Duration',
            'Efficiency Ratio (%)',
            'Day Closing Status',
            'Attendance Status',
            'Work Location',
            'Projects Worked On',
            'Tasks Worked On',
        ];
    }
}
