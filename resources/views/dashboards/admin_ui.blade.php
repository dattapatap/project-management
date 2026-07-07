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

<!-- Top Row: Metrics & Quick Actions -->
<div class="row">
    <!-- KPIs -->
    <div class="col-lg-8">
        <div class="row">
            <div class="col-sm-6 col-xl-3">
                <div class="card admin-kpi-card gradient-primary text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ route('users.index') }}'">
                    <div class="card-body">
                        <i class="dripicons-user-group display-4 mb-2 admin-kpi-icon"></i>
                        <h5 class="text-white font-size-14">Employees</h5>
                        <h3 class="mt-2 text-white">{{ $adminData['total_users'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card admin-kpi-card gradient-info text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ route('departments.index') }}'">
                    <div class="card-body">
                        <i class="dripicons-store display-4 mb-2 admin-kpi-icon"></i>
                        <h5 class="text-white font-size-14">Departments</h5>
                        <h3 class="mt-2 text-white">{{ $adminData['total_departments'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card admin-kpi-card gradient-warning text-white text-center erp-kpi-clickable" onclick="window.location.href='{{ url('projects') }}'">
                    <div class="card-body">
                        <i class="dripicons-folder-open display-4 mb-2 admin-kpi-icon"></i>
                        <h5 class="text-white font-size-14">Projects</h5>
                        <h3 class="mt-2 text-white">{{ $adminData['total_projects'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card admin-kpi-card gradient-success text-white text-center erp-kpi-clickable" onclick="window.location.href='{{ route('clients.index') }}'">
                    <div class="card-body">
                        <i class="dripicons-graph-line display-4 mb-2 admin-kpi-icon"></i>
                        <h5 class="text-white font-size-14">Sales</h5>
                        <h3 class="mt-2 text-white">{{ getTotalSales($user, 'Admin') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-4">Quick Actions</h4>
                <div class="row">
                    <div class="col-6 mb-2">
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-block btn-sm shadow-sm p-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none;">
                            <i class="mdi mdi-account-plus mr-1"></i> User
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('departments.create') }}" class="btn btn-info btn-block btn-sm shadow-sm p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="mdi mdi-domain mr-1"></i> Dept
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts Row -->
<div class="row">
    <div class="col-lg-6">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-4">Global Sales Trends (12 Months)</h4>
                <div id="admin-sales-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-4">Project Health</h4>
                <div id="admin-project-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <h4 class="card-title mb-4">CSD Client Health</h4>
                <div id="admin-csd-health-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

<!-- Data Rows: Recent Users & Recent Projects -->
<div class="row">
    <!-- Recent Users Cards -->
    <div class="col-lg-6">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">Team Performance Overview</h4>
                    <div class="badge badge-soft-primary p-2">Monthly Rankings</div>
                </div>

                <!-- Sales Department Performance -->
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-size-14 mb-0 text-dark font-weight-bold">
                            <i class="mdi mdi-cash-multiple mr-1 text-success"></i> Sales Team <small class="text-muted">(Deals & Followups)</small>
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

                <!-- OD Department Performance -->
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-size-14 mb-0 text-dark font-weight-bold">
                            <i class="mdi mdi-cog-outline mr-1 text-info"></i> OD Team <small class="text-muted">(Efficiency & Time)</small>
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

    <!-- Project Deadlines -->
    <div class="col-lg-6">
        <div class="card pm-dashboard-custom-card trendy-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0">
                        <a href="{{ url('projects?status=InProgress') }}" class="text-dark">Project Deadlines</a>
                        <small class="text-muted">(Next 7 Days)</small>
                    </h4>
                    <span class="badge badge-soft-danger pulse">Action Required</span>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
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
                                    <h5 class="font-size-14 mb-1 text-dark">
                                        <a href="{{ url('projects/taskboard/' . base64_encode($nProj->id)) }}" class="text-dark">{{ $nProj->project_name }}</a>
                                    </h5>
                                    <p class="text-muted mb-0 font-size-12"><i class="mdi mdi-office-building mr-1"></i>{{ $nProj->clients->name ?? 'N/A' }}</p>
                                </td>
                                <td class="py-2">
                                    <span class="text-danger font-weight-bold">
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
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="mdi mdi-check-all display-4 text-success d-block mb-2"></i>
                                    No projects due in the next 7 days.
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
