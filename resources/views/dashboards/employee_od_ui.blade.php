{{-- Regular Employee (OD) Dashboard UI - Premium Version --}}
<style>
    .emp-stat-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1.5px solid #f0f0f0;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
    }

    .emp-stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(85, 110, 230, 0.08) !important;
        border-color: #556ee6;
    }

    .emp-stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .emp-stat-card:hover::before {
        opacity: 1;
    }

    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        transition: all 0.4s ease;
    }

    .emp-stat-card:hover .stat-icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .bg-soft-indigo {
        background: #e0e7ff;
        color: #4338ca;
    }

    .bg-soft-emerald {
        background: #d1fae5;
        color: #047857;
    }

    .bg-soft-amber {
        background: #fef3c7;
        color: #b45309;
    }

    .bg-soft-rose {
        background: #ffe4e6;
        color: #be123c;
    }

    .project-card-mini {
        border-radius: 12px;
        border: 1.5px solid #f1f1f1;
        background: #fafafa;
        padding: 15px;
        transition: all 0.3s ease;
    }

    .project-card-mini:hover {
        background: #ffffff;
        border-color: #556ee6;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04);
    }
</style>

<div class="row mb-4 align-items-center">
    <div class="col-sm-6">
        <div class="d-flex align-items-center">
            <div class="avatar-sm mr-3">
                <span class="avatar-title rounded-circle bg-white shadow-sm text-primary border" style="font-size: 24px;">
                    🎯
                </span>
            </div>
            <div>
                <h4 class="header-title mb-0" style="font-weight: 800; color: #1a1a1a; letter-spacing: -0.8px; font-size: 1.6rem;">My <span class="text-primary">Ecosystem</span></h4>
                <p class="text-muted mb-0 font-size-12 font-weight-medium">Performance analytics for {{ $adminData['selected_year'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="float-sm-right d-flex align-items-center mt-3 mt-sm-0">
            <div class="mr-4 px-3 py-2 bg-white rounded shadow-sm border border-light">
                <p class="text-muted mb-0 font-size-10 text-uppercase letter-spacing-1 font-weight-bold">Select Perspective</p>
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-calendar-range text-primary mr-2 font-size-16"></i>
                    <select class="form-control form-control-sm border-0 shadow-none p-0 font-size-15" id="employee_dashboard_year_filter" style="font-weight: 800; color: #343a40; background: transparent; cursor: pointer; height: auto; width: 80px;">
                        @foreach($adminData['available_years'] as $yr)
                        <option value="{{ $yr }}" {{ $adminData['selected_year'] == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- 📊 KPI Row: Clean Cards -->
    <div class="col-md-3">
        <a href="{{ url('/projects') }}" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none">
                <div class="card-body p-4">
                    <div class="stat-icon-box bg-soft-indigo">
                        <i class="mdi mdi-rocket-launch-outline font-size-24"></i>
                    </div>
                    <p class="text-muted font-weight-bold mb-1 text-uppercase font-size-11">Total Projects</p>
                    <h2 class="mb-0 font-weight-bold" style="color: #1a1a1a;">{{ $adminData['projects_assigned_count'] }}</h2>
                    <div class="mt-2">
                        <span class="badge badge-soft-success font-size-10">{{ $adminData['completed_projects_count'] }} Completed</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ url('/projects') }}?status=Completed" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none">
                <div class="card-body p-4">
                    <div class="stat-icon-box bg-soft-emerald">
                        <i class="mdi mdi-check-all font-size-24"></i>
                    </div>
                    <p class="text-muted font-weight-bold mb-1 text-uppercase font-size-11">Tasks Completed</p>
                    <h2 class="mb-0 font-weight-bold" style="color: #1a1a1a;">{{ $adminData['completed_tasks_count'] }}</h2>
                    <div class="mt-2">
                        <span class="text-muted font-size-11">Total Assigned: {{ $adminData['total_tasks_assigned'] }}</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ url('/projects') }}?status=Pending" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none">
                <div class="card-body p-4">
                    <div class="stat-icon-box bg-soft-amber">
                        <i class="mdi mdi-timer-sand font-size-24"></i>
                    </div>
                    <p class="text-muted font-weight-bold mb-1 text-uppercase font-size-11">Pending Work</p>
                    <h2 class="mb-0 font-weight-bold" style="color: #1a1a1a;">{{ $adminData['pending_tasks_count'] }}</h2>
                    <div class="mt-2">
                        <span class="badge badge-soft-warning font-size-10">{{ $adminData['active_tasks_count'] }} In Progress</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="card emp-stat-card border-0 shadow-none">
            <div class="card-body p-4">
                <div class="stat-icon-box bg-soft-rose">
                    <i class="mdi mdi-clock-fast font-size-24"></i>
                </div>
                <p class="text-muted font-weight-bold mb-1 text-uppercase font-size-11">Work Capacity</p>
                <h2 class="mb-0 font-weight-bold" style="color: #1a1a1a;">{{ $adminData['total_hours'] }}h</h2>
                <div class="mt-2">
                    <span class="text-muted font-size-11">Avg Speed: {{ $adminData['avg_task_duration'] }}h/task</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <!-- 📈 Productivity Curve -->
    <div class="col-lg-8">
        <div class="card trendy-card shadow-sm border-0 rounded-lg overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="font-size-15 mb-0 text-dark font-weight-bold">Productivity Curve</h5>
                    <small class="text-muted">Task completion trend across the year</small>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-soft-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        More Stats
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#">Download Report</a>
                        <a class="dropdown-item" href="#">Detailed View</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="employee-growth-chart" class="apex-charts" style="min-height: 280px;"></div>
            </div>
        </div>

        <!-- 🚀 Recent Projects involved in -->
        <div class="card trendy-card shadow-sm border-0 rounded-lg mt-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-size-15 mb-0 text-dark font-weight-bold">Recent Projects & Contributions</h5>
                <div class="btn-group btn-group-sm">
                    <a href="{{ request()->fullUrlWithQuery(['dash_view' => 'grid']) }}" class="btn {{ request('dash_view') != 'list' ? 'btn-soft-primary active' : 'btn-light' }}">
                        <i class="mdi mdi-view-grid-outline"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['dash_view' => 'list']) }}" class="btn {{ request('dash_view') == 'list' ? 'btn-soft-primary active' : 'btn-light' }}">
                        <i class="mdi mdi-format-list-bulleted"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(request('dash_view') == 'list')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted font-size-11 text-uppercase letter-spacing-1">
                                <th class="border-0">Project</th>
                                <th class="border-0">Progress</th>
                                <th class="border-0 text-center">Tasks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminData['recent_projects'] as $proj)
                            <tr>
                                <td class="py-3">
                                    <h6 class="font-size-13 mb-0 font-weight-bold">{{ Str::limit($proj->project_name, 40) }}</h6>
                                    <small class="text-muted">{{ $proj->clients->name ?? 'Internal' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center" style="min-width: 120px;">
                                        <div class="progress progress-sm flex-grow-1 mr-2" style="height: 4px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $proj->progress }}%"></div>
                                        </div>
                                        <small class="font-weight-bold">{{ $proj->progress }}%</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-soft-info">{{ $proj->user_tasks_count }} Tasks</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted font-size-12">No active projects.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
                <div class="row">
                    @forelse($adminData['recent_projects'] as $proj)
                    <div class="col-md-6 mb-3">
                        <div class="project-card-mini">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge badge-soft-info font-size-10">{{ $proj->category->name ?? 'Internal' }}</span>
                                <small class="text-muted">{{ $proj->user_tasks_count }} Tasks</small>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-1">{{ Str::limit($proj->project_name, 30) }}</h6>
                            <p class="text-muted font-size-11 mb-2">{{ $proj->clients->name ?? 'Internal Project' }}</p>
                            <div class="progress progress-sm" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: {{ $proj->progress }}%"></div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4">
                        <p class="text-muted mb-0">No active projects found for this period.</p>
                    </div>
                    @endforelse
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 📋 Sidebar: Taskboard & Activity -->
    <div class="col-lg-4">
        <div class="card trendy-card shadow-sm border-0 rounded-lg mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="font-size-15 mb-0 text-dark font-weight-bold">Actionable Tasks</h5>
                <span class="badge badge-pill badge-soft-primary px-2">{{ count($adminData['my_tasks']) }} Active</span>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <ul class="list-group list-group-flush">
                    @forelse($adminData['my_tasks'] as $task)
                    <li class="list-group-item border-0 py-3">
                        <div class="d-flex">
                            <div class="mr-3">
                                <div class="avatar-xs">
                                    <span class="avatar-title rounded-circle bg-soft-{{ $task->priority == 'High' ? 'danger' : ($task->priority == 'Medium' ? 'warning' : 'info') }} text-{{ $task->priority == 'High' ? 'danger' : ($task->priority == 'Medium' ? 'warning' : 'info') }} font-size-12">
                                        {{ substr($task->priority, 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="font-size-13 mb-1 text-truncate font-weight-bold">
                                    <a href="{{ url('projects/taskboard/' . base64_encode($task->projectid)) }}" class="text-dark">{{ $task->title }}</a>
                                </h6>
                                <p class="text-muted font-size-11 mb-0 text-truncate">{{ $task->project->project_name }}</p>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4 text-muted">All caught up!</li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer bg-white border-top text-center py-2">
                <a href="{{ url('projects') }}" class="btn btn-sm btn-link text-primary font-weight-bold">View Taskboard</a>
            </div>
        </div>

        <div class="card trendy-card shadow-sm border-0 rounded-lg">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="font-size-15 mb-0 text-dark font-weight-bold">Recent Pulse</h5>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    @forelse($adminData['recent_logs'] as $log)
                    <div class="d-flex mb-3 align-items-center">
                        <div class="avatar-xs mr-3">
                            <span class="avatar-title rounded bg-light text-primary font-size-10">
                                <i class="mdi mdi-history"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-dark font-size-12 mb-0 text-truncate font-weight-medium">{{ $log->log_description }}</p>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }} • {{ $log->time_spend }}m</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted small py-3">No recent updates.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
@include('dashboards.employee_scripts')
@endsection
