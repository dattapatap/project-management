{{-- Project Manager UI --}}
<div class="row erp-dash-header">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="header-title erp-dash-title mb-0">Project Command Center</h4>
        <div class="d-flex gap-2">
            <a href="{{ url('projects') }}" class="btn btn-sm btn-primary action-btn-trendy"><i class="mdi mdi-plus mr-1"></i> New Project</a>
            <a href="{{ client_list_url('Matured') }}" class="btn btn-sm btn-soft-info action-btn-trendy"><i class="mdi mdi-account-multiple mr-1"></i> Clients</a>
        </div>
    </div>
</div>

<!-- PM KPI Row -->
<div class="row">
    <div class="col-6 col-md-4 col-lg">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-primary text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ url('projects') }}'">
            <div class="card-body">
                <i class="mdi mdi-folder-multiple-outline display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Total Projects</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_total_projects'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-warning text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ url('projects?status=ToDo') }}'">
            <div class="card-body">
                <i class="mdi mdi-playlist-plus display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Not Started</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_proj_todo'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-info text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ url('projects?status=InProgress') }}'">
            <div class="card-body">
                <i class="mdi mdi-progress-clock display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">In Progress</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_proj_in_progress'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-success text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ url('projects/search?search=Completed') }}'">
            <div class="card-body">
                <i class="mdi mdi-check-decagram display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Completed</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_proj_completed'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-info text-white text-center shadow-sm">
            <div class="card-body">
                <i class="mdi mdi-format-list-checks display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Total Tasks</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_total_tasks'] }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- PM Analytics & Deadlines -->
