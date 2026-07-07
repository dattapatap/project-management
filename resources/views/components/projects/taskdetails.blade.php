@extends('layouts.app')
@section('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 8px 16px rgba(0, 0, 0, 0.1);
        --primary-gradient: linear-gradient(135deg, #556ee6 0%, #7b91ff 100%);
    }

    .task-details-container {
        padding: 20px 0;
    }

    /* Sidebar Cards */
    .sidebar-card {
        background: #ffffff;
        border-radius: 15px;
        border: 1px solid #f0f0f0;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .sidebar-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .sidebar-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #74788d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .sidebar-card-title i {
        margin-right: 8px;
        font-size: 16px;
        color: #556ee6;
    }

    /* Modern Progress Bar */
    .activity-log-premium {
        max-height: 750px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .progress-premium {
        height: 10px;
        border-radius: 10px;
        background-color: #f0f2f8;
        overflow: visible;
        position: relative;
    }

    .progress-premium .progress-bar {
        border-radius: 10px;
        background: var(--primary-gradient);
        box-shadow: 0 2px 5px rgba(85, 110, 230, 0.3);
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-scroll {
        max-height: calc(100vh - 120px);
        padding-bottom: 30px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -34px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #556ee6;
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px rgba(85, 110, 230, 0.1);
        z-index: 1;
    }

    .timeline-content {
        background: #fff;
        padding: 15px 20px;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        box-shadow: var(--shadow-sm);
    }

    /* Comment Styles */
    .comment-bubble {
        display: flex;
        margin-bottom: 25px;
    }

    .comment-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        margin-right: 15px;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }

    .comment-body {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 0 15px 15px 15px;
        flex-grow: 1;
        position: relative;
        border: 1px solid #edf2f9;
    }

    .comment-author {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        display: block;
    }

    /* Badges */
    .badge-soft-primary {
        background: rgba(85, 110, 230, 0.1);
        color: #556ee6;
    }

    .badge-soft-success {
        background: rgba(52, 195, 143, 0.1);
        color: #34c38f;
    }

    .badge-soft-danger {
        background: rgba(244, 106, 106, 0.1);
        color: #f46a6a;
    }

    .badge-soft-warning {
        background: rgba(241, 180, 76, 0.1);
        color: #f1b44c;
    }

    /* Main Content Card */
    .main-content-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #f0f0f0;
        padding: 0;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .card-premium-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        padding: 25px 30px;
        border-bottom: 1px solid #f0f0f0;
    }

    .card-premium-body {
        padding: 30px;
    }

    .stats-card-premium {
        background: #fcfdfe;
        border: 1px solid #edf2f9;
        border-radius: 15px;
        padding: 20px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .stats-card-premium:hover {
        border-color: #556ee6;
        background: #fff;
    }

    .stats-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 15px;
    }

    .task-description-box {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        border: 1px solid #edf2f9;
        color: #495057;
        font-size: 14.5px;
        line-height: 1.7;
    }

    #progress-range {
        -webkit-appearance: none;
        height: 8px;
        border-radius: 5px;
        outline: none;
        transition: background 0.2s;
    }

    #progress-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #556ee6;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
    }

    #progress-range::-webkit-slider-thumb:hover {
        transform: scale(1.2);
        border-color: #34c38f;
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
        0%, 100% { box-shadow: 0 0 4px rgba(244, 67, 54, 0.15); }
        50% { box-shadow: 0 0 12px rgba(244, 67, 54, 0.3); }
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
        0%, 100% { opacity: 1; }
        50% { opacity: 0.2; }
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center justify-content-between">
                    <a href="{{ url('/projects/taskboard/'.base64_encode($task->project->id)) }}" class="btn-back">
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/projects/taskboard/'.base64_encode($task->project->id)) }}">Taskboard</a></li>
                        <li class="breadcrumb-item active">Task History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="sidebar-card">
                <div class="sidebar-card-title mb-4">
                    <i class="mdi mdi-information-outline"></i> Task Information
                </div>

                <ul class="list-unstyled mb-0">
                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-calendar-range mr-1"></i> Timeline
                        </p>
                        <div class="font-weight-medium">
                            {{ \Carbon\Carbon::parse($task->startdate)->format('d M y') }} - {{ \Carbon\Carbon::parse($task->enddate)->format('d M y') }}
                        </div>
                    </li>

                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-account-star mr-1"></i> Assigned To
                        </p>
                        <div class="d-flex align-items-center">
                            @if ($task->user->profile)
                            <img class="rounded-circle mr-2" style="width: 30px; height: 30px;" src="{{ asset('storage/' . $task->user->profile) }}">
                            @else
                            <img class="rounded-circle mr-2" style="width: 30px; height: 30px;" src="{{ Avatar::create($task->user->name)->toBase64() }}">
                            @endif
                            <span class="font-weight-bold">{{ $task->user->name }}</span>
                        </div>
                    </li>

                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-flag-variant mr-1"></i> Priority
                        </p>
                        <span class="badge @if($task->priority == 'Low') badge-soft-success @elseif($task->priority == 'Medium') badge-soft-warning @else badge-soft-danger @endif px-3 py-2">
                            {{ $task->priority }}
                        </span>
                    </li>

                    @if ($task->act_startdate)
                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-calendar-check mr-1"></i> Actual Start
                        </p>
                        <div class="text-success font-weight-medium">
                            {{ \Carbon\Carbon::parse($task->act_startdate)->format('d M y') }}
                        </div>
                    </li>
                    @endif

                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-trending-up mr-1"></i> Task Progress
                        </p>
                        <div class="progress progress-premium mb-2">
                            <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Overall completion</span>
                            <span class="font-weight-bold text-primary">{{ $task->progress }}%</span>
                        </div>
                    </li>

                    @if ($task->status == 'Completed')
                    <li class="mb-4 p-3 rounded" style="background: rgba(52, 195, 143, 0.05); border-left: 3px solid #34c38f;">
                        <p class="text-success mb-1 small uppercase font-weight-bold">
                            <i class="mdi mdi-check-decagram mr-1"></i> Completed On
                        </p>
                        <div class="font-weight-bold text-success">
                            {{ \Carbon\Carbon::parse($task->act_enddate)->format('d M y') }}
                        </div>
                    </li>
                    @endif

                    <li class="mt-4 pt-4 border-top">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-account-edit mr-1"></i> Created By
                        </p>
                        <div class="d-flex align-items-center">
                            @if ($task->createdby->profile)
                            <img class="rounded-circle mr-2" style="width: 24px; height: 24px;" src="{{ asset('storage/' . $task->createdby->profile) }}">
                            @else
                            <img class="rounded-circle mr-2" style="width: 24px; height: 24px;" src="{{ Avatar::create($task->createdby->name)->toBase64() }}">
                            @endif
                            <span class="text-muted small">{{ $task->createdby->name }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>


        <div class="col-md-9">
            <div class="main-content-card">
                <div class="card-premium-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-1 font-weight-bold text-dark">Task Details</h3>
                            <p class="text-muted mb-0 d-flex align-items-center">
                                <i class="mdi mdi-folder-outline mr-1"></i> {{ $task->project->project_name }}
                                <span class="mx-2 text-light">|</span>
                                <span class="text-primary font-weight-medium">#TASK-{{ $task->id }}</span>
                            </p>
                        </div>
                        <div>
                            @if ($task->status == 'ToDo')
                            <span class="badge badge-soft-danger px-3 py-2 font-size-13"><i class="mdi mdi-circle-outline mr-1"></i> {{ $task->status }}</span>
                            @elseif($task->status == 'InProgress')
                            <span class="badge badge-soft-warning px-3 py-2 font-size-13"><i class="mdi mdi-loading mdi-spin mr-1"></i> {{ $task->status }}</span>
                            @else
                            <span class="badge badge-soft-success px-3 py-2 font-size-13"><i class="mdi mdi-check-circle-outline mr-1"></i> {{ $task->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-premium-body">
                    <h4 class="mb-4 font-weight-bold">{{ $task->title }}</h4>

                    <div class="row mb-5">
                        <div class="col-md-4">
                            <div class="stats-card-premium">
                                <div class="stats-icon bg-soft-primary">
                                    <i class="mdi mdi-timer-outline text-primary"></i>
                                </div>
                                <p class="text-muted mb-1 small uppercase font-weight-bold">Total Time Spent</p>
                                @php
                                $totalMinutes = round($task->logs->whereNotNull('time_spend')->sum('time_spend') * 60);
                                $h = floor($totalMinutes / 60);
                                $m = $totalMinutes % 60;
                                $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                @endphp
                                <h3 class="mb-0 font-weight-bold text-dark">{{ $timeSpentFormatted }}</h3>
                            </div>
                        </div>
                        <div class="col-md-8">
                            @if ($task->status != 'Completed')
                            <div class="stats-card-premium">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <p class="text-muted mb-0 small uppercase font-weight-bold">Task Progress</p>
                                        <h5 class="mb-0 font-weight-bold text-primary task-progress-val">{{ $task->progress }}%</h5>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        @if($task->assigned_to == Auth::id())
                                        @php $activeTimer = $task->activeTimerForUser(Auth::id()); @endphp
                                        <div class="task-timer-widget {{ $activeTimer ? 'timer-running' : '' }}" data-task-id="{{ $task->id }}" style="margin-top: 0;">
                                            @if($activeTimer)
                                                <span class="timer-rec-dot"></span>
                                                <span class="timer-label">REC</span>
                                                <span class="timer-display" data-started="{{ $activeTimer->log_date }} {{ $activeTimer->starttime }}">00:00:00</span>
                                            @else
                                                <span class="timer-label">Timer</span>
                                                <span class="timer-display">00:00:00</span>
                                                <button type="button" class="timer-btn btn-start" data-action="start" data-task-id="{{ $task->id }}" title="Start/Resume Timer">
                                                    <i class="mdi mdi-play"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @endif
                                        <button class="btn btn-sm btn-soft-primary changeStatus px-3" taskid="{{ $task->id }}" currentstatus="{{ $task->status }}">
                                            <i class="mdi mdi-arrow-left-right-bold mr-1"></i> Status
                                        </button>
                                    </div>
                                </div>
                                <div class="progress progress-premium mb-3" style="height: 6px;">
                                    <div class="progress-bar main-progress-bar" role="progressbar" style="width: {{ $task->progress }}%"></div>
                                </div>
                                <div class="d-flex align-items-center mt-3 gap-2">
                                    <div style="flex-grow: 1;">
                                        <select class="form-control select2" id="progress-select" style="border-radius: 8px;">
                                            @php
                                            $progressOptions = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
                                            if (!in_array($task->progress, $progressOptions)) {
                                                $progressOptions[] = $task->progress;
                                                sort($progressOptions);
                                            }
                                            @endphp
                                            @foreach ($progressOptions as $opt)
                                                <option value="{{ $opt }}" {{ $task->progress == $opt ? 'selected' : '' }}>{{ $opt }}% Complete</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="btn btn-primary px-3 shadow-sm" id="btn-update-progress" style="height: 38px; border-radius: 8px;">
                                        <i class="mdi mdi-check-all mr-1"></i> Update
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="stats-card-premium" style="background: rgba(52, 195, 143, 0.05);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-soft-success mb-0 mr-3">
                                            <i class="mdi mdi-trophy-outline text-success"></i>
                                        </div>
                                        <div>
                                            <p class="text-success mb-0 small uppercase font-weight-bold">Task Status</p>
                                            <h5 class="mb-0 font-weight-bold text-success">Mission Accomplished!</h5>
                                        </div>
                                    </div>
                                    @if(Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
                                    <button class="btn btn-sm btn-soft-primary changeStatus px-3" taskid="{{ $task->id }}" currentstatus="{{ $task->status }}">
                                        <i class="mdi mdi-arrow-left-right-bold mr-1"></i> Reopen Task
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="task-description mb-5">
                        <h5 class="font-size-15 mb-3 d-flex align-items-center">
                            <i class="mdi mdi-text-subject mr-2 text-primary"></i>
                            <span class="font-weight-bold">Description</span>
                        </h5>
                        <div class="task-description-box">
                            {!! $task->description !!}
                        </div>
                    </div>

                    <div class="task-timeline-section mb-5">
                        <h5 class="font-size-15 mb-4 d-flex align-items-center">
                            <i class="mdi mdi-history mr-2 text-primary"></i>
                            <span class="font-weight-bold">Activity Log</span>
                        </h5>

                        <div class="activity-log-premium">
                            @php
                            $activities = collect();
                            // Add History (Status/Progress changes)
                            foreach($task->histories as $history) {
                            $activities->push([
                            'type' => 'history',
                            'date' => $history->created_at,
                            'user' => $history->user->name ?? 'User',
                            'title' => 'System Update',
                            'description' => $history->comments,
                            'raw' => $history
                            ]);
                            }
                            $activities = $activities->sortByDesc('date');
                            @endphp

                            @forelse ($activities as $item)
                            <div class="activity-item pb-4 border-left ml-2 pl-4 position-relative" style="border-left: 2px solid #e9ecef !important;">
                                <div class="activity-dot {{ $item['type'] == 'log' ? 'bg-primary' : 'bg-warning' }}" style="position: absolute; left: -7px; top: 0; width: 12px; height: 12px; border-radius: 50%;"></div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $item['title'] }}</h6>
                                    <small class="text-muted font-size-12">
                                        <i class="mdi mdi-clock-outline mr-1"></i>{{ $item['date']->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar-xs mr-2">
                                        <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-10">
                                            {{ substr($item['user'], 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="font-size-12 text-muted font-weight-medium">by {{ $item['user'] }}</span>
                                    @if($item['type'] == 'log')
                                    <span class="mx-2 text-muted">•</span>
                                    @if(!empty($item['is_running']))
                                    <span class="badge badge-soft-danger font-size-11"><i class="mdi mdi-record mr-1" style="animation: rec-blink 1s ease-in-out infinite;"></i> Running</span>
                                    @else
                                    @php
                                    $itemMinutes = round(($item['duration'] ?? 0) * 60);
                                    $itemH = floor($itemMinutes / 60);
                                    $itemM = $itemMinutes % 60;
                                    @endphp
                                    <span class="badge badge-soft-success font-size-11">{{ $itemH }}h {{ $itemM }}m</span>
                                    @endif
                                    @endif
                                </div>
                                <p class="mb-0 text-muted" style="font-size: 14px;">{{ $item['description'] }}</p>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="mdi mdi-history text-muted font-size-40 d-block mb-2"></i>
                                <p class="text-muted">No activities recorded yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="task-comments-section pt-4 border-top">
                        <h5 class="font-size-15 mb-4 d-flex align-items-center">
                            <i class="mdi mdi-comment-multiple-outline mr-2 text-primary"></i>
                            <span class="font-weight-bold">Discussion</span>
                        </h5>

                        <div class="comments-list mb-4">
                            @foreach ($task->comments as $item)
                            <div class="comment-bubble">
                                @if ($item->user->profile)
                                <img class="comment-avatar border" src="{{ asset('storage/' . $item->user->profile) }}">
                                @else
                                <img class="comment-avatar border" src="{{ Avatar::create($item->user->name)->toBase64() }}">
                                @endif
                                <div class="comment-body shadow-none border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="comment-author text-dark">{{ $item->user->name }}</span>
                                        <span class="text-muted small"><i class="mdi mdi-clock-outline mr-1"></i>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 14px;">
                                        {{ $item->comment }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="comment-form bg-light p-4 rounded-xl border">
                            <div class="d-flex align-items-start">
                                @php $user = Auth::user(); @endphp
                                @if ($user->profile)
                                <img class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px;" src="{{ asset('storage/' . $user->profile) }}">
                                @else
                                <img class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px;" src="{{ Avatar::create($user->name)->toBase64() }}">
                                @endif
                                <div class="flex-grow-1">
                                    <form action="{{ url('projects/task/comment') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="task_id" value="{{ $task->id }}">
                                        <textarea name="task_comment" class="form-control border-0 bg-white shadow-sm" placeholder="Type your message here..." rows="3" required style="resize: none; border-radius: 12px;"></textarea>
                                        <div class="text-right mt-3">
                                            <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                                                Post Comment <i class="mdi mdi-send ml-1"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>
    <!-- end row -->
</div>
@endsection
@section('component')
@include('components.projects.components.changestatus')
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        function updateProgressUI(val) {
            $('.task-progress-val').text(val + '%');

            // Dynamic Color Logic
            let color = '#556ee6'; // Default primary (blue)
            if (val < 30) color = '#f46a6a'; // Danger (red)
            else if (val < 70) color = '#f1b44c'; // Warning (orange)
            else color = '#34c38f'; // Success (green)

            // Update all progress bars (sidebar and main)
            $('.progress-bar').css({
                'width': val + '%',
                'background': color + ' !important',
                'background-color': color
            });

            // Force style attribute for !important override
            $('.progress-bar').each(function() {
                this.style.setProperty('background', color, 'important');
                this.style.setProperty('width', val + '%', 'important');
            });

            $('.task-progress-val').css('color', color);

            // Update "Overall completion" percentage in sidebar and any other % text
            $('.font-weight-bold').each(function() {
                if ($(this).text().indexOf('%') !== -1) {
                    $(this).text(val + '%');
                    // Only update color if it's not a static label
                    if ($(this).hasClass('text-primary') || $(this).hasClass('task-progress-val')) {
                        $(this).css('color', color);
                    }
                }
            });

        }

        $('#btn-update-progress').on('click', function(e) {
            e.preventDefault();
            let val = $('#progress-select').val();
            let btn = $(this);

            $.ajax({
                type: 'post',
                url: base_url + '/projects/task/progress',
                data: {
                    'task_id': "{{ $task->id }}",
                    'progerss': val,
                    '_token': '{{ csrf_token() }}'
                },
                dataType: 'json',
                beforeSend: function() {
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Updating...');
                },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="mdi mdi-check-all mr-1"></i> Update');
                    if (res.success == true) {
                        alertify.success(res.message);
                        updateProgressUI(val);
                    } else {
                        alertify.error(res.message);
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false).html('<i class="mdi mdi-check-all mr-1"></i> Update');
                    alertify.error('Something went wrong. Please try again.');
                    console.log(err);
                },
            });
        });

        // Initial UI state
        updateProgressUI("{{ $task->progress }}");

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
            var action = $btn.data('action');
            var url = base_url + '/projects/tasks/' + taskId + '/timer/' + action;

            $btn.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        alertify.success(res.message);
                        location.reload();
                    } else {
                        alertify.error(res.message);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alertify.error('Something went wrong. Please try again.');
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection
