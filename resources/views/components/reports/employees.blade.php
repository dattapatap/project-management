@extends('layouts.app')

@php
$user = Auth::user();
$isSalesTL = $user->hasRole('Team-Leader') && ($user->departments && $user->departments->department == 1);
@endphp

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .emp-report-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .report-stat-card {
        border-radius: 16px;
        padding: 22px 24px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #edf2f7;
    }

    .report-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(0, 0, 0, 0.08);
    }

    .report-stat-card.stat-workforce {
        background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        border-left: 4px solid #2563eb;
    }

    .report-stat-card.stat-ops {
        background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
        border-left: 4px solid #7c3aed;
    }

    .report-stat-card.stat-sales {
        background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%);
        border-left: 4px solid #059669;
    }

    .report-stat-card.stat-efficiency {
        background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
        border-left: 4px solid #d97706;
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-workforce .stat-icon-wrapper {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    .stat-ops .stat-icon-wrapper {
        background: rgba(124, 58, 237, 0.12);
        color: #7c3aed;
    }

    .stat-sales .stat-icon-wrapper {
        background: rgba(5, 150, 105, 0.12);
        color: #059669;
    }

    .stat-efficiency .stat-icon-wrapper {
        background: rgba(217, 119, 6, 0.12);
        color: #d97706;
    }

    .filter-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        background: #ffffff;
    }

    .badge-dept {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-dept-nsd {
        background-color: rgba(127, 0, 255, 0.12);
        color: #7f00ff;
    }

    .badge-dept-od {
        background-color: rgba(46, 134, 222, 0.12);
        color: #2e86de;
    }

    .badge-dept-csd {
        background-color: rgba(16, 172, 132, 0.12);
        color: #10ac84;
    }

    .table-modern thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 14px 18px;
    }

    .table-modern tbody td {
        padding: 15px 18px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .table-modern tbody tr:hover {
        background-color: #f8fafc;
    }

    .progress-modern {
        height: 6px;
        border-radius: 10px;
        background-color: #e2e8f0;
        overflow: hidden;
    }
</style>
@endsection

@section('content')
<div class="container-fluid emp-report-wrapper pb-5">

    {{-- Breadcrumb & Title --}}
    <div class="row align-items-center mb-4 mt-2">
        <div class="col-md-7">
            <h4 class="mb-1 text-dark font-weight-bold d-flex align-items-center">
                <i class="mdi mdi-chart-box-outline text-primary mr-2 font-size-24"></i>
                {{ $isSalesTL ? 'Team Sales Performance Report' : (Auth::user()->hasRole('Team-Leader') ? 'Team Performance Intelligence' : 'Enterprise Workforce & Productivity Report') }}
            </h4>
            <p class="text-muted font-size-13 mb-0">
                Comprehensive productivity analytics, departmental velocity, output tracking, and employee performance reviews.
            </p>
        </div>
        <div class="col-md-5 text-md-right mt-3 mt-md-0">
            <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12 d-inline-flex">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-muted">Reports</a></li>
                <li class="breadcrumb-item active text-dark font-weight-bold">Employee Report</li>
            </ol>
        </div>
    </div>

    {{-- Unified Enterprise Filter Bar --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-3">
            <form id="employeeReportFilterForm">
                <div class="row align-items-center">
                    {{-- 1. Date Range Picker --}}
                    <div class="col-xl-4 col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-11 font-weight-bold text-muted mb-1 text-uppercase letter-spacing-1">
                            <i class="mdi mdi-calendar-range mr-1 text-primary"></i> Performance Date Range
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-calendar"></i></span>
                            </div>
                            <input type="text" id="report_date_range" class="form-control form-control-sm border-left-0 font-weight-medium" style="background: white; cursor: pointer;" readonly placeholder="Select Date Range">
                            <input type="hidden" id="filter_start_date" name="start_date" value="{{ $startDateStr }}">
                            <input type="hidden" id="filter_end_date" name="end_date" value="{{ $endDateStr }}">
                        </div>
                    </div>

                    {{-- 2. Department Selector --}}
                    <div class="col-xl-3 col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-11 font-weight-bold text-muted mb-1 text-uppercase letter-spacing-1">
                            <i class="mdi mdi-domain mr-1 text-primary"></i> Department
                        </label>
                        <select name="dept_id" id="filter_dept_id" class="form-control form-control-sm select2">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ (string)($deptId ?? '') === (string)$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Action Buttons --}}
                    <div class="col-xl-5 col-md-12 text-xl-right mt-2 mt-xl-0 pt-xl-3">
                        <div class="d-inline-flex align-items-center" style="gap: 8px;">
                            <button type="button" id="btnApplyFilter" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-semibold">
                                <i class="mdi mdi-filter mr-1"></i> Apply Filter
                            </button>
                            <button type="button" id="btnResetFilter" class="btn btn-light border btn-sm px-3 font-weight-medium">
                                <i class="mdi mdi-refresh mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Top KPI Cards --}}
    <div class="row mb-4">
        {{-- 1. Total Workforce --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="report-stat-card stat-workforce d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Active Workforce</span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-dark">{{ $employeesCount }}</h2>
                    <small class="text-primary font-size-11 font-weight-semibold">Team Members in Scope</small>
                </div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-group-outline"></i>
                </div>
            </div>
        </div>

        {{-- 2. Operations Delivery --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="report-stat-card stat-ops d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Operations Output</span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-dark">{{ $completedOps }} <small class="font-size-13 text-muted font-weight-normal">Tasks</small></h2>
                    <small class="text-purple font-size-11 font-weight-semibold" style="color: #7c3aed;">{{ number_format($totalHoursLogged, 1) }} Task Hours Logged</small>
                </div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-checkbox-marked-circle-outline"></i>
                </div>
            </div>
        </div>

        {{-- 3. Sales Conversions --}}
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="report-stat-card stat-sales d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Sales Conversions</span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-dark">{{ $maturedCount }} <small class="font-size-13 text-muted font-weight-normal">Matured</small></h2>
                    <small class="text-success font-size-11 font-weight-semibold">{{ $totalLeadsCount }} Leads / {{ $activeFollowupCount }} Active</small>
                </div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-trophy-outline"></i>
                </div>
            </div>
        </div>

        {{-- 4. Overall Efficiency Rate --}}
        <div class="col-xl-3 col-md-6">
            <div class="report-stat-card stat-efficiency d-flex align-items-center justify-content-between">
                <div>
                    <span class="font-size-11 font-weight-bold text-uppercase text-muted letter-spacing-1">Ops Delivery Rate</span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-dark">{{ $opsRate }}%</h2>
                    <small class="font-size-11 font-weight-semibold" style="color: #d97706;">Completion Efficiency</small>
                </div>
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-speedometer"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Trend Chart --}}
    <div class="card border shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                    <i class="mdi mdi-chart-areaspline text-primary mr-1"></i> Organizational Velocity & Monthly Delivery Trend (FY {{ $selectedYear }})
                </h5>
                <small class="text-muted">Monthly tracking of Completed Operations Tasks vs Matured Sales Conversions</small>
            </div>
        </div>
        <div class="card-body p-4">
            <div id="efficiency-trend-chart" style="height: 300px;"></div>
        </div>
    </div>

    {{-- Main Performance Matrix Table --}}
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                    <i class="mdi mdi-table-account text-primary mr-1"></i> Employee Performance Matrix
                </h5>
                <small class="text-muted">Detailed productivity metrics, work hours, and deliverable outputs per team member</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="modern-employees-table" class="table table-modern table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px; width: 28%;">Employee</th>
                            <th style="width: 18%;">Department & Role</th>
                            <th style="width: 16%;">Logged Work Time</th>
                            <th style="width: 20%;">Key Deliverables</th>
                            <th style="width: 10%; text-align: center;">Productivity</th>
                            <th style="padding-right: 24px; width: 8%; text-align: right;">Action</th>
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
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 dropdowns
        if ($('.select2').length) {
            $('.select2').select2({
                width: '100%'
            });
        }

        var startDate = "{{ $startDateStr }}";
        var endDate = "{{ $endDateStr }}";

        // Date Range Picker Initialization
        $('#report_date_range').daterangepicker({
            startDate: moment(startDate),
            endDate: moment(endDate),
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
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
            $('#filter_start_date').val(start.format('YYYY-MM-DD'));
            $('#filter_end_date').val(end.format('YYYY-MM-DD'));
            $('#report_date_range').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        });

        // Set initial display value
        $('#report_date_range').val(moment(startDate).format('YYYY-MM-DD') + ' - ' + moment(endDate).format('YYYY-MM-DD'));

        // Initialize DataTable
        var table = $('#modern-employees-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [25, 50, 100, 200],
            ajax: {
                url: "{{ route('reports.employees.data') }}",
                data: function(d) {
                    d.start_date = $('#filter_start_date').val();
                    d.end_date = $('#filter_end_date').val();
                    d.dept_id = $('#filter_dept_id').val();
                }
            },
            columns: [
                // 1. Employee Identity
                {
                    data: 'name',
                    name: 'name',
                    render: function(data, type, row) {
                        var initial = data ? data.charAt(0).toUpperCase() : '?';
                        return `<div class="d-flex align-items-center" style="padding-left: 8px;">
                                    <div class="avatar-xs mr-3" style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                                        ${initial}
                                    </div>
                                    <div>
                                        <a href="${row.action_link}" class="font-weight-bold text-dark d-block font-size-13 text-truncate" style="max-width: 220px;">${data}</a>
                                        <small class="text-muted font-size-11">${row.email || 'UID: #EMP-' + (row.id + 1000)}</small>
                                    </div>
                                </div>`;
                    }
                },
                // 2. Department & Role
                {
                    data: 'dept_type',
                    name: 'dept_type',
                    render: function(data, type, row) {
                        var deptUpper = (data || 'OD').toUpperCase();
                        var badgeClass = 'badge-dept-od';
                        if (deptUpper === 'NSD' || deptUpper === 'SALES') {
                            badgeClass = 'badge-dept-nsd';
                        } else if (deptUpper === 'CSD') {
                            badgeClass = 'badge-dept-csd';
                        }
                        var role = (row.roles && row.roles[0]) ? row.roles[0].name : 'Executive';
                        return `<div>
                                    <span class="badge badge-dept ${badgeClass} mb-1">${deptUpper}</span>
                                    <small class="text-muted d-block font-size-11 font-weight-medium">${role}</small>
                                </div>`;
                    }
                },
                // 3. Logged Work Time
                {
                    data: 'total_hours',
                    name: 'total_hours',
                    render: function(data, type, row) {
                        var val = parseFloat(data || 0);
                        var hrs = Math.floor(val);
                        var mins = Math.round((val - hrs) * 60);
                        if (mins >= 60) { hrs += 1; mins = 0; }
                        var hours = hrs + '.' + (mins < 10 ? '0' + mins : mins);
                        var days = row.days_worked || 0;
                        return `<div>
                                    <span class="font-weight-bold text-dark font-size-13 d-block"><i class="mdi mdi-clock-outline mr-1 text-primary"></i>${hours} Hrs</span>
                                    <small class="text-muted font-size-11">${days} Active Days Logged</small>
                                </div>`;
                    }
                },
                // 4. Key Output / Deliverables
                {
                    data: 'completed_tasks',
                    name: 'completed_tasks',
                    render: function(data, type, row) {
                        var dept = (row.dept_type || '').toLowerCase();
                        if (dept === 'nsd') {
                            var matured = row.matured_count || row.matured_clients || 0;
                            var followups = row.followups_count || row.active_followups || 0;
                            return `<div>
                                        <span class="font-weight-bold text-success d-block font-size-12"><i class="mdi mdi-trophy mr-1"></i>${matured} Matured Clients</span>
                                        <small class="text-muted font-size-11">${followups} Active Followups</small>
                                    </div>`;
                        } else if (dept === 'csd') {
                            var resolved = row.tickets_resolved || 0;
                            var comms = row.comms_count || 0;
                            return `<div>
                                        <span class="font-weight-bold text-teal d-block font-size-12" style="color: #059669;"><i class="mdi mdi-check-circle mr-1"></i>${resolved} Resolved Tickets</span>
                                        <small class="text-muted font-size-11">${comms} Communications</small>
                                    </div>`;
                        } else {
                            var tasksDone = data || 0;
                            var activeTasks = row.active_tasks || 0;
                            return `<div>
                                        <span class="font-weight-bold text-primary d-block font-size-12"><i class="mdi mdi-checkbox-marked-circle mr-1"></i>${tasksDone} Tasks Delivered</span>
                                        <small class="text-muted font-size-11">${activeTasks} In Progress</small>
                                    </div>`;
                        }
                    }
                },
                // 5. Productivity Index
                {
                    data: 'productivity',
                    name: 'productivity',
                    className: 'text-center',
                    render: function(data) {
                        var score = parseInt(data || 0);
                        if (score > 100) score = 100;
                        var barColor = '#10b981'; // green
                        var badgeClass = 'badge-soft-success';
                        if (score < 50) {
                            barColor = '#ef4444'; // red
                            badgeClass = 'badge-soft-danger';
                        } else if (score < 80) {
                            barColor = '#3b82f6'; // blue
                            badgeClass = 'badge-soft-primary';
                        }
                        return `<div style="max-width: 100px; margin: 0 auto;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge ${badgeClass} font-size-11 font-weight-bold px-2 py-0.5">${score}%</span>
                                    </div>
                                    <div class="progress-modern">
                                        <div class="progress-bar" style="width: ${score}%; background-color: ${barColor};"></div>
                                    </div>
                                </div>`;
                    }
                },
                // 6. Action Link
                {
                    data: 'action_link',
                    name: 'action_link',
                    orderable: false,
                    searchable: false,
                    className: 'text-right',
                    render: function(data) {
                        return `<a href="${data}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 font-weight-semibold shadow-sm" style="font-size: 11.5px;">
                                    Report <i class="mdi mdi-arrow-right ml-0.5"></i>
                                </a>`;
                    }
                }
            ],
            language: {
                processing: '<div class="text-center py-4"><i class="bx bx-loader-circle bx-spin font-size-24 text-primary"></i> <span class="d-block mt-2 text-muted">Loading Performance Matrix...</span></div>'
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        // Apply Filter Button Click
        $('#btnApplyFilter').on('click', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        // Prevent default form submission
        $('#employeeReportFilterForm').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        // Reset Filter Button Click
        $('#btnResetFilter').on('click', function(e) {
            e.preventDefault();
            var defaultStart = moment().startOf('month').format('YYYY-MM-DD');
            var defaultEnd = moment().format('YYYY-MM-DD');

            $('#filter_start_date').val(defaultStart);
            $('#filter_end_date').val(defaultEnd);
            $('#report_date_range').val(defaultStart + ' - ' + defaultEnd);
            $('#filter_dept_id').val('').trigger('change');

            table.ajax.reload();
        });

        // Auto-reload on department change
        $('#filter_dept_id').on('change', function() {
            table.ajax.reload();
        });

        // Render Trend Chart
        var performanceTrend = @json($performanceTrend);
        var efficiencyOptions = {
            series: [
                {
                    name: 'Completed Tasks (Ops)',
                    data: performanceTrend.map(function(item) { return item.ops; })
                },
                @if($showSales)
                {
                    name: 'Matured Clients (Sales)',
                    data: performanceTrend.map(function(item) { return item.sales; })
                }
                @endif
            ],
            chart: {
                height: 280,
                type: 'area',
                toolbar: { show: false }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#2563eb', '#10b981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.02,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: performanceTrend.map(function(item) { return item.month; }),
                axisBorder: { show: false },
                labels: { style: { colors: '#64748b', fontWeight: 600 } }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontWeight: 600 } }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        new ApexCharts(document.querySelector("#efficiency-trend-chart"), efficiencyOptions).render();
    });
</script>
@endsection
