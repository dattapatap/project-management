{{-- Regular Employee (OD) Dashboard UI - Premium Version --}}

<div class="row erp-dash-hero-row align-items-center mb-3">
    <div class="col-sm-6">
        <div class="d-flex align-items-center">
            <div class="avatar-sm mr-3">
                <span class="avatar-title rounded-circle bg-white shadow-sm text-primary border" style="font-size: 20px;">
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
        <div class="float-sm-right d-flex align-items-center mt-3 mt-sm-0" style="gap: 8px;">
            <button type="button" class="btn btn-sm btn-white border shadow-sm px-3 py-1.5 font-weight-bold" data-toggle="modal" data-target="#staffDirectoryModal" style="border-radius: 10px;">
                <i class="mdi mdi-account-group text-primary mr-1"></i> Staffs
            </button>
            <a href="{{ route('hrms.holidays.index') }}" class="btn btn-sm btn-white border shadow-sm px-3 py-1.5 font-weight-bold" style="border-radius: 10px;">
                <i class="mdi mdi-beach text-warning mr-1"></i> Holidays
            </a>
            <div class="erp-dash-year-box ml-2">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-calendar-range text-primary mr-1.5"></i>
                    <select class="form-control form-control-sm erp-dash-year-select" id="employee_dashboard_year_filter" style="border-radius: 8px;">
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
        <div class="card erp-pulse-banner shadow-sm" style="border-radius: 16px;">
            <div class="card-body py-3.5 px-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="display-4 mr-3.5" style="font-size: 32px;">⚡</div>
                            <div>
                                <h4 class="text-white font-weight-bold mb-1 font-size-17">Your Daily Pulse</h4>
                                <p class="text-white-50 mb-0 font-weight-medium font-size-12">
                                    You've crushed <span class="text-white font-weight-bold">{{ $adminData['daily_pulse']['tasks_completed_today'] ?? 0 }} tasks</span> and logged <span class="text-white font-weight-bold">{{ $adminData['daily_pulse']['hours_logged_today'] ?? 0 }} hours</span> today. Keep it up!
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <div class="d-inline-flex align-items-center px-3 py-1.5 rounded-pill" style="background: rgba(255,255,255,0.18); backdrop-filter: blur(10px);">
                            <div class="spinner-grow spinner-grow-sm text-white mr-2" role="status" style="width: 8px; height: 8px;"></div>
                            <span class="text-white font-weight-bold font-size-12">Live Session Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- 📊 KPI Row: Clean Cards -->
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/projects') }}" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none h-100">
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
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/projects') }}?status=Completed" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none h-100">
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
    <div class="col-md-3 mb-3 mb-md-0">
        <a href="{{ url('/projects') }}" class="text-decoration-none">
            <div class="card emp-stat-card border-0 shadow-none h-100">
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
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card emp-stat-card border-0 shadow-none h-100">
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

