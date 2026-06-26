@extends('layouts.app')

@section('styles')
<style>
.csd-team-report__filters {
    background: #fff;
    border: 1px solid var(--erp-border, #e9ecef);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
.csd-team-report__filters label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
    margin-bottom: 0.35rem;
}
.csd-team-report__score-ring {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    font-weight: 700;
    border: 4px solid var(--dept-csd, #42968b);
    color: var(--dept-csd, #42968b);
    background: rgba(66, 150, 139, 0.08);
}
.csd-team-report__metric-table th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    white-space: nowrap;
}
.csd-team-report__metric-table td {
    vertical-align: middle;
}
.csd-team-report__exec-row {
    cursor: pointer;
}
.csd-team-report__exec-row:hover {
    background: rgba(66, 150, 139, 0.06);
}
.csd-team-report__exec-row.is-selected {
    background: rgba(66, 150, 139, 0.12);
}
</style>
@endsection

@section('content')
@php
    $m = $reportData['metrics'];
    $snap = $reportData['snapshot'];
    $isTeam = $reportData['is_team_view'];
    $selectedUser = $reportData['selected_user'];
    $period = $reportData['period_label'];
@endphp
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'CSD Team Report',
        'subtitle' => $isTeam
            ? 'Team-wide performance for ' . $period
            : 'Performance for ' . ($selectedUser->name ?? 'Executive') . ' — ' . $period,
    ])

    {{-- Filters --}}
    <div class="csd-team-report__filters">
        <form method="GET" action="{{ route('csd.reports.team') }}" id="csdTeamReportForm" class="row align-items-end">
            <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                <label for="filterUser">Executive</label>
                <select name="user" id="filterUser" class="form-control form-control-sm">
                    <option value="" {{ $isTeam ? 'selected' : '' }}>All Team Members</option>
                    @foreach($reportData['members'] as $member)
                    <option value="{{ $member->id }}" {{ (int) $reportData['selected_user_id'] === $member->id ? 'selected' : '' }}>
                        {{ $member->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2 mb-2 mb-md-0">
                <label for="filterYear">Year</label>
                <select name="year" id="filterYear" class="form-control form-control-sm">
                    @foreach($reportData['available_years'] as $yr)
                    <option value="{{ $yr }}" {{ $reportData['year'] == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2 mb-2 mb-md-0">
                <label for="filterMonth">Month</label>
                <select name="month" id="filterMonth" class="form-control form-control-sm">
                    @foreach($reportData['months'] as $mo)
                    <option value="{{ $mo }}" {{ $reportData['month'] === $mo ? 'selected' : '' }}>{{ $mo === 'All' ? 'All Months' : $mo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-lg-2 mb-2 mb-md-0">
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="mdi mdi-filter-outline"></i> Apply
                </button>
            </div>
            <div class="col-lg-3 text-lg-right">
                <span class="badge badge-soft-primary font-size-12 px-3 py-2">
                    <i class="mdi mdi-account-group-outline"></i> {{ $reportData['team_size'] }} in scope
                </span>
            </div>
        </form>
    </div>

    {{-- KPI row: period activity --}}
    <div class="row erp-dash-kpi-row mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--csd h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-account-check-outline"></i></div>
                <div class="erp-stat-card__value">{{ $m['active_clients'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Active Clients</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--indigo h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-message-text-outline"></i></div>
                <div class="erp-stat-card__value">{{ $m['communications'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Communications ({{ $period }})</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--success h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-lifebuoy"></i></div>
                <div class="erp-stat-card__value">{{ $m['tickets_resolved'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Tickets Resolved</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--warning h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-trending-up"></i></div>
                <div class="erp-stat-card__value">{{ $m['opportunities_won'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Opportunities Won</div>
            </div>
        </div>
    </div>

    <div class="row erp-dash-kpi-row mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--danger h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                <div class="erp-stat-card__value">{{ $m['at_risk_clients'] ?? 0 }}</div>
                <div class="erp-stat-card__label">At-Risk Clients</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--warning h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-cash-check"></i></div>
                <div class="erp-stat-card__value">{{ $m['collections_paid'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Collections Paid</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--indigo h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-file-document-edit-outline"></i></div>
                <div class="erp-stat-card__value">{{ $m['change_requests_completed'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Change Requests Done</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card erp-stat-card--csd h-100">
                <div class="erp-stat-card__icon"><i class="mdi mdi-autorenew"></i></div>
                <div class="erp-stat-card__value">{{ $m['renewals_completed'] ?? 0 }}</div>
                <div class="erp-stat-card__label">Renewals Completed</div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row mb-3">
        <div class="col-lg-8">
            <div class="card erp-table-card h-100">
                <div class="card-body">
                    <h5 class="card-title font-size-14 font-weight-bold mb-1">Monthly Activity — {{ $reportData['year'] }}</h5>
                    <p class="text-muted font-size-12 mb-3">
                        {{ $isTeam ? 'Team aggregate across all executives' : ($selectedUser->name ?? 'Executive') . ' monthly breakdown' }}
                    </p>
                    <div id="csdTeamTrendChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card erp-table-card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title font-size-14 font-weight-bold mb-1">Performance Score</h5>
                    <p class="text-muted font-size-12 mb-3">Weighted index for selected period</p>
                    <div class="text-center mb-3">
                        <div class="csd-team-report__score-ring mx-auto">{{ $reportData['performance_score'] }}</div>
                    </div>
                    <div id="csdTeamWorkloadChart" style="min-height: 220px; flex: 1;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card erp-table-card">
                <div class="card-body">
                    <h5 class="card-title font-size-14 font-weight-bold mb-1">Executive Comparison — {{ $period }}</h5>
                    <p class="text-muted font-size-12 mb-3">Period activity by team member (click a row to drill down)</p>
                    <div id="csdTeamCompareChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Live snapshot (current, not period-filtered) --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card erp-table-card">
                <div class="card-body py-3">
                    <h6 class="font-size-12 text-uppercase text-muted font-weight-bold mb-3">Current Snapshot (live)</h6>
                    <div class="row text-center">
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <span class="d-block text-muted font-size-11">Open Tickets</span>
                            <strong class="font-size-18">{{ $snap['open_tickets'] }}</strong>
                        </div>
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <span class="d-block text-muted font-size-11">SLA Breaches</span>
                            <strong class="font-size-18 text-danger">{{ $snap['sla_breaches'] }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="d-block text-muted font-size-11">Pending Collections</span>
                            <strong class="font-size-18">{{ $snap['pending_collections'] }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="d-block text-muted font-size-11">Expiring AMC</span>
                            <strong class="font-size-18">{{ $snap['expiring_amc'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed metrics table --}}
    <div class="card erp-table-card mb-3">
        <div class="card-body">
            <h5 class="card-title font-size-14 font-weight-bold mb-3">Period Detail — {{ $period }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 csd-team-report__metric-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Metric</th>
                            <th class="text-right">Count</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Communications logged</td><td class="text-right font-weight-bold">{{ $m['communications'] ?? 0 }}</td><td class="text-muted font-size-12">Calls, meetings, emails, WhatsApp, notes</td></tr>
                        <tr><td>Support tickets resolved</td><td class="text-right font-weight-bold">{{ $m['tickets_resolved'] ?? 0 }}</td><td class="text-muted font-size-12">Resolved or closed in period</td></tr>
                        <tr><td>Open tickets (now)</td><td class="text-right font-weight-bold">{{ $m['open_tickets'] ?? 0 }}</td><td class="text-muted font-size-12">Open / in progress</td></tr>
                        <tr><td>Collections marked paid</td><td class="text-right font-weight-bold">{{ $m['collections_paid'] ?? 0 }}</td><td class="text-muted font-size-12">Follow-ups closed as paid</td></tr>
                        <tr><td>Overdue collections (now)</td><td class="text-right font-weight-bold text-danger">{{ $m['collections_overdue'] ?? 0 }}</td><td class="text-muted font-size-12">Requires immediate attention</td></tr>
                        <tr><td>Change requests completed</td><td class="text-right font-weight-bold">{{ $m['change_requests_completed'] ?? 0 }}</td><td class="text-muted font-size-12">Scope delivered or closed</td></tr>
                        <tr><td>Change requests pending (now)</td><td class="text-right font-weight-bold">{{ $m['change_requests_pending'] ?? 0 }}</td><td class="text-muted font-size-12">Excludes rejected</td></tr>
                        <tr><td>Opportunities won</td><td class="text-right font-weight-bold">{{ $m['opportunities_won'] ?? 0 }}</td><td class="text-muted font-size-12">Upsell converted in period</td></tr>
                        <tr><td>Open opportunities (now)</td><td class="text-right font-weight-bold">{{ $m['open_opportunities'] ?? 0 }}</td><td class="text-muted font-size-12">Identified or proposed</td></tr>
                        <tr><td>Renewals completed</td><td class="text-right font-weight-bold">{{ $m['renewals_completed'] ?? 0 }}</td><td class="text-muted font-size-12">Marked renewed in period</td></tr>
                        @if($isTeam)
                        <tr><td>Unassigned clients (now)</td><td class="text-right font-weight-bold">{{ $m['unassigned_clients'] ?? 0 }}</td><td class="text-muted font-size-12">Awaiting executive assignment</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Executive breakdown --}}
    <div class="card erp-table-card">
        <div class="card-body">
            <h5 class="card-title font-size-14 font-weight-bold mb-3">Executive Breakdown — {{ $period }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 csd-team-report__metric-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Executive</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Clients</th>
                            <th class="text-center">At Risk</th>
                            <th class="text-center">Comms</th>
                            <th class="text-center">Tickets ↓</th>
                            <th class="text-center">Open Tkt</th>
                            <th class="text-center">Coll. OD</th>
                            <th class="text-center">Opp Won</th>
                            <th class="text-center">CR Pend</th>
                            <th class="text-center">Renewals</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['executives'] as $exec)
                        <tr class="csd-team-report__exec-row {{ (int) $reportData['selected_user_id'] === $exec->id ? 'is-selected' : '' }}"
                            data-user-id="{{ $exec->id }}">
                            <td class="font-weight-medium">{{ $exec->name }}</td>
                            <td class="text-center"><span class="badge badge-soft-primary">{{ $exec->performance_score }}</span></td>
                            <td class="text-center">{{ $exec->active_clients }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $exec->at_risk_clients > 0 ? 'warning' : 'success' }}">{{ $exec->at_risk_clients }}</span>
                            </td>
                            <td class="text-center">{{ $exec->communications }}</td>
                            <td class="text-center">{{ $exec->tickets_resolved }}</td>
                            <td class="text-center">{{ $exec->open_tickets }}</td>
                            <td class="text-center">{{ $exec->overdue_collections }}</td>
                            <td class="text-center">{{ $exec->opportunities_won }}</td>
                            <td class="text-center">{{ $exec->change_requests_pending }}</td>
                            <td class="text-center">{{ $exec->renewals_completed }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="text-center text-muted py-4">No CSD executives in scope. Configure teams under Admin → Departments → CSD.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
(function () {
    var form = document.getElementById('csdTeamReportForm');
    ['filterUser', 'filterYear', 'filterMonth'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                if (id === 'filterYear') {
                    document.getElementById('filterMonth').value = 'All';
                }
                form.submit();
            });
        }
    });

    document.querySelectorAll('.csd-team-report__exec-row[data-user-id]').forEach(function (row) {
        row.addEventListener('click', function () {
            document.getElementById('filterUser').value = row.getAttribute('data-user-id');
            form.submit();
        });
    });

    var months = @json($reportData['monthly_trend']->pluck('month'));
    var trendComms = @json($reportData['monthly_trend']->pluck('communications'));
    var trendTickets = @json($reportData['monthly_trend']->pluck('tickets_resolved'));
    var trendWon = @json($reportData['monthly_trend']->pluck('opportunities_won'));
    var trendRenewals = @json($reportData['monthly_trend']->pluck('renewals_completed'));
    var trendCrs = @json($reportData['monthly_trend']->pluck('change_requests_completed'));

    var csdColor = '#42968b';

    new ApexCharts(document.querySelector('#csdTeamTrendChart'), {
        series: [
            { name: 'Communications', data: trendComms },
            { name: 'Tickets Resolved', data: trendTickets },
            { name: 'Opportunities Won', data: trendWon },
            { name: 'Renewals', data: trendRenewals },
            { name: 'Change Requests', data: trendCrs },
        ],
        chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: [csdColor, '#10b981', '#f59e0b', '#6366f1', '#ef4444'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
        xaxis: { categories: months },
        yaxis: { labels: { formatter: function (v) { return Math.round(v); } } },
        legend: { position: 'top', horizontalAlign: 'left' },
        grid: { borderColor: '#f1f5f9' },
    }).render();

    var workload = @json($reportData['workload_mix']);
    var workloadLabels = workload.map(function (w) { return w.label; });
    var workloadValues = workload.map(function (w) { return w.value; });

    new ApexCharts(document.querySelector('#csdTeamWorkloadChart'), {
        series: workloadValues,
        labels: workloadLabels,
        chart: { type: 'donut', height: 240, fontFamily: 'inherit' },
        colors: ['#ef4444', '#f59e0b', '#6366f1', '#dc2626', '#f97316'],
        legend: { position: 'bottom', fontSize: '11px' },
        plotOptions: { pie: { donut: { size: '62%' } } },
        dataLabels: { enabled: true, formatter: function (v) { return Math.round(v) + '%'; } },
    }).render();

    var compare = @json($reportData['comparison']);

    new ApexCharts(document.querySelector('#csdTeamCompareChart'), {
        series: [
            { name: 'Communications', data: compare.communications },
            { name: 'Tickets Resolved', data: compare.tickets_resolved },
            { name: 'Opportunities Won', data: compare.opportunities_won },
        ],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit', stacked: false },
        colors: [csdColor, '#10b981', '#f59e0b'],
        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
        xaxis: { categories: compare.labels },
        yaxis: { labels: { formatter: function (v) { return Math.round(v); } } },
        legend: { position: 'top' },
        grid: { borderColor: '#f1f5f9' },
    }).render();
})();
</script>
@endsection
