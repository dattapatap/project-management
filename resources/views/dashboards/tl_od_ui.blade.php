{{-- Team Leader OD Dashboard UI --}}
<div class="row erp-dash-hero-row">
    <div class="col-sm-6">
        <div class="d-flex align-items-center">
            <div class="avatar-sm mr-3">
                <span class="avatar-title rounded-circle bg-primary bg-gradient-primary shadow-sm">
                    <i class="mdi mdi-shield-crown text-white"></i>
                </span>
            </div>
            <div>
                <h4 class="header-title erp-dash-title mb-0">Active Operations <span class="text-primary">Command</span></h4>
                <div class="d-flex align-items-center mt-1">
                    <span class="badge badge-soft-success font-size-10 mr-2"><i class="mdi mdi-circle font-size-8 mr-1"></i>LIVE STATUS</span>
                    <p class="text-muted mb-0 font-size-11 font-weight-medium">Team performance monitoring system</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="float-sm-right d-flex align-items-center mt-3 mt-sm-0">
            <div class="mr-4 erp-dash-year-box">
                <p class="erp-dash-year-box__label">Operational Year</p>
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-calendar-range text-primary mr-2"></i>
                    <select class="form-control form-control-sm erp-dash-year-select" id="tl_dashboard_year_filter">
                        @foreach($adminData['available_years'] as $yr)
                        <option value="{{ $yr }}" {{ $adminData['selected_year'] == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="text-right">
                <span class="badge badge-primary p-2 shadow-sm" style="background: linear-gradient(135deg, #3b5de7 0%, #50a5f1 100%); border-radius: 8px;">
                    <i class="mdi mdi-security mr-1"></i> OD Dept Oversight
                </span>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tl_dashboard_year_filter').addEventListener('change', function() {
        window.location.href = "{{ url('/home') }}?year=" + this.value;
    });
</script>

