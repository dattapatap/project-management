{{-- Branch Manager — cross-department oversight (NSD · CSD · OD) --}}
@php
    $nsd = $adminData['nsd'] ?? [];
    $csd = $adminData['csd'] ?? [];
    $od = $adminData['od'] ?? [];
    $dept = $adminData['dept_headcount'] ?? ['nsd' => 0, 'csd' => 0, 'od' => 0];
    $overview = $adminData['department_overview'] ?? [];
@endphp

<div class="row erp-dash-header mb-3 align-items-center">
    <div class="col-lg-8">
        <h4 class="header-title erp-dash-title mb-1">{{ $adminData['branch_name'] ?? 'Branch' }} Overview</h4>
        <p class="text-muted mb-0 font-size-13">NSD · CSD · OD — department-wise performance &amp; pipeline</p>
    </div>
    <div class="col-lg-4 text-lg-right mt-2 mt-lg-0">
        <div class="d-inline-flex align-items-center bg-white rounded shadow-sm border px-3 py-2">
            <i class="mdi mdi-calendar-range text-primary mr-2"></i>
            <select class="form-control form-control-sm border-0 shadow-none p-0 font-weight-bold" id="bm_dashboard_year_filter" style="width: 90px; background: transparent; cursor: pointer;">
                @foreach($adminData['available_years'] ?? [date('Y')] as $yr)
                <option value="{{ $yr }}" {{ ($adminData['selected_year'] ?? date('Y')) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </div>
        <span class="badge badge-soft-primary ml-2">Branch Manager</span>
    </div>
</div>

{{-- Top KPI row --}}
<div class="row">
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-kpi-card gradient-primary text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ route('users.index') }}'">
            <div class="card-body">
                <i class="mdi mdi-account-group display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">Branch Staff</h5>
                <h3 class="mt-2 text-white">{{ $adminData['total_users'] ?? 0 }}</h3>
                <small class="text-white-50">NSD {{ $dept['nsd'] }} · CSD {{ $dept['csd'] }} · OD {{ $dept['od'] }}</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-kpi-card gradient-success text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ client_list_url('Fresh') }}'">
            <div class="card-body">
                <i class="mdi mdi-office-building display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">NSD Companies</h5>
                <h3 class="mt-2 text-white">{{ $adminData['total_clients'] ?? 0 }}</h3>
                <small class="text-white-50">{{ $adminData['matured_clients'] ?? 0 }} matured total</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-kpi-card gradient-info text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ route('csd.clients.index') }}'">
            <div class="card-body">
                <i class="mdi mdi-account-heart display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">CSD Active Clients</h5>
                <h3 class="mt-2 text-white">{{ $csd['active_clients'] ?? 0 }}</h3>
                <small class="text-white-50">₹ {{ number_format($csd['outstanding_amount'] ?? 0, 0) }} outstanding</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-kpi-card gradient-warning text-white text-center shadow-sm erp-kpi-clickable" onclick="window.location.href='{{ url('projects') }}'">
            <div class="card-body">
                <i class="mdi mdi-folder-multiple display-4 mb-2 admin-kpi-icon"></i>
                <h5 class="text-white font-size-14">OD Projects</h5>
                <h3 class="mt-2 text-white">{{ $adminData['total_projects'] ?? 0 }}</h3>
                <small class="text-white-50">{{ $adminData['active_tasks'] ?? 0 }} active tasks</small>
            </div>
        </div>
    </div>
</div>

