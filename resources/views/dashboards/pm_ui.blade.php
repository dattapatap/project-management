{{-- Project Manager UI --}}
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="header-title mb-0">Project Command Center</h4>
        <div class="d-flex">
            <a href="{{ url('projects') }}" class="btn btn-sm btn-primary action-btn-trendy mr-2"><i class="mdi mdi-plus mr-1"></i> New Project</a>
            <a href="{{ url('client/Matured') }}" class="btn btn-sm btn-soft-info action-btn-trendy"><i class="mdi mdi-account-multiple mr-1"></i> Clients</a>
        </div>
    </div>
</div>

<!-- PM KPI Row -->
<div class="row">
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-primary text-white text-center shadow-sm" onclick="window.location.href='{{ url('projects') }}'" style="cursor: pointer;">
            <div class="card-body">
                <i class="mdi mdi-folder-multiple-outline display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Total Projects</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_total_projects'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-info text-white text-center shadow-sm">
            <div class="card-body">
                <i class="mdi mdi-format-list-checks display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Total Tasks</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_total_tasks'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-warning text-white text-center shadow-sm">
            <div class="card-body">
                <i class="mdi mdi-progress-clock display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">InProgress Projects</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_proj_in_progress'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card admin-kpi-card gradient-success text-white text-center shadow-sm">
            <div class="card-body">
                <i class="mdi mdi-check-decagram display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Completed Projects</h5>
                <h3 class="mt-2 text-white">{{ $adminData['pm_proj_completed'] }}</h3>
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
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
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
                            <tr>
                                <td class="font-weight-bold">{{ $nProj->project_name }}</td>
                                <td>
                                    <a href="{{ route('client.detail', [base64_encode($nProj->clients->id), 'sts']) }}" class="text-dark font-weight-bold">
                                        {{ $nProj->clients->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-danger font-weight-bold">
                                        <i class="mdi mdi-clock-alert-outline mr-1"></i>
                                        {{ \Carbon\Carbon::parse($nProj->end_date)->format('d M, Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-soft-warning">{{ $nProj->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="btn btn-xs btn-primary">Track</a>
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
                <div class="row" style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
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
                                    <div class="progress progress-sm" style="height: 6px; border-radius: 3px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $aTask->progress }}%"></div>
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ Avatar::create($emp->name)->toBase64() }}" class="rounded-circle mr-2" style="width: 28px; height: 28px; border: 1px solid #eee;">
                                        <span class="font-weight-bold text-dark small">{{ $emp->name }}</span>
                                    </div>
                                </td>
                                <td><span class="badge badge-soft-primary small">{{ $emp->active_tasks }} Active</span></td>
                                <td><span class="badge badge-soft-success small">{{ $emp->completed_tasks }} Done</span></td>
                                <td>
                                    <span class="font-weight-bold text-dark small">{{ number_format($emp->total_hours, 1) }}h</span>
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
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
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
                                    <div class="d-flex align-items-center">
                                        <img src="{{ Avatar::create($u->name)->toBase64() }}" class="rounded-circle mr-2" style="width: 24px; height: 24px;">
                                        <span class="font-size-13 text-dark">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td class="py-2 text-center">
                                    <span class="text-primary font-weight-bold">{{ $u->active_tasks }}</span>
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
