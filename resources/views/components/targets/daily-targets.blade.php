@extends('layouts.app')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .targets-wrapper {
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
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
        min-height: 100px;
    }

    .kpi-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
    }

    .kpi-indigo::before { background: #4f46e5; }
    .kpi-emerald::before { background: #10b981; }
    .kpi-rose::before { background: #ef4444; }
    .kpi-amber::before { background: #f59e0b; }

    .kpi-bubble {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .kpi-indigo .kpi-bubble { background: #eef2ff; color: #4f46e5; }
    .kpi-emerald .kpi-bubble { background: #ecfdf5; color: #10b981; }
    .kpi-rose .kpi-bubble { background: #fef2f2; color: #ef4444; }
    .kpi-amber .kpi-bubble { background: #fffbeb; color: #f59e0b; }

    .custom-table thead th {
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

    .custom-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .badge-dept {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }

    .badge-nsd { background-color: #fef3c7; color: #b45309; }
    .badge-csd { background-color: #ccfbf1; color: #0f766e; }
    .badge-od  { background-color: #ede9fe; color: #6d28d9; }

    .badge-soft-success { background-color: #d1fae5; color: #065f46; }
    .badge-soft-danger  { background-color: #fee2e2; color: #991b1b; }
    .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
    .badge-soft-info    { background-color: #e0f2fe; color: #0369a1; }
    .badge-soft-secondary { background-color: #f1f5f9; color: #475569; }

    .col-remarks {
        max-width: 260px;
        white-space: normal !important;
        word-break: break-word;
        font-size: 12px;
        color: #64748b;
    }

    .pagination-rounded .page-link {
        border-radius: 8px !important;
        margin: 0 3px;
        border: 1px solid #e2e8f0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid targets-wrapper pb-5">

    <!-- Header Card -->
    <div class="card mb-4 header-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <div class="d-flex align-items-center">
                    <a href="{{ url('/') }}" class="btn btn-light border btn-sm px-3 mr-3 shadow-sm font-weight-medium">
                        <i class="mdi mdi-arrow-left mr-1"></i> Dashboard
                    </a>
                    <div>
                        <h4 class="mb-1 text-dark font-weight-bold">🎯 Daily Targets & Closing Audit</h4>
                        <span class="text-muted font-size-13">Track daily parameter achievements, shift submissions, and missed days compliance</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap mt-3 mt-md-0" style="gap: 8px;">
                <a href="{{ route('daily-targets.configure') }}" class="btn btn-primary btn-sm shadow-sm font-weight-semibold px-3">
                    <i class="mdi mdi-cog mr-1"></i> Set Target Benchmark
                </a>
                <button type="button" id="btn-export" class="btn btn-success btn-sm shadow-sm font-weight-semibold px-3">
                    <i class="mdi mdi-file-excel mr-1"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border shadow-sm mb-4" style="border-radius: 14px; overflow: hidden;">
        <div class="card-body py-3 px-4 bg-white">
            <div class="row align-items-center">
                <div class="col-lg-3 mb-2 mb-lg-0">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-filter-variant text-primary font-size-22 mr-2"></i>
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark font-size-14">Filter & Audit Records</h6>
                            <small class="text-muted font-size-12">Filter by employee or date range</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="form-row justify-content-lg-end align-items-center">
                        {{-- Employee Select --}}
                        <div class="form-group col-md-4 col-sm-6 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-account-search text-primary"></i></span>
                                </div>
                                <select id="filter-employee" class="form-control font-weight-medium border-left-0 font-size-12" style="height: 36px;">
                                    <option value="">All Employees (Team Overview)</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} (#EMP-{{ $emp->id + 1000 }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Date Range Picker --}}
                        <div class="form-group col-md-4 col-sm-6 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-calendar text-primary"></i></span>
                                </div>
                                <input type="text" id="filter-date-range" class="form-control font-weight-medium border-left-0 font-size-12" style="height: 36px; cursor: pointer; background: white;" readonly placeholder="Select Date Range">
                                <input type="hidden" id="start-date" value="">
                                <input type="hidden" id="end-date" value="">
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="form-group col-md-4 col-sm-12 mb-0 d-flex" style="gap: 6px;">
                            <button type="button" id="btn-apply" class="btn btn-primary btn-sm flex-fill font-weight-semibold shadow-sm" style="height: 36px;">
                                <i class="mdi mdi-filter mr-1"></i> Apply Filter
                            </button>
                            <button type="button" id="btn-reset" class="btn btn-light border btn-sm flex-fill font-weight-medium" style="height: 36px;" title="Reset filters">
                                <i class="mdi mdi-refresh mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Specific Employee Summary & Missed Submissions Alert Banner (Dynamic) --}}
    <div id="employee-summary-section" style="display: none;">
        <div class="row mb-4">
            {{-- Card 1: Working Days in Filter Range --}}
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="kpi-stat-card kpi-indigo">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pl-1">
                            <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Working Days in Range</span>
                            <div class="font-size-22 font-weight-bold text-dark my-1" id="stat-total-days">0</div>
                            <small class="text-muted font-size-11" id="stat-emp-identity">Sundays excluded</small>
                        </div>
                        <div class="kpi-bubble">
                            <i class="mdi mdi-calendar-check-outline"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Submitted Day Closings --}}
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="kpi-stat-card kpi-emerald">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pl-1">
                            <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Submitted Day Closings</span>
                            <div class="font-size-22 font-weight-bold text-dark my-1" id="stat-submitted-days">0</div>
                            <small class="text-success font-size-11 font-weight-semibold" id="stat-submission-rate"><i class="mdi mdi-check-circle-outline mr-0.5"></i> 0% Submission Rate</small>
                        </div>
                        <div class="kpi-bubble">
                            <i class="mdi mdi-file-check-outline"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Missed Submissions Alert --}}
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="kpi-stat-card kpi-rose" id="kpi-missed-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pl-1">
                            <span class="text-uppercase font-size-11 font-weight-bold text-danger d-block" style="letter-spacing: 0.5px;">Submission Missed Days</span>
                            <div class="font-size-22 font-weight-bold text-danger my-1" id="stat-missed-days">0 Days</div>
                            <small class="text-muted font-size-11" id="stat-missed-subtext">Past unsubmitted working days</small>
                        </div>
                        <div class="kpi-bubble">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Target Benchmarks Met --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-stat-card kpi-amber">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="pl-1">
                            <span class="text-uppercase font-size-11 font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Target Goals Met</span>
                            <div class="font-size-22 font-weight-bold text-dark my-1" id="stat-met-targets">0</div>
                            <small class="text-muted font-size-11">Days meeting daily targets</small>
                        </div>
                        <div class="kpi-bubble">
                            <i class="mdi mdi-trophy-outline"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Missed Submission Dates Highlight Banner --}}
        <div id="missed-dates-banner" class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px; display: none; background: #fee2e2;">
            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert-octagon-outline text-danger font-size-20 mr-2"></i>
                    <strong class="text-danger font-size-13">
                        ⚠️ Day Closing Submissions Missed for <span id="missed-banner-emp-name">Employee</span> (<span id="missed-banner-total-count">0</span> Days Missed):
                    </strong>
                </div>
                <button type="button" id="btn-toggle-all-missed" class="btn btn-outline-danger btn-xs py-1 px-2.5 font-size-11 font-weight-bold shadow-sm" style="display: none; border-radius: 6px; background: white;">
                    <span id="toggle-missed-text">+0 More</span>
                </button>
            </div>
            <div id="missed-dates-pills-visible" class="d-flex flex-wrap align-items-center" style="gap: 6px;"></div>
            <div id="missed-dates-pills-hidden" class="flex-wrap align-items-center mt-2" style="gap: 6px; display: none;"></div>
        </div>
    </div>

    <!-- Achievements Table Card -->
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                    <i class="mdi mdi-format-list-checks text-primary mr-1"></i> Daily Performance & Target Audit Logs
                </h5>
                <small class="text-muted">Chronological day-by-day logs, achieved parameters, and closing status (Sundays excluded)</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="daily-targets-table" class="table custom-table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="padding-left: 20px; width: 14%;">Date & Day</th>
                            <th style="width: 18%;">Employee</th>
                            <th style="width: 14%;">Department</th>
                            <th style="width: 22%;">Target Parameters</th>
                            <th style="width: 10%; text-align: center;">Target Status</th>
                            <th style="width: 12%; text-align: center;">Closing Status</th>
                            <th style="padding-right: 20px; width: 10%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated dynamically by DataTables -->
                    </tbody>
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
        var startDefault = moment().subtract(6, 'days').format('YYYY-MM-DD');
        var endDefault = moment().format('YYYY-MM-DD');

        $('#filter-date-range').daterangepicker({
            startDate: moment(startDefault),
            endDate: moment(endDefault),
            locale: {
                format: 'YYYY-MM-DD'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            $('#start-date').val(start.format('YYYY-MM-DD'));
            $('#end-date').val(end.format('YYYY-MM-DD'));
            $('#filter-date-range').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        });

        $('#start-date').val(startDefault);
        $('#end-date').val(endDefault);
        $('#filter-date-range').val(startDefault + ' - ' + endDefault);

        var table = $('#daily-targets-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [15, 25, 50, 100],
            ajax: {
                url: "{{ route('daily-targets.data') }}",
                data: function(d) {
                    d.employee_id = $('#filter-employee').val();
                    d.start_date = $('#start-date').val() || startDefault;
                    d.end_date = $('#end-date').val() || endDefault;
                },
                dataSrc: function(json) {
                    if (json.summary && json.summary.has_employee) {
                        $('#employee-summary-section').fadeIn(200);
                        $('#stat-total-days').text(json.summary.total_working_days + ' Days');
                        $('#stat-emp-identity').text(json.summary.employee_name + ' (' + json.summary.department + ')');
                        $('#stat-submitted-days').text(json.summary.submitted_days_count + ' Days');
                        $('#stat-submission-rate').html('<i class="mdi mdi-check-circle mr-0.5"></i> ' + json.summary.submission_rate + '% Submission Rate');
                        $('#stat-missed-days').text(json.summary.missed_days_count + ' Days');
                        $('#stat-met-targets').text(json.summary.met_targets_count + ' Days');

                        if (json.summary.missed_days_count > 0 && json.summary.missed_dates && json.summary.missed_dates.length > 0) {
                            $('#missed-dates-banner').show();
                            $('#missed-banner-emp-name').text(json.summary.employee_name);
                            $('#missed-banner-total-count').text(json.summary.missed_days_count);

                            var totalMissed = json.summary.missed_dates.length;
                            var visibleLimit = 10;
                            var visibleHtml = '';
                            var hiddenHtml = '';

                            json.summary.missed_dates.forEach(function(item, index) {
                                var pill = '<span class="badge badge-soft-danger px-2.5 py-1 font-size-11 font-weight-bold" style="background-color: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;"><i class="mdi mdi-calendar-remove mr-1"></i>' + item.formatted + '</span>';
                                if (index < visibleLimit) {
                                    visibleHtml += pill;
                                } else {
                                    hiddenHtml += pill;
                                }
                            });

                            $('#missed-dates-pills-visible').html(visibleHtml);
                            $('#missed-dates-pills-hidden').html(hiddenHtml).hide();

                            if (totalMissed > visibleLimit) {
                                var extraCount = totalMissed - visibleLimit;
                                $('#btn-toggle-all-missed').show();
                                $('#toggle-missed-text').text('+' + extraCount + ' More Missed Days');
                            } else {
                                $('#btn-toggle-all-missed').hide();
                            }
                        } else {
                            $('#missed-dates-banner').hide();
                        }
                    } else {
                        $('#employee-summary-section').hide();
                        $('#missed-dates-banner').hide();
                    }
                    return json.data;
                }
            },
            columns: [
                {
                    data: 'date',
                    name: 'closing_date',
                    className: 'pl-4'
                },
                {
                    data: 'employee',
                    name: 'user_id',
                    orderable: false
                },
                {
                    data: 'department',
                    name: 'department',
                    orderable: false
                },
                {
                    data: 'parameters',
                    name: 'parameters',
                    orderable: false
                },
                {
                    data: 'target_status',
                    name: 'target_status',
                    className: 'text-center'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },
                {
                    data: 'remarks',
                    name: 'remarks',
                    orderable: false,
                    className: 'col-remarks pr-4'
                }
            ],
            order: [
                [0, 'desc']
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
        $('#btn-apply').on('click', function(e) {
            e.preventDefault();
            table.draw();
        });

        // Employee dropdown change
        $('#filter-employee').on('change', function() {
            table.draw();
        });

        // Filter Reset Click
        $('#btn-reset').on('click', function(e) {
            e.preventDefault();
            $('#filter-employee').val('');
            $('#start-date').val(startDefault);
            $('#end-date').val(endDefault);
            $('#filter-date-range').val(startDefault + ' - ' + endDefault);
            table.draw();
        });

        // Toggle Missed Dates Visibility
        $(document).on('click', '#btn-toggle-all-missed', function(e) {
            e.preventDefault();
            var hiddenDiv = $('#missed-dates-pills-hidden');
            if (hiddenDiv.is(':visible')) {
                hiddenDiv.slideUp(150);
                var extra = $('#missed-dates-pills-hidden .badge').length;
                $('#toggle-missed-text').text('+' + extra + ' More Missed Days');
            } else {
                hiddenDiv.slideDown(150).css('display', 'flex');
                $('#toggle-missed-text').text('Show Less');
            }
        });

        // Export Excel Click
        $('#btn-export').on('click', function() {
            var employeeId = $('#filter-employee').val() || '';
            var startDate = $('#start-date').val() || startDefault;
            var endDate = $('#end-date').val() || endDefault;
            var url = "{{ route('daily-targets.export') }}?employee_id=" + employeeId + "&start_date=" + startDate + "&end_date=" + endDate;
            window.location.href = url;
        });
    });
</script>
@endsection
