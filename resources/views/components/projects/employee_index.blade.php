@extends('layouts.app')

@section('styles')
<style>
    .project-hero-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1.5px solid #eaeaea;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
    }
    .project-hero-card:hover {
        transform: translateY(-10px);
        border-color: #556ee6;
        box-shadow: 0 20px 40px rgba(85, 110, 230, 0.08) !important;
    }
    .project-badge {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .progress-bar-trendy {
        height: 6px;
        border-radius: 10px;
        background-color: #f1f1f1;
    }
    .task-item-mini {
        padding: 12px 15px;
        border-radius: 12px;
        border: 1px solid #f5f5f5;
        background: #fafafa;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }
    .task-item-mini:hover {
        background: #ffffff;
        border-color: #e0e0e0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }
    .btn-action-trendy {
        border-radius: 10px;
        font-weight: 600;
        font-size: 12px;
        padding: 8px 16px;
        transition: all 0.2s ease;
    }
    .btn-action-trendy:hover {
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<div class="container-fluid pb-5">
    <!-- Header Section -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="mb-0 font-weight-bold text-dark" style="font-size: 1.8rem; letter-spacing: -0.8px;">My <span class="text-primary">Workspace</span></h4>
            <p class="text-muted mb-0">Manage your assigned projects and track task delivery.</p>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            <div class="d-inline-flex bg-white p-1 rounded-pill shadow-sm border">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="btn btn-sm px-4 rounded-pill {{ request('view') != 'list' ? 'btn-primary shadow' : 'btn-light' }}">
                    <i class="mdi mdi-view-grid-outline mr-1"></i> Grid
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="btn btn-sm px-4 rounded-pill {{ request('view') == 'list' ? 'btn-primary shadow' : 'btn-light' }}">
                    <i class="mdi mdi-format-list-bulleted mr-1"></i> List
                </a>
            </div>
        </div>
    </div>

    @if(isset($stats))
    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ url('projects?status=ToDo') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm" style="border-radius: 16px; border-top: 3px solid #f1b44c !important;">
                    <div class="card-body py-3">
                        <p class="text-muted font-size-11 font-weight-bold text-uppercase mb-1">Not Started</p>
                        <h3 class="mb-0 font-weight-bold text-dark">{{ $stats['not_started'] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ url('projects?status=InProgress') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm" style="border-radius: 16px; border-top: 3px solid #50a5f1 !important;">
                    <div class="card-body py-3">
                        <p class="text-muted font-size-11 font-weight-bold text-uppercase mb-1">In Progress</p>
                        <h3 class="mb-0 font-weight-bold text-dark">{{ $stats['in_progress'] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; border-top: 3px solid #34c38f !important;">
                <div class="card-body py-3">
                    <p class="text-muted font-size-11 font-weight-bold text-uppercase mb-1">Completed</p>
                    <h3 class="mb-0 font-weight-bold text-dark">{{ $stats['completed'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ url('projects') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm" style="border-radius: 16px; border-top: 3px solid #556ee6 !important;">
                    <div class="card-body py-3">
                        <p class="text-muted font-size-11 font-weight-bold text-uppercase mb-1">Total Projects</p>
                        <h3 class="mb-0 font-weight-bold text-dark">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif

    @if(request('view') == 'list')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr class="text-uppercase font-size-11 letter-spacing-1 text-muted">
                                    <th class="px-4 py-3 border-0">Project Detail</th>
                                    <th class="py-3 border-0">Client / Org</th>
                                    <th class="py-3 border-0 text-center">Workload</th>
                                    <th class="py-3 border-0" style="width: 250px;">Momentum</th>
                                    <th class="py-3 border-0 text-right pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                @php $myTasks = $project->tasks->where('assigned_to', Auth::id()); @endphp
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs mr-3">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary font-weight-bold">
                                                    {{ substr($project->project_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="font-weight-bold text-dark mb-0">{{ Str::limit($project->project_name, 50) }}</h6>
                                                <small class="text-muted">{{ $project->category->name ?? 'Internal' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <span class="text-dark font-weight-medium">{{ $project->clients->name ?? 'Company Project' }}</span>
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="badge badge-soft-info px-3 py-2" style="border-radius: 8px;">
                                            <i class="mdi mdi-checkbox-multiple-marked-outline mr-1"></i> {{ $myTasks->count() }} Tasks
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <div class="d-flex align-items-center">
                                            <div class="progress progress-sm flex-grow-1 mr-3" style="height: 8px; border-radius: 10px; background-color: #f1f1f1;">
                                                <div class="progress-bar bg-primary shadow-none" style="width: {{ $project->progress }}%; border-radius: 10px;"></div>
                                            </div>
                                            <span class="font-weight-bold text-dark">{{ $project->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-right pr-4">
                                        <div class="btn-group">
                                            <a href="{{ url('projects/taskboard/'.base64_encode($project->id)) }}" class="btn btn-sm btn-primary px-3 rounded-pill shadow-sm">
                                                <i class="mdi mdi-view-week-outline mr-1"></i> Board
                                            </a>
                                            <a href="{{ url('projects/'.base64_encode($project->id).'/history') }}" class="btn btn-sm btn-light px-3 rounded-pill ml-2 border">
                                                <i class="mdi mdi-pulse mr-1 text-primary"></i> Pulse
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.png') }}" alt="" style="height: 120px; opacity: 0.5;">
                                        <h5 class="mt-3 text-muted">No projects found in your workspace</h5>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        @forelse($projects as $project)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card project-hero-card h-100 shadow-none">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="project-badge bg-soft-primary text-primary">{{ $project->category->name ?? 'Internal' }}</span>
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
                        <div class="progress progress-bar-trendy">
                            <div class="progress-bar bg-primary" style="width: {{ $project->progress }}%"></div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                        <div class="avatar-group">
                            @php $myTasks = $project->tasks->where('assigned_to', Auth::id()); @endphp
                            <span class="badge badge-soft-info px-2 py-1" style="border-radius: 8px;">
                                <i class="mdi mdi-checkbox-multiple-marked-outline mr-1"></i> {{ $myTasks->count() }} My Tasks
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url('projects/'.base64_encode($project->id).'/history') }}" class="btn btn-action-trendy btn-soft-secondary mr-2">
                                <i class="mdi mdi-pulse mr-1"></i> Pulse
                            </a>
                            <button class="btn btn-action-trendy btn-primary shadow-sm" type="button" data-toggle="collapse" data-target="#tasks-{{ $project->id }}">
                                <i class="mdi mdi-layers-outline mr-1"></i> Taskboard
                            </button>
                        </div>
                    </div>

                    <!-- Collapsible Tasks Section -->
                    <div class="collapse mt-4" id="tasks-{{ $project->id }}">
                        <div class="pt-3 border-top">
                            <h6 class="font-size-12 text-uppercase font-weight-bold text-muted mb-3">My Current Tasks</h6>
                            @forelse($myTasks->whereIn('status', ['ToDo', 'InProgress']) as $task)
                            <div class="task-item-mini d-flex align-items-center justify-content-between">
                                <div class="overflow-hidden mr-3">
                                    <h6 class="font-size-13 mb-1 text-truncate">
                                        <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="text-dark font-weight-bold">{{ $task->title }}</a>
                                    </h6>
                                    <span class="badge badge-soft-{{ $task->status == 'InProgress' ? 'warning' : 'secondary' }} font-size-10">{{ $task->status }}</span>
                                </div>
                                <a href="{{ url('projects/task/'.base64_encode($task->id).'/history') }}" class="btn btn-sm btn-light rounded-circle">
                                    <i class="mdi mdi-arrow-right text-primary"></i>
                                </a>
                            </div>
                            @empty
                            <p class="text-center text-muted font-size-12 py-3">No active tasks for you here.</p>
                            @endforelse

                            @if($myTasks->where('status', 'Completed')->count() > 0)
                            <div class="text-center mt-2">
                                <a href="{{ url('projects/taskboard/'.base64_encode($project->id)) }}" class="font-size-11 font-weight-bold text-muted">View {{ $myTasks->where('status', 'Completed')->count() }} Completed Tasks</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="avatar-lg mx-auto mb-4">
                <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-24">
                    <i class="mdi mdi-folder-open-outline"></i>
                </span>
            </div>
            <h5 class="text-dark font-weight-bold">No Projects Assigned Yet</h5>
            <p class="text-muted">When you're assigned to a project, it will appear here for you to manage.</p>
        </div>
        @endforelse
    </div>
    @endif

    <!-- Pagination -->
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            {{ $projects->links() }}
        </div>
    </div>
</div>
@endsection
