@extends('layouts.app')

@section('styles')
<style>
    /* Premium Stabilized Design for History View */
    .sticky-header-fallback {
        position: sticky;
        top: 70px !important;
        /* Offset for fixed navbar */
        z-index: 999 !important;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .animate-slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .timeline-scroll {
        max-height: 700px;
        overflow-y: auto;
        padding-right: 10px;
        scrollbar-width: thin;
        scrollbar-color: #556ee6 rgba(85, 110, 230, 0.05);
    }

    .timeline-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .timeline-scroll::-webkit-scrollbar-track {
        background: rgba(85, 110, 230, 0.05);
        border-radius: 10px;
    }

    .timeline-scroll::-webkit-scrollbar-thumb {
        background: #556ee6;
        border-radius: 10px;
    }

    .timeline-scroll::-webkit-scrollbar-thumb:hover {
        background: #344ec5;
    }

    .card {
        border-radius: 12px !important;
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #556ee6 0%, #344ec5 100%);
    }

    .bg-light-soft {
        background-color: #f8f9fa;
    }

    .text-primary {
        color: #556ee6 !important;
    }

    .bg-soft-primary {
        background-color: rgba(85, 110, 230, 0.1);
        color: #556ee6;
    }

    .bg-soft-success {
        background-color: rgba(52, 195, 143, 0.1);
        color: #34c38f;
    }

    .bg-soft-danger {
        background-color: rgba(244, 106, 106, 0.1);
        color: #f46a6a;
    }

    .bg-soft-warning {
        background-color: rgba(241, 180, 76, 0.1);
        color: #f1b44c;
    }

    .timeline-dot.pulse {
        animation: dot-pulse 2s infinite;
    }

    @keyframes dot-pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(85, 110, 230, 0.4);
        }

        70% {
            transform: scale(1.1);
            box-shadow: 0 0 0 10px rgba(85, 110, 230, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(85, 110, 230, 0);
        }
    }
</style>
@endsection

