<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\DayClosing;
use App\Models\EmployeeLeave;
use App\Models\GlobalAttendanceLog;
use App\Models\Holiday;
use App\Models\TaskLog;
use App\Models\User;
use App\Services\BranchScopeService;
use App\Services\Od\GlobalTimerService;
use App\Services\Reports\OdWorkReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function __construct(
        private BranchScopeService $branchScope
    ) {}

    /**
     * Parse date or date range from request.
     */
    private function parseDateRange(Request $request): array
    {
        $dateInput = $request->input('date') ?? $request->input('date_range');
        if (!empty($dateInput) && str_contains($dateInput, ' - ')) {
            $parts = explode(' - ', $dateInput);
            $startDate = Carbon::parse(trim($parts[0]))->startOfDay();
            $endDate = Carbon::parse(trim($parts[1]))->endOfDay();
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            $singleDate = !empty($dateInput) ? Carbon::parse($dateInput) : Carbon::today();
            $startDate = $singleDate->copy()->startOfDay();
            $endDate = $singleDate->copy()->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        // Limit range to max 90 days for peak performance
        if ($startDate->diffInDays($endDate) > 90) {
            $endDate = $startDate->copy()->addDays(90)->endOfDay();
        }

        $isSingleDay = $startDate->isSameDay($endDate);
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        $selectedDateStr = $isSingleDay ? $startDateStr : "{$startDateStr} - {$endDateStr}";

        return [$startDate, $endDate, $startDateStr, $endDateStr, $selectedDateStr, $isSingleDay];
    }

    /**
     * Display the Admin and Branch Manager Attendances & Live Workforce Monitoring Dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isGlobalAdmin() && !$user->isBranchManager())) {
            abort(403, 'Unauthorized access. This module is restricted to Global Administrators and Branch Managers only.');
        }

        [$startDate, $endDate, $startDateStr, $endDateStr, $selectedDateStr, $isSingleDay] = $this->parseDateRange($request);
        $isToday = $isSingleDay && $startDate->isToday();
        $isSunday = $isSingleDay && $startDate->isSunday();
        $todayStr = Carbon::today()->format('Y-m-d');

        // Departments list for filter
        $departments = [
            '' => 'All Departments',
            '1' => 'Sales (NSD)',
            '2' => 'Operations (OD)',
            '3' => 'Customer Success (CSD)',
        ];

        // Query active employees (excluding Admin and Client roles)
        $query = User::where('status', 'Active')
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']));

        if ($user->isBranchManager() && !$user->isGlobalAdmin()) {
            $branchId = $this->branchScope->resolveBranchId($user);
            if ($branchId) {
                $branchUserIds = $this->branchScope->getBranchUserIds($user);
                $query->whereIn('id', $branchUserIds);
            }
        }

        $activeEmployees = $query->get();
        $totalEmployeesCount = $activeEmployees->count();
        $empIds = $activeEmployees->pluck('id')->toArray();

        if ($isSingleDay) {
            $attendanceLogs = GlobalAttendanceLog::whereIn('userid', $empIds)
                ->where('log_date', $startDateStr)
                ->get()
                ->groupBy('userid');

            $presentCount = $attendanceLogs->count();
            $absentCount = $isSunday ? 0 : max(0, $totalEmployeesCount - $presentCount);

            // Currently working count (active running task timers)
            $runningTimersCount = TaskLog::whereIn('userid', $empIds)
                ->whereNull('endtime')
                ->where('log_date', $startDateStr)
                ->distinct('userid')
                ->count('userid');

            // Missed day closings count for the selected date (Sundays excluded)
            $closingsOnDate = DayClosing::whereIn('user_id', $empIds)
                ->where('closing_date', $startDateStr)
                ->pluck('user_id')
                ->toArray();

            $missedClosingsCount = 0;
            if ($startDateStr < $todayStr && !$isSunday) {
                $missedClosingsCount = max(0, $totalEmployeesCount - count($closingsOnDate));
            }
        } else {
            // Build date list
            $dateList = [];
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $dateList[] = $d->format('Y-m-d');
            }

            $attendancesByEmpDate = GlobalAttendanceLog::whereIn('userid', $empIds)
                ->whereBetween('log_date', [$startDateStr, $endDateStr])
                ->select('userid', 'log_date')
                ->distinct()
                ->get()
                ->groupBy(fn($i) => $i->userid . '_' . Carbon::parse($i->log_date)->format('Y-m-d'));

            $presentCount = $attendancesByEmpDate->count();

            $allClosings = DayClosing::whereIn('user_id', $empIds)
                ->whereBetween('closing_date', [$startDateStr, $endDateStr])
                ->select('user_id', 'closing_date')
                ->distinct()
                ->get()
                ->groupBy(fn($i) => $i->user_id . '_' . Carbon::parse($i->closing_date)->format('Y-m-d'));

            $absentCount = 0;
            $missedClosingsCount = 0;
            foreach ($dateList as $dStr) {
                $dObj = Carbon::parse($dStr);
                if ($dObj->isSunday()) continue;

                foreach ($empIds as $eId) {
                    $k = $eId . '_' . $dStr;
                    $hasAtt = $attendancesByEmpDate->has($k);
                    if (!$hasAtt) {
                        $absentCount++;
                    }
                    if ($dStr < $todayStr && $hasAtt && !$allClosings->has($k)) {
                        $missedClosingsCount++;
                    }
                }
            }

            $runningTimersCount = TaskLog::whereIn('userid', $empIds)
                ->whereNull('endtime')
                ->where('log_date', $todayStr)
                ->distinct('userid')
                ->count('userid');
        }

        return view('components.administration.attendances.index', compact(
            'selectedDateStr',
            'startDateStr',
            'endDateStr',
            'isSingleDay',
            'isToday',
            'departments',
            'totalEmployeesCount',
            'presentCount',
            'absentCount',
            'runningTimersCount',
            'missedClosingsCount'
        ));
    }

    /**
     * AJAX endpoint for server-side / client-side DataTables workforce matrix.
     * Displays individual day records for each employee across the selected date filter.
     */
    public function getData(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isGlobalAdmin() && !$user->isBranchManager())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        [$startDate, $endDate, $startDateStr, $endDateStr, $selectedDateStr, $isSingleDay] = $this->parseDateRange($request);
        $todayStr = Carbon::today()->format('Y-m-d');

        $departmentFilter = $request->input('department');
        $statusFilter = $request->input('work_status'); // all, working, idle, completed, present, absent, missed_closing
        $searchQuery = $request->input('search_query');

        // Exclude Admin and Client roles so only employees are listed
        $query = User::where('status', 'Active')
            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['Admin', 'Client']))
            ->with(['roles', 'departments.dept', 'emp']);

        if ($user->isBranchManager() && !$user->isGlobalAdmin()) {
            $branchId = $this->branchScope->resolveBranchId($user);
            if ($branchId) {
                $branchUserIds = $this->branchScope->getBranchUserIds($user);
                $query->whereIn('id', $branchUserIds);
            }
        }

        if ($departmentFilter) {
            $query->whereHas('departments', fn($q) => $q->where('department', $departmentFilter));
        }

        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%")
                  ->orWhere('id', 'like', "%{$searchQuery}%");
            });
        }

        $employees = $query->get();
        $empIds = $employees->pluck('id')->toArray();

        // Build list of dates in descending order (most recent date first)
        $dateList = [];
        for ($d = $endDate->copy(); $d->gte($startDate); $d->subDay()) {
            $dateList[] = $d->format('Y-m-d');
        }

        // Eager fetch all attendance, task logs, and closings for the selected date range grouped by empId_date
        $attendancesByEmpAndDate = GlobalAttendanceLog::whereIn('userid', $empIds)
            ->whereBetween('log_date', [$startDateStr, $endDateStr])
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy(fn($item) => $item->userid . '_' . Carbon::parse($item->log_date)->format('Y-m-d'));

        $taskLogsByEmpAndDate = TaskLog::whereIn('userid', $empIds)
            ->whereBetween('log_date', [$startDateStr, $endDateStr])
            ->with(['task.project.clients'])
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy(fn($item) => $item->userid . '_' . Carbon::parse($item->log_date)->format('Y-m-d'));

        $closingsByEmpAndDate = DayClosing::whereIn('user_id', $empIds)
            ->whereBetween('closing_date', [$startDateStr, $endDateStr])
            ->with('approver')
            ->get()
            ->groupBy(fn($item) => $item->user_id . '_' . Carbon::parse($item->closing_date)->format('Y-m-d'));

        $holidays = Holiday::whereBetween('holiday_date', [$startDateStr, $endDateStr])
            ->get()
            ->keyBy(fn($h) => Carbon::parse($h->holiday_date)->format('Y-m-d'));

        $approvedLeaves = EmployeeLeave::whereIn('user_id', $empIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $endDateStr)
            ->whereDate('end_date', '>=', $startDateStr)
            ->with('leaveType')
            ->get();

        // Active timers running right now (for live today indicators)
        $activeRunningTimers = TaskLog::whereIn('userid', $empIds)
            ->whereNull('endtime')
            ->with(['task.project.clients'])
            ->get()
            ->keyBy('userid');

        $rows = [];
        $totalShiftHoursSum = 0.0;
        $totalTaskHoursSum = 0.0;
        $presentCounter = 0;
        $absentCounter = 0;
        $workingNowCounter = 0;
        $missedClosingsCounter = 0;

        foreach ($dateList as $dateStr) {
            $dateObj = Carbon::parse($dateStr);
            $isDateToday = ($dateStr === $todayStr);
            $isDateSunday = $dateObj->isSunday();
            $isDatePast = ($dateStr < $todayStr);
            $singleHoliday = $holidays->get($dateStr);

            foreach ($employees as $emp) {
                $key = $emp->id . '_' . $dateStr;
                $empAttendance = $attendancesByEmpAndDate->get($key, collect());
                $empTaskLogs = $taskLogsByEmpAndDate->get($key, collect());
                $empClosing = $closingsByEmpAndDate->get($key)?->first();

                // Check for approved leave on this date
                $approvedLeave = $approvedLeaves->first(function ($leave) use ($emp, $dateStr) {
                    $lStart = Carbon::parse($leave->start_date)->format('Y-m-d');
                    $lEnd = Carbon::parse($leave->end_date)->format('Y-m-d');
                    return (int)$leave->user_id === (int)$emp->id && $lStart <= $dateStr && $lEnd >= $dateStr;
                });

                $runningTimer = $isDateToday ? $activeRunningTimers->get($emp->id) : null;
                $isPresent = $empAttendance->isNotEmpty();

                if ($isPresent) {
                    $presentCounter++;
                } elseif (!$isDateSunday && !$singleHoliday && !$approvedLeave) {
                    $absentCounter++;
                }

                $firstAttendance = $empAttendance->first();
                $lastAttendance = $empAttendance->last();
                $shiftStart = $firstAttendance && $firstAttendance->starttime ? Carbon::parse($firstAttendance->starttime)->format('h:i A') : null;
                
                // Check if shift is still open
                $isShiftOpen = $empAttendance->contains(fn($l) => is_null($l->endtime));
                $shiftEnd = null;
                if ($lastAttendance && $lastAttendance->endtime && !$isShiftOpen) {
                    $shiftEnd = Carbon::parse($lastAttendance->endtime)->format('h:i A');
                } elseif ($isShiftOpen && $isDateToday) {
                    $shiftEnd = 'In Progress';
                }

                // Raw Hours
                $rawShiftHours = (float) $empAttendance->sum('time_spend');
                $rawTaskHours = (float) $empTaskLogs->sum('time_spend');

                // If running timer is active right now for today, calculate live task time
                if ($runningTimer && $isDateToday) {
                    $now = Carbon::now();
                    $tStarted = Carbon::parse($dateStr . ' ' . ($runningTimer->starttime ?: $runningTimer->created_at->format('H:i:s')));
                    if ($now->gt($tStarted)) {
                        $rawTaskHours += ($tStarted->diffInSeconds($now) / 3600);
                    }
                }

                $isMissedClosing = false;
                $closingStatus = 'Not Submitted';
                $approverName = null;

                if ($empClosing) {
                    $closingStatus = $empClosing->status ? ucfirst($empClosing->status) : 'Submitted';
                    $approverName = $empClosing->approver?->name;
                } else {
                    if ($isDateSunday) {
                        $closingStatus = $isPresent ? 'Sunday Work' : 'Weekend (Sunday)';
                    } elseif ($singleHoliday) {
                        $closingStatus = $isPresent ? 'Holiday Work' : 'Holiday';
                    } elseif ($approvedLeave) {
                        $closingStatus = 'On Leave';
                    } elseif ($isPresent) {
                        if ($isDatePast) {
                            $isMissedClosing = true;
                            $closingStatus = 'Missed Day Closing';
                            $missedClosingsCounter++;
                        } else {
                            $closingStatus = $isShiftOpen ? 'Pending Today' : 'Pending Submission';
                        }
                    } else {
                        if ($isDatePast) {
                            $closingStatus = 'Absent';
                        } else {
                            $closingStatus = 'Not Clocked In';
                        }
                    }
                }

                // If Day Closing is missed, shift hours do not count toward daily basis; count ONLY actual task logged hours
                if ($isMissedClosing) {
                    $verifiedShiftHours = 0.0;
                    $verifiedTaskHours = round($rawTaskHours, 2);
                } else {
                    $verifiedShiftHours = round($rawShiftHours, 2);
                    $verifiedTaskHours = round($rawTaskHours, 2);
                }

                // Live Activity & Active Project / Task for this date
                $liveStatusKey = 'absent';
                $activeTaskTitle = '—';
                $activeProjectName = '—';
                $activeClientName = '—';
                $activeProjectId = null;
                $activeTaskId = null;
                $taskboardUrl = null;

                if ($runningTimer && $isDateToday) {
                    $liveStatusKey = 'working';
                    $workingNowCounter++;
                    $taskObj = $runningTimer->task;
                    $projObj = $taskObj?->project;
                    $activeTaskId = $taskObj?->id ?? $runningTimer->taskid;
                    $activeProjectId = $projObj?->id;
                    $activeTaskTitle = $taskObj?->title ?? ('Task #' . $runningTimer->taskid);
                    $activeProjectName = $projObj?->project_name ?? 'Internal Project';
                    $activeClientName = $projObj?->clients?->name ?? ($projObj?->project_name ?? 'Direct Client');
                    if ($activeProjectId) {
                        $taskboardUrl = url('projects/taskboard/' . base64_encode($activeProjectId)) . ($activeTaskId ? '?task_id=' . $activeTaskId : '');
                    }
                } elseif ($isShiftOpen && $isDateToday) {
                    $liveStatusKey = 'idle';
                    $lastWorked = $empTaskLogs->last();
                    if ($lastWorked) {
                        $taskObj = $lastWorked->task;
                        $projObj = $taskObj?->project;
                        $activeTaskId = $taskObj?->id ?? $lastWorked->taskid;
                        $activeProjectId = $projObj?->id;
                        $activeTaskTitle = 'Last: ' . ($taskObj?->title ?? 'Task #' . $lastWorked->taskid);
                        $activeProjectName = $projObj?->project_name ?? 'Internal Project';
                        $activeClientName = $projObj?->clients?->name ?? ($projObj?->project_name ?? 'Direct Client');
                        if ($activeProjectId) {
                            $taskboardUrl = url('projects/taskboard/' . base64_encode($activeProjectId)) . ($activeTaskId ? '?task_id=' . $activeTaskId : '');
                        }
                    } else {
                        $activeTaskTitle = 'No active task timer started';
                    }
                } elseif ($isPresent) {
                    $liveStatusKey = 'completed';
                    $lastWorked = $empTaskLogs->last();
                    if ($lastWorked) {
                        $taskObj = $lastWorked->task;
                        $projObj = $taskObj?->project;
                        $activeTaskId = $taskObj?->id ?? $lastWorked->taskid;
                        $activeProjectId = $projObj?->id;
                        $activeTaskTitle = $taskObj?->title ?? ('Task #' . $lastWorked->taskid);
                        $activeProjectName = $projObj?->project_name ?? 'Internal Project';
                        $activeClientName = $projObj?->clients?->name ?? ($projObj?->project_name ?? 'Direct Client');
                        if ($activeProjectId) {
                            $taskboardUrl = url('projects/taskboard/' . base64_encode($activeProjectId)) . ($activeTaskId ? '?task_id=' . $activeTaskId : '');
                        }
                    } else {
                        $activeTaskTitle = 'Shift Recorded (No Tasks Logged)';
                    }
                }

                // In-memory Break Hours calculation for this date
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

                if ($lastAttendance && $lastAttendance->status === 'paused' && $lastAttendance->endtime && $isDateToday) {
                    $lastEnd = Carbon::parse($dateStr . ' ' . $lastAttendance->endtime);
                    $now = Carbon::now();
                    $capTime = Carbon::parse($dateStr . ' 23:00:00');
                    if ($now->gt($capTime)) $now = $capTime;
                    if ($now->gt($lastEnd)) {
                        $breakSeconds += $lastEnd->diffInSeconds($now);
                    }
                }
                $breakHours = round($breakSeconds / 3600, 2);
                $isOverBreak = $breakHours > 1.5;

                // Punctuality & Clock-in Detection (Standard Shift: 10:00 AM - 06:30 PM)
                $punctualityKey = 'on_time';
                $punctualityLabel = 'On-Time';
                if ($firstAttendance && $firstAttendance->starttime) {
                    $startObj = Carbon::parse($dateStr . ' ' . $firstAttendance->starttime);
                    $expectedStart = Carbon::parse($dateStr . ' 10:30:00'); // 30m grace after 10:00 AM
                    $officialStart = Carbon::parse($dateStr . ' 10:00:00');
                    if ($startObj->gt($expectedStart)) {
                        $lateMins = abs((int) round($startObj->diffInMinutes($officialStart)));
                        $punctualityKey = 'late';
                        if ($lateMins >= 60) {
                            $hrs = floor($lateMins / 60);
                            $remMins = $lateMins % 60;
                            $punctualityLabel = "Late (+{$hrs}h " . ($remMins > 0 ? "{$remMins}m" : "0m") . ")";
                        } else {
                            $punctualityLabel = "Late (+{$lateMins}m)";
                        }
                    }

                    if ($lastAttendance && $lastAttendance->endtime && !$isShiftOpen) {
                        $endObj = Carbon::parse($dateStr . ' ' . $lastAttendance->endtime);
                        $expectedEnd = Carbon::parse($dateStr . ' 18:00:00');
                        $officialEnd = Carbon::parse($dateStr . ' 18:30:00');
                        if ($endObj->lt($expectedEnd)) {
                            $earlyMins = abs((int) round($officialEnd->diffInMinutes($endObj)));
                            $punctualityKey = ($punctualityKey === 'late') ? 'late_early' : 'early_out';
                            if ($earlyMins >= 60) {
                                $eHrs = floor($earlyMins / 60);
                                $eRemMins = $earlyMins % 60;
                                $earlyFormatted = "Early (-{$eHrs}h " . ($eRemMins > 0 ? "{$eRemMins}m" : "0m") . ")";
                            } else {
                                $earlyFormatted = "Early (-{$earlyMins}m)";
                            }
                            $punctualityLabel .= ($punctualityKey === 'late_early' ? " / " : "") . $earlyFormatted;
                        }
                    }
                } elseif (!$isPresent) {
                    $punctualityKey = 'absent';
                    $punctualityLabel = 'Absent';
                }

                // Attendance Status Classification
                $attendanceStatus = 'Absent';
                $attendanceStatusClass = 'badge-soft-danger';

                if ($isPresent) {
                    if ($approvedLeave && $approvedLeave->is_half_day) {
                        $halfType = $approvedLeave->half_day_type === 'second_half' ? '2nd Half' : '1st Half';
                        $attendanceStatus = 'Half Day (' . $halfType . ')';
                        $attendanceStatusClass = 'badge-soft-warning';
                    } elseif ($rawShiftHours > 0 && $rawShiftHours < 4.5 && !$isShiftOpen) {
                        $attendanceStatus = 'Half Day';
                        $attendanceStatusClass = 'badge-soft-warning';
                    } else {
                        $attendanceStatus = 'Present';
                        $attendanceStatusClass = 'badge-soft-success';
                    }
                } elseif ($approvedLeave) {
                    $attendanceStatus = 'Leave (' . ($approvedLeave->leaveType->code ?? 'Approved') . ')';
                    $attendanceStatusClass = 'badge-soft-info';
                } elseif ($singleHoliday) {
                    $attendanceStatus = 'Holiday: ' . $singleHoliday->name;
                    $attendanceStatusClass = 'badge-soft-primary';
                } elseif ($isDateSunday) {
                    $attendanceStatus = 'Sunday Weekend';
                    $attendanceStatusClass = 'badge-soft-secondary';
                } elseif ($isDatePast) {
                    $attendanceStatus = 'Absent';
                    $attendanceStatusClass = 'badge-soft-danger';
                } else {
                    $attendanceStatus = 'Not Clocked In';
                    $attendanceStatusClass = 'badge-soft-danger';
                }

                $totalShiftHoursSum += $verifiedShiftHours;
                $totalTaskHoursSum += $verifiedTaskHours;

                // Filter out by work_status if requested
                if ($statusFilter) {
                    if ($statusFilter === 'working' && $liveStatusKey !== 'working') continue;
                    if ($statusFilter === 'idle' && $liveStatusKey !== 'idle') continue;
                    if ($statusFilter === 'completed' && $liveStatusKey !== 'completed') continue;
                    if ($statusFilter === 'absent' && $isPresent) continue;
                    if ($statusFilter === 'present' && !$isPresent) continue;
                    if ($statusFilter === 'missed_closing' && !($isMissedClosing ?? false)) continue;
                }

                $deptName = $emp->departments->dept->name ?? ($emp->roles[0]->name ?? 'General');
                $deptClass = 'badge-od';
                $deptId = $emp->departments->department ?? 2;
                if ($deptId == 1) $deptClass = 'badge-nsd';
                elseif ($deptId == 3) $deptClass = 'badge-csd';

                // Productivity Efficiency Ratio
                $productivityRatio = 0;
                $productivityClass = 'danger';
                if ($verifiedShiftHours > 0) {
                    $productivityRatio = min(100, round(($verifiedTaskHours / $verifiedShiftHours) * 100));
                    if ($productivityRatio >= 75) {
                        $productivityClass = 'success';
                    } elseif ($productivityRatio >= 50) {
                        $productivityClass = 'warning';
                    } else {
                        $productivityClass = 'danger';
                    }
                }

                // Tasks mapping for modal inspection
                $tasksList = $empTaskLogs->map(function($l) {
                    $task = $l->task;
                    $project = $task?->project;
                    $client = $project?->clients;
                    $projectId = $project?->id;
                    $taskboardUrl = $projectId ? url('projects/taskboard/' . base64_encode($projectId)) . ($l->taskid ? '?task_id=' . $l->taskid : '') : null;

                    return [
                        'task_id' => $l->taskid,
                        'task_title' => $task?->title ?? ('Task #' . $l->taskid),
                        'task_name' => $task?->title ?? ('Task #' . $l->taskid),
                        'project_id' => $projectId,
                        'project_name' => $project?->project_name ?? 'Internal Project',
                        'client_name' => $client?->name ?? ($project?->project_name ?? 'Direct Client'),
                        'taskboard_url' => $taskboardUrl,
                        'hours' => round((float)$l->time_spend, 2),
                        'hours_formatted' => OdWorkReportService::formatToTimingHours((float)$l->time_spend),
                        'starttime' => $l->starttime ? Carbon::parse($l->starttime)->format('h:i A') : '',
                        'endtime' => $l->endtime ? Carbon::parse($l->endtime)->format('h:i A') : '',
                        'description' => $l->log_description ?: 'No detailed work note.',
                        'log_date' => $l->log_date ? Carbon::parse($l->log_date)->format('d M Y') : '',
                    ];
                })->values()->toArray();

                $rowKey = $emp->id . '_' . $dateStr;

                $rows[] = [
                    'row_key' => $rowKey,
                    'date' => $dateStr,
                    'date_formatted' => $dateObj->format('d M Y'),
                    'day_name' => $dateObj->format('l'),
                    'is_today' => $isDateToday,
                    'is_sunday' => $isDateSunday,
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'emp_id' => '#EMP-' . ($emp->id + 1000),
                    'avatar' => strtoupper(substr($emp->name, 0, 2)),
                    'department' => $deptName,
                    'dept_class' => $deptClass,
                    'role' => $emp->roles[0]->name ?? 'Specialist',
                    'live_status' => $liveStatusKey,
                    'attendance_status' => $attendanceStatus,
                    'attendance_status_class' => $attendanceStatusClass,
                    'active_task_title' => $activeTaskTitle,
                    'active_project_name' => $activeProjectName,
                    'active_client_name' => $activeClientName,
                    'active_project_id' => $activeProjectId,
                    'active_task_id' => $activeTaskId,
                    'taskboard_url' => $taskboardUrl,
                    'shift_start' => $shiftStart ?: '—',
                    'shift_end' => $shiftEnd ?: '—',
                    'punctuality_key' => $punctualityKey,
                    'punctuality_label' => $punctualityLabel,
                    'is_present' => $isPresent,
                    'raw_shift_hours' => $rawShiftHours,
                    'raw_task_hours' => $rawTaskHours,
                    'shift_hours_formatted' => OdWorkReportService::formatToTimingHours($verifiedShiftHours),
                    'task_hours_formatted' => OdWorkReportService::formatToTimingHours($verifiedTaskHours),
                    'break_hours_formatted' => OdWorkReportService::formatToTimingHours($breakHours),
                    'is_over_break' => $isOverBreak,
                    'productivity_ratio' => $productivityRatio,
                    'productivity_class' => $productivityClass,
                    'is_missed_closing' => $isMissedClosing ?? false,
                    'closing_status' => $closingStatus,
                    'approver_name' => $approverName ?? null,
                    'tasks_count' => $empTaskLogs->unique('taskid')->count(),
                    'tasks' => $tasksList,
                ];
            }
        }

        return response()->json([
            'data' => $rows,
            'summary' => [
                'total_employees' => count($employees),
                'present_count' => $presentCounter,
                'absent_count' => $absentCounter,
                'working_now_count' => $workingNowCounter,
                'missed_closings_count' => $missedClosingsCounter,
                'total_shift_hours_formatted' => OdWorkReportService::formatToTimingHours($totalShiftHoursSum),
                'total_task_hours_formatted' => OdWorkReportService::formatToTimingHours($totalTaskHoursSum),
            ]
        ]);
    }

    /**
     * Export Attendances and Payroll audit data to Excel.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isGlobalAdmin() && !$user->isBranchManager())) {
            abort(403, 'Unauthorized access.');
        }

        [$startDate, $endDate, $startDateStr, $endDateStr, $selectedDateStr, $isSingleDay] = $this->parseDateRange($request);
        $departmentId = $request->input('department');
        $statusFilter = $request->input('work_status');

        $fileName = 'Attendances_' . str_replace(' ', '', $selectedDateStr) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AdminAttendancesExport($selectedDateStr, $departmentId, $statusFilter, $user),
            $fileName
        );
    }
}
