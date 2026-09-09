@extends('layouts.app')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .ops-report-wrapper {
        color: #1e293b;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .kpi-card {
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 16px 20px;
        position: relative;
        overflow: hidden;
        min-height: 96px;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
    }
    .kpi-card.kpi-indigo::before { background: #4f46e5; }
    .kpi-card.kpi-emerald::before { background: #10b981; }
    .kpi-card.kpi-blue::before { background: #0284c7; }
    .kpi-card.kpi-purple::before { background: #8b5cf6; }

    .kpi-icon-bubble {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .kpi-indigo .kpi-icon-bubble { background: #eef2ff; color: #4f46e5; }
    .kpi-emerald .kpi-icon-bubble { background: #ecfdf5; color: #10b981; }
    .kpi-blue .kpi-icon-bubble { background: #f0f9ff; color: #0284c7; }
    .kpi-purple .kpi-icon-bubble { background: #f5f3ff; color: #8b5cf6; }

    .table-modern thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .table-modern tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
    }
    .avatar-bubble {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: white;
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        flex-shrink: 0;
        margin-right: 12px !important;
    }
    .pagination-rounded .page-link {
        border-radius: 8px !important;
        margin: 0 3px;
        border: 1px solid #e2e8f0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid ops-report-wrapper pb-5">

    {{-- Top Header Bar --}}
    <div class="row align-items-center mb-4 mt-2">
        <div class="col-lg-5">
            <div class="d-flex align-items-center">
                <a href="{{ url('/') }}" class="btn btn-light border btn-sm px-3 mr-3 shadow-sm font-weight-medium">
                    <i class="mdi mdi-arrow-left mr-1"></i> Dashboard
                </a>
                <div>
                    <h4 class="mb-1 text-dark font-weight-bold">Department Work Report</h4>
                    <span class="text-muted font-size-13">
                        Performance matrix & deliverable analytics for <strong class="text-dark">{{ $branchLabel }}</strong>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-7 text-lg-right mt-3 mt-lg-0">
            <form id="opsFilterForm" class="d-inline-flex align-items-center flex-wrap" style="gap: 8px;">
                {{-- Department Selector (If Allowed) --}}
                @if(Auth::user()->hasBranchWideAccess())
                <div class="input-group input-group-sm shadow-sm" style="width: 190px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-domain text-primary"></i></span>
                    </div>
                    <select name="department" id="filter_department" class="form-control font-weight-medium border-left-0 font-size-12">
                        @foreach($departments as $id => $label)
                        <option value="{{ $id }}" {{ $departmentId == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <input type="hidden" name="department" id="filter_department" value="{{ $departmentId }}">
                @endif

                {{-- Unified Date Range Picker --}}
                <div class="input-group input-group-sm shadow-sm" style="width: 240px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-calendar text-primary"></i></span>
                    </div>
                    <input type="text" id="ops_date_range_picker" class="form-control font-weight-medium border-left-0 font-size-12" style="background: white; cursor: pointer;" readonly>
                    <input type="hidden" name="start_date" id="filter_start_date" value="{{ $startDateStr }}">
                    <input type="hidden" name="end_date" id="filter_end_date" value="{{ $endDateStr }}">
                </div>

                {{-- Action Buttons --}}
                <button type="button" id="btnApplyOpsFilter" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-semibold">
                    <i class="mdi mdi-filter mr-1"></i> Apply
                </button>
                <button type="button" id="btnResetOpsFilter" class="btn btn-light border btn-sm px-2.5 font-weight-medium" title="Reset to Current Month">
                    <i class="mdi mdi-refresh"></i> Reset
                </button>
            </form>
        </div>
    </div>

    {{-- Executive Department Summary KPI Cards --}}
    <div class="row mb-4">
        {{-- Card 1: Department Workforce --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="kpi-card kpi-indigo">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="pl-1">
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Active Workforce</span>
                        <div class="font-size-22 font-weight-bold text-dark my-1">{{ $employeeCount }}</div>
                        <small class="text-muted font-size-11">{{ $departments[$departmentId] ?? 'Department' }}</small>
                    </div>
                    <div class="kpi-icon-bubble">
                        <i class="mdi mdi-account-group-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Deliverables Output --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="kpi-card kpi-emerald">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="pl-1">
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">{{ $outputLabel }}</span>
                        <div class="font-size-22 font-weight-bold text-dark my-1">{{ number_format($totalOutput) }}</div>
                        <small class="text-success font-size-11 font-weight-semibold"><i class="mdi mdi-check-circle-outline mr-0.5"></i> Total Output</small>
                    </div>
                    <div class="kpi-icon-bubble">
                        <i class="mdi mdi-check-decagram-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Time & Hours Logged --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="kpi-card kpi-blue">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="pl-1">
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">{{ $hoursLabel }}</span>
                        <div class="font-size-22 font-weight-bold text-dark my-1">{{ number_format($totalHours, 1) }}</div>
                        <small class="text-muted font-size-11">Total Time Invested</small>
                    </div>
                    <div class="kpi-icon-bubble">
                        <i class="mdi mdi-clock-time-four-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Average Productivity --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card kpi-purple">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="pl-1" style="flex: 1;">
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Productivity Index</span>
                        <div class="font-size-22 font-weight-bold text-dark my-1">{{ $avgProductivity }}%</div>
                        <div class="progress" style="height: 4px; max-width: 130px; border-radius: 2px; background: #ede9fe;">
                            <div class="progress-bar bg-purple" role="progressbar" style="width: {{ $avgProductivity }}%; background-color: #8b5cf6;" aria-valuenow="{{ $avgProductivity }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="kpi-icon-bubble">
                        <i class="mdi mdi-speedometer"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Performance Matrix Table Card --}}
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                    <i class="mdi mdi-table text-primary mr-1"></i> Staff Performance & Work Audit Matrix
                </h5>
                <small class="text-muted">Individual activity logs, deliverable breakdown, and hours for the filtered period</small>
            </div>
            <span class="badge badge-soft-primary px-3 py-1 rounded-pill font-size-11 font-weight-bold">
                {{ $employeeCount }} Staff Members
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="operations-report-table" class="table table-modern table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px; width: 22%;">Employee</th>
                            <th style="width: 14%;">Department</th>
                            @if($departmentId == 1)
                            <th style="width: 11%; text-align: center;">Leads Assigned</th>
                            <th style="width: 11%; text-align: center;">Active Followups</th>
                            <th style="width: 11%; text-align: center;">Matured Clients</th>
                            <th style="width: 13%; text-align: right;">Sales Volume</th>
                            <th style="width: 10%; text-align: center;">Callbacks</th>
                            @elseif($departmentId == 3)
                            <th style="width: 12%; text-align: center;">Active Clients</th>
                            <th style="width: 12%; text-align: center;">Communications</th>
                            <th style="width: 12%; text-align: center;">Tickets Resolved</th>
                            <th style="width: 10%; text-align: center;">Opps Won</th>
                            <th style="width: 10%; text-align: center;">Collections</th>
                            @else
                            <th style="width: 10%; text-align: center;">Days Worked</th>
                            <th style="width: 12%; text-align: center;">Tasks Completed</th>
                            <th style="width: 12%; text-align: right;">Total Hours</th>
                            <th style="width: 10%; text-align: center;">Avg Hrs/Day</th>
                            <th style="width: 10%; text-align: center;">Log Entries</th>
                            @endif
                            <th style="width: 14%; text-align: center;">Productivity</th>
                            <th style="padding-right: 24px; width: 10%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        var selectedStart = "{{ $startDateStr }}";
        var selectedEnd = "{{ $endDateStr }}";

        $('#ops_date_range_picker').daterangepicker({
            startDate: moment(selectedStart),
            endDate: moment(selectedEnd),
            locale: {
                format: 'YYYY-MM-DD'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Quarter': [moment().startOf('quarter'), moment().endOf('quarter')],
                'This Year': [moment().startOf('year'), moment().endOf('year')]
            }
        }, function(start, end) {
            selectedStart = start.format('YYYY-MM-DD');
            selectedEnd = end.format('YYYY-MM-DD');
            $('#filter_start_date').val(selectedStart);
            $('#filter_end_date').val(selectedEnd);
            $('#ops_date_range_picker').val(selectedStart + ' - ' + selectedEnd);
        });

        $('#ops_date_range_picker').val(moment(selectedStart).format('YYYY-MM-DD') + ' - ' + moment(selectedEnd).format('YYYY-MM-DD'));

        var opsTable = $('#operations-report-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [15, 25, 50, 100],
            ajax: {
                url: "{{ route('reports.operations.data') }}",
                data: function(d) {
                    d.department = $('#filter_department').val();
                    d.start_date = $('#filter_start_date').val() || selectedStart;
                    d.end_date = $('#filter_end_date').val() || selectedEnd;
                    d.date_from = $('#filter_start_date').val() || selectedStart;
                    d.date_to = $('#filter_end_date').val() || selectedEnd;
                }
            },
            columns: [
                {
                    data: 'name',
                    render: function(data, type, row) {
                        var initial = data ? data.charAt(0).toUpperCase() : 'U';
                        return `
                            <div class="d-flex align-items-center">
                                <div class="avatar-bubble mr-3" style="width: 34px; height: 34px; border-radius: 8px; font-size: 13px; margin-right: 12px !important;">${initial}</div>
                                <div>
                                    <div class="font-weight-bold text-dark font-size-13">${data}</div>
                                    <small class="text-muted font-size-11">#EMP-${row.id + 1000}</small>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'departments',
                    render: function(data) {
                        var name = (data && data.dept) ? data.dept.name : 'Operations';
                        return `<span class="badge badge-soft-primary px-2 py-0.5 rounded-pill font-size-11 font-weight-medium">${name}</span>`;
                    }
                },
                @if($departmentId == 1)
                {
                    data: 'leads_count',
                    className: 'text-center',
                    render: function(d) { return `<span class="badge badge-light border px-2 py-0.5 font-size-12">${d || 0}</span>`; }
                },
                {
                    data: 'followups_count',
                    className: 'text-center',
                    render: function(d) { return `<span class="font-weight-medium font-size-12 text-dark">${d || 0}</span>`; }
                },
                {
                    data: 'matured_count',
                    className: 'text-center',
                    render: function(d) { return `<span class="badge badge-soft-success px-2 py-0.5 rounded-pill font-weight-bold font-size-12">${d || 0}</span>`; }
                },
                {
                    data: 'sales_amount',
                    className: 'text-right',
                    render: function(data) {
                        return `<span class="font-weight-bold text-success font-size-13">₹${parseFloat(data || 0).toLocaleString()}</span>`;
                    }
                },
                {
                    data: 'callback_logs_count',
                    className: 'text-center',
                    render: function(d) { return `<span class="font-weight-medium font-size-12 text-dark">${d || 0}</span>`; }
                },
                @elseif($departmentId == 3)
                {
                    data: 'active_clients',
                    className: 'text-center',
                    render: function(d) { return `<span class="badge badge-light border px-2 py-0.5 font-size-12">${d || 0}</span>`; }
                },
                {
                    data: 'comms_count',
                    className: 'text-center',
                    render: function(d) { return `<span class="font-weight-medium font-size-12 text-dark">${d || 0}</span>`; }
                },
                {
                    data: 'tickets_resolved',
                    className: 'text-center',
                    render: function(d) { return `<span class="badge badge-soft-success px-2 py-0.5 rounded-pill font-weight-bold font-size-12">${d || 0}</span>`; }
                },
                {
                    data: 'opportunities_won',
                    className: 'text-center',
                    render: function(d) { return `<span class="badge badge-soft-info px-2 py-0.5 rounded-pill font-size-12">${d || 0}</span>`; }
                },
                {
                    data: 'collections_paid',
                    className: 'text-center',
                    render: function(d) { return `<span class="font-weight-medium font-size-12 text-dark">${d || 0}</span>`; }
                },
                @else
                {
                    data: 'days_worked',
                    className: 'text-center',
                    render: function(d) { return `<span class="font-weight-medium font-size-12 text-dark">${d || 0} days</span>`; }
                },
                {
                    data: 'completed_tasks',
                    className: 'text-center',
                    render: function(d) { return `<span class="badge badge-soft-success px-2 py-0.5 rounded-pill font-weight-bold font-size-12">${d || 0} Tasks</span>`; }
                },
                {
                    data: 'total_hours',
                    className: 'text-right',
                    render: function(data, type, row) {
                        if (row.total_hours_formatted) {
                            return `<span class="font-weight-bold text-primary font-size-13">${row.total_hours_formatted} hrs</span>`;
                        }
                        var val = parseFloat(data || 0);
                        var hrs = Math.floor(val);
                        var mins = Math.round((val - hrs) * 60);
                        if (mins >= 60) { hrs += 1; mins = 0; }
                        var formatted = hrs + '.' + (mins < 10 ? '0' + mins : mins);
                        return `<span class="font-weight-bold text-primary font-size-13">${formatted} hrs</span>`;
                    }
                },
                {
                    data: 'avg_hours_per_day',
                    className: 'text-center',
                    render: function(d, type, row) {
                        if (row.avg_hours_formatted) {
                            return `<span class="font-weight-semibold font-size-12 text-dark">${row.avg_hours_formatted} h/d</span>`;
                        }
                        var val = parseFloat(d || 0);
                        var hrs = Math.floor(val);
                        var mins = Math.round((val - hrs) * 60);
                        if (mins >= 60) { hrs += 1; mins = 0; }
                        var formatted = hrs + '.' + (mins < 10 ? '0' + mins : mins);
                        return `<span class="font-weight-semibold font-size-12 text-dark">${formatted} h/d</span>`;
                    }
                },
                {
                    data: 'log_entries',
                    className: 'text-center',
                    render: function(d) { return `<span class="badge badge-light border px-2 py-0.5 font-size-11">${d || 0} logs</span>`; }
                },
                @endif
                {
                    data: 'productivity',
                    className: 'text-center',
                    render: function(data) {
                        var score = parseInt(data) || 0;
                        var colorClass = score >= 75 ? 'bg-success' : (score >= 40 ? 'bg-primary' : 'bg-warning');
                        var badgeClass = score >= 75 ? 'badge-soft-success' : (score >= 40 ? 'badge-soft-primary' : 'badge-soft-warning');
                        return `
                            <div class="d-inline-flex flex-column align-items-center" style="width: 100px;">
                                <span class="badge ${badgeClass} px-2 py-0.5 rounded-pill font-size-11 font-weight-bold mb-1">${score}%</span>
                                <div class="progress w-100" style="height: 4px; border-radius: 2px;">
                                    <div class="progress-bar ${colorClass}" style="width: ${score}%;"></div>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'action_link',
                    orderable: false,
                    searchable: false,
                    className: 'text-right pr-4',
                    render: function(url) {
                        return `<a href="${url}" class="btn btn-outline-primary btn-sm px-2.5 py-1 font-size-12 font-weight-semibold shadow-sm" style="border-radius: 8px;"><i class="mdi mdi-chart-box-outline mr-0.5"></i> Report</a>`;
                    }
                }
            ],
            order: [
                @if($departmentId == 1)[4, 'desc']
                @elseif($departmentId == 3)[2, 'desc']
                @else[4, 'desc']
                @endif
            ],
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            dom: '<"d-flex justify-content-between align-items-center p-3"<"font-size-12"l><"font-size-12"f>>rt<"d-flex justify-content-between align-items-center p-3"<"font-size-12"i><"font-size-12"p>>',
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        // Filter Apply Click
        $('#btnApplyOpsFilter').on('click', function(e) {
            e.preventDefault();
            $('#opsFilterForm').submit();
        });

        // Filter Department Change
        $('#filter_department').on('change', function() {
            $('#opsFilterForm').submit();
        });

        // Filter Reset Click
        $('#btnResetOpsFilter').on('click', function(e) {
            e.preventDefault();
            var defaultStart = moment().startOf('month').format('YYYY-MM-DD');
            var defaultEnd = moment().format('YYYY-MM-DD');

            var url = new URL(window.location.href);
            url.searchParams.set('start_date', defaultStart);
            url.searchParams.set('end_date', defaultEnd);
            url.searchParams.set('date_from', defaultStart);
            url.searchParams.set('date_to', defaultEnd);
            window.location.href = url.toString();
        });
    });
</script>
@endsection
