{{-- Regular Employee (OD) Dashboard UI - Premium Version --}}

<div class="row erp-dash-hero-row">
    <div class="col-sm-6">
        <div class="d-flex align-items-center">
            <div class="avatar-sm mr-3">
                <span class="avatar-title rounded-circle bg-white shadow-sm text-primary border">
                    🎯
                </span>
            </div>
            <div>
                <h4 class="header-title erp-dash-title mb-0">My <span class="text-primary">Ecosystem</span></h4>
                <p class="text-muted mb-0 font-size-12 font-weight-medium">Performance analytics for {{ $adminData['selected_year'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="float-sm-right d-flex align-items-center mt-3 mt-sm-0">
            <div class="mr-4 erp-dash-year-box">
                <p class="erp-dash-year-box__label">Select Perspective</p>
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-calendar-range text-primary mr-2"></i>
                    <select class="form-control form-control-sm erp-dash-year-select" id="employee_dashboard_year_filter">
                        @foreach($adminData['available_years'] as $yr)
                        <option value="{{ $yr }}" {{ $adminData['selected_year'] == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ⚡ Feature 2: Daily Pulse Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card erp-pulse-banner shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="display-4 mr-3">⚡</div>
                            <div>
                                <h3 class="text-white font-weight-bold mb-1">Your Daily Pulse</h3>
                                <p class="text-white-50 mb-0 font-weight-medium">
                                    You've crushed <span class="text-white font-weight-bold">{{ $adminData['daily_pulse']['tasks_completed_today'] ?? 0 }} tasks</span> and logged <span class="text-white font-weight-bold">{{ $adminData['daily_pulse']['hours_logged_today'] ?? 0 }} hours</span> today. Keep it up!
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <div class="d-inline-flex align-items-center bg-white-10 px-3 py-2 rounded-pill" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                            <div class="spinner-grow spinner-grow-sm text-white mr-2" role="status"></div>
                            <span class="text-white font-weight-bold font-size-13">Live Session Active</span>
                        </div>
                    </div>
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
                <div class="card-body p-3">
                    <div class="stat-icon-box bg-soft-indigo">
                        <i class="mdi mdi-checkbox-marked-circle-outline font-size-24"></i>
                    </div>
                    <p class="text-muted font-weight-bold mb-1 text-uppercase font-size-11">Total Tasks</p>
                    <h2 class="mb-0 font-weight-bold text-dark">{{ $adminData['total_tasks_assigned'] }}</h2>
                    <div class="mt-2 d-flex flex-wrap gap-1">
                        <span class="badge badge-soft-warning font-size-10 mr-1 mb-1">{{ $adminData['todo_tasks_count'] }} To Do</span>
                        <span class="badge badge-soft-info font-size-10 mr-1 mb-1">{{ $adminData['active_tasks_count'] }} In Progress</span>
                        <span class="badge badge-soft-success font-size-10 mb-1">{{ $adminData['completed_tasks_count'] }} Completed</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ url('/projects') }}?status=Completed" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none">
                <div class="card-body p-3">
                    <div class="stat-icon-box bg-soft-emerald">
                        <i class="mdi mdi-check-all font-size-24"></i>
                    </div>
                    <p class="text-muted font-weight-bold mb-1 text-uppercase font-size-11">Tasks Completed</p>
                    <h2 class="mb-0 font-weight-bold text-dark">{{ $adminData['completed_tasks_count'] }}</h2>
                    <div class="mt-2">
                        <span class="text-muted font-size-11">Total Assigned: {{ $adminData['total_tasks_assigned'] }}</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ url('/projects') }}" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none">
                <div class="card-body p-3">
                    <div class="stat-icon-box bg-soft-amber">
                        <i class="mdi mdi-timer-sand font-size-24"></i>
                    </div>
                    <p class="text-muted font-weight-bold mb-1 text-uppercase font-size-11">Inprogress Tasks</p>
                    <h2 class="mb-0 font-weight-bold text-dark">{{ $adminData['active_tasks_count'] }}</h2>
                    <div class="mt-2">
                        <span class="badge badge-soft-warning font-size-10">{{ $adminData['pending_tasks_count'] }} Pending</span>
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
                <h2 class="mb-0 font-weight-bold text-dark">{{ $adminData['total_hours'] }}h</h2>
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
                                <span class="badge badge-soft-info font-size-10">{{ $proj->projectCategory->category ?? 'Internal' }}</span>
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
            <div class="card-header bg-white border-bottom py-3">
                <ul class="nav nav-tabs-custom card-header-tabs border-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" data-toggle="tab" href="#todays-tasks" role="tab">
                            Today's Tasks <span class="badge badge-pill badge-soft-info ml-1">{{ count($adminData['todays_tasks']) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" data-toggle="tab" href="#active-tasks" role="tab">
                            Active Tasks <span class="badge badge-pill badge-soft-primary ml-1">{{ count($adminData['my_tasks']) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" data-toggle="tab" href="#completed-tasks" role="tab">
                            Completed <span class="badge badge-pill badge-soft-success ml-1">{{ count($adminData['recently_completed_tasks']) }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content">
                    <!-- Today's Tasks Tab -->
                    <div class="tab-pane active" id="todays-tasks" role="tabpanel">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <ul class="list-group list-group-flush">
                                @forelse($adminData['todays_tasks'] as $task)
                                <li class="list-group-item border-0 py-3 mb-2 mx-2 rounded-lg transition-all" style="transition: transform 0.2s;">
                                    @php
                                    $isUrgent = $task->priority === 'High' || $task->priority === 'Urgent';
                                    $isCompleted = $task->status === 'Completed';
                                    @endphp
                                    <div class="d-flex align-items-center {{ $isCompleted ? 'bg-soft-success border-left border-success' : 'bg-soft-info' }} p-2 rounded-lg">
                                        <div class="mr-3">
                                            <div class="avatar-xs">
                                                <span class="avatar-title rounded-circle bg-{{ $isCompleted ? 'success' : ($isUrgent ? 'warning' : 'info') }} text-white font-size-12">
                                                    @if($isCompleted)
                                                    <i class="mdi mdi-check"></i>
                                                    @elseif($isUrgent)
                                                    <i class="mdi mdi-clock-alert"></i>
                                                    @else
                                                    <i class="mdi mdi-calendar-check"></i>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="font-size-13 mb-1 text-dark text-truncate font-weight-bold">
                                                <a href="{{ url('projects/taskboard/' . base64_encode($task->projectid)) }}" class="text-dark">{{ $task->title }}</a>
                                                @if($isCompleted)
                                                <span class="badge badge-success ml-1">COMPLETED</span>
                                                @else
                                                <span class="badge badge-info ml-1">{{ $task->status }}</span>
                                                @endif
                                            </h6>
                                            <p class="text-muted font-size-11 mb-0 text-truncate">
                                                {{ $proj->project_name ?? ($task->project->project_name ?? 'Task Project') }} • Priority: <strong>{{ $task->priority }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                @empty
                                <li class="list-group-item text-center py-4 text-muted">No tasks recorded or worked on today.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Active Tasks Tab -->
                    <div class="tab-pane" id="active-tasks" role="tabpanel">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <ul class="list-group list-group-flush">
                                @forelse($adminData['my_tasks'] as $task)
                                <li class="list-group-item border-0 py-3 mb-2 mx-2 rounded-lg transition-all" style="transition: transform 0.2s;">
                                    @php
                                    $isUrgent = false;
                                    $isOverdue = false;
                                    $isNew = \Carbon\Carbon::parse($task->created_at)->gt(\Carbon\Carbon::now()->subHours(24));

                                    if($task->enddate) {
                                    $end = \Carbon\Carbon::parse($task->enddate);
                                    $daysLeft = \Carbon\Carbon::now()->diffInDays($end, false);
                                    if($daysLeft < 0) {
                                        $isOverdue=true;
                                        $isUrgent=true;
                                        } elseif($daysLeft <=2) {
                                        $isUrgent=true;
                                        }
                                        }
                                        @endphp
                                        <div class="d-flex align-items-center {{ $isOverdue ? 'bg-soft-rose border-left border-danger' : ($isNew ? 'new-task-glow bg-soft-info' : '') }} p-2 rounded-lg">
                                        <div class="mr-3">
                                            <div class="avatar-xs">
                                                <span class="avatar-title rounded-circle bg-{{ $isOverdue ? 'danger' : ($isUrgent ? 'warning' : ($isNew ? 'info' : 'primary')) }} text-white font-size-12 {{ $isUrgent || $isNew ? 'urgent-pulse' : '' }}">
                                                    @if($isOverdue)
                                                    <i class="mdi mdi-alert-circle"></i>
                                                    @elseif($isUrgent)
                                                    <i class="mdi mdi-clock-alert"></i>
                                                    @elseif($isNew)
                                                    <i class="mdi mdi-star"></i>
                                                    @else
                                                    {{ substr($task->priority, 0, 1) }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="font-size-13 mb-1 text-truncate font-weight-bold">
                                                <a href="{{ url('projects/taskboard/' . base64_encode($task->projectid)) }}" class="text-dark">{{ $task->title }}</a>
                                                @if($isOverdue)
                                                <span class="badge badge-danger ml-1">OVERDUE</span>
                                                @elseif($isUrgent)
                                                <span class="badge badge-warning ml-1">URGENT</span>
                                                @elseif($isNew)
                                                <span class="badge badge-info ml-1">NEW</span>
                                                @endif
                                            </h6>
                                            <p class="text-muted font-size-11 mb-0 text-truncate">
                                                {{ $task->project->project_name }}
                                                @if($task->enddate)
                                                • <span class="{{ $isUrgent ? 'text-danger font-weight-bold' : '' }}">
                                                    @if($isOverdue)
                                                    Missed by {{ abs($daysLeft) }} days
                                                    @else
                                                    Due {{ \Carbon\Carbon::parse($task->enddate)->format('d M') }}
                                                    @endif
                                                </span>
                                                @endif
                                            </p>
                                        </div>
                        </div>
                        </li>
                        @empty
                        <li class="list-group-item text-center py-4 text-muted">All caught up!</li>
                        @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Completed Tasks Tab -->
                <div class="tab-pane" id="completed-tasks" role="tabpanel">
                    <div style="max-height: 400px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            @forelse($adminData['recently_completed_tasks'] as $task)
                            <li class="list-group-item border-0 py-3 bg-light-50">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="avatar-xs">
                                            <span class="avatar-title rounded-circle bg-soft-success text-success font-size-12">
                                                <i class="mdi mdi-check"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="font-size-13 mb-1 text-truncate font-weight-bold text-muted">
                                            <a href="{{ url('projects/taskboard/' . base64_encode($task->projectid)) }}" class="text-muted">{{ $task->title }}</a>
                                        </h6>
                                        <small class="text-muted">Done {{ $task->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="list-group-item text-center py-4 text-muted">No recently completed tasks.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
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
                        <p class="text-dark font-size-12 mb-0 text-truncate font-weight-medium">
                            {{ $log->log_description ?: ($log->endtime ? 'Work Logged' : 'Timer Started') }}
                        </p>
                        @php
                        if (is_null($log->time_spend)) {
                            $timeSpentFormatted = 'Running';
                        } else {
                            $totalMinutes = round($log->time_spend * 60);
                            $h = floor($totalMinutes / 60);
                            $m = $totalMinutes % 60;
                            $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                        }
                        @endphp
                        <small class="text-muted">{{ $log->created_at->diffForHumans() }} • {{ $timeSpentFormatted }}</small>
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