<div class="row mb-4">
    <!-- 📈 1. Productivity Curve (col-lg-6) -->
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="card trendy-card shadow-sm border-0 rounded-lg overflow-hidden h-100" style="border-radius: 16px; background: #ffffff;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-radius: 16px 16px 0 0;">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center mr-3 text-primary" style="background: #eef2ff; width: 32px; height: 32px;">
                        <i class="mdi mdi-chart-timeline-variant font-size-16"></i>
                    </div>
                    <div>
                        <h5 class="font-size-15 mb-0 text-dark font-weight-bold">Productivity Curve</h5>
                        <small class="text-muted">Task completion trend across {{ $adminData['selected_year'] }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-3.5">
                <div id="employee-growth-chart" class="apex-charts" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>

    <!-- 📋 2. View Project Taskboard Card (col-lg-6) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
            <div class="card-header bg-white py-3 px-3.5 border-bottom" style="border-radius: 16px 16px 0 0;">
                <ul class="nav nav-pills nav-justified" role="tablist" style="gap: 4px;">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold font-size-12 py-1.5 px-2 text-truncate" data-toggle="tab" href="#todays-tasks" role="tab" style="border-radius: 8px;">
                            Today <span class="badge badge-pill badge-soft-info ml-1">{{ count($adminData['todays_tasks']) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold font-size-12 py-1.5 px-2 text-truncate" data-toggle="tab" href="#active-tasks" role="tab" style="border-radius: 8px;">
                            Active <span class="badge badge-pill badge-soft-primary ml-1">{{ count($adminData['my_tasks']) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold font-size-12 py-1.5 px-2 text-truncate" data-toggle="tab" href="#completed-tasks" role="tab" style="border-radius: 8px;">
                            Done <span class="badge badge-pill badge-soft-success ml-1">{{ count($adminData['recently_completed_tasks']) }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content">
                    <!-- Today's Tasks Tab -->
                    <div class="tab-pane active" id="todays-tasks" role="tabpanel">
                        <div style="max-height: 290px; overflow-y: auto; padding: 12px;">
                            <div class="d-flex flex-column" style="gap: 8px;">
                                @forelse($adminData['todays_tasks'] as $task)
                                @php
                                $isUrgent = $task->priority === 'High' || $task->priority === 'Urgent';
                                $isCompleted = $task->status === 'Completed';
                                @endphp
                                <div class="p-2.5 rounded-lg d-flex align-items-center justify-content-between {{ $isCompleted ? 'bg-soft-success' : 'bg-light' }}" style="border-radius: 12px; border: 1px solid #edf2f7;">
                                    <div class="d-flex align-items-center overflow-hidden mr-2">
                                        <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 {{ $isCompleted ? 'bg-success' : ($isUrgent ? 'bg-warning' : 'bg-primary') }}" style="width: 32px; height: 32px; font-size: 13px; margin-right: 14px !important;">
                                            <i class="mdi {{ $isCompleted ? 'mdi-check' : ($isUrgent ? 'mdi-clock-alert' : 'mdi-checkbox-marked-circle-outline') }}"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="font-size-12 mb-0.5 text-truncate font-weight-bold">
                                                <a href="{{ url('projects/taskboard/' . base64_encode($task->projectid)) }}" class="text-dark">{{ $task->title }}</a>
                                            </h6>
                                            <span class="text-muted font-size-10 d-block text-truncate">
                                                {{ $task->project->project_name ?? 'Project' }} &bull; {{ $task->priority }} Priority
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge {{ $isCompleted ? 'badge-success' : 'badge-soft-primary' }} font-size-9 font-weight-bold flex-shrink-0 px-1.5 py-0.5">
                                        {{ $task->status }}
                                    </span>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted font-size-12">
                                    <i class="mdi mdi-checkbox-marked-circle-outline font-size-24 d-block text-muted mb-1"></i>
                                    No tasks recorded or worked on today.
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Active Tasks Tab -->
                    <div class="tab-pane" id="active-tasks" role="tabpanel">
                        <div style="max-height: 290px; overflow-y: auto; padding: 12px;">
                            <div class="d-flex flex-column" style="gap: 8px;">
                                @forelse($adminData['my_tasks'] as $task)
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
                                    <div class="p-2.5 rounded-lg d-flex align-items-center justify-content-between {{ $isOverdue ? 'bg-soft-danger' : ($isNew ? 'bg-soft-info' : 'bg-light') }}" style="border-radius: 12px; border: 1px solid #edf2f7;">
                                    <div class="d-flex align-items-center overflow-hidden mr-2">
                                        <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 {{ $isOverdue ? 'bg-danger' : ($isUrgent ? 'bg-warning' : 'bg-primary') }}" style="width: 32px; height: 32px; font-size: 13px; margin-right: 14px !important;">
                                            <i class="mdi {{ $isOverdue ? 'mdi-alert-circle' : ($isUrgent ? 'mdi-clock-alert' : 'mdi-play-circle-outline') }}"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="font-size-12 mb-0.5 text-truncate font-weight-bold">
                                                <a href="{{ url('projects/taskboard/' . base64_encode($task->projectid)) }}" class="text-dark">{{ $task->title }}</a>
                                            </h6>
                                            <span class="text-muted font-size-10 d-block text-truncate">
                                                {{ $task->project->project_name ?? 'Project' }}
                                                @if($task->enddate)
                                                &bull; <span class="{{ $isOverdue ? 'text-danger font-weight-bold' : '' }}">Due {{ \Carbon\Carbon::parse($task->enddate)->format('d M') }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge {{ $isOverdue ? 'badge-danger' : ($isUrgent ? 'badge-warning' : 'badge-soft-primary') }} font-size-9 font-weight-bold flex-shrink-0 px-1.5 py-0.5">
                                        {{ $isOverdue ? 'OVERDUE' : $task->status }}
                                    </span>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted font-size-12">
                                <i class="mdi mdi-check-all font-size-24 d-block text-success mb-1"></i>
                                All caught up! No active tasks pending.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Completed Tasks Tab -->
                <div class="tab-pane" id="completed-tasks" role="tabpanel">
                    <div style="max-height: 290px; overflow-y: auto; padding: 12px;">
                        <div class="d-flex flex-column" style="gap: 8px;">
                            @forelse($adminData['recently_completed_tasks'] as $task)
                            <div class="p-2.5 rounded-lg d-flex align-items-center justify-content-between bg-light" style="border-radius: 12px; border: 1px solid #edf2f7;">
                                <div class="d-flex align-items-center overflow-hidden mr-2">
                                    <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-success bg-soft-success flex-shrink-0" style="width: 32px; height: 32px; font-size: 13px; margin-right: 14px !important;">
                                        <i class="mdi mdi-check"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="font-size-12 mb-0.5 text-truncate font-weight-bold text-muted">
                                            <a href="{{ url('projects/taskboard/' . base64_encode($task->projectid)) }}" class="text-muted">{{ $task->title }}</a>
                                        </h6>
                                        <span class="text-muted font-size-10 d-block">Done {{ $task->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <span class="badge badge-soft-success font-size-9 font-weight-bold flex-shrink-0">DONE</span>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted font-size-12">No recently completed tasks.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-top text-center py-2.5" style="border-radius: 0 0 16px 16px;">
            <a href="{{ url('projects') }}" class="font-size-12 text-primary font-weight-bold">
                View Project Taskboard <i class="mdi mdi-arrow-right ml-0.5"></i>
            </a>
        </div>
    </div>
</div>
</div>

{{-- 🚀 3. Recent Pulse Live Activity Card (Underneath Productivity Curve & Taskboard) --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #ffffff;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-radius: 16px 16px 0 0;">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center mr-3 text-primary" style="background: #eef2ff; width: 32px; height: 32px;">
                        <i class="mdi mdi-pulse font-size-16"></i>
                    </div>
                    <div>
                        <h6 class="font-size-14 mb-0 text-dark font-weight-bold">Recent Pulse</h6>
                        <span class="text-muted font-size-11">Live real-time work updates & activity</span>
                    </div>
                </div>
                <span class="badge badge-light border text-muted font-size-10 font-weight-bold px-2 py-1" style="border-radius: 6px;">Live Activity</span>
            </div>
            <div class="card-body p-3.5">
                <div class="d-flex flex-column" style="gap: 10px; max-height: 280px; overflow-y: auto;">
                    @forelse($adminData['recent_logs'] as $log)
                    @php
                    if (is_null($log->time_spend)) {
                    $timeSpentFormatted = 'Running';
                    $badgeClass = 'badge-soft-warning';
                    } else {
                    $totalMinutes = round($log->time_spend * 60);
                    $h = floor($totalMinutes / 60);
                    $m = $totalMinutes % 60;
                    $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d h', $h, $m) : sprintf('%02d m', $m);
                    $badgeClass = 'badge-soft-success';
                    }
                    @endphp
                    <div class="d-flex align-items-center justify-content-between p-2.5 rounded" style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px;">
                        <div class="d-flex align-items-center overflow-hidden mr-2">
                            <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-primary bg-white border flex-shrink-0" style="width: 32px; height: 32px; font-size: 13px; margin-right: 14px !important;">
                                <i class="mdi mdi-history"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-dark font-size-12 mb-0 text-truncate font-weight-semibold d-block">
                                    {{ $log->log_description ?: ($log->endtime ? 'Work Logged' : 'Timer Started') }}
                                </span>
                                <small class="text-muted font-size-11">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <span class="badge {{ $badgeClass }} font-size-10 font-weight-bold flex-shrink-0 px-2 py-1" style="border-radius: 6px;">
                            {{ $timeSpentFormatted }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted font-size-12">
                        <i class="mdi mdi-clock-outline font-size-26 text-muted d-block mb-1"></i>
                        No recent time logs recorded today.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
@include('dashboards.employee_scripts')
@endsection