<!-- Active Work KPI Row (Clean Design) -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card shadow-sm border-0" onclick="window.location.href='{{ url('projects?status=ToDo') }}'" style="border-top: 3px solid #f1b44c !important; cursor: pointer;">
            <div class="card-body py-3">
                <h5 class="text-muted font-size-12 mb-1">ToDo Projects</h5>
                <h3 class="mb-0 text-dark font-size-20">{{ projects('ToDo', $user, $adminData['selected_year']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card shadow-sm border-0" onclick="window.location.href='{{ url('projects?status=InProgress') }}'" style="border-top: 3px solid #50a5f1 !important; cursor: pointer;">
            <div class="card-body py-3">
                <h5 class="text-muted font-size-12 mb-1">Active Projects</h5>
                <h3 class="mb-0 text-dark font-size-20">{{ projects('InProgress', $user, $adminData['selected_year']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card shadow-sm border-0" onclick="window.location.href='{{ url('projects') }}'" style="border-top: 3px solid #3b5de7 !important; cursor: pointer;">
            <div class="card-body py-3">
                <h5 class="text-muted font-size-12 mb-1">ToDo Tasks</h5>
                <h3 class="mb-0 text-dark font-size-20">{{ tasks('ToDo', $user, $adminData['selected_year']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card pm-dashboard-custom-card shadow-sm border-0" style="border-top: 3px solid #34c38f !important;">
            <div class="card-body py-3">
                <h5 class="text-muted font-size-12 mb-1">Working Tasks</h5>
                <h3 class="mb-0 text-dark font-size-20">{{ tasks('InProgress', $user, $adminData['selected_year']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Active Team Projects Oversight -->
<div class="row">
    <div class="col-12">
        <div class="card pm-dashboard-custom-card trendy-card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="font-size-15 mb-0 text-dark font-weight-bold">
                    <i class="mdi mdi-rocket-launch-outline mr-1 text-primary"></i> Ongoing Team Projects
                </h5>
                <div class="badge badge-soft-primary">{{ count($adminData['active_team_projects'] ?? []) }} Active</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0 trendy-table">
                        <thead class="thead-light">
                            <tr class="small text-uppercase">
                                <th>Project</th>
                                <th>Progress</th>
                                <th class="text-center">Tasks (D/T)</th>
                                <th>Deadline</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminData['active_team_projects'] ?? [] as $aProj)
                            <tr>
                                <td>
                                    <h5 class="font-size-13 mb-1">
                                        <a href="{{ url('projects/taskboard/' . base64_encode($aProj->id)) }}" class="text-dark">{{ $aProj->project_name }}</a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <small class="text-muted mr-2">{{ $aProj->clients->name ?? 'N/A' }}</small>
                                        @if($aProj->is_overdue)
                                        <span class="badge badge-soft-danger font-size-10">Overdue</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="progress progress-sm" style="height: 5px; border-radius: 3px; background-color: rgba(0,0,0,0.05);">
                                        <div class="progress-bar bg-{{ $aProj->is_overdue ? 'danger' : ($aProj->progress >= 80 ? 'success' : ($aProj->progress >= 40 ? 'primary' : 'warning')) }}" role="progressbar" style="width: {{ $aProj->progress }}%"></div>
                                    </div>
                                    <small class="text-muted font-weight-bold">{{ $aProj->progress }}%</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge badge-soft-info font-size-11 px-2">{{ $aProj->completed_task_count }}/{{ $aProj->tasks_count }}</span>
                                        @if($aProj->tasks_count > 0 && ($aProj->completed_task_count / $aProj->tasks_count) < 0.3 && \Carbon\Carbon::parse($aProj->end_date)->diffInDays(now()) < 5)
                                                <span class="badge badge-soft-danger font-size-9 mt-1 pulse">Slow Progress</span>
                                                @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="font-weight-bold {{ $aProj->is_overdue ? 'text-danger' : 'text-dark' }}">
                                            {{ \Carbon\Carbon::parse($aProj->end_date)->format('d M') }}
                                        </small>
                                        @if(!$aProj->is_overdue)
                                        <small class="text-muted" style="font-size: 9px;">{{ \Carbon\Carbon::parse($aProj->end_date)->diffForHumans() }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td><a href="{{ url('projects/taskboard/' . base64_encode($aProj->id)) }}" class="btn btn-sm btn-soft-primary btn-rounded px-3">Manage</a></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No active projects.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics & Team Oversight -->
<div class="row">
    <div class="col-lg-8">
        <div class="card pm-dashboard-custom-card trendy-card shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="font-size-15 mb-0 text-dark font-weight-bold">
                    <i class="mdi mdi-account-group-outline mr-1 text-info"></i> Your Team: Active Work Summary
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                    <table class="table table-centered table-nowrap mb-0 trendy-table">
                        <thead class="thead-light">
                            <tr class="small text-uppercase">
                                <th class="pl-4">Team Member</th>
                                <th>Active Tasks</th>
                                <th>Working On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminData['team_employees'] ?? [] as $emp)
                            @php
                            $isRecentActive = false;
                            $lastLog = $emp->taskLogs->first(); // TaskLogs are typically ordered by latest
                            if($lastLog && \Carbon\Carbon::parse($lastLog->created_at)->gt(now()->subHours(3))) {
                            $isRecentActive = true;
                            }
                            @endphp
                            <tr>
                                <td class="pl-4 py-3">
                                    <a href="{{ route('reports.employee.detail', $emp->id) }}" class="d-flex align-items-center text-dark">
                                        <div class="position-relative">
                                            <img src="{{ Avatar::create($emp->name)->toBase64() }}" class="rounded-circle mr-2" style="width: 32px; height: 32px; border: 2px solid {{ $isRecentActive ? '#34c38f' : 'transparent' }};">
                                            @if($isRecentActive)
                                            <span class="position-absolute" style="bottom: 0; right: 8px; width: 10px; height: 10px; background: #34c38f; border: 2px solid #fff; border-radius: 50%;"></span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-size-13 text-dark font-weight-bold d-block">
                                                {{ $emp->name }}
                                                @if($isRecentActive)
                                                <span class="badge badge-soft-success font-size-9 ml-1"
                                                    data-toggle="tooltip"
                                                    title="Working on: {{ $emp->taskLogs->first()->task->title ?? 'General Tasks' }}">
                                                    ACTIVE
                                                </span>
                                                @endif
                                            </span>
                                            <small class="text-muted">{{ $emp->getRoleNames()->first() }}</small>
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    @php
                                    $workloadColor = 'success';
                                    $workloadLabel = 'Optimal';
                                    if($emp->active_tasks_count >= 5) { $workloadColor = 'danger'; $workloadLabel = 'Overloaded'; }
                                    elseif($emp->active_tasks_count >= 3) { $workloadColor = 'warning'; $workloadLabel = 'Busy'; }
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2" style="width: 10px; height: 10px; border-radius: 50%; background-color: var(--{{ $workloadColor }});" title="Workload: {{ $workloadLabel }}"></div>
                                        <span class="badge badge-soft-{{ $workloadColor }} px-2 font-size-11">{{ $emp->active_tasks_count }} Tasks</span>
                                        @if($emp->active_tasks_count >= 5)
                                        <i class="mdi mdi-fire text-danger ml-1 animated swing infinite" title="High Workload Bottleneck"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                    $activeProjs = $emp->tasks->pluck('project.project_name')->unique()->take(2);
                                    @endphp
                                    @forelse($activeProjs as $pName)
                                    <span class="badge badge-soft-info mb-1 font-weight-medium" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $pName }}</span>
                                    @empty
                                    <span class="text-muted small italic">Awaiting allocation</span>
                                    @endforelse
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">No team members assigned to you.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card pm-dashboard-custom-card trendy-card h-100 shadow-sm border-0 mb-2">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-1">
                    <div class="avatar-xs">
                        <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-18">
                            <i class="mdi mdi-heart-pulse"></i>
                        </span>
                    </div>
                    <h4 class="card-title mb-0">Project Health Index</h4>
                </div>

                <div id="tl-project-health-chart" class="apex-charts flex-grow-1" style="min-height: 280px;" dir="ltr"></div>

                <div class="mt-auto border-top">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <p class="text-muted mb-1 font-size-12">On Track</p>
                            <h5 class="mb-0 text-primary">{{ $adminData['project_health']['On Track'] }}</h5>
                        </div>
                        <div class="col-6 mb-2">
                            <p class="text-muted mb-1 font-size-12">Delayed</p>
                            <h5 class="mb-0 text-danger">{{ $adminData['project_health']['Delayed'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Workload Heatmap Widget --}}
@if(!empty($adminData['workload_heatmap']) && $adminData['workload_heatmap']->count())
<div class="row mb-4">
    <div class="col-12">
        <div class="card pm-dashboard-custom-card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="font-size-15 mb-0 text-dark font-weight-bold">
                    <i class="mdi mdi-chart-bar mr-1 text-warning"></i> Team Workload Heatmap
                </h5>
                <a href="{{ route('projects.resources') }}" class="btn btn-sm btn-soft-primary btn-rounded px-3">
                    <i class="mdi mdi-open-in-new mr-1"></i> Full View
                </a>
            </div>
            <div class="card-body pb-2">
                <div class="row">
                    @foreach($adminData['workload_heatmap'] as $member)
                    @php
                        $active = $member->todo_count + $member->inprogress_count;
                        $loadColor = match(true) {
                            $active === 0         => '#f0fff4',
                            $active <= 2          => '#c6f6d5',
                            $active <= 4          => '#fef9c3',
                            $active <= 6          => '#fed7aa',
                            default               => '#feb2b2',
                        };
                        $textColor = match(true) {
                            $active === 0         => '#2f855a',
                            $active <= 2          => '#276749',
                            $active <= 4          => '#744210',
                            $active <= 6          => '#7b341e',
                            default               => '#c53030',
                        };
                        $loadLabel = match(true) {
                            $active === 0 => 'Idle',
                            $active <= 2  => 'Light',
                            $active <= 4  => 'Moderate',
                            $active <= 6  => 'High',
                            default       => 'Overloaded',
                        };
                    @endphp
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="d-flex align-items-center p-3 rounded" style="background:{{ $loadColor }};border-radius:10px!important;">
                            <img src="{{ Avatar::create($member->name)->toBase64() }}" class="rounded-circle mr-3" style="width:38px;height:38px;border:2px solid rgba(0,0,0,.08);">
                            <div class="flex-grow-1 min-width-0">
                                <div class="font-weight-bold text-dark text-truncate" style="font-size:.85rem;">{{ $member->name }}</div>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="badge" style="background:{{ $textColor }};color:#fff;font-size:.68rem;padding:2px 7px;border-radius:20px;">{{ $loadLabel }}</span>
                                    <small class="text-muted">{{ $active }} active · {{ $member->completed_count }} done</small>
                                </div>
                                @if($member->overdue_count > 0)
                                <small class="text-danger font-weight-bold"><i class="mdi mdi-alert-circle-outline"></i> {{ $member->overdue_count }} overdue</small>
                                @endif
                            </div>
                            <div class="text-right ml-2" style="min-width:36px;">
                                <div class="font-weight-bold" style="font-size:1.3rem;line-height:1;color:{{ $textColor }};">{{ $active }}</div>
                                <small style="font-size:.65rem;color:#718096;">tasks</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                {{-- Mini legend --}}
                <div class="d-flex gap-3 flex-wrap pb-1" style="gap:12px;">
                    <small><span style="display:inline-block;width:10px;height:10px;background:#c6f6d5;border-radius:3px;margin-right:4px;"></span>Light (1–2)</small>
                    <small><span style="display:inline-block;width:10px;height:10px;background:#fef9c3;border-radius:3px;margin-right:4px;"></span>Moderate (3–4)</small>
                    <small><span style="display:inline-block;width:10px;height:10px;background:#fed7aa;border-radius:3px;margin-right:4px;"></span>High (5–6)</small>
                    <small><span style="display:inline-block;width:10px;height:10px;background:#feb2b2;border-radius:3px;margin-right:4px;"></span>Overloaded (7+)</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Urgent Deadlines Table -->
<div class="row mb-5 pb-4 pt-4">
    <div class="col-12">
        <div class="card pm-dashboard-custom-card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">Urgent Deadlines (7 Days)</h4>
                    <span class="badge badge-soft-danger pulse">Action Required</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-centered trendy-table mb-0">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminData['near_deadline_projects'] ?? [] as $nProj)
                            @php
                            $isLate = \Carbon\Carbon::parse($nProj->end_date)->isPast();
                            @endphp
                            <tr>
                                <td class="font-weight-bold">
                                    <a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="text-dark">{{ $nProj->project_name }}</a>
                                </td>
                                <td>
                                    <span class="font-weight-bold {{ $isLate ? 'text-danger' : 'text-warning' }}">
                                        {{ \Carbon\Carbon::parse($nProj->end_date)->format('d M, Y') }}
                                        @if($isLate) <small class="badge badge-soft-danger ml-1">LATE</small> @endif
                                    </span>
                                </td>
                                <td><span class="badge badge-soft-warning">{{ $nProj->status }}</span></td>
                                <td><a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="btn btn-xs btn-primary">Track</a></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">All clear!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
