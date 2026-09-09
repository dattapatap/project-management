@extends('layouts.app')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .tasks-mgmt-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    .tasks-stat-card {
        border-radius: 16px;
        padding: 20px 22px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }
    
    .tasks-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
    }
    
    .tasks-stat-card.stat-active {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #bfdbfe;
    }
    
    .tasks-stat-card.stat-inprogress {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 1px solid #fde68a;
    }
    
    .tasks-stat-card.stat-completed {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
    }
    
    .tasks-stat-card.stat-hours {
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        border: 1px solid #e9d5ff;
    }
    
    .tasks-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    
    .stat-active .tasks-stat-icon {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.28);
    }
    
    .stat-inprogress .tasks-stat-icon {
        background: #f59e0b;
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(245, 158, 11, 0.28);
    }
    
    .stat-completed .tasks-stat-icon {
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.28);
    }
    
    .stat-hours .tasks-stat-icon {
        background: #8b5cf6;
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(139, 92, 246, 0.28);
    }
    
    .custom-pill-tabs .nav-link {
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-weight: 600;
        font-size: 13.5px;
        padding: 8px 18px;
        border-radius: 30px;
        margin-right: 8px;
        background: #ffffff;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .custom-pill-tabs .nav-link:hover {
        color: #1e293b;
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    
    .custom-pill-tabs .nav-link.active {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }
    
    .custom-pill-tabs .nav-link.active .badge-tab {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    
    .badge-tab {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        background: #f1f5f9;
        color: #475569;
    }
    
    .sub-filter-pill {
        font-size: 12px;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        color: #64748b;
        background: #ffffff;
        text-decoration: none !important;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .sub-filter-pill:hover {
        background: #f8fafc;
        color: #1e293b;
    }
    
    .sub-filter-pill.active {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
    }
    
    .tasks-table th {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.7px;
        color: #64748b !important;
        font-weight: 700 !important;
        background-color: #f8fafc;
        border-bottom: 2px solid #edf2f7 !important;
        padding: 12px 16px !important;
    }
    
    .tasks-table td {
        vertical-align: middle !important;
        color: #334155;
        font-size: 13px;
        padding: 14px 16px !important;
        border-top: 1px solid #f1f5f9;
    }
    
    .tasks-table tbody tr {
        transition: background-color 0.15s ease;
    }
    
    .tasks-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .badge-priority {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.3px;
    }
    
    .priority-high { background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
    .priority-medium { background-color: #fffbeb; color: #f59e0b; border: 1px solid #fde68a; }
    .priority-low { background-color: #f0fdf4; color: #10b981; border: 1px solid #bbf7d0; }
    
    .badge-team {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    
    .time-spend-badge {
        font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
        font-weight: 700;
        font-size: 12px;
        color: #0f172a;
        background: #f8fafc;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .filter-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
</style>
@endsection

@section('content')
<div class="container-fluid tasks-mgmt-wrapper pb-5">

    {{-- Page Header --}}
    <div class="row mb-3 align-items-center pt-2">
        <div class="col-md-6">
            <h4 class="mb-1 font-weight-bold text-dark" style="font-size: 1.65rem; letter-spacing: -0.6px;">
                <i class="mdi mdi-checkbox-multiple-marked-circle-outline text-primary mr-1"></i> Tasks <span class="text-primary">Management</span>
            </h4>
            <p class="text-muted mb-0 font-size-13">
                @if($isTl && !$isAuthority)
                    Overview of your assigned tasks and your team members' active and completed deliverables.
                @else
                    Monitor, track, and filter organization-wide active and completed task progress across all teams.
                @endif
            </p>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            @if(Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
            <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 font-weight-bold shadow-sm btn_header_add_task" style="gap: 6px; display: inline-flex; align-items: center; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none;">
                <i class="mdi mdi-plus-circle font-size-15 text-white"></i> Create New Task
            </button>
            @endif
        </div>
    </div>

    {{-- KPI Metric Cards --}}
    @php
        $stats = ($isTl && !$isAuthority) ? ($tab === 'team_tasks' ? $teamStats : $myStats) : $adminStats;
    @endphp
    <div class="row mb-4">
        {{-- 1. Active Tasks (Blue Theme) --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="tasks-stat-card stat-active d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-12 font-weight-bold text-uppercase letter-spacing-1" style="color: #1e40af;">Active Tasks</span>
                    <h2 class="mb-0 mt-1 font-weight-bold" style="color: #1d4ed8;">{{ $stats['active'] ?? 0 }}</h2>
                    <small class="font-size-11 font-weight-medium" style="color: #3b82f6;">To Do + In Progress</small>
                </div>
                <div class="tasks-stat-icon">
                    <i class="mdi mdi-clock-fast"></i>
                </div>
            </div>
        </div>

        {{-- 2. In Progress Tasks (Amber Theme) --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="tasks-stat-card stat-inprogress d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-12 font-weight-bold text-uppercase letter-spacing-1" style="color: #92400e;">In Progress</span>
                    <h2 class="mb-0 mt-1 font-weight-bold" style="color: #d97706;">{{ $stats['in_progress'] ?? 0 }}</h2>
                    <small class="font-size-11 font-weight-medium" style="color: #b45309;">Currently running</small>
                </div>
                <div class="tasks-stat-icon">
                    <i class="mdi mdi-progress-clock"></i>
                </div>
            </div>
        </div>

        {{-- 3. Completed Tasks (Emerald Theme) --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="tasks-stat-card stat-completed d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-12 font-weight-bold text-uppercase letter-spacing-1" style="color: #166534;">Completed</span>
                    <h2 class="mb-0 mt-1 font-weight-bold" style="color: #059669;">{{ $stats['completed'] ?? 0 }}</h2>
                    <small class="font-size-11 font-weight-medium" style="color: #15803d;">Done & delivered</small>
                </div>
                <div class="tasks-stat-icon">
                    <i class="mdi mdi-check-circle-outline"></i>
                </div>
            </div>
        </div>

        {{-- 4. Total Hours Spent (Purple Theme) --}}
        <div class="col-xl-3 col-md-6">
            <div class="tasks-stat-card stat-hours d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-12 font-weight-bold text-uppercase letter-spacing-1" style="color: #5b21b6;">Total Hours Spent</span>
                    <h2 class="mb-0 mt-1 font-weight-bold" style="color: #7c3aed;">
                        {{ $stats['total_hours'] ?? 0 }} <span class="font-size-14 font-weight-semibold" style="color: #8b5cf6;">Hrs</span>
                    </h2>
                    <small class="font-size-11 font-weight-medium" style="color: #6b21a8;">Logged across tasks</small>
                </div>
                <div class="tasks-stat-icon">
                    <i class="mdi mdi-timer-sand"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs & Sub-Filter Bar --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-6 mb-2 mb-lg-0">
            @if($isTl && !$isAuthority)
                {{-- Team Leader Tabs: My Tasks vs Team Members Tasks --}}
                <ul class="nav nav-pills custom-pill-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'my_tasks' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'my_tasks'])) }}">
                            <i class="mdi mdi-account-circle-outline font-size-15"></i> My Tasks
                            <span class="badge-tab">{{ $myStats['total'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'team_tasks' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'team_tasks'])) }}">
                            <i class="mdi mdi-account-group-outline font-size-15"></i> Team Members' Tasks
                            <span class="badge-tab">{{ $teamStats['total'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
            @else
                {{-- Admin / Manager Tabs: Active vs Completed vs All --}}
                <ul class="nav nav-pills custom-pill-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'active' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page', 'status']), ['tab' => 'active'])) }}">
                            <i class="mdi mdi-lightning-bolt-outline font-size-15"></i> Active Tasks
                            <span class="badge-tab">{{ $adminStats['active'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'completed' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page', 'status']), ['tab' => 'completed'])) }}">
                            <i class="mdi mdi-check-all font-size-15"></i> Completed Tasks
                            <span class="badge-tab">{{ $adminStats['completed'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}">
                            <i class="mdi mdi-format-list-bulleted font-size-15"></i> All Tasks
                            <span class="badge-tab">{{ $adminStats['total'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('tasks.index') }}" id="taskFilterForm">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @php
                    $hasTeamFilter = $teams->count() > 0 && ($isAuthority || Auth::user()->hasRole(['Admin', 'Branch-Manager']));
                @endphp
                <div class="row align-items-end">
                    {{-- Search Input --}}
                    <div class="col-xl-3 col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Search</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-magnify"></i></span>
                            </div>
                            <input type="text" class="form-control form-control-sm border-left-0" name="search" placeholder="Task, Project, Company name..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    {{-- Team Filter (Only for Admin & Branch Manager) --}}
                    @if($hasTeamFilter)
                    <div class="col-xl-2 col-md-3 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Team</label>
                        <select name="team_id" class="form-control form-control-sm select2">
                            <option value="">All Teams</option>
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}" {{ (string)($filters['team_id'] ?? '') === (string)$t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Assignee Filter --}}
                    @if($members->count() > 0)
                    <div class="col-xl-3 col-md-3 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Assignee / Member</label>
                        <select name="assigned_to" class="form-control form-control-sm select2">
                            <option value="">All Assignees</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ (string)($filters['assigned_to'] ?? '') === (string)$m->id ? 'selected' : '' }}>
                                    {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Status Filter (For Team Leader) --}}
                    @if($isTl && !$isAuthority)
                    <div class="col-xl-2 col-md-3 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="Active" {{ ($filters['status'] ?? '') === 'Active' ? 'selected' : '' }}>Active (To Do + In Progress)</option>
                            <option value="ToDo" {{ ($filters['status'] ?? '') === 'ToDo' ? 'selected' : '' }}>To Do</option>
                            <option value="InProgress" {{ ($filters['status'] ?? '') === 'InProgress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed" {{ ($filters['status'] ?? '') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    @endif

                    {{-- Date Range (Picker) --}}
                    <div class="col-xl-3 col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Date Range</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-calendar-range font-size-13 text-muted"></i></span>
                            </div>
                            <input type="text" id="task_date_range" class="form-control form-control-sm border-left-0" placeholder="Select date range..." readonly style="background: #ffffff; cursor: pointer;">
                            <input type="hidden" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="col-xl-1 col-md-3 text-right">
                        <div class="d-flex" style="gap: 4px;">
                            <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Filter Results">
                                <i class="mdi mdi-filter font-size-13"></i>
                            </button>
                            <a href="{{ route('tasks.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border" title="Reset Filters">
                                <i class="mdi mdi-refresh font-size-13"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tasks List Table Card --}}
    <div class="card border shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <div class="card-body p-0">
            @if($tasks->isEmpty())
                <div class="text-center py-5">
                    <div class="avatar-md mx-auto mb-3" style="background: rgba(241, 245, 249, 0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="mdi mdi-clipboard-text-outline font-size-24 text-muted"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1">No tasks found</h5>
                    <p class="text-muted font-size-13 mb-3">No task records match your current tab or filter criteria.</p>
                    <a href="{{ route('tasks.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                        <i class="mdi mdi-refresh mr-1"></i> Clear Filters
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table tasks-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="padding-left: 20px; width: 28%;">Task Name</th>
                                <th style="width: 22%;">Company & Project</th>
                                <th style="width: 12%;">Team</th>
                                <th style="width: 14%;">Assignee</th>
                                <th style="width: 10%;">Time Spent</th>
                                <th style="width: 14%;">Dates</th>
                                <th style="width: 10%;">Status</th>
                                <th class="text-right" style="padding-right: 20px; width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                            <tr>
                                {{-- 1. Task Name & Priority --}}
                                <td style="padding-left: 20px;">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1 flex-wrap" style="gap: 6px;">
                                            @php 
                                                $prio = strtolower($task->priority); 
                                                $prioClass = in_array($prio, ['high', 'medium', 'low']) ? 'priority-' . $prio : 'priority-low';
                                            @endphp
                                            <span class="badge-priority {{ $prioClass }}">
                                                {{ $task->priority ?? 'Medium' }}
                                            </span>
                                            <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="text-dark font-weight-bold font-size-13 text-truncate" style="max-width: 280px;" title="{{ $task->title }}">
                                                {{ $task->title }}
                                            </a>
                                        </div>
                                        @if($task->description)
                                            <p class="text-muted font-size-11 mb-0 text-truncate" style="max-width: 320px;" title="{{ strip_tags($task->description) }}">
                                                {{ Str::limit(strip_tags($task->description), 50) }}
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                {{-- 2. Company & Project --}}
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark font-size-13 text-truncate" style="max-width: 220px;" title="{{ $task->project->clients->name ?? 'Internal / No Client' }}">
                                            <i class="mdi mdi-domain font-size-12 text-muted mr-1"></i>
                                            {{ $task->project->clients->name ?? 'Internal / Direct' }}
                                        </span>
                                        <span class="text-muted font-size-11 text-truncate" style="max-width: 220px;" title="{{ $task->project->project_name ?? 'Internal Project' }}">
                                            <i class="mdi mdi-folder-outline font-size-11 text-muted mr-1"></i>
                                            {{ $task->project->project_name ?? 'Internal Project' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 3. Team --}}
                                <td>
                                    @php
                                        $teamName = $task->project->project_team->team->name ?? null;
                                    @endphp
                                    @if($teamName)
                                        <span class="badge-team text-truncate d-inline-block" style="max-width: 130px;" title="{{ $teamName }}">
                                            <i class="mdi mdi-account-multiple-outline mr-0.5"></i> {{ $teamName }}
                                        </span>
                                    @else
                                        <span class="text-muted font-size-12">—</span>
                                    @endif
                                </td>

                                {{-- 4. Who Worked on it (Assignee) --}}
                                <td>
                                    @if($task->user)
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <img src="{{ Avatar::create($task->user->name)->toBase64() }}" class="rounded-circle avatar-xs" alt="{{ $task->user->name }}" style="width: 26px; height: 26px; min-width: 26px;">
                                            <div class="text-truncate" style="max-width: 120px;">
                                                <span class="font-weight-semibold font-size-12 text-dark d-block text-truncate" title="{{ $task->user->name }}">
                                                    {{ $task->user->name }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted font-size-12">Unassigned</span>
                                    @endif
                                </td>

                                {{-- 5. Total Time Spent --}}
                                <td>
                                    @php
                                        $totalMinutes = round(($task->total_time ?? 0) * 60);
                                        $h = floor($totalMinutes / 60);
                                        $m = $totalMinutes % 60;
                                        $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                    @endphp
                                    <span class="time-spend-badge">
                                        <i class="mdi mdi-timer-outline text-muted"></i> {{ $timeSpentFormatted }}
                                    </span>
                                </td>

                                {{-- 6. Start Date & End Date --}}
                                <td>
                                    <div class="d-flex flex-column font-size-11">
                                        <span class="text-muted">
                                            <strong class="text-dark">Start:</strong> {{ $task->startdate ? \Carbon\Carbon::parse($task->startdate)->format('d M Y') : ($task->created_at ? \Carbon\Carbon::parse($task->created_at)->format('d M Y') : '-') }}
                                        </span>
                                        <span class="text-muted mt-0.5">
                                            @if($task->status === 'Completed' && $task->act_enddate)
                                                <strong class="text-success">Done:</strong> {{ \Carbon\Carbon::parse($task->act_enddate)->format('d M Y') }}
                                            @else
                                                <strong class="text-dark">Due:</strong> {{ $task->enddate ? \Carbon\Carbon::parse($task->enddate)->format('d M Y') : '-' }}
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                {{-- 7. Status --}}
                                <td>
                                    @if($task->status === 'Completed')
                                        <span class="badge badge-soft-success font-size-11 px-2.5 py-1 font-weight-bold">
                                            <i class="mdi mdi-check mr-0.5"></i> Completed
                                        </span>
                                    @elseif($task->status === 'InProgress')
                                        <span class="badge badge-soft-warning font-size-11 px-2.5 py-1 font-weight-bold">
                                            <i class="mdi mdi-progress-clock mr-0.5"></i> In Progress
                                        </span>
                                    @else
                                        <span class="badge badge-soft-secondary font-size-11 px-2.5 py-1 font-weight-bold">
                                            <i class="mdi mdi-clock-outline mr-0.5"></i> To Do
                                        </span>
                                    @endif
                                </td>

                                {{-- 8. Actions --}}
                                <td class="text-right" style="padding-right: 20px;">
                                    <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="btn btn-light btn-sm rounded-pill px-3 py-1 border shadow-sm font-weight-semibold" title="Task Details & History" style="display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="mdi mdi-magnify font-size-13 text-primary"></i> Details
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap" style="background: #fafbfc;">
                    <div class="d-flex align-items-center flex-wrap mb-2 mb-sm-0" style="gap: 12px;">
                        <span class="text-muted font-size-12">
                            Showing <strong>{{ $tasks->firstItem() ?? 0 }}</strong> to <strong>{{ $tasks->lastItem() ?? 0 }}</strong> of <strong>{{ $tasks->total() }}</strong> tasks
                        </span>
                        <div class="d-inline-flex align-items-center" style="gap: 6px;">
                            <label class="text-muted font-size-11 mb-0">Per page:</label>
                            <select class="form-control form-control-sm" style="width: 75px; height: 30px; font-size: 12px; padding: 2px 6px;" onchange="window.location.href='{{ route('tasks.index', array_merge(request()->except(['page', 'per_page']), [])) }}&per_page=' + this.value">
                                <option value="25" {{ ($perPage ?? 50) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ ($perPage ?? 50) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ ($perPage ?? 50) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    @if($tasks->hasPages())
                    <div>
                        {{ $tasks->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({
                width: '100%'
            });
        }

        // Date Range Picker Initialization
        $('#task_date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        @if(!empty($filters['start_date']) && !empty($filters['end_date']))
            $('#task_date_range').val('{{ $filters['start_date'] }} - {{ $filters['end_date'] }}');
        @elseif(!empty($filters['start_date']))
            $('#task_date_range').val('From {{ $filters['start_date'] }}');
        @endif

        $('#task_date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
        });

        $('#task_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start_date').val('');
            $('#end_date').val('');
        });
    });
</script>
@endsection