@section('content')
<div class="project-history-page min-h-screen pb-5">
    <!-- Header Summary Bar (Sticky) -->
    <!-- Sub Header Below Topbar -->
    <div class="sticky-header-fallback px-4 py-2 border-bottom">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-7 d-flex align-items-center">
                    <a href="{{ url('/projects') }}" class="btn btn-sm btn-light rounded-circle mr-3" title="Back to Projects">
                        <i class="mdi mdi-arrow-left font-size-18"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 font-weight-bold text-dark mr-2">{{ $project->project_name }}</h5>
                            <span class="badge badge-pill @if($project->status == 'ToDo') badge-danger @elseif($project->status == 'InProgress') badge-info @else badge-success @endif" style="font-size: 10px; padding: 4px 8px;">
                                {{ $project->status }}
                            </span>
                        </div>
                        <p class="mb-0 text-muted small"><i class="mdi mdi-history mr-1"></i> Project Details, Timeline & Audit Trail</p>
                    </div>
                </div>
                <div class="col-md-5 d-flex justify-content-end align-items-center">
                    @php
                    $totalTasks = $project->tasks->count();
                    $completedTasksCount = $project->tasks->where('status', 'Completed')->count();
                    $progress = $totalTasks > 0 ? round(($completedTasksCount / $totalTasks) * 100) : 0;
                    $timeline = $project->timeline_performance;
                    $developerStats = $project->developer_stats;
                    $timeSpentFormatted = $project->total_time_spent_formatted;
                    @endphp
                    <div class="d-none d-md-flex align-items-center mr-3">
                        <div class="text-right mr-2">
                            <div class="text-muted small font-weight-bold" style="font-size: 10px;">PROGRESS</div>
                            <div class="font-weight-bold text-primary" style="font-size: 13px;">{{ $progress }}%</div>
                        </div>
                        <div class="progress rounded-pill" style="width: 80px; height: 6px; background-color: #f1f1f1;">
                            <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    <a href="{{ url('/projects/taskboard/' . base64_encode($project->id)) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm font-weight-medium">
                        <i class="mdi mdi-view-week-outline mr-1"></i> Open Taskboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Timeline Performance Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid @if($timeline['type'] == 'under_timeline') #34c38f @elseif($timeline['type'] == 'over_timeline' || $timeline['type'] == 'overdue') #f46a6a @else #556ee6 @endif !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm mr-3 flex-shrink-0">
                                    <span class="avatar-title rounded-circle {{ $timeline['bg_class'] }} {{ $timeline['text_color'] }} font-size-20">
                                        <i class="mdi {{ $timeline['icon'] }}"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $timeline['label'] }}</h6>
                                        <span class="badge {{ $timeline['badge'] }} px-2 py-0.5 font-size-11">{{ $timeline['status'] }}</span>
                                    </div>
                                    <p class="mb-0 text-muted font-size-13 mt-0.5">
                                        {{ $timeline['detail'] }}
                                        <span class="mx-1 text-light">|</span>
                                        <span class="text-dark font-weight-medium">Est. Start:</span> {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}
                                        <span class="mx-1 text-light">•</span>
                                        <span class="text-dark font-weight-medium">Deadline:</span> {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}
                                        @if($project->act_end_date)
                                        <span class="mx-1 text-light">•</span>
                                        <span class="text-dark font-weight-medium">Delivered:</span> <span class="text-success font-weight-bold">{{ \Carbon\Carbon::parse($project->act_end_date)->format('d M Y, h:i A') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <div class="text-right px-3 py-1 bg-light rounded border">
                                    <span class="text-muted font-size-10 d-block text-uppercase font-weight-bold">Total Time Spent</span>
                                    <strong class="text-primary font-size-14"><i class="mdi mdi-clock-outline mr-0.5"></i>{{ $timeSpentFormatted }}</strong>
                                </div>
                                <div class="text-right px-3 py-1 bg-light rounded border">
                                    <span class="text-muted font-size-10 d-block text-uppercase font-weight-bold">Working Devs</span>
                                    <strong class="text-dark font-size-14"><i class="mdi mdi-account-group mr-0.5 text-primary"></i>{{ $developerStats->count() }} Dev(s)</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- LEFT SECTION (70%) -->
            <div class="col-xl-8 col-lg-7">

                <!-- 1. Project Overview Card -->
                <div class="card mb-4 shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-light border-bottom-0 py-3 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark" style="letter-spacing: 1px;">
                            <i class="mdi mdi-view-dashboard-outline text-primary mr-2"></i> Project Details & Specifications
                        </h6>
                        <span class="badge badge-soft-primary px-3 py-2 rounded-pill font-weight-bold uppercase">
                            {{ $project->status }}
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Client</label>
                                <p class="mb-0 font-weight-bold text-dark">
                                    <i class="mdi mdi-office-building text-primary mr-1"></i>{{ $project->clients->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Category</label>
                                <p class="mb-0 font-weight-bold text-dark">
                                    <span class="badge badge-soft-info px-2 py-1"><i class="mdi mdi-tag-outline mr-0.5"></i>{{ $project->projectCategory->category ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Start Date & Time</label>
                                <p class="mb-0 font-weight-bold text-dark">
                                    <i class="mdi mdi-calendar-start text-primary mr-1"></i>{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y, h:i A') }}
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Deadline / Due Date</label>
                                @php $isOverdue = \Carbon\Carbon::parse($project->end_date)->isPast() && $project->status != 'Completed'; @endphp
                                <p class="mb-0 font-weight-bold {{ $isOverdue ? 'text-danger' : 'text-dark' }}">
                                    <i class="mdi mdi-calendar-clock {{ $isOverdue ? 'text-danger' : 'text-danger' }} mr-1"></i>{{ \Carbon\Carbon::parse($project->end_date)->format('d M Y, h:i A') }}
                                    @if($isOverdue) <i class="mdi mdi-alert-circle ml-0.5" title="Overdue"></i> @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Total Time Spent</label>
                                <p class="mb-0 font-weight-bold text-primary font-size-14">
                                    <i class="mdi mdi-timer mr-1"></i>{{ $timeSpentFormatted }}
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Actual Start</label>
                                <p class="mb-0 font-weight-bold text-dark">
                                    @if($project->act_start_date)
                                    <i class="mdi mdi-calendar-check text-success mr-1"></i>{{ \Carbon\Carbon::parse($project->act_start_date)->format('d M Y, h:i A') }}
                                    @else
                                    <span class="text-muted font-weight-normal">Not Started</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Completed Date & Time</label>
                                <p class="mb-0 font-weight-bold {{ $project->act_end_date ? 'text-success' : 'text-dark' }}">
                                    @if($project->act_end_date)
                                    <i class="mdi mdi-check-circle-outline text-success mr-1"></i>{{ \Carbon\Carbon::parse($project->act_end_date)->format('d M Y, h:i A') }}
                                    @else
                                    <span class="text-muted font-weight-normal">In Progress</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Created By</label>
                                <p class="mb-0 font-weight-bold text-dark">
                                    <i class="mdi mdi-account-edit mr-1 text-muted"></i>{{ $project->creator->name ?? 'Admin' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 10px;">Overall Project Completion</span>
                                <span class="font-weight-bold text-primary">{{ $progress }}%</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 10px; background-color: #f1f1f1;">
                                <div class="progress-bar bg-gradient-primary rounded-pill shadow-sm" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Involved Developers & Team Contribution Card -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark">
                            <i class="mdi mdi-account-group-outline text-primary mr-2"></i> Involved Developers & Work Contribution ({{ $developerStats->count() }})
                        </h6>
                        <span class="badge badge-soft-primary px-3 py-1 rounded-pill font-size-11">
                            Team Breakdown
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="thead-custom-teal">
                                    <tr class="text-uppercase font-weight-bold text-muted" style="font-size: 10px;">
                                        <th class="border-0 px-4">Developer</th>
                                        <th class="border-0 text-center">Tasks Assigned</th>
                                        <th class="border-0 text-center">Completed</th>
                                        <th class="border-0 text-center">Time Spent</th>
                                        <th class="border-0">Progress</th>
                                        <th class="border-0 text-center pr-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($developerStats as $devStat)
                                    @php $devUser = $devStat['user']; @endphp
                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs mr-2">
                                                    @if($devUser->profile)
                                                    <img src="{{ asset('storage/' . $devUser->profile) }}" alt="{{ $devUser->name }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                                    @else
                                                    <img src="{{ Avatar::create($devUser->name)->toBase64() }}" alt="{{ $devUser->name }}" class="rounded-circle border" style="width: 32px; height: 32px;">
                                                    @endif
                                                </div>
                                                <div>
                                                    <a href="{{ url('/reports/employee/' . $devUser->id) }}" class="font-weight-bold text-dark d-block font-size-13" title="View Employee Audit">
                                                        {{ $devUser->name }}
                                                    </a>
                                                    <span class="badge badge-light border text-muted font-size-10">{{ $devUser->roles->pluck('name')->first() ?? 'Developer' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold text-dark font-size-13">
                                            {{ $devStat['tasks_count'] }}
                                        </td>
                                        <td class="text-center font-weight-bold text-success font-size-13">
                                            {{ $devStat['completed_tasks_count'] }}
                                        </td>
                                        <td class="text-center font-weight-bold text-primary font-size-13">
                                            <i class="mdi mdi-clock-outline mr-0.5"></i>{{ $devStat['time_formatted'] }}
                                        </td>
                                        <td style="width: 140px;">
                                            <div class="d-flex align-items-center">
                                                <span class="font-size-11 font-weight-bold mr-2">{{ $devStat['progress'] }}%</span>
                                                <div class="progress flex-grow-1" style="height: 6px; border-radius: 6px; background: #f0f2f8;">
                                                    <div class="progress-bar bg-success" style="width: {{ $devStat['progress'] }}%; border-radius: 6px;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center pr-4">
                                            @if($devStat['is_active_now'])
                                            <span class="badge badge-soft-danger px-2 py-1 font-size-11">
                                                <i class="mdi mdi-record mr-1" style="animation: rec-blink 1s ease-in-out infinite;"></i> Working Now
                                            </span>
                                            @else
                                            <span class="badge badge-light border text-muted px-2 py-1 font-size-11">
                                                Active Dev
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-account-off-outline font-size-24 d-block mb-1"></i>
                                            No developers involved in this project yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 3. Task Summary Counters -->
                <div class="row">
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-primary p-2 rounded text-primary mr-2">
                                    <i class="mdi mdi-checkbox-multiple-marked-outline font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Total Tasks</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ $project->tasks->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-success p-2 rounded text-success mr-2">
                                    <i class="mdi mdi-check-decagram font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Completed</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ $project->tasks->where('status', 'Completed')->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-warning p-2 rounded text-warning mr-2">
                                    <i class="mdi mdi-clock-fast font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Pending</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ $project->tasks->whereIn('status', ['Pending', 'InProgress', 'ToDo'])->count() }}</h4>
                        </div>
                    </div>
                    @php $overdueTasks = $project->tasks->filter(fn($t) => \Carbon\Carbon::parse($t->enddate)->isPast() && $t->status != 'Completed')->count(); @endphp
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100 {{ $overdueTasks > 0 ? 'bg-soft-danger' : '' }}" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-danger p-2 rounded text-danger mr-2">
                                    <i class="mdi mdi-alert-circle-outline font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Overdue</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-danger">{{ $overdueTasks }}</h4>
                        </div>
                    </div>
                </div>

                <!-- 4. Task Analytics Table -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark">
                            <i class="mdi mdi-format-list-bulleted text-primary mr-2"></i> Project Task Breakdown & Time Logs
                        </h6>
                        <span class="badge badge-light rounded-pill">{{ $project->tasks->count() }} Tasks</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-custom-teal">
                                    <tr class="text-uppercase font-weight-bold text-muted" style="font-size: 10px;">
                                        <th class="border-0 px-4">Task Information</th>
                                        <th class="border-0">Assignee</th>
                                        <th class="border-0">Timeline (Est & Actual)</th>
                                        <th class="border-0">Time Spend</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0 text-right">Progress</th>
                                        <th class="border-0 text-center pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->tasks as $task)
                                    @php $isTOverdue = \Carbon\Carbon::parse($task->enddate)->isPast() && $task->status != 'Completed'; @endphp
                                    <tr class="{{ $isTOverdue ? 'table-danger' : '' }}">
                                        <td class="px-4">
                                            <a href="{{ url('/projects/task/' . base64_encode($task->id) . '/history') }}" class="font-weight-bold text-dark d-block" title="View Task Details">
                                                {{ $task->title }}
                                            </a>
                                            <span class="badge @if($task->priority == 'Low') badge-soft-success @elseif($task->priority == 'Medium') badge-soft-warning @else badge-soft-danger @endif font-size-10">
                                                {{ $task->priority }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($task->user && $task->user->profile)
                                                <img src="{{ asset('storage/' . $task->user->profile) }}" alt="" class="rounded-circle mr-1.5" style="width: 24px; height: 24px; object-fit: cover;">
                                                @else
                                                <img src="{{ Avatar::create($task->user->name ?? 'Unassigned')->toBase64() }}" alt="" class="rounded-circle mr-1.5" style="width: 24px; height: 24px;">
                                                @endif
                                                <span class="font-size-12 text-muted font-weight-medium">{{ $task->user->name ?? 'Unassigned' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="font-size-11">
                                                <div><span class="text-muted">Start:</span> <span class="font-weight-semibold">{{ \Carbon\Carbon::parse($task->startdate)->format('d M Y, h:i A') }}</span></div>
                                                <div><span class="text-muted">Due:</span> <span class="font-weight-bold {{ $isTOverdue ? 'text-danger' : 'text-dark' }}">{{ \Carbon\Carbon::parse($task->enddate)->format('d M Y, h:i A') }}</span></div>
                                                @if($task->act_enddate)
                                                <div class="text-success"><span class="text-muted">End:</span> {{ \Carbon\Carbon::parse($task->act_enddate)->format('d M Y, h:i A') }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                            $totalMinutes = round(($task->logs->whereNotNull('time_spend')->sum('time_spend') ?? 0) * 60);
                                            $h = floor($totalMinutes / 60);
                                            $m = $totalMinutes % 60;
                                            $timeSpentFormattedTask = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                            @endphp
                                            <span class="badge badge-light border text-dark font-weight-bold font-size-11">
                                                <i class="mdi mdi-clock-outline text-primary mr-0.5"></i>{{ $timeSpentFormattedTask }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                            $tColor = 'secondary';
                                            if($task->status == 'Completed') $tColor = 'success';
                                            elseif($task->status == 'InProgress') $tColor = 'primary';
                                            elseif($task->status == 'ToDo') $tColor = 'danger';
                                            @endphp
                                            <span class="badge badge-soft-{{ $tColor }} px-2 py-1">{{ $task->status }}</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="font-weight-bold mr-2">{{ $task->progress }}%</span>
                                                <div class="progress" style="width: 50px; height: 5px; border-radius: 5px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $task->progress }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center pr-4">
                                            @if(!auth()->user()->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant']))
                                            <div class="btn-group">
                                                <a href="{{ url('/projects/task/' . base64_encode($task->id) . '/history') }}" class="btn btn-soft-primary btn-sm rounded-pill mr-1" title="View Task Details">
                                                    <i class="mdi mdi-eye-outline"></i>
                                                </a>
                                                <button type="button" class="btn btn-soft-warning btn-sm rounded-pill nudge-btn" data-task-id="{{ $task->id }}" title="Request Progress Update (Nudge)">
                                                    <i class="mdi mdi-bell-ring-outline"></i>
                                                </button>
                                            </div>
                                            @else
                                            <a href="{{ url('/projects/task/' . base64_encode($task->id) . '/history') }}" class="btn btn-soft-primary btn-sm rounded-pill" title="View Task Details">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No tasks found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 5. Project Documents Gallery -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark">
                            <i class="mdi mdi-attachment text-success mr-2"></i> Project Documents
                        </h6>
                        <span class="badge badge-light rounded-pill">{{ $project->documents->count() }} Files</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @forelse($project->documents as $doc)
                            @php
                            $ext = strtolower($doc->file_type);
                            $icon = 'mdi-file-outline';
                            $color = 'primary';
                            if(in_array($ext, ['jpg','jpeg','png','gif'])) { $icon = 'mdi-file-image'; $color = 'info'; }
                            elseif($ext == 'pdf') { $icon = 'mdi-file-pdf-box'; $color = 'danger'; }
                            elseif(in_array($ext, ['doc','docx'])) { $icon = 'mdi-file-word'; $color = 'primary'; }
                            @endphp
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center p-2 rounded border border-light bg-light-soft">
                                    <div class="avatar-sm mr-3">
                                        <span class="avatar-title rounded bg-soft-{{ $color }} text-{{ $color }} font-size-18">
                                            <i class="mdi {{ $icon }}"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="font-size-12 mb-1 text-truncate"><a href="{{ route('documents.download', $doc->id) }}" class="text-dark">{{ $doc->original_name }}</a></h5>
                                        <p class="text-muted font-size-10 mb-0">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-4 text-muted">No documents found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION (30%) -->
            <div class="col-xl-4 col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark">
                            <i class="mdi mdi-history text-primary mr-2"></i> Full Activity Trace
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline-scroll pr-2">
                            @php
                            $allHistories = $project->histories->merge($project->tasks->flatMap(function($task) {
                            return $task->histories;
                            }))->sortByDesc('created_at');
                            @endphp

                            @forelse($allHistories as $history)
                            <div class="position-relative pl-4 pb-4 border-left ml-2">
                                <!-- Dot -->
                                <div class="timeline-dot pulse position-absolute" style="left: -7px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #556ee6; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 2;"></div>

                                <div class="card border-0 shadow-none bg-light-soft mb-0 p-3 hover-lift" style="border-radius: 12px;">
                                    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap" style="gap: 4px;">
                                        <div class="badge badge-soft-primary px-2 py-1 font-size-10 text-uppercase">{{ $history->status ?? 'UPDATE' }}</div>
                                        <span class="badge badge-light border text-muted font-size-10 font-weight-normal">
                                            <i class="mdi mdi-clock-outline mr-0.5 text-primary"></i>{{ \Carbon\Carbon::parse($history->created_at)->format('d M Y, h:i A') }}
                                        </span>
                                    </div>

                                    <p class="mb-1 text-dark font-size-12 font-weight-medium">{{ $history->comments }}</p>

                                    <div class="d-flex align-items-center mt-2">
                                        <div class="avatar-xs mr-2" style="width: 20px; height: 20px;">
                                            <img src="{{ Avatar::create($history->user->name ?? 'System')->toBase64() }}" class="rounded-circle img-fluid">
                                        </div>
                                        <span class="text-muted font-size-11 font-weight-medium">{{ $history->user->name ?? 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="mdi mdi-history text-muted font-size-24 d-block mb-2"></i>
                                <p class="text-muted small">No history records found.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inject Project Title into Main Navbar Header
        const projectTitle = "{{ $project->project_name }}";
        const titleHtml = `
            <div class="d-none d-lg-flex align-items-center ml-3">
                <div style="height: 20px; width: 1.5px; background: rgba(0,0,0,0.08); margin: 0 15px;"></div>
                <h4 class="mb-0 font-weight-bold text-dark" style="font-size: 15px; letter-spacing: 0.5px;">
                    ${projectTitle}
                    <span class="badge badge-soft-primary ml-2 px-2" style="font-size: 9px; vertical-align: middle; background-color: rgba(85, 110, 230, 0.1); color: #556ee6;">HISTORY</span>
                </h4>
            </div>
        `;

        // Append after the vertical menu button or brand box
        if ($('#vertical-menu-btn').length) {
            $('#vertical-menu-btn').after(titleHtml);
        } else if ($('.navbar-brand-box').length) {
            $('.navbar-brand-box').after(titleHtml);
        }

        // Nudge functionality
        $('.nudge-btn').on('click', function() {
            const btn = $(this);
            const taskId = btn.data('task-id');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i>');

            $.ajax({
                url: "{{ url('/projects/tasks/nudge') }}/" + taskId,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error("Failed to send nudge. Please try again.");
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
@endsection
