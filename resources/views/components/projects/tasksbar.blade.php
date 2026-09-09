@extends('layouts.app')
@section('content')
<style>
    .highlight-task {
        border: 2px solid #556ee6 !important;
        box-shadow: 0 0 15px rgba(85, 110, 230, 0.4) !important;
        animation: highlight-pulse 2s infinite !important;
        z-index: 10;
        position: relative;
    }

    @keyframes highlight-pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 15px rgba(85, 110, 230, 0.4);
        }

        50% {
            transform: scale(1.02);
            box-shadow: 0 0 25px rgba(85, 110, 230, 0.6);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 15px rgba(85, 110, 230, 0.4);
        }
    }

    /* ── Task Timer Widget ── */
    .task-timer-widget {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(85, 110, 230, 0.08), rgba(85, 110, 230, 0.04));
        border: 1px solid rgba(85, 110, 230, 0.15);
        margin-top: 8px;
        transition: all 0.3s ease;
    }

    .task-timer-widget.timer-running {
        background: linear-gradient(135deg, rgba(244, 67, 54, 0.08), rgba(244, 67, 54, 0.04));
        border-color: rgba(244, 67, 54, 0.25);
        animation: timer-glow 2s ease-in-out infinite;
    }

    @keyframes timer-glow {

        0%,
        100% {
            box-shadow: 0 0 4px rgba(244, 67, 54, 0.15);
        }

        50% {
            box-shadow: 0 0 12px rgba(244, 67, 54, 0.3);
        }
    }

    .timer-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        color: #fff;
    }

    .timer-btn.btn-start {
        background: linear-gradient(135deg, #34c759, #30d158);
    }

    .timer-btn.btn-start:hover {
        background: linear-gradient(135deg, #30d158, #28b84c);
        transform: scale(1.1);
    }

    .timer-btn.btn-pause {
        background: linear-gradient(135deg, #f44336, #e53935);
    }

    .timer-btn.btn-pause:hover {
        background: linear-gradient(135deg, #e53935, #d32f2f);
        transform: scale(1.1);
    }

    .timer-display {
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
        font-weight: 700;
        color: #495057;
        min-width: 60px;
        letter-spacing: 0.5px;
    }

    .timer-running .timer-display {
        color: #f44336;
    }

    .timer-rec-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f44336;
        animation: rec-blink 1s ease-in-out infinite;
    }

    @keyframes rec-blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.2;
        }
    }

    .timer-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #868e96;
        font-weight: 600;
    }

    .timer-running .timer-label {
        color: #f44336;
    }

    /* ── Compact Small Timer for Kanban Footer ── */
    .task-timer-widget.small-timer {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 12px;
        margin-top: 0;
        background: linear-gradient(135deg, rgba(85, 110, 230, 0.08), rgba(85, 110, 230, 0.04));
        border: 1px solid rgba(85, 110, 230, 0.15);
        box-shadow: none;
    }

    .task-timer-widget.small-timer.timer-running {
        background: linear-gradient(135deg, rgba(244, 67, 54, 0.08), rgba(244, 67, 54, 0.04));
        border-color: rgba(244, 67, 54, 0.25);
    }

    .task-timer-widget.small-timer .timer-display {
        font-size: 11px;
        min-width: unset;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .task-timer-widget.small-timer .timer-btn {
        width: 20px;
        height: 20px;
        font-size: 11px;
    }

    .task-timer-widget.small-timer .timer-rec-dot {
        width: 6px;
        height: 6px;
    }

    .task-timer-widget.small-timer .timer-label {
        font-size: 8px;
        font-weight: 700;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center">
                    <a href="{{ url('/projects') }}" class="btn-back mr-3" style="text-decoration: none;">
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                    <h4 class="mb-0 font-weight-bold text-dark mr-3">{{ $project->project_name }}</h4>
                    <span class="badge badge-pill @if($project->status == 'ToDo') badge-danger @elseif($project->status == 'InProgress') badge-info @else badge-success @endif mr-3" style="padding: 6px 12px; font-size: 11px;">
                        {{ $project->status }}
                    </span>
                    @if(Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
                    <button type="button" class="btn btn-sm btn-success btn_project_status px-3 ml-2" projectid="{{ $project->id }}" status="{{ $project->status }}">
                        <i class="mdi mdi-check-decagram mr-1"></i> Change Status
                    </button>
                    @endif
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/projects') }}">Projects</a></li>
                        <li class="breadcrumb-item active">Task Board</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @php
        $totalProjectTasks = $project->tasks->count();
        $todoCount = count($todo);
        $inProgressCount = count($inprocess);
        $completedCount = count($completed);
        $projectProgress = $totalProjectTasks > 0 ? round(($completedCount / $totalProjectTasks) * 100) : 0;
        $workingDevs = $project->working_devs;
        $timeline = $project->timeline_performance;
        $timeSpentFormatted = $project->total_time_spent_formatted;
    @endphp

    <!-- Project Details & Overview Banner on Taskboard -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border shadow-sm mb-2" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <!-- 1. Client & Category -->
                        <div class="col-xl-3 col-lg-3 col-md-6 mb-2 mb-lg-0 border-right">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm mr-3 flex-shrink-0">
                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-18">
                                        <i class="mdi mdi-office-building"></i>
                                    </span>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-muted mb-0 small text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Client & Category</p>
                                    <h6 class="text-dark font-weight-bold mb-1 text-truncate" title="{{ $project->clients->name ?? 'No Client' }}">
                                        {{ $project->clients->name ?? 'No Client' }}
                                    </h6>
                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                        <span class="badge badge-soft-info px-2 py-0.5 font-size-11">
                                            <i class="mdi mdi-tag-outline mr-0.5"></i>{{ $project->projectCategory->category ?? 'General' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Timeline (Start & End Date + Timeline Performance) -->
                        <div class="col-xl-3 col-lg-3 col-md-6 mb-2 mb-lg-0 border-right">
                            <p class="text-muted mb-1 small text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                <i class="mdi mdi-calendar-range mr-1 text-primary"></i> Timeline Schedule
                            </p>
                            <div class="d-flex align-items-center justify-content-between font-size-12 mb-1">
                                <span class="text-muted font-size-11">Start: <strong class="text-dark">{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</strong></span>
                                <span class="text-muted font-size-11">Due: <strong class="text-dark">{{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</strong></span>
                            </div>
                            <div>
                                <span class="badge {{ $timeline['badge'] }} px-2 py-1 font-size-11 font-weight-semibold">
                                    <i class="mdi {{ $timeline['icon'] }} mr-1"></i>{{ $timeline['label'] }} ({{ $timeline['detail'] }})
                                </span>
                            </div>
                        </div>

                        <!-- 3. Total Tasks & Progress -->
                        <div class="col-xl-3 col-lg-3 col-md-6 mb-2 mb-lg-0 border-right">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <p class="text-muted mb-0 small text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-checkbox-marked-circle-outline mr-1 text-primary"></i> Total Tasks: <strong class="text-dark">{{ $totalProjectTasks }}</strong>
                                </p>
                                <span class="font-weight-bold text-primary font-size-12">{{ $projectProgress }}%</span>
                            </div>
                            <div class="progress mb-1.5" style="height: 6px; border-radius: 6px; background-color: #f0f2f8;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $projectProgress }}%; border-radius: 6px;"></div>
                            </div>
                            <div class="d-flex align-items-center font-size-11 text-muted justify-content-between flex-wrap" style="gap: 4px;">
                                <span class="badge badge-soft-danger px-1.5 py-0.5">ToDo: {{ $todoCount }}</span>
                                <span class="badge badge-soft-warning px-1.5 py-0.5">In Progress: {{ $inProgressCount }}</span>
                                <span class="badge badge-soft-success px-1.5 py-0.5">Done: {{ $completedCount }}</span>
                                <span class="badge badge-light border text-dark px-1.5 py-0.5" title="Total Time Spent"><i class="mdi mdi-clock-outline text-primary mr-0.5"></i>{{ $timeSpentFormatted }}</span>
                            </div>
                        </div>

                        <!-- 4. Working Devs & Quick Details Link -->
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <p class="text-muted mb-0 small text-uppercase font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-account-group-outline mr-1 text-primary"></i> Working Devs ({{ $workingDevs->count() }})
                                </p>
                                <a href="{{ url('/projects/' . base64_encode($project->id) . '/history') }}" class="btn btn-sm btn-soft-primary px-2 py-0.5 font-size-11" title="View Full Project History & Details">
                                    <i class="mdi mdi-eye-outline mr-1"></i> Details
                                </a>
                            </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                @forelse($workingDevs->take(5) as $dev)
                                    <div class="d-inline-flex align-items-center" title="{{ $dev->name }} ({{ $dev->roles->pluck('name')->first() ?? 'Developer' }})">
                                        @if($dev->profile)
                                            <img src="{{ asset('storage/' . $dev->profile) }}" alt="{{ $dev->name }}" class="rounded-circle border border-white shadow-sm" style="width: 28px; height: 28px; object-fit: cover;" data-toggle="tooltip" data-placement="top" title="{{ $dev->name }}">
                                        @else
                                            <img src="{{ Avatar::create($dev->name)->toBase64() }}" alt="{{ $dev->name }}" class="rounded-circle border border-white shadow-sm" style="width: 28px; height: 28px;" data-toggle="tooltip" data-placement="top" title="{{ $dev->name }}">
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-muted font-size-11 italic"><i class="mdi mdi-account-off-outline mr-1"></i>No devs assigned</span>
                                @endforelse
                                @if($workingDevs->count() > 5)
                                    <span class="avatar-title rounded-circle bg-light border text-muted font-size-10 font-weight-bold" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;" data-toggle="tooltip" data-placement="top" title="+{{ $workingDevs->count() - 5 }} more devs">
                                        +{{ $workingDevs->count() - 5 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div id="kanbanCustomBoard" class="js-kanban">
            <div class="kanban-container">
                <div class="kanban-board kanban-danger">

                    <header class="kanban-board-header kanban-danger">
                        <div class="kanban-title-board d-flex" style="justify-content: space-between">
                            <div class="kanban-title-content">
                                <h6 class="title">ToDo</h6>
                                <span class="count"> {{ count($todo) }}</span>
                            </div>
                            @if($project->status != 'Completed' && Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
                            <span projectid="{{ $project->id }}" class="todos-task-add add-task"><i class="mdi mdi-plus-outline"></i></span>
                            @endif
                        </div>
                    </header>

                    <main class="kanban-drag" data-status="ToDo">

                        @foreach ($todo as $items)
                        <div class="kanban-item task-body {{ request('task_id') == $items->id ? 'highlight-task' : '' }}" data-id="{{ $items->id }}" data-url="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}" style="cursor: pointer;">
                            <div class="kanban-item-title">
                                <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                    <h6 class="title c-p">
                                        @if( $items->priority == "Low")
                                        <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority"></i>
                                        @elseif( $items->priority == "Medium")
                                        <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority"></i>
                                        @else
                                        <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority"></i>
                                        @endif
                                        {{ Str::limit($items->title, 28) }}
                                    </h6>
                                </a>
                                @if($project->status != 'Completed')
                                <ul class="task-action">
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" currentstatus="{{ $items->status }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                            <i class="mdi mdi-arrow-left-right-bold"></i>
                                        </a>
                                    </li>
                                    @if(Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" class="edittask" href="javascript:void(0);" title="Edit Task">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                    </li>
                                    <li class="task-edit">
                                        <form method="post" action="{{ route('tasks.destroy',[$items->id]) }}" onsubmit="return confirmation();" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" href="javascript:void(0)" title="Delete Task">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                                @endif
                            </div>


                            <div class="kanban-item-text c-m">
                                {!! Str::limit($items->description, 120) !!}

                                <div class="task-schedule-time">
                                    <span class="">
                                        <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time"></i>
                                        {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                    </span>
                                    @if($items->status != 'COMPLETED')
                                    <span class="small light-danger-bg p-1 rounded" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="mdi mdi-clock-outline"></i>
                                        @if( \Carbon\Carbon::parse($items->enddate)->gt(\Carbon\Carbon::now()))
                                        {{ \Carbon\Carbon::parse($items->enddate)->diffForhumans(null, true) }} Left
                                        @else
                                        {{ \Carbon\Carbon::parse($items->enddate)->diffForhumans(null, true) }} Over
                                        @endif
                                    </span>
                                    @else
                                    <span class="small bg-success p-1 rounded">
                                        {{ $items->status }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="task-card-footer">
                                <div class="project-members d-flex align-items-center justify-content-between" style="gap: 10px;">
                                    <div class="project-matrix-group-divs flex-grow-1 mr-2" style="margin-bottom: 0;">
                                        <span class="project-metrics__metric-group-item__title project-matrix-group-items mb-0">
                                            Progress
                                        </span>
                                        <div class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm mb-0" style="flex-grow: 1;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $items->progress }}%"
                                                aria-valuenow="{{ $items->progress }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="project-matrix-group-items project-metrics__metric-group-item__value mb-0">
                                            {{ $items->progress }} %
                                        </span>
                                    </div>

                                    <ul class="project-users mb-0" style="padding-left: 0; margin-left: 0 !important; min-width: 25px; display: flex; justify-content: flex-end; flex-shrink: 0;">
                                        <li class="cursor">
                                            @if ($items->user->profile)
                                            <img title="{{ $items->user->name }}" src="{{ asset('storage/'. $items->user->profile )}}" style="margin-left: 0 !important;">
                                            @else
                                            <img title="{{ $items->user->name }}" src="{{ Avatar::create($items->user->name)->toBase64()  }}" style="margin-left: 0 !important;">
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        @endforeach
                    </main>
                </div>
                {{-- INPROGRESS --}}
                <div class="kanban-board">
                    <header class="kanban-board-header kanban-primary">
                        <div class="kanban-title-board">
                            <div class="kanban-title-content">
                                <h6 class="title">In Progress</h6>
                                <span class="count">{{ count($inprocess) }}</span>
                            </div>
                        </div>
                    </header>
                    <main class="kanban-drag" data-status="InProgress">
                        @foreach ($inprocess as $items)
                        <div class="kanban-item task-body {{ request('task_id') == $items->id ? 'highlight-task' : '' }}" data-id="{{ $items->id }}" data-url="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}" style="cursor: pointer;">
                            <div class="kanban-item-title">
                                <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                    <h6 class="title c-p">
                                        @if( $items->priority == "Low")
                                        <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority"></i>
                                        @elseif( $items->priority == "Medium")
                                        <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority"></i>
                                        @else
                                        <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority"></i>
                                        @endif
                                        {{ Str::limit($items->title, 28) }}
                                    </h6>
                                </a>
                                <ul class="task-action">
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" currentstatus="{{ $items->status }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                            <i class="mdi mdi-arrow-left-right-bold"></i>
                                        </a>
                                    </li>
                                    @if(Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" class="edittask" href="javascript:void(0);" title="Edit Task">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                    </li>
                                    <li class="task-edit">
                                        <form method="post" action="{{ route('tasks.destroy',[$items->id]) }}" onsubmit="return confirmation();" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" href="javascript:void(0)" title="Delete Task">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>


                            <div class="kanban-item-text c-m">
                                {!! Str::limit($items->description, 120) !!}

                                <div class="task-schedule-time d-flex align-items-center justify-content-between flex-wrap" style="gap: 5px; margin-top: 5px;">
                                    <span class="">
                                        <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time"></i>
                                        {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                    </span>
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                        $totalMinutes = round(($items->total_time ?? 0) * 60);
                                        $h = floor($totalMinutes / 60);
                                        $m = $totalMinutes % 60;
                                        $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                        @endphp
                                        <span class="small badge badge-soft-info p-1 rounded font-weight-bold" title="Total Spent Time" style="display: inline-flex; align-items: center; gap: 2px;">
                                            <i class="mdi mdi-timer-outline mr-1"></i>{{ $timeSpentFormatted }}
                                        </span>
                                        <span class="small bg-info p-1 rounded text-white">
                                            {{ $items->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="task-card-footer">
                                <div class="project-members d-flex align-items-center justify-content-between" style="gap: 10px;">
                                    <div class="project-matrix-group-divs flex-grow-1 mr-2" style="margin-bottom: 0;">
                                        <span class="project-metrics__metric-group-item__title project-matrix-group-items mb-0">
                                            Progress
                                        </span>
                                        <div class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm mb-0" style="flex-grow: 1;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $items->progress }}%"
                                                aria-valuenow="{{ $items->progress }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="project-matrix-group-items project-metrics__metric-group-item__value mb-0">
                                            {{ $items->progress }} %
                                        </span>
                                    </div>

                                    {{-- Timer Widget for InProgress --}}
                                    @if($items->assigned_to == Auth::id())
                                    @php $activeTimer = $items->activeTimerForUser(Auth::id()); @endphp
                                    <div class="task-timer-widget small-timer {{ $activeTimer ? 'timer-running' : '' }}" data-task-id="{{ $items->id }}" style="flex-shrink: 0; margin-top: 0;">
                                        @if($activeTimer)
                                        <span class="timer-rec-dot"></span>
                                        <span class="timer-display" data-started="{{ $activeTimer->log_date }} {{ $activeTimer->starttime }}">00:00:00</span>
                                        @else
                                        <span class="timer-display">00:00:00</span>
                                        <button type="button" class="timer-btn btn-start" data-action="start" data-task-id="{{ $items->id }}" title="Start/Resume Timer">
                                            <i class="mdi mdi-play"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @endif

                                    <ul class="project-users mb-0" style="padding-left: 0; margin-left: 0 !important; min-width: 25px; display: flex; justify-content: flex-end; flex-shrink: 0;">
                                        <li class="cursor">
                                            @if ($items->user->profile)
                                            <img title="{{ $items->user->name }}" src="{{ asset('storage/'. $items->user->profile )}}" style="margin-left: 0 !important;">
                                            @else
                                            <img title="{{ $items->user->name }}" src="{{ Avatar::create($items->user->name)->toBase64()  }}" style="margin-left: 0 !important;">
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        @endforeach
                    </main>
                    <footer></footer>
                </div>


                {{-- COMPLETED --}}
                <div class="kanban-board">
                    <header class="kanban-board-header kanban-success">
                        <div class="kanban-title-board">
                            <div class="kanban-title-content">
                                <h6 class="title">Completed</h6>
                                <span class="count">{{ count($completed) }} </span>
                            </div>
                        </div>
                    </header>
                    <main class="kanban-drag" data-status="Completed">
                        @foreach ($completed as $items)
                        <div class="kanban-item task-body {{ request('task_id') == $items->id ? 'highlight-task' : '' }}" data-id="{{ $items->id }}" data-url="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}" style="cursor: pointer;">
                            <div class="kanban-item-title">
                                <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                    <h6 class="title c-p">
                                        @if( $items->priority == "Low")
                                        <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority"></i>
                                        @elseif( $items->priority == "Medium")
                                        <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority"></i>
                                        @else
                                        <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority"></i>
                                        @endif
                                        {{ Str::limit($items->title, 28) }}
                                    </h6>
                                </a>
                                @if($project->status != 'Completed')
                                <ul class="task-action">
                                    @if(Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" currentstatus="{{ $items->status }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                            <i class="mdi mdi-arrow-left-right-bold"></i>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                                @endif
                            </div>

                            <div class="kanban-item-text c-m">
                                {!! Str::limit($items->description, 120) !!}
                                <div class="task-schedule-time d-flex align-items-center justify-content-between flex-wrap" style="gap: 5px; margin-top: 5px;">
                                    <span class="">
                                        <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time"></i>
                                        {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                    </span>
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                        $totalMinutes = round(($items->total_time ?? 0) * 60);
                                        $h = floor($totalMinutes / 60);
                                        $m = $totalMinutes % 60;
                                        $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                        @endphp
                                        <span class="small badge badge-soft-success p-1 rounded font-weight-bold" title="Total Spent Time" style="display: inline-flex; align-items: center; gap: 2px;">
                                            <i class="mdi mdi-timer-outline mr-1"></i>{{ $timeSpentFormatted }}
                                        </span>
                                        <span class="small bg-success p-1 rounded text-white">
                                            {{ $items->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="task-card-footer">
                                <div class="project-members">
                                    <div class="project-matrix-group-divs">
                                        <span class="project-metrics__metric-group-item__title project-matrix-group-items">
                                            Progress
                                        </span>
                                        <div class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $items->progress }}%"
                                                aria-valuenow="{{ $items->progress }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="project-matrix-group-items project-metrics__metric-group-item__value">
                                            {{ $items->progress }} %
                                        </span>
                                    </div>

                                    <ul class="project-users">
                                        <li class="cursor">
                                            @if ($items->user->profile)
                                            <img title="{{ $items->user->name }}" src="{{ asset('storage/'. $items->user->profile )}}">
                                            @else
                                            <img title="{{ $items->user->name }}" src="{{ Avatar::create($items->user->name)->toBase64()  }}">
                                            @endif

                                        </li>
                                    </ul>

                                </div>
                            </div>

                        </div>
                        @endforeach
                    </main>
                    <footer></footer>
                </div>

            </div>
        </div>
    </div>
    <!-- end row -->
</div>
@endsection
@section('component')

@include('components.projects.components.changestatus')
@include('components.projects.components.edittask')
@include('components.projects.components.projectstatus')

@endsection

@section('scripts')
<script>
    window.WMS_USER = {
        is_management: {{ Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']) ? 'true' : 'false' }}
    };
</script>
<script src="{{ asset('assets/libs/draggable/Sortable.min.js') }}"></script>
<script src="{{ asset('js/kanban.js') }}?v={{ time() }}"></script>
<script>
    function confirmation() {
        if (confirm('Do you want to delete this task? it will not revert once deleted!')) {
            return true;
        } else {
            return false;
        }
    }

    $(document).ready(function() {
        // Navigate to task details on card click
        $(document).on('click', '.kanban-item', function(e) {
            if ($(e.target).closest('.task-action, .changeStatus, .task-timer-widget, .timer-btn, a, button').length) {
                return;
            }
            let url = $(this).attr('data-url');
            if (url) {
                window.location.href = url;
            }
        });

        /* ── Timer Tick: update all running timers every second ── */
        setInterval(function() {
            $('.task-timer-widget.timer-running .timer-display').each(function() {
                var started = $(this).data('started');
                if (!started) return;
                var startTime = new Date(started);
                var now = new Date();
                var diff = Math.floor((now - startTime) / 1000);
                if (diff < 0) diff = 0;
                var h = String(Math.floor(diff / 3600)).padStart(2, '0');
                var m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
                var s = String(diff % 60).padStart(2, '0');
                $(this).text(h + ':' + m + ':' + s);
            });
        }, 1000);

        /* ── Timer Button Click Handler ── */
        $(document).on('click', '.timer-btn', function(e) {
            e.stopPropagation();
            e.preventDefault();

            var $btn = $(this);
            var taskId = $btn.data('task-id');
            var action = $btn.data('action'); // 'start' or 'pause'
            var url = '/projects/tasks/' + taskId + '/timer/' + action;

            $btn.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        // Reload page to reflect timer state
                        location.reload();
                    } else {
                        toastr.error(res.message);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong. Please try again.');
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection
