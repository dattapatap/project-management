@extends('layouts.app')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .attendance-wrapper {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #1e293b;
    }

    .header-card-glass {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    }

    .kpi-stat-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 14px 18px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
        min-height: 95px;
    }

    .kpi-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
    }

    .kpi-indigo::before {
        background: #4f46e5;
    }

    .kpi-emerald::before {
        background: #10b981;
    }

    .kpi-cyan::before {
        background: #06b6d4;
    }

    .kpi-rose::before {
        background: #ef4444;
    }

    .kpi-amber::before {
        background: #f59e0b;
    }

    .kpi-bubble {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .kpi-indigo .kpi-bubble {
        background: #eef2ff;
        color: #4f46e5;
    }

    .kpi-emerald .kpi-bubble {
        background: #ecfdf5;
        color: #10b981;
    }

    .kpi-cyan .kpi-bubble {
        background: #ecfeff;
        color: #0891b2;
    }

    .kpi-rose .kpi-bubble {
        background: #fef2f2;
        color: #ef4444;
    }

    .kpi-amber .kpi-bubble {
        background: #fffbeb;
        color: #f59e0b;
    }

    /* Clean Full-Screen Table */
    .custom-table {
        width: 100% !important;
        table-layout: fixed;
        margin-bottom: 0 !important;
    }

    .custom-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 12px;
        vertical-align: middle;
    }

    .custom-table tbody td {
        padding: 10px 12px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-size: 12.5px;
    }

    .badge-dept {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        letter-spacing: 0.4px;
        display: inline-block;
    }

    .badge-nsd {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .badge-csd {
        background-color: #ccfbf1;
        color: #0f766e;
        border: 1px solid #99f6e4;
    }

    .badge-od {
        background-color: #ede9fe;
        color: #6d28d9;
        border: 1px solid #ddd6fe;
    }

    .live-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 1.8s infinite;
        vertical-align: middle;
        margin-right: 4px;
    }

    .live-idle-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #f59e0b;
        display: inline-block;
        vertical-align: middle;
        margin-right: 4px;
    }

    .live-completed-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #94a3b8;
        display: inline-block;
        vertical-align: middle;
        margin-right: 4px;
    }

    .live-absent-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #ef4444;
        display: inline-block;
        vertical-align: middle;
        margin-right: 4px;
    }

    @keyframes pulse-green {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .avatar-bubble {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef2ff;
        color: #4f46e5;
        font-weight: 700;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px !important;
        flex-shrink: 0;
    }

    .progress-bar-thin {
        height: 5px;
        border-radius: 3px;
        background-color: #e2e8f0;
        overflow: hidden;
    }

    .pagination-rounded .page-link {
        border-radius: 8px !important;
        margin: 0 3px;
        border: 1px solid #e2e8f0;
    }

    .task-link-hover {
        transition: color 0.15s ease;
    }

    .task-link-hover:hover {
        color: #4f46e5 !important;
        text-decoration: underline !important;
    }

    .badge-link-hover {
        transition: transform 0.15s ease, opacity 0.15s ease;
        text-decoration: none !important;
    }

    .badge-link-hover:hover {
        opacity: 0.85;
        transform: translateY(-1px);
        text-decoration: none !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid attendance-wrapper pb-5">

    <!-- Header Card -->
    <div class="card mb-3 header-card-glass">
        <div class="card-body py-2.5 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <div class="d-flex align-items-center">
                    <a href="{{ url('/') }}" class="btn btn-light border btn-sm px-3 mr-3 shadow-sm font-weight-medium">
                        <i class="mdi mdi-arrow-left mr-1"></i> Dashboard
                    </a>
                    <div>
                        <h4 class="mb-0 text-dark font-weight-bold font-size-17">
                            <i class="mdi mdi-shield-account text-primary mr-1"></i> Workforce Attendances & Live Activity Monitor
                        </h4>
                        <span class="text-muted font-size-12">Shift Timings, Live Project & Task, Break Times, Efficiency Ratio, and Day Closing Audit</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0" style="gap: 8px;">
                <span class="badge badge-soft-success px-2.5 py-1 font-size-11 font-weight-bold" id="auto-refresh-badge" title="Automatically updates every 60 seconds">
                    <span class="live-pulse-dot"></span> Live Auto-Refresh: 60s
                </span>
                <button type="button" id="btn-export-excel" class="btn btn-success btn-sm px-3 shadow-sm font-weight-bold" style="border-radius: 8px;">
                    <i class="mdi mdi-file-excel-outline mr-1"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Summary Stat Cards -->
    <div class="row mb-3">
        {{-- 1. Total Workforce --}}
        <div class="col-xl col-md-4 col-sm-6 mb-2 mb-xl-0">
            <div class="kpi-stat-card kpi-indigo">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Staff Workforce</span>
                        <div class="font-size-20 font-weight-bold text-dark my-0.5" id="kpi-total-workforce">{{ $totalEmployeesCount }}</div>
                        <small class="text-muted font-size-11">Active Employees</small>
                    </div>
                    <div class="kpi-bubble">
                        <i class="mdi mdi-account-group-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Present On Date --}}
        <div class="col-xl col-md-4 col-sm-6 mb-2 mb-xl-0">
            <div class="kpi-stat-card kpi-emerald">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Present</span>
                        <div class="font-size-20 font-weight-bold text-success my-0.5" id="kpi-present-count">{{ $presentCount }}</div>
                        <small class="text-success font-size-11 font-weight-semibold" id="kpi-present-rate">Clocked in</small>
                    </div>
                    <div class="kpi-bubble">
                        <i class="mdi mdi-account-check-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Currently Working (Live Timers) --}}
        <div class="col-xl col-md-4 col-sm-6 mb-2 mb-xl-0">
            <div class="kpi-stat-card kpi-cyan">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Working Now</span>
                        <div class="font-size-20 font-weight-bold text-cyan my-0.5" id="kpi-working-now" style="color: #0891b2;">{{ $runningTimersCount }}</div>
                        <small class="text-muted font-size-11"><span class="live-pulse-dot"></span> Live Timers</small>
                    </div>
                    <div class="kpi-bubble">
                        <i class="mdi mdi-timer-play-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Absent / Not Logged In --}}
        <div class="col-xl col-md-6 col-sm-6 mb-2 mb-xl-0">
            <div class="kpi-stat-card kpi-rose">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Absent</span>
                        <div class="font-size-20 font-weight-bold text-danger my-0.5" id="kpi-absent-count">{{ $absentCount }}</div>
                        <small class="text-muted font-size-11">No clock-in</small>
                    </div>
                    <div class="kpi-bubble">
                        <i class="mdi mdi-account-remove-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Missed Day Closings --}}
        <div class="col-xl col-md-6 col-sm-12">
            <div class="kpi-stat-card kpi-amber">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase font-size-11 font-weight-bold text-danger d-block" style="letter-spacing: 0.5px;">Missed Day Closings</span>
                        <div class="font-size-20 font-weight-bold text-danger my-0.5" id="kpi-missed-closings">{{ $missedClosingsCount }}</div>
                        <small class="text-muted font-size-11">Unsubmitted closings</small>
                    </div>
                    <div class="kpi-bubble">
                        <i class="mdi mdi-alert-octagon-outline"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border shadow-sm mb-3" style="border-radius: 14px; overflow: hidden;">
        <div class="card-body py-2.5 px-4 bg-white">
            <div class="row align-items-center">
                <div class="col-lg-3 mb-2 mb-lg-0">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-filter-variant text-primary font-size-20 mr-2"></i>
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark font-size-13">Filter Workforce</h6>
                            <small class="text-muted font-size-11">Date, Department & Status</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="form-row justify-content-lg-end align-items-center">
                        {{-- Date Range Picker --}}
                        <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-calendar text-primary"></i></span>
                                </div>
                                <input type="text" id="filter-date-range" class="form-control font-weight-medium border-left-0 font-size-12" style="height: 34px; cursor: pointer; background: #ffffff;" readonly value="{{ $selectedDateStr }}">
                            </div>
                        </div>

                        {{-- Department Filter --}}
                        <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                            <select id="filter-dept" class="form-control form-control-sm font-weight-medium font-size-12" style="height: 34px;">
                                @foreach($departments as $dId => $dName)
                                <option value="{{ $dId }}">{{ $dName }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Work Status Filter --}}
                        <div class="form-group col-md-3 col-sm-6 mb-2 mb-md-0">
                            <select id="filter-work-status" class="form-control form-control-sm font-weight-medium font-size-12" style="height: 34px;">
                                <option value="">All Work Statuses</option>
                                <option value="working">🟢 Working Now (Live Timers)</option>
                                <option value="idle">🟡 Shift Active (Idle)</option>
                                <option value="completed">⚪ Shift Completed</option>
                                <option value="present">Present (Any)</option>
                                <option value="absent">🔴 Absent</option>
                                <option value="missed_closing">⚠️ Missed Day Closing</option>
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="form-group col-md-3 col-sm-6 mb-0 d-flex" style="gap: 6px;">
                            <button type="button" id="btn-apply-filter" class="btn btn-primary btn-sm flex-fill font-weight-semibold shadow-sm" style="height: 34px;">
                                <i class="mdi mdi-filter mr-1"></i> Apply
                            </button>
                            <button type="button" id="btn-reset-filter" class="btn btn-light border btn-sm flex-fill font-weight-medium" style="height: 34px;">
                                <i class="mdi mdi-refresh mr-1"></i> Today
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table Card -->
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-0">
            <table id="attendances-table" class="table custom-table table-hover align-middle mb-0 w-100">
                <thead>
                    <tr>
                        <th style="padding-left: 16px; width: 11%;">Date</th>
                        <th style="width: 19%;">Employee & Activity</th>
                        <th style="width: 12%; text-align: center;">Attendance Status</th>
                        <th style="width: 18%;">Active Project & Task</th>
                        <th style="width: 14%;">Shift & Punctuality</th>
                        <th style="width: 12%; text-align: center;">Hours & Break</th>
                        <th style="width: 11%; text-align: center;">Productivity & Closing</th>
                        <th style="padding-right: 16px; width: 6%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated dynamically by DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Inspect Day Tasks for Employee --}}
<div class="modal fade" id="dayTasksModal" tabindex="-1" role="dialog" aria-labelledby="dayTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
            <div class="modal-header bg-light py-2.5 px-4 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="mr-2.5 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 8px; width: 34px; height: 34px; font-size: 18px;">
                        <i class="mdi mdi-calendar-clock"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-size-14 font-weight-bold text-dark mb-0" id="dayTasksModalLabel">
                            Daily Task Breakdown & Time Logs
                        </h5>
                        <small class="text-muted font-size-11" id="modal-emp-info">Employee: —</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                {{-- Day Summary Bar --}}
                <div class="p-2.5 mb-3 bg-light rounded-lg border d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
                    <div>
                        <small class="text-muted font-size-10 text-uppercase font-weight-bold d-block">Shift Duration</small>
                        <span class="badge badge-soft-info px-2 py-0.5 rounded font-weight-bold font-size-11" id="modal-shift-hours">0.00 hrs</span>
                    </div>
                    <div>
                        <small class="text-muted font-size-10 text-uppercase font-weight-bold d-block">Total Task Hours</small>
                        <span class="badge badge-soft-primary px-2 py-0.5 rounded font-weight-bold font-size-11" id="modal-task-hours">0.00 hrs</span>
                    </div>
                    <div>
                        <small class="text-muted font-size-10 text-uppercase font-weight-bold d-block">Closing Status</small>
                        <span id="modal-closing-status" class="badge badge-soft-secondary px-2 py-0.5 rounded font-weight-bold font-size-11">—</span>
                    </div>
                </div>

                {{-- Tasks Header --}}
                <h6 class="font-weight-bold text-dark mb-2 font-size-12">
                    <i class="mdi mdi-format-list-checks text-primary mr-1"></i> Tasks Worked On (<span id="modal-tasks-count">0</span>):
                </h6>

                {{-- Tasks List --}}
                <div id="modal-tasks-container" class="d-flex flex-column" style="gap: 10px;"></div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 border-top">
                <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
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
        var startDateParam = '{{ $startDateStr }}';
        var endDateParam = '{{ $endDateStr }}';
        var start = moment(startDateParam);
        var end = moment(endDateParam);
        var employeeDataset = {};

        function updateDateInput(start, end) {
            if (start.format('YYYY-MM-DD') === end.format('YYYY-MM-DD')) {
                $('#filter-date-range').val(start.format('DD-MM-YYYY'));
            } else {
                $('#filter-date-range').val(start.format('DD-MM-YYYY') + ' to ' + end.format('DD-MM-YYYY'));
            }
        }

        $('#filter-date-range').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: 'DD-MM-YYYY'
            },
            opens: 'left',
            autoUpdateInput: false
        }, function(chosenStart, chosenEnd) {
            start = chosenStart;
            end = chosenEnd;
            updateDateInput(start, end);
            table.ajax.reload();
        });

        updateDateInput(start, end);

        var table = $('#attendances-table').DataTable({
            processing: true,
            serverSide: false, // Client-side search & pagination over dataset
            autoWidth: false,
            responsive: false,
            pageLength: 25,
            lengthMenu: [15, 25, 50, 100],
            ajax: {
                url: "{{ route('admin.attendances.data') }}",
                data: function(d) {
                    d.date = start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');
                    d.department = $('#filter-dept').val();
                    d.work_status = $('#filter-work-status').val();
                },
                dataSrc: function(json) {
                    employeeDataset = {};
                    if (json.data) {
                        json.data.forEach(function(row) {
                            employeeDataset[row.row_key] = row;
                        });
                    }

                    // Update Top KPI Cards
                    if (json.summary) {
                        $('#kpi-total-workforce').text(json.summary.total_employees);
                        $('#kpi-present-count').text(json.summary.present_count);
                        var totalEntries = json.summary.present_count + json.summary.absent_count;
                        var rate = totalEntries > 0 ? Math.round((json.summary.present_count / totalEntries) * 100) : (json.summary.total_employees > 0 ? Math.round((json.summary.present_count / json.summary.total_employees) * 100) : 0);
                        $('#kpi-present-rate').text(rate + '% Attendance Rate');
                        $('#kpi-working-now').text(json.summary.working_now_count);
                        $('#kpi-absent-count').text(json.summary.absent_count);
                        $('#kpi-missed-closings').text(json.summary.missed_closings_count);
                    }

                    return json.data;
                }
            },
            columns: [{
                    data: 'date',
                    className: 'pl-3',
                    render: function(data, type, row) {
                        var todayBadge = row.is_today ? `<span class="badge badge-soft-success font-size-10 px-1 py-0 rounded ml-1 font-weight-bold">Today</span>` : '';
                        var dayClass = row.is_sunday ? 'text-danger font-weight-bold' : 'text-muted';
                        return `
                            <div>
                                <div class="d-flex align-items-center">
                                    <span class="font-weight-bold text-dark font-size-12">${row.date_formatted}</span>
                                    ${todayBadge}
                                </div>
                                <small class="${dayClass} font-size-10 font-weight-medium">${row.day_name}</small>
                            </div>
                        `;
                    }
                },

                {
                    data: 'name',
                    render: function(data, type, row) {
                        var statusBadge = '';
                        if (row.live_status === 'working') {
                            statusBadge = `<span class="badge badge-soft-success px-1.5 py-0.5 rounded font-size-10 font-weight-bold" title="Task timer is actively recording right now"><span class="live-pulse-dot"></span> Working</span>`;
                        } else if (row.live_status === 'idle') {
                            statusBadge = `<span class="badge badge-soft-warning px-1.5 py-0.5 rounded font-size-10 font-weight-bold" title="Shift is open/active, but no task timer is running"><span class="live-idle-dot"></span> Idle (On Shift)</span>`;
                        } else if (row.live_status === 'completed') {
                            statusBadge = `<span class="badge badge-soft-secondary px-1.5 py-0.5 rounded font-size-10"><span class="live-completed-dot"></span> Ended</span>`;
                        } else {
                            statusBadge = `<span class="badge badge-soft-danger px-1.5 py-0.5 rounded font-size-10 font-weight-bold"><span class="live-absent-dot"></span> Absent</span>`;
                        }

                        return `
                            <div class="d-flex align-items-center">
                                <div class="avatar-bubble">${row.avatar}</div>
                                <div style="min-width: 0;">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="font-weight-bold text-dark font-size-13 text-truncate mr-1">${row.name}</span>
                                        <span class="badge badge-dept ${row.dept_class}">${row.department}</span>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap mt-0.5" style="gap: 4px;">
                                        ${statusBadge}
                                        <small class="text-muted font-size-11">${row.emp_id}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                },

                {
                    data: 'attendance_status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        var badgeClass = row.attendance_status_class || 'badge-soft-secondary';
                        var icon = 'mdi-check-circle-outline';

                        if (row.attendance_status.indexOf('Not Clocked In') !== -1) {
                            return `<span class="badge badge-soft-danger px-2 py-1 rounded font-size-11 font-weight-bold" style="white-space: nowrap; border: 1px solid rgba(239, 68, 68, 0.3); background-color: #fee2e2; color: #b91c1c;"><i class="mdi mdi-clock-alert-outline mr-0.5"></i> Not Clocked In</span>`;
                        } else if (row.attendance_status.indexOf('Absent') !== -1) {
                            icon = 'mdi-close-circle-outline';
                        } else if (row.attendance_status.indexOf('Half Day') !== -1) {
                            icon = 'mdi-clock-alert-outline';
                        } else if (row.attendance_status.indexOf('Leave') !== -1) {
                            icon = 'mdi-calendar-account-outline';
                        } else if (row.attendance_status.indexOf('Holiday') !== -1) {
                            icon = 'mdi-calendar-star';
                        } else if (row.attendance_status.indexOf('Sunday') !== -1 || row.attendance_status.indexOf('Weekend') !== -1) {
                            icon = 'mdi-calendar-weekend';
                        }

                        return `<span class="badge ${badgeClass} px-2 py-1 rounded font-size-11 font-weight-bold" style="white-space: nowrap;"><i class="mdi ${icon} mr-0.5"></i>${row.attendance_status}</span>`;
                    }
                },

                {
                    data: 'active_project_name',
                    render: function(data, type, row) {
                        if (row.active_project_name === '—' || !row.active_project_name) {
                            return `<span class="text-muted font-size-11">—</span>`;
                        }
                        var clientBadge = row.active_client_name && row.active_client_name !== '—' ?
                            `<span class="badge badge-soft-primary px-1.5 py-0.2 rounded font-size-10 mr-1 text-truncate" style="max-width: 110px;" title="${row.active_client_name}"><i class="mdi mdi-domain mr-0.5"></i>${row.active_client_name}</span>` :
                            '';

                        var projectDisplay = '';
                        var taskDisplay = '';

                        if (row.taskboard_url) {
                            projectDisplay = `
                                <a href="${row.taskboard_url}" target="_blank" class="badge badge-soft-info badge-link-hover px-1.5 py-0.2 rounded font-size-10 font-weight-semibold mr-1 text-truncate" style="max-width: 130px; display: inline-flex; align-items: center;" title="Open ${row.active_project_name} Taskboard">
                                    <i class="mdi mdi-folder-outline mr-0.5"></i>${row.active_project_name} <i class="mdi mdi-open-in-new font-size-9 ml-0.5 opacity-75"></i>
                                </a>
                            `;
                            taskDisplay = `
                                <a href="${row.taskboard_url}" target="_blank" class="font-weight-medium text-dark font-size-11 d-block text-truncate task-link-hover" style="max-width: 220px;" title="Open in Taskboard: ${row.active_task_title}">
                                    <i class="mdi mdi-rocket mr-0.5 text-primary"></i>${row.active_task_title} <i class="mdi mdi-arrow-top-right text-muted font-size-10 ml-0.5"></i>
                                </a>
                            `;
                        } else {
                            projectDisplay = `
                                <span class="badge badge-soft-info px-1.5 py-0.2 rounded font-size-10 font-weight-semibold mr-1 text-truncate" style="max-width: 120px;" title="${row.active_project_name}">
                                    <i class="mdi mdi-folder-outline mr-0.5"></i>${row.active_project_name}
                                </span>
                            `;
                            taskDisplay = `
                                <span class="font-weight-medium text-dark font-size-11 d-block text-truncate" style="max-width: 220px;" title="${row.active_task_title}">
                                    ${row.active_task_title}
                                </span>
                            `;
                        }

                        return `
                            <div>
                                <div class="d-flex align-items-center flex-wrap mb-0.5">
                                    ${projectDisplay}
                                    ${clientBadge}
                                </div>
                                ${taskDisplay}
                            </div>
                        `;
                    }
                },

                {
                    data: 'shift_start',
                    render: function(data, type, row) {
                        if (row.shift_start === '—' && row.shift_end === '—') {
                            return `<span class="text-muted font-size-11">—</span>`;
                        }
                        var punctualityBadge = '';
                        if (row.punctuality_key === 'on_time') {
                            punctualityBadge = `<span class="badge badge-soft-success px-1.5 py-0.2 rounded font-size-10 font-weight-bold">${row.punctuality_label}</span>`;
                        } else if (row.punctuality_key === 'late' || row.punctuality_key === 'late_early') {
                            punctualityBadge = `<span class="badge badge-soft-warning px-1.5 py-0.2 rounded font-size-10 font-weight-bold text-danger">${row.punctuality_label}</span>`;
                        } else if (row.punctuality_key === 'early_out') {
                            punctualityBadge = `<span class="badge badge-soft-secondary px-1.5 py-0.2 rounded font-size-10 font-weight-bold">${row.punctuality_label}</span>`;
                        } else {
                            punctualityBadge = `<span class="badge badge-soft-info px-1.5 py-0.2 rounded font-size-10 font-weight-semibold">${row.punctuality_label}</span>`;
                        }

                        return `
                            <div class="font-size-11">
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="text-dark font-weight-semibold">${row.shift_start}</span>
                                    <span class="text-muted font-size-10 mx-1">&rarr;</span>
                                    <span class="${row.shift_end === 'In Progress' ? 'text-primary font-weight-bold' : 'text-dark font-weight-semibold'}">${row.shift_end}</span>
                                </div>
                                <div class="mt-0.5">${punctualityBadge}</div>
                            </div>
                        `;
                    }
                },

                {
                    data: 'shift_hours_formatted',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (!row.is_present) {
                            return `<span class="text-muted font-size-11">—</span>`;
                        }
                        var shiftDisplay = row.is_missed_closing ?
                            `<span class="text-danger font-size-11" title="Shift hours excluded because Day Closing was missed"><del>${row.shift_hours_formatted}</del></span>` :
                            `<span class="text-dark font-weight-semibold font-size-11">${row.shift_hours_formatted}</span>`;

                        var taskDisplay = `<span class="text-primary font-weight-bold font-size-11">${row.task_hours_formatted}</span>`;

                        var breakDisplay = '';
                        if (row.break_hours_formatted && row.break_hours_formatted !== '0.00 hrs' && row.break_hours_formatted !== '0.0 hrs') {
                            if (row.is_over_break) {
                                breakDisplay = `<div class="mt-0.5"><span class="badge badge-soft-danger px-1.5 py-0.2 rounded font-size-10 font-weight-bold"><i class="mdi mdi-coffee mr-0.5"></i>Break: ${row.break_hours_formatted}</span></div>`;
                            } else {
                                breakDisplay = `<div class="mt-0.5 text-muted font-size-10"><i class="mdi mdi-coffee-outline mr-0.5"></i>Break: ${row.break_hours_formatted}</div>`;
                            }
                        }

                        return `
                            <div>
                                <div class="font-size-11">
                                    <small class="text-muted">S:</small> ${shiftDisplay}
                                    <span class="text-muted mx-1">|</span>
                                    <small class="text-muted">T:</small> ${taskDisplay}
                                </div>
                                ${breakDisplay}
                            </div>
                        `;
                    }
                },

                {
                    data: 'productivity_ratio',
                    className: 'text-center',
                    render: function(data, type, row) {
                        var closingBadge = '';
                        if (row.is_missed_closing || row.closing_status === 'Missed Day Closing') {
                            closingBadge = `<span class="badge badge-soft-danger px-1.5 py-0.5 rounded font-size-10 font-weight-bold" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #f87171;">Missed Closing</span>`;
                        } else if (row.closing_status === 'Approved') {
                            var approverInfo = row.approver_name ? `<small class="text-muted d-block font-size-10 mt-0.5"><i class="mdi mdi-account-check text-success mr-0.5"></i>by ${row.approver_name}</small>` : '';
                            closingBadge = `<span class="badge badge-soft-success px-1.5 py-0.5 rounded font-size-10 font-weight-bold"><i class="mdi mdi-check-all mr-0.5"></i>Approved</span>${approverInfo}`;
                        } else if (row.closing_status === 'Submitted' || row.closing_status === 'Pending') {
                            closingBadge = `<span class="badge badge-soft-warning px-1.5 py-0.5 rounded font-size-10 font-weight-bold"><i class="mdi mdi-clock-outline mr-0.5"></i>Submitted</span>`;
                        } else {
                            closingBadge = `<span class="badge badge-soft-light text-muted px-1.5 py-0.5 rounded font-size-10">${row.closing_status}</span>`;
                        }

                        if (!row.is_present || row.shift_hours_formatted === '0.00 hrs') {
                            return `<div>${closingBadge}</div>`;
                        }

                        var pClass = row.productivity_class || 'success';
                        var badgeClass = 'badge-soft-' + pClass;
                        if (pClass === 'danger') badgeClass = 'badge-soft-danger text-danger';

                        return `
                            <div style="max-width: 130px; margin: 0 auto;">
                                <div class="d-flex align-items-center justify-content-between mb-0.5">
                                    <small class="text-muted font-size-10 font-weight-bold">Ratio</small>
                                    <span class="badge ${badgeClass} font-size-10 font-weight-bold px-1.5 py-0.2 rounded">${row.productivity_ratio}%</span>
                                </div>
                                <div class="progress progress-bar-thin mb-1">
                                    <div class="progress-bar bg-${pClass}" role="progressbar" style="width: ${row.productivity_ratio}%" aria-valuenow="${row.productivity_ratio}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div>${closingBadge}</div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'row_key',
                    className: 'text-right pr-3',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <button type="button" class="btn btn-outline-primary btn-sm px-2 py-0.5 font-size-11 font-weight-semibold shadow-sm btn-inspect-tasks" data-row-key="${row.row_key}" style="border-radius: 6px; white-space: nowrap;">
                                <i class="mdi mdi-clipboard-text-clock mr-0.5"></i> Tasks (${row.tasks_count})
                            </button>
                        `;
                    }
                }
            ],
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            dom: '<"d-flex justify-content-between align-items-center p-3 flex-wrap"<"font-size-12 mb-2 mb-sm-0"l><"font-size-12"f>>rt<"d-flex justify-content-between align-items-center p-3 flex-wrap"<"font-size-12 mb-2 mb-sm-0"i><"font-size-12"p>>',
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        // Live Auto-Refresh every 60 seconds (Silent background refresh without page flickers)
        setInterval(function() {
            if (!document.hidden) {
                table.ajax.reload(null, false); // false preserves user pagination/scroll
            }
        }, 60000);

        // Filter Actions
        $('#btn-apply-filter').on('click', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('#filter-dept, #filter-work-status').on('change', function() {
            table.ajax.reload();
        });

        $('#btn-reset-filter').on('click', function(e) {
            e.preventDefault();
            start = moment();
            end = moment();
            $('#filter-date-range').data('daterangepicker').setStartDate(start);
            $('#filter-date-range').data('daterangepicker').setEndDate(end);
            updateDateInput(start, end);
            $('#filter-dept').val('');
            $('#filter-work-status').val('');
            table.ajax.reload();
        });

        // Export to Excel Button Action
        $('#btn-export-excel').on('click', function(e) {
            e.preventDefault();
            var dateVal = start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD');
            var dept = $('#filter-dept').val() || '';
            var status = $('#filter-work-status').val() || '';
            var exportUrl = "{{ route('admin.attendances.export') }}?date=" + encodeURIComponent(dateVal) + "&department=" + encodeURIComponent(dept) + "&work_status=" + encodeURIComponent(status);
            window.location.href = exportUrl;
        });

        // Inspect Tasks Modal
        $(document).on('click', '.btn-inspect-tasks', function(e) {
            e.preventDefault();
            var rowKey = $(this).data('row-key');
            var row = employeeDataset[rowKey];
            if (!row) return;

            $('#modal-emp-info').text(row.name + ' (' + row.department + ' — ' + row.emp_id + ') on ' + row.date_formatted + ' (' + row.day_name + ')');
            $('#modal-shift-hours').text(row.shift_hours_formatted);
            $('#modal-task-hours').text(row.task_hours_formatted);
            $('#modal-closing-status').text(row.closing_status);
            $('#modal-tasks-count').text(row.tasks ? row.tasks.length : 0);

            var tasks = row.tasks || [];
            if (tasks.length === 0) {
                $('#modal-tasks-container').html(`
                    <div class="p-3 text-center text-muted bg-light rounded border">
                        <i class="mdi mdi-information-outline font-size-22 text-muted d-block mb-1"></i>
                        No specific task time logs were recorded for this employee on this date.
                    </div>
                `);
            } else {
                var html = '';
                tasks.forEach(function(t, idx) {
                    var timeWindow = (t.starttime && t.endtime) ? `<span class="badge badge-light border px-2 py-0.5 font-size-10 mr-1.5"><i class="mdi mdi-clock-outline mr-0.5"></i> ${t.starttime} - ${t.endtime}</span>` : '';
                    var clientBadge = `<span class="badge badge-soft-primary px-2 py-1 rounded font-size-11 font-weight-bold mr-1.5"><i class="mdi mdi-domain mr-1"></i> Client: ${t.client_name}</span>`;
                    
                    var projectBadge = t.taskboard_url ?
                        `<a href="${t.taskboard_url}" target="_blank" class="badge badge-soft-info badge-link-hover px-2 py-1 rounded font-size-11 font-weight-bold mr-1.5" title="Open ${t.project_name} Taskboard"><i class="mdi mdi-folder-outline mr-1"></i> Project: ${t.project_name} <i class="mdi mdi-open-in-new font-size-9 ml-0.5 opacity-75"></i></a>` :
                        `<span class="badge badge-soft-info px-2 py-1 rounded font-size-11 font-weight-bold mr-1.5"><i class="mdi mdi-folder-outline mr-1"></i> Project: ${t.project_name}</span>`;

                    var taskTitleDisplay = t.taskboard_url ?
                        `<a href="${t.taskboard_url}" target="_blank" class="font-weight-bold text-dark font-size-13 task-link-hover" title="Open Taskboard">${t.task_title} <i class="mdi mdi-open-in-new text-primary font-size-11 ml-0.5"></i></a>` :
                        `<span class="font-weight-bold text-dark font-size-13">${t.task_title}</span>`;

                    var taskboardBtn = t.taskboard_url ?
                        `<a href="${t.taskboard_url}" target="_blank" class="btn btn-sm btn-outline-primary px-2.5 py-0.5 font-size-11 font-weight-semibold shadow-none ml-2" style="border-radius: 6px;"><i class="mdi mdi-view-week-outline mr-1"></i> Open Taskboard &rarr;</a>` :
                        '';

                    html += `
                        <div class="p-2.5 bg-white rounded-lg border shadow-sm" style="border-left: 4px solid #4f46e5 !important;">
                            <div class="d-flex align-items-start justify-content-between flex-wrap mb-1.5">
                                <div class="mr-2">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge badge-secondary px-1.5 py-0.2 font-size-10 mr-1.5">Task #${t.task_id || (idx + 1)}</span>
                                        ${taskTitleDisplay}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-1 mt-sm-0">
                                    <span class="badge badge-soft-success px-2 py-1 rounded-pill font-weight-bold font-size-11">
                                        <i class="mdi mdi-timer-outline mr-0.5"></i> ${t.hours_formatted} spent
                                    </span>
                                    ${taskboardBtn}
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-wrap my-1.5">
                                ${projectBadge}
                                ${clientBadge}
                                ${timeWindow}
                            </div>

                            <div class="p-2 bg-light rounded text-dark font-size-11 mt-1.5" style="border-left: 3px solid #cbd5e1; white-space: pre-wrap; line-height: 1.4;">
                                <span class="text-muted font-weight-bold font-size-10 d-block mb-0.5"><i class="mdi mdi-note-text-outline mr-0.5"></i> Work Log Description:</span>
                                ${t.description || 'No detailed note provided.'}
                            </div>
                        </div>
                    `;
                });
                $('#modal-tasks-container').html(html);
            }

            $('#dayTasksModal').modal('show');
        });

        // Close modal handlers
        $(document).on('click', '#dayTasksModal [data-dismiss="modal"], #dayTasksModal [data-bs-dismiss="modal"], #dayTasksModal .close', function(e) {
            e.preventDefault();
            $('#dayTasksModal').modal('hide');
        });
    });
</script>
@endsection