{{-- Overview charts --}}
<div class="row mt-3">
    <div class="col-lg-4">
        <div class="card pm-dashboard-custom-card h-100">
            <div class="card-body">
                <h5 class="card-title font-size-15 mb-3"><i class="mdi mdi-account-multiple-outline text-primary mr-1"></i> Staff by Department</h5>
                <div id="bm-dept-staff-chart" class="apex-charts" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card pm-dashboard-custom-card h-100">
            <div class="card-body">
                <h5 class="card-title font-size-15 mb-3"><i class="mdi mdi-briefcase-outline text-info mr-1"></i> Active Workload</h5>
                <div id="bm-dept-workload-chart" class="apex-charts" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card pm-dashboard-custom-card h-100">
            <div class="card-body">
                <h5 class="card-title font-size-15 mb-3"><i class="mdi mdi-chart-line text-success mr-1"></i> NSD Matured Sales ({{ $adminData['selected_year'] ?? date('Y') }})</h5>
                <div id="bm-nsd-trend-chart" class="apex-charts" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs dashboard-tabs mt-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#bm-nsd" role="tab"><i class="mdi mdi-chart-line"></i> NSD (Sales)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#bm-csd" role="tab"><i class="mdi mdi-account-heart-outline"></i> CSD</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#bm-od" role="tab"><i class="mdi mdi-briefcase-outline"></i> OD</a>
    </li>
</ul>

