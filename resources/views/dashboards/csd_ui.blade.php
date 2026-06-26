{{-- CSD Executive / Personal Workspace Dashboard --}}
@php
    $year = $adminData['selected_year'] ?? date('Y');
@endphp

<div class="row erp-dash-hero-row mb-3">
    <div class="col-md-7">
        <div class="d-flex align-items-center">
            <div class="avatar-sm mr-3">
                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                    <i class="mdi mdi-account-heart-outline font-size-18"></i>
                </span>
            </div>
            <div>
                <h4 class="header-title erp-dash-title mb-0">Customer Success <span class="text-primary">Dashboard</span></h4>
                <p class="text-muted mb-0 font-size-12">Retain · Collect · Grow — {{ $year }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="float-md-right mt-3 mt-md-0 erp-dash-year-box">
            <p class="erp-dash-year-box__label mb-1">Reporting Year</p>
            <div class="d-flex align-items-center">
                <i class="mdi mdi-calendar-range text-primary mr-2"></i>
                <select class="form-control form-control-sm erp-dash-year-select" id="csd_dashboard_year_filter">
                    @foreach($adminData['available_years'] ?? range(date('Y'), date('Y') - 5) as $yr)
                    <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row erp-dash-kpi-row mb-3">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('csd.clients.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-stat-card erp-stat-card--csd erp-kpi-clickable h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-account-check-outline"></i></div>
                <div class="erp-stat-card__value">{{ $adminData['active_clients'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Active Clients</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('csd.collections.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-stat-card erp-stat-card--warning erp-kpi-clickable h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-cash-clock"></i></div>
                <div class="erp-stat-card__value">{{ $adminData['payments_due'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Payments Due</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="erp-stat-card erp-stat-card--success h-100">
            <div class="erp-stat-card__icon"><i class="mdi mdi-cash-multiple"></i></div>
            <div class="erp-stat-card__value">₹{{ number_format($adminData['collections_this_month'] ?? 0, 0) }}</div>
            <div class="erp-stat-card__label">Collections ({{ $year }})</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('csd.change-requests.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-stat-card erp-stat-card--indigo erp-kpi-clickable h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-file-document-edit-outline"></i></div>
                <div class="erp-stat-card__value">{{ $adminData['pending_change_requests'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Open Change Requests</div>
            </div>
        </a>
    </div>
</div>

<div class="row erp-dash-kpi-row mb-3">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('csd.renewals.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-stat-card erp-stat-card--warning erp-kpi-clickable h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-autorenew"></i></div>
                <div class="erp-stat-card__value">{{ $adminData['renewal_due_clients'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Renewals Due (30d)</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="erp-stat-card erp-stat-card--success h-100">
            <div class="erp-stat-card__icon"><i class="mdi mdi-emoticon-happy-outline"></i></div>
            <div class="erp-stat-card__value">{{ $adminData['satisfaction_score'] ?? '—' }}</div>
            <div class="erp-stat-card__label">Avg Satisfaction</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('csd.support.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-stat-card erp-stat-card--danger erp-kpi-clickable h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-lifebuoy"></i></div>
                <div class="erp-stat-card__value">{{ $adminData['open_tickets'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Open Tickets</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('csd.clients.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-stat-card erp-stat-card--csd erp-kpi-clickable h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                <div class="erp-stat-card__value">{{ $adminData['at_risk_clients'] ?? 0 }}</div>
                <div class="erp-stat-card__label">At-Risk Clients</div>
            </div>
        </a>
    </div>
</div>

<div class="row erp-dash-kpi-row mb-3">
    <div class="col-lg-6">
        <div class="card erp-table-card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3 font-size-14 font-weight-bold">Recent Handoffs <span class="text-muted font-weight-normal">({{ $year }})</span></h5>
                <div class="table-responsive erp-scroll-panel">
                    <table class="table table-sm trendy-table mb-0">
                        <thead><tr><th>Client</th><th>Project</th><th>Assigned</th></tr></thead>
                        <tbody>
                            @forelse($adminData['recent_handoffs'] ?? [] as $handoff)
                            <tr>
                                <td class="font-weight-medium">{{ $handoff->client->name ?? '-' }}</td>
                                <td>{{ $handoff->project->project_name ?? '-' }}</td>
                                <td>{{ $handoff->assignee->name ?? 'Unassigned' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No handoffs in {{ $year }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card erp-table-card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3 font-size-14 font-weight-bold">Upcoming Follow-ups</h5>
                <div class="table-responsive erp-scroll-panel">
                    <table class="table table-sm trendy-table mb-0">
                        <thead><tr><th>Client</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($adminData['upcoming_followups'] ?? [] as $followup)
                            <tr>
                                <td class="font-weight-medium">{{ $followup->client->name ?? '-' }}</td>
                                <td>{{ $followup->next_followup ? \Carbon\Carbon::parse($followup->next_followup)->format('d M Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No follow-ups scheduled this week</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row erp-dash-kpi-row mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h6 class="text-muted font-size-11 mb-1 text-uppercase">Overdue Collections</h6>
                <h4 class="text-danger mb-0">{{ $adminData['overdue_collections'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h6 class="text-muted font-size-11 mb-1 text-uppercase">SLA Breaches</h6>
                <h4 class="text-danger mb-0">{{ $adminData['sla_breaches'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h6 class="text-muted font-size-11 mb-1 text-uppercase">Expiring AMC</h6>
                <h4 class="mb-0">{{ $adminData['expiring_amc'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <h6 class="text-muted font-size-11 mb-1 text-uppercase">Follow-up Due</h6>
                <h4 class="mb-0">{{ $adminData['clients_requiring_followup'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>
