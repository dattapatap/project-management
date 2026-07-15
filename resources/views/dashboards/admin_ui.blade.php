{{-- Admin Dashboard UI --}}
<div class="row mb-3 align-items-center">
    <div class="col-lg-6">
        <h4 class="header-title erp-dash-title mb-0">System Overview</h4>
    </div>
    <div class="col-lg-6 text-lg-right mt-2 mt-lg-0 d-flex align-items-center justify-content-lg-end flex-wrap" style="gap: 8px;">
        <a href="{{ route('daily-targets.index') }}" class="btn btn-outline-primary btn-sm shadow-sm" style="border-radius: 8px; font-weight: 600; padding: 6px 12px;">
            <i class="mdi mdi-target mr-1"></i> Set Daily Target
        </a>
        <a href="{{ route('sales.targets.index') }}" class="btn btn-outline-success btn-sm shadow-sm" style="border-radius: 8px; font-weight: 600; padding: 6px 12px;">
            <i class="mdi mdi-trophy-outline mr-1"></i> Set Sales Target
        </a>
        <span class="badge badge-soft-danger py-2 px-3" style="border-radius: 8px;">Admin</span>
    </div>
</div>

{{-- ══ Row 1: 8 KPI Cards ══════════════════════════════════════════════════════ --}}
<div class="row mb-1">
    {{-- Employees --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card admin-kpi-card gradient-primary text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ route('users.index') }}'">
            <div class="card-body py-3">
                <i class="dripicons-user-group display-4 mb-1 admin-kpi-icon" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">Employees</h5>
                <h3 class="mt-0 text-white mb-0">{{ $adminData['total_users'] }}</h3>
            </div>
        </div>
    </div>
    {{-- Departments --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card admin-kpi-card gradient-info text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ route('departments.index') }}'">
            <div class="card-body py-3">
                <i class="dripicons-store display-4 mb-1 admin-kpi-icon" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">Departments</h5>
                <h3 class="mt-0 text-white mb-0">{{ $adminData['total_departments'] }}</h3>
            </div>
        </div>
    </div>
    {{-- Projects --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card admin-kpi-card gradient-warning text-white text-center erp-kpi-clickable" onclick="window.location.href='{{ url('projects') }}'">
            <div class="card-body py-3">
                <i class="dripicons-folder-open display-4 mb-1 admin-kpi-icon" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">Projects</h5>
                <h3 class="mt-0 text-white mb-0">{{ $adminData['total_projects'] }}</h3>
            </div>
        </div>
    </div>
    {{-- Total Sales (Clients) --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card admin-kpi-card gradient-success text-white text-center erp-kpi-clickable" onclick="window.location.href='{{ route('clients.index') }}'">
            <div class="card-body py-3">
                <i class="dripicons-graph-line display-4 mb-1 admin-kpi-icon" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">Sales</h5>
                <h3 class="mt-0 text-white mb-0">{{ getTotalSales($user, 'Admin') }}</h3>
            </div>
        </div>
    </div>
    {{-- Matured Clients --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card text-white text-center shadow-sm erp-kpi-clickable" style="background: linear-gradient(135deg,#11998e,#38ef7d);border-radius:12px;" onclick="window.location.href='{{ url('reports/dsr/salesreports') }}'">
            <div class="card-body py-3">
                <i class="mdi mdi-trophy-outline d-block mb-1" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">Matured Clients</h5>
                <h3 class="mt-0 text-white mb-0">{{ $adminData['total_matured_clients'] }}</h3>
            </div>
        </div>
    </div>
    {{-- Active Tasks --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card text-white text-center shadow-sm erp-kpi-clickable" style="background: linear-gradient(135deg,#4e54c8,#8f94fb);border-radius:12px;" onclick="window.location.href='{{ url('projects') }}'">
            <div class="card-body py-3">
                <i class="mdi mdi-checkbox-marked-circle-outline d-block mb-1" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">Active Tasks</h5>
                <h3 class="mt-0 text-white mb-0">{{ $adminData['total_active_tasks'] }}</h3>
            </div>
        </div>
    </div>
    {{-- Pending Approvals --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card text-white text-center shadow-sm erp-kpi-clickable" style="background: linear-gradient(135deg,#f7971e,#ffd200);border-radius:12px;" onclick="window.location.href='{{ route('day-closing.approvals') }}'">
            <div class="card-body py-3">
                <i class="mdi mdi-check-decagram d-block mb-1" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">Pending Approvals</h5>
                <h3 class="mt-0 text-white mb-0">{{ $adminData['pending_approvals'] }}</h3>
            </div>
        </div>
    </div>
    {{-- CSD Active --}}
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card text-white text-center shadow-sm erp-kpi-clickable" style="background: linear-gradient(135deg,#cc2b5e,#753a88);border-radius:12px;" onclick="window.location.href='{{ route('csd.clients.index') }}'">
            <div class="card-body py-3">
                <i class="mdi mdi-headset d-block mb-1" style="font-size:28px;"></i>
                <h5 class="text-white font-size-13 mb-1">CSD Assignments</h5>
                <h3 class="mt-0 text-white mb-0">{{ $adminData['csd_active_count'] }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- ══ Row 2: Charts ═══════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- Global Sales Trends --}}
    <div class="col-lg-5">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-3" style="font-size:13px;">Global Sales Trends (12 Months)</h4>
                <div id="admin-sales-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>

    {{-- Task Completion Trend --}}
    <div class="col-lg-4">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-3" style="font-size:13px;">Task Completion Trend</h4>
                <div id="admin-task-trend-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>

    {{-- Project Health + CSD Health stacked --}}
    <div class="col-lg-3">
        <div class="card pm-dashboard-custom-card trendy-card mb-3">
            <div class="card-body py-3">
                <h4 class="card-title mb-2" style="font-size:13px;">Project Health</h4>
                <div id="admin-project-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body py-3">
                <h4 class="card-title mb-2" style="font-size:13px;">CSD Client Health</h4>
                <div id="admin-csd-health-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

{{-- ══ Row 3: Category Distribution + CSD Alerts ══════════════════════════════ --}}
<div class="row">
    {{-- Project Category Distribution --}}
    <div class="col-lg-6">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-3" style="font-size:13px;">Projects by Category</h4>
                <div id="admin-dept-projects-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>

    {{-- CSD Alerts --}}
    <div class="col-lg-6">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title mb-0" style="font-size:13px;">⚠️ CSD Client Alerts</h4>
                    <a href="{{ route('csd.clients.index') }}" class="badge badge-soft-danger py-1 px-2 font-size-11">View All</a>
                </div>
                @forelse($adminData['csd_alerts'] as $alert)
                @php
                $alertColor = $alert->health_status === 'churning' ? '#f46a6a' : '#f1b44c';
                $alertBadge = $alert->health_status === 'churning' ? 'badge-soft-danger' : 'badge-soft-warning';
                $alertLabel = $alert->health_status === 'churning' ? 'Churning' : 'At Risk';
                @endphp
                <div class="d-flex align-items-center py-2" style="border-bottom:1px solid #f5f5f5;">
                    <div class="mr-2" style="width:6px;height:36px;background:{{ $alertColor }};border-radius:4px;flex-shrink:0;"></div>
                    <div class="flex-grow-1">
                        <div class="font-weight-700 font-size-12 text-dark">{{ $alert->client->name ?? 'Unknown' }}</div>
                        <div class="font-size-11 text-muted">
                            <i class="mdi mdi-account-outline mr-1"></i>{{ $alert->assignee->name ?? 'Unassigned' }}
                        </div>
                    </div>
                    <span class="badge {{ $alertBadge }} font-size-10 px-2">{{ $alertLabel }}</span>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="mdi mdi-shield-check-outline text-success d-block mb-1" style="font-size:32px;"></i>
                    <span class="text-muted font-size-12">All CSD clients are healthy!</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══ Row 4: Team Performance + Project Deadlines ════════════════════════════ --}}
<div class="row">
    {{-- Team Performance Overview --}}
    <div class="col-lg-6">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">Team Performance Overview</h4>
                    <div class="badge badge-soft-primary p-2">Monthly Rankings</div>
                </div>

                {{-- Sales Department Performance --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-size-14 mb-0 text-dark font-weight-bold">
                            <i class="mdi mdi-cash-multiple mr-1 text-success"></i> Sales Team <small class="text-muted">(Deals &amp; Followups)</small>
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-centered table-nowrap mb-0 trendy-table">
                            <thead class="thead-light">
                                <tr class="small text-uppercase">
                                    <th class="py-2">Employee</th>
                                    <th class="py-2 text-center">Matured</th>
                                    <th class="py-2 text-center">Followup</th>
                                    <th class="py-2 text-center">Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminData['sales_performance'] as $perf)
                                @php
                                $diff = $perf->this_month_matured - $perf->last_month_matured;
                                $growth = $perf->last_month_matured > 0 ? (int)round(($diff / $perf->last_month_matured) * 100) : ($perf->this_month_matured > 0 ? 100 : 0);
                                @endphp
                                <tr>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ Avatar::create($perf->name)->toBase64() }}" class="rounded-circle mr-2" style="width: 28px; height: 28px;">
                                            <span class="font-size-13 text-dark">{{ $perf->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="badge badge-soft-success font-size-12 px-2">{{ $perf->total_matured }}</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="badge badge-soft-info font-size-12 px-2">{{ $perf->followup_clients }}</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="text-{{ $growth >= 0 ? ($growth > 0 ? 'success' : 'muted') : 'danger' }} small font-weight-bold">
                                            <i class="mdi mdi-trending-{{ $growth >= 0 ? ($growth > 0 ? 'up' : 'neutral') : 'down' }} mr-1"></i>{{ $growth }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No sales performance data available.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="border-top: 1px dashed #e9ecef; margin: 1.5rem 0;"></div>

                {{-- OD Department Performance --}}
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-size-14 mb-0 text-dark font-weight-bold">
                            <i class="mdi mdi-cog-outline mr-1 text-info"></i> OD Team <small class="text-muted">(Efficiency &amp; Time)</small>
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-centered table-nowrap mb-0 trendy-table">
                            <thead class="thead-light">
                                <tr class="small text-uppercase">
                                    <th class="py-2">Employee</th>
                                    <th class="py-2 text-center">Time</th>
                                    <th class="py-2 text-center">Done</th>
                                    <th class="py-2 text-center">InProcess</th>
                                    <th class="py-2 text-center">Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminData['od_performance'] as $perf)
                                @php
                                $hours = floor(($perf->total_minutes ?? 0) / 60);
                                $mins = ($perf->total_minutes ?? 0) % 60;
                                $timeStr = sprintf('%dh %dm', $hours, $mins);
                                $diff = $perf->this_month_completed - $perf->last_month_completed;
                                $growth = $perf->last_month_completed > 0 ? (int)round(($diff / $perf->last_month_completed) * 100) : ($perf->this_month_completed > 0 ? 100 : 0);
                                @endphp
                                <tr>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ Avatar::create($perf->name)->toBase64() }}" class="rounded-circle mr-2" style="width: 28px; height: 28px;">
                                            <span class="font-size-13 text-dark">{{ $perf->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="text-muted small font-weight-bold">{{ $timeStr }}</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="badge badge-soft-primary font-size-11 px-2">{{ $perf->total_completed }}</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="badge badge-soft-warning font-size-11 px-2">{{ $perf->active_tasks }}</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="text-{{ $growth >= 0 ? ($growth > 0 ? 'success' : 'muted') : 'danger' }} small font-weight-bold">
                                            <i class="mdi mdi-trending-{{ $growth >= 0 ? ($growth > 0 ? 'up' : 'neutral') : 'down' }} mr-1"></i>{{ $growth }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No OD performance data available.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Project Deadlines + Today's Pending Day Closings stacked --}}
    <div class="col-lg-6">
        {{-- Project Deadlines --}}
        <div class="card pm-dashboard-custom-card trendy-card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title mb-0">
                        <a href="{{ url('projects?status=InProgress') }}" class="text-dark">Project Deadlines</a>
                        <small class="text-muted">(Next 7 Days)</small>
                    </h4>
                    <span class="badge badge-soft-danger pulse">Action Required</span>
                </div>

                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-centered table-nowrap mb-0 trendy-table">
                        <thead class="thead-light">
                            <tr class="small text-uppercase">
                                <th class="py-2">Project</th>
                                <th class="py-2">Deadline</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Track</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminData['near_deadline_projects'] as $nProj)
                            <tr>
                                <td class="py-2">
                                    <h5 class="font-size-13 mb-1 text-dark">
                                        <a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="text-dark">{{ Str::limit($nProj->project_name, 28) }}</a>
                                    </h5>
                                    <p class="text-muted mb-0 font-size-11"><i class="mdi mdi-office-building mr-1"></i>{{ $nProj->clients->name ?? 'N/A' }}</p>
                                </td>
                                <td class="py-2">
                                    <span class="text-danger font-weight-bold font-size-12">
                                        <i class="mdi mdi-clock-alert-outline mr-1"></i>
                                        {{ \Carbon\Carbon::parse($nProj->end_date)->format('d M, Y') }}
                                    </span>
                                </td>
                                <td class="py-2">
                                    @php
                                    $stClass = 'warning';
                                    if($nProj->status == 'Completed') $stClass = 'success';
                                    if($nProj->status == 'ToDo') $stClass = 'secondary';
                                    @endphp
                                    <span class="badge badge-soft-{{ $stClass }} font-size-11">{{ $nProj->status }}</span>
                                </td>
                                <td class="py-2">
                                    <a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="btn btn-xs btn-outline-primary rounded-pill">Track</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="mdi mdi-check-all display-4 text-success d-block mb-1" style="font-size:32px;"></i>
                                    No projects due in the next 7 days.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Today's Pending Day-Closings --}}
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title mb-0" style="font-size:13px;">
                        📋 Pending Day-Closings
                        <small class="text-muted">— Today ({{ now()->format('d M') }})</small>
                    </h4>
                    <a href="{{ route('day-closing.approvals') }}" class="badge badge-soft-warning py-1 px-2 font-size-11">Approvals</a>
                </div>

                @if($adminData['pending_day_closings']->isEmpty())
                <div class="text-center py-3">
                    <i class="mdi mdi-check-circle-outline text-success d-block mb-1" style="font-size:30px;"></i>
                    <span class="text-muted font-size-12">All employees have submitted their day-closing! ✅</span>
                </div>
                @else
                <div style="max-height:300px;overflow-y:auto;">
                    @foreach($adminData['pending_day_closings'] as $pending)
                    <div class="d-flex align-items-center py-1" style="border-bottom:1px solid #f5f5f5;">
                        <img src="{{ Avatar::create($pending->name)->toBase64() }}" class="rounded-circle mr-2" style="width:26px;height:26px;flex-shrink:0;">
                        <div class="flex-grow-1">
                            <span class="font-size-12 font-weight-600 text-dark">{{ $pending->name }}</span>
                        </div>
                        <span class="badge badge-soft-danger font-size-10 px-2">Pending</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-2 text-right">
                    <small class="text-muted font-size-11">{{ $adminData['pending_day_closings']->count() }} employee(s) haven't submitted today.</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
