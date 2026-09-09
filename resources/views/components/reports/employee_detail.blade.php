@extends('layouts.app')

@php
$user = Auth::user();
$isOwnProfile = Auth::id() == $employee->id;
$deptName = $employee->departments->dept->name ?? 'Operations';
$roleName = $employee->roles[0]->name ?? 'Specialist';
@endphp

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .emp-dossier-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .live-status-banner {
        border-radius: 16px;
        padding: 20px 24px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    .live-status-banner.is-active {
        border-left: 5px solid #10b981;
        background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    }

    .live-status-banner.is-offline {
        border-left: 5px solid #94a3b8;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .live-pulse-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #10b981;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 1.8s infinite;
    }

    .live-offline-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #94a3b8;
        display: inline-block;
    }

    @keyframes pulse-green {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .stat-card-premium {
        border-radius: 16px;
        padding: 20px 22px;
        background: #ffffff;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }

    .stat-card-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .stat-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .table-modern thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 16px;
    }

    .table-modern tbody td {
        padding: 13px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .max-time-task-row {
        background-color: #fff5f5 !important;
        border-left: 4px solid #ef4444 !important;
    }

    .max-time-task-row:hover {
        background-color: #fee2e2 !important;
    }

    .badge-max-time {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
    }

    .progress-bar-thin {
        height: 6px;
        border-radius: 10px;
        background-color: #e2e8f0;
        overflow: hidden;
    }
</style>
@endsection

@section('content')
<div class="container-fluid emp-dossier-wrapper pb-5">

    {{-- Top Header & Navigation --}}
    <div class="row align-items-center mb-4 mt-2">
        <div class="col-lg-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.employees') }}" class="btn btn-light border btn-sm px-3 mr-3 shadow-sm font-weight-medium">
                    <i class="mdi mdi-arrow-left mr-1"></i> Back to Reports
                </a>
                <div>
                    <h4 class="mb-1 text-dark font-weight-bold">
                        {{ $isOwnProfile ? 'My Performance Insights' : 'Executive Employee Dossier' }}
                    </h4>
                    <span class="text-muted font-size-13">
                        Detailed productivity tracking, task time logs & daily closing audits for <strong class="text-dark">{{ $employee->name }}</strong>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
            <div class="d-inline-flex align-items-center flex-wrap" style="gap: 8px;">
                {{-- Date Range Picker Input --}}
                <div class="input-group input-group-sm shadow-sm" style="width: 240px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-calendar text-primary"></i></span>
                    </div>
                    <input type="text" id="report_date_range_picker" class="form-control font-weight-medium border-left-0 font-size-12" style="background: white; cursor: pointer;" readonly>
                    <input type="hidden" id="filter_start_date" value="{{ $startDateStr }}">
                    <input type="hidden" id="filter_end_date" value="{{ $endDateStr }}">
                </div>

                {{-- Filter Action Buttons --}}
                <button type="button" id="btnApplyDetailFilter" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-semibold">
                    <i class="mdi mdi-filter mr-1"></i> Apply
                </button>
                <button type="button" id="btnResetDetailFilter" class="btn btn-light border btn-sm px-2.5 font-weight-medium" title="Reset to Current Month">
                    <i class="mdi mdi-refresh"></i> Reset
                </button>

                {{-- Download PDF Action --}}
                <a href="{{ route('reports.employee.pdf', array_merge(['id' => base64_encode($employee->id)], request()->all())) }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm font-weight-semibold">
                    <i class="mdi mdi-file-pdf-box mr-1"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    {{-- 1. Real-time Live Activity & Identity Banner --}}
    <div class="live-status-banner mb-4 {{ $liveStatus['is_working'] ? 'is-active' : 'is-offline' }}">
        <div class="row align-items-center">
            <div class="col-md-7 d-flex align-items-center">
                <div class="avatar-md mr-3" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </div>
                <div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <h4 class="mb-0 font-weight-bold text-dark">{{ $employee->name }}</h4>
                        <span class="badge badge-soft-primary px-2 py-0.5 rounded-pill font-size-11 font-weight-bold">UID: #EMP-{{ $employee->id + 1000 }}</span>
                        <span class="badge badge-soft-info px-2 py-0.5 rounded-pill font-size-11 font-weight-bold">{{ $deptName }} · {{ $roleName }}</span>
                    </div>
                    <p class="text-muted font-size-12 mb-0 mt-1">
                        <i class="mdi mdi-email-outline mr-1"></i> {{ $employee->email }} 
                        @if(!empty($employee->emp?->phone) || !empty($employee->emp?->contact_no))
                            | <i class="mdi mdi-phone-outline mr-1"></i> {{ $employee->emp->phone ?? $employee->emp->contact_no }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-5 text-md-right mt-3 mt-md-0">
                <div class="d-inline-flex flex-column align-items-md-end">
                    <div class="d-flex align-items-center mb-1">
                        <span class="{{ $liveStatus['is_working'] ? 'live-pulse-dot' : 'live-offline-dot' }} mr-2"></span>
                        <strong class="font-size-13 {{ $liveStatus['is_working'] ? 'text-success' : 'text-muted' }}">
                            {{ $liveStatus['status_label'] }}
                        </strong>
                    </div>
                    <small class="text-muted font-size-12 text-md-right d-block" style="max-width: 320px;">
                        {{ $liveStatus['detail'] }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Enterprise Performance & Hours KPI Cards --}}
    <div class="row mb-4">
        {{-- Card 1: Task Productivity Index --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="stat-card-premium" style="border-left: 4px solid #4f46e5;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Task Productivity Index</span>
                    <div class="stat-icon-circle" style="background: rgba(79, 70, 229, 0.12); color: #4f46e5;">
                        <i class="mdi mdi-speedometer"></i>
                    </div>
                </div>
                <h2 class="font-weight-bold text-dark mb-1">{{ $taskPerformanceScore }}%</h2>
                <div class="progress-bar-thin mb-2">
                    <div class="progress-bar" style="width: {{ $taskPerformanceScore }}%; background: {{ $taskPerformanceScore >= 75 ? '#10b981' : ($taskPerformanceScore >= 50 ? '#3b82f6' : '#ef4444') }};"></div>
                </div>
                <small class="font-size-11 {{ $taskPerformanceScore >= 75 ? 'text-success' : ($taskPerformanceScore >= 50 ? 'text-primary' : 'text-danger') }} font-weight-semibold">
                    <i class="mdi mdi-information-outline mr-0.5"></i> Based on daily task work vs 6.5h target
                </small>
            </div>
        </div>

        {{-- Card 2: Average Task Hours Spent / Day --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="stat-card-premium" style="border-left: 4px solid #2563eb;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Avg Daily Task Spend</span>
                    <div class="stat-icon-circle" style="background: rgba(37, 99, 235, 0.12); color: #2563eb;">
                        <i class="mdi mdi-clock-check-outline"></i>
                    </div>
                </div>
                <h2 class="font-weight-bold text-dark mb-1">{{ $avgDailyTaskHoursFormatted }} <small class="font-size-13 text-muted font-weight-normal">Hrs / Day</small></h2>
                <p class="text-muted font-size-11 mb-0">
                    Total Task Time: <strong class="text-dark">{{ $totalTaskHoursFormatted }} hrs</strong> in {{ $totalWorkingDaysCount }} working days
                </p>
            </div>
        </div>

        {{-- Card 3: Average Shift Hours Logged --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="stat-card-premium" style="border-left: 4px solid #059669;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Avg Shift Duration</span>
                    <div class="stat-icon-circle" style="background: rgba(5, 150, 105, 0.12); color: #059669;">
                        <i class="mdi mdi-timer-outline"></i>
                    </div>
                </div>
                <h2 class="font-weight-bold text-dark mb-1">{{ $avgDailyShiftHoursFormatted }} <small class="font-size-13 text-muted font-weight-normal">Hrs / Day</small></h2>
                <p class="text-muted font-size-11 mb-0">
                    Total Shift Logged: <strong class="text-dark">{{ $totalShiftHoursFormatted }} hrs</strong>
                </p>
            </div>
        </div>

        {{-- Card 4: Day Closing Compliance (Excluding Sundays) --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-premium" style="border-left: 4px solid {{ $unsubmittedClosingDaysCount > 0 ? '#ef4444' : '#10b981' }};">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Unsubmitted Day Closings</span>
                    <div class="stat-icon-circle" style="background: {{ $unsubmittedClosingDaysCount > 0 ? 'rgba(239, 68, 68, 0.12)' : 'rgba(16, 185, 129, 0.12)' }}; color: {{ $unsubmittedClosingDaysCount > 0 ? '#ef4444' : '#10b981' }};">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                </div>
                <h2 class="font-weight-bold {{ $unsubmittedClosingDaysCount > 0 ? 'text-danger' : 'text-success' }} mb-1">
                    {{ $unsubmittedClosingDaysCount }} <small class="font-size-13 text-muted font-weight-normal">Missing Days</small>
                </h2>
                <p class="font-size-11 mb-0 text-muted">
                    Submitted: <strong class="text-success">{{ $submittedClosingDaysCount }} of {{ $totalWorkingDaysCount }}</strong> <small>(Sundays skipped)</small>
                </p>
            </div>
        </div>
    </div>

    {{-- 3. Hours per Task Table (With Maximum Time Took Task Highlighted in Red) --}}
    {{-- 3. Hours per Task Table (With Time Frame comparison & Max Time Highlight) --}}
    @if($isOd && $odTaskBreakdown->count() > 0)
    <div class="card border shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                    <i class="mdi mdi-format-list-checks text-primary mr-1"></i> Task Time Allocation & Consumption Audit
                </h5>
                <small class="text-muted">Task schedule time frames, allocated office shift hours (10:00 AM – 6:30 PM, Sundays excluded), actual hours spent, and overtime variances.</small>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                @if($maxTaskHours > 0)
                <span class="badge badge-danger px-3 py-1 font-size-11 font-weight-bold rounded-pill">
                    <i class="mdi mdi-fire mr-1"></i> Max Single Task: {{ $maxTaskHours }} hrs
                </span>
                @endif
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill font-size-11 font-weight-bold">
                    {{ $odTaskBreakdown->count() }} Tasks
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="task-allocation-table" class="table table-modern table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px; width: 28%;">Task Title</th>
                            <th style="width: 18%;">Project / Client</th>
                            <th style="width: 22%;">Scheduled Time Frame</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 14%; text-align: right;">Actual vs Allotted</th>
                            <th style="padding-right: 24px; width: 8%; text-align: right;">Logs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($odTaskBreakdown as $taskRow)
                        @php
                            $isMaxTask = ($taskRow->total_hours == $maxTaskHours && $maxTaskHours > 0);
                            $isOverdue = $taskRow->is_overdue ?? false;
                        @endphp
                        <tr class="{{ $isMaxTask ? 'max-time-task-row' : '' }}">
                            <td style="padding-left: 24px;">
                                <div class="d-flex align-items-center">
                                    @if($isMaxTask)
                                    <span class="badge-max-time mr-2" title="Highest Time Consumed Task in Period">
                                        <i class="mdi mdi-fire"></i> MAX TIME
                                    </span>
                                    @endif
                                    <div>
                                        <span class="font-weight-bold {{ $isMaxTask ? 'text-danger' : 'text-dark' }} d-block font-size-13">
                                            {{ $taskRow->task_title }}
                                        </span>
                                        <small class="text-muted font-size-11">Task #{{ $taskRow->task_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-weight-medium text-dark font-size-12 d-block">{{ $taskRow->project_name }}</span>
                            </td>
                            <td>
                                <div>
                                    <span class="font-weight-semibold text-dark font-size-12 d-block">
                                        <i class="mdi mdi-calendar-range text-primary mr-1"></i>{{ $taskRow->time_frame_label }}
                                    </span>
                                    <small class="text-muted font-size-11">Allotted Shift: <strong>{{ $taskRow->allocated_hours_formatted ?? \App\Services\Reports\OdWorkReportService::formatToTimingHours($taskRow->allocated_hours) }} hrs</strong></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $taskRow->status === 'Completed' ? 'success' : ($taskRow->status === 'InProgress' ? 'primary' : 'secondary') }} px-2 py-0.5 rounded-pill font-size-11">
                                    {{ $taskRow->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div>
                                    <span class="font-weight-bold font-size-13 {{ $isMaxTask || $isOverdue ? 'text-danger' : 'text-dark' }} d-block">
                                        {{ $taskRow->total_hours_formatted ?? \App\Services\Reports\OdWorkReportService::formatToTimingHours($taskRow->total_hours) }} hrs
                                    </span>
                                    @if($isOverdue)
                                    <span class="badge badge-soft-danger px-1.5 py-0.5 rounded font-size-10 font-weight-bold">
                                        <i class="mdi mdi-alert-circle mr-0.5"></i> +{{ $taskRow->variance_hours_formatted ?? \App\Services\Reports\OdWorkReportService::formatToTimingHours($taskRow->variance_hours) }}h Exceeded
                                    </span>
                                    @else
                                    <span class="badge badge-soft-success px-1.5 py-0.5 rounded font-size-10 font-weight-medium">
                                        <i class="mdi mdi-check mr-0.5"></i> Within Budget
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right" style="padding-right: 24px;">
                                <span class="badge badge-light border px-2 py-0.5 font-size-11">{{ $taskRow->log_count }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No task logs recorded in this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. Daily Work Rhythm & Every Day Task Spend Table --}}
    <div class="card border shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                    <i class="mdi mdi-calendar-clock text-primary mr-1"></i> Daily Work Rhythm & Task Log Audit
                </h5>
                <small class="text-muted">Click any row to inspect task breakdown, client name, and time spent on that day</small>
            </div>
            <span class="badge badge-soft-primary px-3 py-1 rounded-pill font-size-11 font-weight-bold">
                {{ $dailyWorkDays->count() }} Working Days
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="daily-work-rhythm-table" class="table table-modern table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px; width: 17%;">Date & Day</th>
                            <th style="width: 13%;">Shift Hours</th>
                            <th style="width: 15%;">Task Time Spent</th>
                            <th style="width: 12%;">Tasks Count</th>
                            <th style="width: 16%;">Day Closing Status</th>
                            <th style="width: 14%;">Target Status</th>
                            <th style="padding-right: 24px; width: 13%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyWorkDays->reverse() as $day)
                        <tr class="daily-rhythm-row" style="{{ $day->is_today ? 'background-color: #f0fdf4;' : '' }} cursor: pointer;" data-date="{{ $day->date }}" title="Click to view task details for this day">
                            <td style="padding-left: 24px;">
                                <span class="font-weight-bold text-dark d-block font-size-13">{{ $day->label }}</span>
                                <small class="text-muted font-size-11 font-weight-semibold">{{ $day->day_name }} {{ $day->is_today ? '(Today)' : '' }}</small>
                            </td>
                            <td>
                                @if($day->shift_hours > 0)
                                <span class="font-weight-semibold text-dark font-size-12"><i class="mdi mdi-clock-outline text-primary mr-1"></i>{{ \App\Services\Reports\OdWorkReportService::formatToTimingHours($day->shift_hours) }} hrs</span>
                                @else
                                <span class="text-muted font-size-11">—</span>
                                @endif
                            </td>
                            <td>
                                @if($day->task_hours > 0)
                                <span class="font-weight-bold font-size-13 {{ $day->task_hours >= 6 ? 'text-success' : 'text-primary' }}">
                                    <i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i>{{ \App\Services\Reports\OdWorkReportService::formatToTimingHours($day->task_hours) }} hrs
                                </span>
                                @else
                                <span class="text-muted font-size-11">0.00 hrs</span>
                                @endif
                            </td>
                            <td>
                                @if($day->task_count > 0)
                                <span class="badge badge-soft-info px-2 py-0.5 rounded-pill font-size-11 font-weight-bold">{{ $day->task_count }} Tasks</span>
                                @else
                                <span class="text-muted font-size-11">0</span>
                                @endif
                            </td>
                            <td>
                                @if($day->closing_status === 'Approved' || $day->closing_status === 'Submitted')
                                <span class="badge badge-soft-success px-2.5 py-1 rounded-pill font-size-11 font-weight-bold">
                                    <i class="mdi mdi-check-all mr-0.5"></i> {{ $day->closing_status }}
                                </span>
                                @elseif($day->closing_status === 'Not Submitted')
                                <span class="badge badge-soft-danger px-2.5 py-1 rounded-pill font-size-11 font-weight-bold">
                                    <i class="mdi mdi-close-circle mr-0.5"></i> Not Submitted
                                </span>
                                @else
                                <span class="badge badge-soft-light text-muted px-2.5 py-1 rounded-pill font-size-11">{{ $day->closing_status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($day->target_status === 'Met')
                                <span class="badge badge-soft-success px-2 py-0.5 rounded-pill font-size-11 font-weight-bold"><i class="mdi mdi-check"></i> Target Met</span>
                                @elseif($day->target_status === 'On Leave')
                                <span class="badge badge-soft-warning px-2 py-0.5 rounded-pill font-size-11 font-weight-bold">On Leave</span>
                                @else
                                <span class="badge badge-soft-danger px-2 py-0.5 rounded-pill font-size-11 font-weight-bold"><i class="mdi mdi-alert"></i> Under Target</span>
                                @endif
                            </td>
                            <td class="text-right" style="padding-right: 24px;">
                                @if($day->tasks && count($day->tasks) > 0)
                                <button type="button" class="btn btn-outline-primary btn-sm px-2.5 py-1 font-size-11 font-weight-semibold shadow-sm btn-open-day-tasks" data-date="{{ $day->date }}" style="border-radius: 8px;">
                                    <i class="mdi mdi-clipboard-text-clock mr-0.5"></i> Tasks ({{ count($day->tasks) }})
                                </button>
                                @else
                                <span class="text-muted font-size-11">No Logs</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No working day logs found for this date range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: Day Task Details --}}
    <div class="modal fade" id="dayTaskDetailsModal" tabindex="-1" role="dialog" aria-labelledby="dayTaskDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="mr-2.5 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 8px; width: 36px; height: 36px; font-size: 20px;">
                            <i class="mdi mdi-calendar-clock"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0" id="dayTaskDetailsModalLabel">
                                Daily Task Logs & Activity Audit
                            </h5>
                            <small class="text-muted" id="modal-day-subtitle">Date: —</small>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    {{-- Day Summary Bar --}}
                    <div class="p-3 mb-3 bg-light rounded-lg border d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                        <div>
                            <small class="text-muted font-size-11 text-uppercase font-weight-bold d-block">Employee</small>
                            <strong class="text-dark font-size-13">{{ $employee->name }}</strong>
                        </div>
                        <div>
                            <small class="text-muted font-size-11 text-uppercase font-weight-bold d-block">Task Time Spent</small>
                            <span class="badge badge-soft-primary px-2.5 py-1 rounded font-weight-bold font-size-12" id="modal-day-task-hours">0.00 hrs</span>
                        </div>
                        <div>
                            <small class="text-muted font-size-11 text-uppercase font-weight-bold d-block">Shift Duration</small>
                            <span class="badge badge-soft-info px-2.5 py-1 rounded font-weight-bold font-size-12" id="modal-day-shift-hours">0.00 hrs</span>
                        </div>
                        <div>
                            <small class="text-muted font-size-11 text-uppercase font-weight-bold d-block">Closing Status</small>
                            <span id="modal-day-closing-status" class="badge badge-soft-secondary px-2.5 py-1 rounded font-weight-bold font-size-12">—</span>
                        </div>
                    </div>

                    {{-- Tasks List Header --}}
                    <h6 class="font-weight-bold text-dark mb-3 font-size-13">
                        <i class="mdi mdi-format-list-checks text-primary mr-1"></i> Tasks Worked On That Day (<span id="modal-day-tasks-count">0</span>):
                    </h6>

                    {{-- Tasks Container --}}
                    <div id="modal-day-tasks-list" class="d-flex flex-column" style="gap: 12px;"></div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Active Projects / Assignments --}}
    @if($currentProjects->count() > 0)
    <div class="card border shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                <i class="mdi mdi-folder-outline text-primary mr-1"></i> Current Active Projects & Deliverables
            </h5>
        </div>
        <div class="card-body p-3">
            <div class="row">
                @foreach($currentProjects as $p)
                <div class="col-md-4 mb-3">
                    <div class="p-3 bg-light rounded-lg border border-light d-flex align-items-center justify-content-between">
                        <div>
                            <span class="font-weight-bold text-dark d-block font-size-13">{{ $p->name }}</span>
                            <small class="text-muted font-size-11">{{ $p->status }}</small>
                        </div>
                        <span class="badge badge-soft-primary px-2 py-1 rounded-pill font-size-10 font-weight-bold">Active</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        var selectedStart = "{{ $startDateStr }}";
        var selectedEnd = "{{ $endDateStr }}";

        $('#report_date_range_picker').daterangepicker({
            startDate: moment(selectedStart),
            endDate: moment(selectedEnd),
            locale: {
                format: 'YYYY-MM-DD'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Quarter': [moment().startOf('quarter'), moment().endOf('quarter')],
                'This Year': [moment().startOf('year'), moment().endOf('year')]
            }
        }, function(start, end) {
            selectedStart = start.format('YYYY-MM-DD');
            selectedEnd = end.format('YYYY-MM-DD');
            $('#filter_start_date').val(selectedStart);
            $('#filter_end_date').val(selectedEnd);
            $('#report_date_range_picker').val(selectedStart + ' - ' + selectedEnd);
        });

        $('#report_date_range_picker').val(moment(selectedStart).format('YYYY-MM-DD') + ' - ' + moment(selectedEnd).format('YYYY-MM-DD'));

        // Apply Filter Action
        $('#btnApplyDetailFilter').on('click', function() {
            var startVal = $('#filter_start_date').val() || selectedStart;
            var endVal = $('#filter_end_date').val() || selectedEnd;

            var url = new URL(window.location.href);
            url.searchParams.set('date_from', startVal);
            url.searchParams.set('date_to', endVal);
            url.searchParams.set('start_date', startVal);
            url.searchParams.set('end_date', endVal);
            window.location.href = url.toString();
        });

        // Reset Filter Action
        $('#btnResetDetailFilter').on('click', function() {
            var defaultStart = moment().startOf('month').format('YYYY-MM-DD');
            var defaultEnd = moment().format('YYYY-MM-DD');

            var url = new URL(window.location.href);
            url.searchParams.set('date_from', defaultStart);
            url.searchParams.set('date_to', defaultEnd);
            url.searchParams.set('start_date', defaultStart);
            url.searchParams.set('end_date', defaultEnd);
            window.location.href = url.toString();
        });

        if ($('#task-allocation-table').length) {
            $('#task-allocation-table').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                ordering: false,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                dom: '<"d-flex justify-content-between align-items-center p-3"<"font-size-12"l><"font-size-12"f>>rt<"d-flex justify-content-between align-items-center p-3"<"font-size-12"i><"font-size-12"p>>',
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });
        }

        if ($('#daily-work-rhythm-table').length) {
            $('#daily-work-rhythm-table').DataTable({
                pageLength: 15,
                lengthMenu: [15, 25, 50, 100],
                ordering: false,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                dom: '<"d-flex justify-content-between align-items-center p-3"<"font-size-12"l><"font-size-12"f>>rt<"d-flex justify-content-between align-items-center p-3"<"font-size-12"i><"font-size-12"p>>',
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });
        }

        // Open Day Task Modal Handler
        function openDayTaskModal(dayData) {
            if (!dayData) return;

            $('#modal-day-subtitle').text(dayData.label + ' (' + dayData.day_name + ')');
            
            // Format hours using timing format helper in JS
            function formatDecimalToTime(decimalVal) {
                var val = parseFloat(decimalVal) || 0;
                if (val <= 0) return '0.00';
                var hrs = Math.floor(val);
                var mins = Math.round((val - hrs) * 60);
                if (mins >= 60) { hrs += 1; mins = 0; }
                return hrs + '.' + (mins < 10 ? '0' + mins : mins);
            }

            var taskHoursFormatted = formatDecimalToTime(dayData.task_hours) + ' hrs';
            var shiftHoursFormatted = formatDecimalToTime(dayData.shift_hours) + ' hrs';

            $('#modal-day-task-hours').text(taskHoursFormatted);
            $('#modal-day-shift-hours').text(shiftHoursFormatted);
            $('#modal-day-closing-status').text(dayData.closing_status || 'Not Submitted');

            var tasks = dayData.tasks || [];
            $('#modal-day-tasks-count').text(tasks.length);

            if (tasks.length === 0) {
                $('#modal-day-tasks-list').html(`
                    <div class="p-4 text-center text-muted bg-light rounded border">
                        <i class="mdi mdi-information-outline font-size-24 text-muted d-block mb-1"></i>
                        No individual task log descriptions were recorded on this day.
                    </div>
                `);
            } else {
                var html = '';
                tasks.forEach(function(t, idx) {
                    var taskName = t.task_name || t.task_title || ('Task #' + (t.task_id || (idx + 1)));
                    var projName = t.project_name || 'Internal Project';
                    var clName = t.client_name || 'Internal Client';
                    var timeWindow = (t.starttime && t.endtime) ? `<span class="badge badge-light border px-2 py-1 font-size-11 mr-2"><i class="mdi mdi-clock-outline mr-0.5"></i> ${t.starttime} - ${t.endtime}</span>` : '';
                    var clientBadge = `<span class="badge badge-soft-primary px-2.5 py-1.5 rounded font-size-12 font-weight-bold mr-2"><i class="mdi mdi-domain mr-1"></i> Client: ${clName}</span>`;
                    var projectBadge = `<span class="badge badge-soft-info px-2.5 py-1.5 rounded font-size-12 font-weight-bold mr-2"><i class="mdi mdi-folder-outline mr-1"></i> Project: ${projName}</span>`;
                    var hoursFormatted = t.hours_formatted || (formatDecimalToTime(t.hours) + ' hrs');

                    html += `
                        <div class="p-3 bg-white rounded-lg border shadow-sm" style="border-left: 4px solid #4f46e5 !important;">
                            <div class="d-flex align-items-start justify-content-between flex-wrap mb-2">
                                <div class="mr-2">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge badge-secondary px-2 py-0.5 font-size-11 mr-2">Task #${t.task_id || (idx + 1)}</span>
                                        <span class="font-weight-bold text-dark font-size-14">${taskName}</span>
                                    </div>
                                </div>
                                <span class="badge badge-soft-success px-2.5 py-1.5 rounded-pill font-weight-bold font-size-12 mt-1 mt-sm-0">
                                    <i class="mdi mdi-timer-outline mr-0.5"></i> ${hoursFormatted} spent
                                </span>
                            </div>

                            <div class="d-flex align-items-center flex-wrap my-2">
                                ${projectBadge}
                                ${clientBadge}
                                ${timeWindow}
                            </div>

                            <div class="p-2.5 bg-light rounded text-dark font-size-12 mt-2" style="border-left: 3px solid #cbd5e1; white-space: pre-wrap; line-height: 1.5;">
                                <span class="text-muted font-weight-bold font-size-11 d-block mb-0.5"><i class="mdi mdi-note-text-outline mr-0.5"></i> Work Log Description:</span>
                                ${t.description || 'No detailed note provided.'}
                            </div>
                        </div>
                    `;
                });
                $('#modal-day-tasks-list').html(html);
            }

            $('#dayTaskDetailsModal').modal('show');
        }

        var dailyWorkData = @json($dailyWorkDays->keyBy('date'));

        $(document).on('click', '.btn-open-day-tasks', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var dateKey = $(this).data('date');
            var dayData = dailyWorkData ? dailyWorkData[dateKey] : null;
            if (dayData) {
                openDayTaskModal(dayData);
            }
        });

        $(document).on('click', '.daily-rhythm-row', function(e) {
            if ($(e.target).closest('button, a').length) return;
            var dateKey = $(this).data('date');
            var dayData = dailyWorkData ? dailyWorkData[dateKey] : null;
            if (dayData) {
                openDayTaskModal(dayData);
            }
        });

        // Explicit modal close click handler
        $(document).on('click', '#dayTaskDetailsModal [data-dismiss="modal"], #dayTaskDetailsModal [data-bs-dismiss="modal"], #dayTaskDetailsModal .close', function(e) {
            e.preventDefault();
            $('#dayTaskDetailsModal').modal('hide');
        });
    });
</script>
@endsection
