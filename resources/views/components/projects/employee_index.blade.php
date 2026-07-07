@extends('layouts.app')

@section('styles')
<style>
    .employee-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    .custom-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        font-size: 14px;
        padding: 10px 20px;
        border-radius: 30px;
        margin-right: 8px;
        transition: all 0.2s ease;
    }
    .custom-tabs .nav-link.active {
        background-color: #556ee6;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(85, 110, 230, 0.2);
    }
    .task-card-trendy {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #eaeaea;
        transition: all 0.3s ease;
    }
    .task-card-trendy:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
        border-color: #556ee6;
    }
    .custom-table th {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.8px;
        color: #5c6a7a !important;
        font-weight: 700 !important;
        background-color: #f8fafc;
        border-bottom: 2px solid #edf2f7 !important;
    }
    .custom-table td {
        vertical-align: middle !important;
        color: #2d3748;
        font-size: 13.5px;
    }
    .badge-priority {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .priority-high { background-color: rgba(244, 67, 54, 0.1); color: #f44336; }
    .priority-medium { background-color: rgba(255, 152, 0, 0.1); color: #ff9800; }
    .priority-low { background-color: rgba(76, 175, 80, 0.1); color: #4caf50; }
</style>
@endsection

@section('content')
<div class="container-fluid employee-wrapper pb-5">
    
    <!-- Workspace Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="mb-0 font-weight-bold text-dark" style="font-size: 1.8rem; letter-spacing: -0.8px;">📁 My <span class="text-primary">Workspace</span></h4>
            <p class="text-muted mb-0">Track and manage your tasks, timelines, and deliverables.</p>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            <ul class="nav nav-pills custom-tabs d-inline-flex bg-white p-1 rounded-pill shadow-sm border" id="workspaceTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tasks-tab" data-toggle="tab" href="#tasks" role="tab" aria-controls="tasks" aria-selected="true">
                        <i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i> Active Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="completed-tab" data-toggle="tab" href="#completed-tasks" role="tab" aria-controls="completed-tasks" aria-selected="false">
                        <i class="mdi mdi-history mr-1"></i> Completed History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="projects-tab" data-toggle="tab" href="#projects" role="tab" aria-controls="projects" aria-selected="false">
                        <i class="mdi mdi-briefcase-outline mr-1"></i> My Projects
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tabs Content -->
    <div class="tab-content" id="workspaceTabsContent">
        
        <!-- Tab 1: Active Tasks (Default view) -->
        <div class="tab-pane fade show active" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
            <div class="card border shadow-sm">
                <div class="card-body p-0">
                    @if($activeTasks->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-clipboard-text-outline display-4 text-warning mb-2 d-block"></i>
                            <h5 class="font-weight-bold">No active tasks assigned to you</h5>
                            <p class="font-size-13 mb-0">You're currently all caught up on your tasks!</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table custom-table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th style="padding-left: 24px;">Task Title</th>
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Time Spent</th>
                                        <th>Due Date</th>
                                        <th class="text-right" style="padding-right: 24px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeTasks as $task)
                                    <tr>
                                        <td style="padding-left: 24px;">
                                            <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="text-dark font-weight-bold">
                                                {{ Str::limit($task->title, 60) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="text-muted font-weight-semibold">
                                                {{ $task->project->project_name ?? 'Internal Project' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task->status === 'ToDo')
                                                <span class="badge badge-soft-secondary font-size-11 px-2.5 py-0.5">To Do</span>
                                            @elseif($task->status === 'InProgress')
                                                <span class="badge badge-soft-warning font-size-11 px-2.5 py-0.5">In Progress</span>
                                            @else
                                                <span class="badge badge-soft-info font-size-11 px-2.5 py-0.5">{{ $task->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php 
                                                $prio = strtolower($task->priority); 
                                                $class = in_array($prio, ['high', 'medium', 'low']) ? 'priority-' . $prio : 'priority-low';
                                            @endphp
                                            <span class="badge-priority {{ $class }}">
                                                {{ $task->priority }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                            $totalMinutes = round(($task->total_time ?? 0) * 60);
                                            $h = floor($totalMinutes / 60);
                                            $m = $totalMinutes % 60;
                                            $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                            @endphp
                                            <span class="font-weight-semibold text-dark">{{ $timeSpentFormatted }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $task->enddate ? \Carbon\Carbon::parse($task->enddate)->format('d M Y') : '-' }}
                                            </span>
                                        </td>
                                        <td class="text-right" style="padding-right: 24px;">
                                            @if($task->project)
                                                <a href="{{ url('projects/taskboard/'.base64_encode($task->project->id)) }}" class="btn btn-primary btn-sm rounded-pill px-3 mr-1 shadow-sm">
                                                    <i class="mdi mdi-view-week-outline mr-1"></i> Board
                                                </a>
                                            @endif
                                            <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="btn btn-light btn-sm rounded-pill px-3 border">
                                                <i class="mdi mdi-magnify mr-1"></i> Details
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tab 1.5: Completed Task History -->
        <div class="tab-pane fade" id="completed-tasks" role="tabpanel" aria-labelledby="completed-tab">
            <div class="card border shadow-sm">
                <div class="card-body p-0">
                    @if($completedTasks->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-history display-4 text-secondary mb-2 d-block"></i>
                            <h5 class="font-weight-bold">No completed tasks yet</h5>
                            <p class="font-size-13 mb-0">Tasks you complete will show up in this history tab.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table custom-table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th style="padding-left: 24px;">Task Title</th>
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Time Spent</th>
                                        <th>Completed Date</th>
                                        <th class="text-right" style="padding-right: 24px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completedTasks as $task)
                                    <tr>
                                        <td style="padding-left: 24px;">
                                            <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="text-dark font-weight-bold">
                                                {{ Str::limit($task->title, 60) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="text-muted font-weight-semibold">
                                                {{ $task->project->project_name ?? 'Internal Project' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-success font-size-11 px-2.5 py-0.5">Completed</span>
                                        </td>
                                        <td>
                                            @php 
                                                $prio = strtolower($task->priority); 
                                                $class = in_array($prio, ['high', 'medium', 'low']) ? 'priority-' . $prio : 'priority-low';
                                            @endphp
                                            <span class="badge-priority {{ $class }}">
                                                {{ $task->priority }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                            $totalMinutes = round(($task->total_time ?? 0) * 60);
                                            $h = floor($totalMinutes / 60);
                                            $m = $totalMinutes % 60;
                                            $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                            @endphp
                                            <span class="font-weight-semibold text-dark">{{ $timeSpentFormatted }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $task->act_enddate ? \Carbon\Carbon::parse($task->act_enddate)->format('d M Y h:i A') : ($task->updated_at ? \Carbon\Carbon::parse($task->updated_at)->format('d M Y') : '-') }}
                                            </span>
                                        </td>
                                        <td class="text-right" style="padding-right: 24px;">
                                            @if($task->project)
                                                <a href="{{ url('projects/taskboard/'.base64_encode($task->project->id)) }}" class="btn btn-primary btn-sm rounded-pill px-3 mr-1 shadow-sm">
                                                    <i class="mdi mdi-view-week-outline mr-1"></i> Board
                                                </a>
                                            @endif
                                            <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="btn btn-light btn-sm rounded-pill px-3 border">
                                                <i class="mdi mdi-magnify mr-1"></i> Details
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tab 2: Projects (Show only if they select it) -->
        <div class="tab-pane fade" id="projects" role="tabpanel" aria-labelledby="projects-tab">
            <div class="row">
                @forelse($projects as $project)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card task-card-trendy h-100 shadow-none">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge badge-soft-primary px-2.5 py-1" style="border-radius: 6px; font-size: 11px;">
                                    {{ $project->projectCategory->category ?? 'Internal' }}
                                </span>
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle arrow-none text-muted" data-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical font-size-18"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{{ url('projects/'.base64_encode($project->id).'/history') }}"><i class="mdi mdi-history mr-1"></i> View Pulse</a>
                                        <a class="dropdown-item" href="{{ url('projects/taskboard/'.base64_encode($project->id)) }}"><i class="mdi mdi-view-week-outline mr-1"></i> Open Board</a>
                                    </div>
                                </div>
                            </div>

                            <h5 class="font-weight-bold text-dark mb-1">{{ Str::limit($project->project_name, 40) }}</h5>
                            <p class="text-muted font-size-12 mb-3">
                                <i class="mdi mdi-account-tie mr-1"></i> {{ $project->clients->name ?? 'Company Project' }}
                            </p>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="font-size-12 text-muted font-weight-medium">Delivery Momentum</span>
                                    <span class="font-size-12 font-weight-bold text-dark">{{ $project->progress }}%</span>
                                </div>
                                <div class="progress progress-sm" style="height: 6px; border-radius: 10px; background-color: #f1f1f1;">
                                    <div class="progress-bar bg-primary" style="width: {{ $project->progress }}%"></div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                                @php $myTasksCount = $project->tasks->where('assigned_to', Auth::id())->count(); @endphp
                                <span class="badge badge-soft-info px-2.5 py-1" style="border-radius: 8px;">
                                    <i class="mdi mdi-checkbox-multiple-marked-outline mr-1"></i> {{ $myTasksCount }} Tasks
                                </span>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('projects/'.base64_encode($project->id).'/history') }}" class="btn btn-sm btn-soft-secondary mr-2 rounded-pill px-3">
                                        <i class="mdi mdi-pulse mr-1"></i> Pulse
                                    </a>
                                    <a href="{{ url('projects/taskboard/'.base64_encode($project->id)) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="mdi mdi-layers-outline mr-1"></i> Taskboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="mdi mdi-folder-open-outline display-4 d-block mb-2"></i>
                    <p class="font-size-13 mb-0">No projects found in your workspace.</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="row">
                <div class="col-12 d-flex justify-content-center">
                    {{ $projects->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