<div class="tab-content tab-content-animate mt-3">
    {{-- NSD TAB --}}
    <div class="tab-pane fade show active" id="bm-nsd" role="tabpanel">
        <div class="row">
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Fresh Leads</h6><h3 class="mb-0 text-primary">{{ $nsd['fresh_leads'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Active Pipeline</h6><h3 class="mb-0">{{ $nsd['total_active_leads'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Matured {{ $adminData['selected_year'] ?? date('Y') }}</h6><h3 class="mb-0 text-success">{{ $nsd['matured_year'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Overdue Callbacks</h6><h3 class="mb-0 {{ ($nsd['overdue_tbros_count'] ?? 0) > 0 ? 'text-danger' : '' }}">{{ $nsd['overdue_tbros_count'] ?? 0 }}</h3></div></div></div>
        </div>
        <div class="row mt-3">
            <div class="col-lg-5">
                <div class="card pm-dashboard-custom-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Lead Stage Distribution</h5>
                        <div id="bm-nsd-stage-chart" class="apex-charts" style="min-height: 280px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card pm-dashboard-custom-card h-100">
                    <div class="card-body">
                        <h4 class="card-title mb-3">NSD Sales Performance</h4>
                        <div class="table-responsive">
                            <table class="table table-sm trendy-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Executive</th>
                                        <th>Active Leads</th>
                                        <th>Matured (Year)</th>
                                        <th>Today CB</th>
                                        <th>Overdue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($nsd['team_performance_matrix'] ?? [] as $member)
                                    <tr>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->active_leads_count ?? 0 }}</td>
                                        <td><span class="badge badge-soft-success">{{ $member->matured_leads_count ?? 0 }}</span></td>
                                        <td>{{ $member->today_callbacks_count ?? 0 }}</td>
                                        <td class="{{ ($member->overdue_callbacks_count ?? 0) > 0 ? 'text-danger font-weight-bold' : '' }}">{{ $member->overdue_callbacks_count ?? 0 }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">No NSD staff linked to this branch.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card pm-dashboard-custom-card mt-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Recent Matured Clients</h4>
                <div class="table-responsive">
                    <table class="table table-sm trendy-table mb-0">
                        <thead><tr><th>Company</th><th>Executive</th><th>Matured On</th></tr></thead>
                        <tbody>
                            @forelse($nsd['recent_matured'] ?? [] as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ optional($client->referral)->name ?? '—' }}</td>
                                <td>{{ $client->updated_at ? $client->updated_at->format('d M Y') : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">No matured clients in {{ $adminData['selected_year'] ?? date('Y') }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CSD TAB --}}
    <div class="tab-pane fade" id="bm-csd" role="tabpanel">
        <div class="row">
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Active Clients</h6><h3 class="mb-0">{{ $csd['active_clients'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Outstanding</h6><h3 class="mb-0">₹ {{ number_format($csd['outstanding_amount'] ?? 0, 0) }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Open Tickets</h6><h3 class="mb-0">{{ $csd['open_tickets'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">At-Risk Clients</h6><h3 class="mb-0 text-warning">{{ $csd['at_risk_clients'] ?? 0 }}</h3></div></div></div>
        </div>
        <div class="row mt-3">
            <div class="col-lg-4">
                <div class="card pm-dashboard-custom-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">CSD Health Snapshot</h5>
                        <div id="bm-csd-health-chart" class="apex-charts" style="min-height: 280px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card pm-dashboard-custom-card h-100">
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-4"><small class="text-muted d-block">Renewals Due (30d)</small><strong>{{ $csd['renewal_due_clients'] ?? 0 }}</strong></div>
                            <div class="col-4"><small class="text-muted d-block">Collections This Month</small><strong>₹ {{ number_format($csd['collections_this_month'] ?? 0, 0) }}</strong></div>
                            <div class="col-4"><small class="text-muted d-block">New Clients (Month)</small><strong>{{ $csd['new_clients_this_month'] ?? 0 }}</strong></div>
                        </div>
                        <h4 class="card-title mb-3">CSD Team Performance</h4>
                        <div class="table-responsive">
                            <table class="table table-sm trendy-table mb-0">
                                <thead><tr><th>Executive</th><th>Active Clients</th><th>At Risk</th></tr></thead>
                                <tbody>
                                    @forelse($csd['team_performance_matrix'] ?? [] as $member)
                                    <tr>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->active_clients_count ?? 0 }}</td>
                                        <td class="{{ ($member->at_risk_count ?? 0) > 0 ? 'text-danger' : '' }}">{{ $member->at_risk_count ?? 0 }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center text-muted">No CSD staff in this branch yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card pm-dashboard-custom-card mt-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Unassigned Handoffs</h4>
                <div class="table-responsive">
                    <table class="table table-sm trendy-table mb-0">
                        <thead><tr><th>Client</th><th>Project</th></tr></thead>
                        <tbody>
                            @forelse($csd['unassigned_handoffs'] ?? [] as $handoff)
                            <tr>
                                <td>{{ $handoff->client->name ?? '—' }}</td>
                                <td>{{ optional($handoff->project)->project_name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted">No unassigned handoffs.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- OD TAB --}}
    <div class="tab-pane fade" id="bm-od" role="tabpanel">
        <div class="row">
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">To Do</h6><h3 class="mb-0">{{ $od['projects_todo'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">In Progress</h6><h3 class="mb-0 text-warning">{{ $od['projects_in_progress'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">Completed</h6><h3 class="mb-0 text-success">{{ $od['projects_completed'] ?? 0 }}</h3></div></div></div>
            <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center"><h6 class="text-muted mb-1">OD Team</h6><h3 class="mb-0">{{ $od['team_size'] ?? 0 }}</h3></div></div></div>
        </div>
        <div class="row mt-3">
            <div class="col-lg-4">
                <div class="card pm-dashboard-custom-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Project Status</h5>
                        <div id="bm-od-project-chart" class="apex-charts" style="min-height: 280px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card pm-dashboard-custom-card h-100">
                    <div class="card-body">
                        <h4 class="card-title mb-3">OD Team Performance</h4>
                        <div class="table-responsive">
                            <table class="table table-sm trendy-table mb-0">
                                <thead><tr><th>Member</th><th>Active Tasks</th><th>Completed</th><th>Hours Logged</th></tr></thead>
                                <tbody>
                                    @forelse($od['team_performance_matrix'] ?? [] as $member)
                                    <tr>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->active_tasks ?? 0 }}</td>
                                        <td>{{ $member->completed_tasks ?? 0 }}</td>
                                        <td>{{ number_format($member->total_hours ?? 0, 1) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted">No OD staff in this branch yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card pm-dashboard-custom-card mt-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Projects Near Deadline</h4>
                <div class="table-responsive">
                    <table class="table table-sm trendy-table mb-0">
                        <thead><tr><th>Project</th><th>Client</th><th>End Date</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($od['near_deadline_projects'] ?? [] as $project)
                            <tr>
                                <td>{{ $project->project_name ?? '—' }}</td>
                                <td>{{ optional($project->clients)->name ?? '—' }}</td>
                                <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '—' }}</td>
                                <td><span class="badge badge-soft-warning">{{ $project->status }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">No upcoming deadlines in this branch.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