<div class="row">
    <div class="col-lg-8">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">Urgent Project Deadlines (Next 7 Days)</h4>
                    <span class="badge badge-soft-danger pulse">Action Required</span>
                </div>
                <div class="table-responsive erp-scroll-panel">
                    <table class="table table-centered trendy-table mb-0">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Client</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                             @forelse($adminData['near_deadline_projects'] as $nProj)
                            @php
                                $isOverdue = \Carbon\Carbon::parse($nProj->end_date)->isPast();
                            @endphp
                            <tr class="{{ $isOverdue ? 'bg-soft-rose' : '' }}">
                                <td class="font-weight-bold">
                                    <div class="d-flex align-items-center">
                                        @if($isOverdue) <i class="mdi mdi-alert-circle text-danger mr-1 pulse"></i> @endif
                                        <a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="text-dark">{{ $nProj->project_name }}</a>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('client.detail', [base64_encode($nProj->clients->id), 'sts']) }}" class="text-dark font-weight-bold">
                                        {{ $nProj->clients->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="{{ $isOverdue ? 'text-danger' : 'text-warning' }} font-weight-bold">
                                            <i class="mdi mdi-clock-alert-outline mr-1"></i>
                                            {{ \Carbon\Carbon::parse($nProj->end_date)->format('d M, Y') }}
                                        </span>
                                        <small class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($nProj->end_date)->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft-{{ $isOverdue ? 'danger' : 'warning' }} px-2 py-1">{{ $isOverdue ? 'OVERDUE' : $nProj->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="btn btn-sm btn-soft-primary rounded-pill px-3">Review</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Great! No urgent deadlines in the next 7 days.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-4">Project Health</h4>
                <div id="pm-project-health-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

<!-- PM Performance & Productivity -->
<div class="row">
    <!-- Active Performance Tracking -->
    <div class="col-lg-5">
        <div class="card pm-dashboard-custom-card trendy-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">Active Performance Tracking</h4>
                    <a href="{{ url('projects') }}" class="btn btn-sm btn-soft-primary action-btn-trendy">View All</a>
                </div>
                <div class="row erp-scroll-panel--tall">
                    @forelse($adminData['active_tasks'] as $aTask)
                    <div class="col-12 mb-3">
                        <div class="card shadow-none border mb-0 p-3" style="border-radius: 12px;">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h5 class="font-size-14 mb-0 text-truncate">
                                            <a href="{{ url('projects/task/details/' . base64_encode($aTask->id)) }}" class="text-dark">{{ $aTask->title }}</a>
                                        </h5>
                                        <span class="text-primary font-weight-bold">{{ $aTask->progress }}%</span>
                                    </div>
                                    <p class="text-muted font-size-12 mb-2"><i class="mdi mdi-account-circle-outline mr-1"></i>{{ $aTask->user->name }} | {{ $aTask->project->project_name }}</p>
                                    <div class="d-flex align-items-center">
                                        <div class="progress progress-sm flex-grow-1 mr-2" style="height: 6px; border-radius: 3px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $aTask->progress }}%"></div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-soft-warning nudge-btn px-2 py-0" data-task-id="{{ $aTask->id }}" title="Nudge for update">
                                            <i class="mdi mdi-bell-ring-outline font-size-14"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-5">
                        No tasks currently in progress.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Team Productivity & Workload Oversight -->
    <div class="col-lg-7">
        <div class="card pm-dashboard-custom-card trendy-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">Team Productivity Oversight</h4>
                    <span class="text-muted small">Top Contributors</span>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-centered trendy-table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Workload</th>
                                <th>Completed</th>
                                <th>Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                             @forelse($adminData['employee_performance'] as $emp)
                            <tr>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative">
                                            <img src="{{ Avatar::create($emp->name)->toBase64() }}" class="rounded-circle mr-2" style="width: 32px; height: 32px; border: 2px solid {{ $emp->total_hours > 6 ? '#34c38f' : '#eee' }};">
                                            @if($emp->total_hours > 6)
                                                <span class="position-absolute" style="bottom: 0; right: 8px; width: 10px; height: 10px; background: #34c38f; border: 2px solid #fff; border-radius: 50%;"></span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-weight-bold text-dark font-size-13 d-block">{{ $emp->name }}</span>
                                            @if($emp->total_hours > 6)
                                                <small class="badge badge-soft-success font-size-9" data-toggle="tooltip" title="Working on: {{ $emp->taskLogs->first()->task->title ?? 'General Tasks' }}">ACTIVE NOW</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $wColor = 'success';
                                        if($emp->active_tasks >= 5) $wColor = 'danger';
                                        elseif($emp->active_tasks >= 3) $wColor = 'warning';
                                    @endphp
                                    <span class="badge badge-soft-{{ $wColor }} px-2">{{ $emp->active_tasks }} Active</span>
                                </td>
                                <td><span class="badge badge-soft-success px-2">{{ $emp->completed_tasks }} Done</span></td>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ number_format($emp->total_hours, 1) }}h</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No activity data.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PM Team-wise Employee Oversight (OD Department Only) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex align-items-center mb-3">
            <div class="avatar-xs mr-2">
                <span class="avatar-title rounded-circle bg-info text-white">
                    <i class="mdi mdi-account-group"></i>
                </span>
            </div>
            <h4 class="header-title mb-0">OD Department: Team Performance Oversight</h4>
        </div>
    </div>
    @forelse($adminData['team_performance'] as $team)
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card pm-dashboard-custom-card trendy-card h-100 shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="font-size-15 mb-0 text-dark font-weight-bold">
                        <i class="mdi mdi-account-multiple-outline mr-1 text-info"></i> {{ $team->name }}
                    </h5>
                    <span class="badge badge-soft-info">{{ count($team->teammembers) }} Members</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive erp-scroll-panel">
                    <table class="table table-centered table-nowrap mb-0 trendy-table">
                        <thead class="thead-light">
                            <tr class="small text-uppercase">
                                <th class="py-2">Employee</th>
                                <th class="py-2 text-center">Tasks</th>
                                <th class="py-2 text-right pr-3">Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($team->teammembers as $tm)
                            @php $u = $tm->users; @endphp
                            @if($u)
                            <tr>
                                <td class="py-2">
                                    @php
                                        $uActive = false;
                                        $uTask = 'None';
                                        $uLastLog = $u->taskLogs->first();
                                        if($uLastLog && \Carbon\Carbon::parse($uLastLog->created_at)->gt(now()->subHours(3))) {
                                            $uActive = true;
                                            $uTask = $uLastLog->task->title ?? 'General';
                                        }
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative">
                                            <img src="{{ Avatar::create($u->name)->toBase64() }}" class="rounded-circle mr-2" style="width: 24px; height: 24px; border: 1.5px solid {{ $uActive ? '#34c38f' : 'transparent' }};">
                                            @if($uActive)
                                                <span class="position-absolute" style="bottom: 0; right: 8px; width: 8px; height: 8px; background: #34c38f; border: 1.5px solid #fff; border-radius: 50%;" data-toggle="tooltip" title="Active on: {{ $uTask }}"></span>
                                            @endif
                                        </div>
                                        <span class="font-size-13 {{ $uActive ? 'text-success font-weight-bold' : 'text-dark' }}">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td class="py-2 text-center">
                                    @php
                                        $twColor = 'primary';
                                        if($u->active_tasks >= 5) $twColor = 'danger';
                                        elseif($u->active_tasks >= 3) $twColor = 'warning';
                                    @endphp
                                    <span class="badge badge-soft-{{ $twColor }} font-weight-bold">{{ $u->active_tasks }}</span>
                                    <span class="text-muted mx-1">/</span>
                                    <span class="text-success font-weight-bold">{{ $u->completed_tasks }}</span>
                                </td>
                                <td class="py-2 text-right pr-3">
                                    <span class="font-weight-bold text-dark">{{ number_format($u->total_hours, 1) }}h</span>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted small">No members in this team.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="avatar-lg mx-auto mb-3">
            <span class="avatar-title rounded-circle bg-soft-info text-info display-4">
                <i class="mdi mdi-information-outline"></i>
            </span>
        </div>
        <h5>No teams found in OD department.</h5>
    </div>
    @endforelse
</div>
