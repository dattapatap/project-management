@extends('layouts.app')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .targets-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    .header-card-glass {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 255, 0.95) 100%);
        border-left: 5px solid #00c6ff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .text-premium {
        color: #2b3a4a;
        font-weight: 700;
    }

    .custom-table th {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.8px;
        color: #5c6a7a !important;
        font-weight: 700 !important;
        background-color: #f8fafc;
        border-bottom: 2px solid #edf2f7 !important;
    }

    .custom-table td {
        vertical-align: middle !important;
        color: #2d3748;
        font-size: 13.5px;
    }

    .badge-dept {
        font-size: 11px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 6px;
    }

    .badge-nsd {
        background-color: rgba(246, 173, 85, 0.15);
        color: #dd6b20;
    }

    .badge-csd {
        background-color: rgba(79, 209, 197, 0.15);
        color: #319795;
    }

    .badge-od {
        background-color: rgba(159, 122, 234, 0.15);
        color: #805ad5;
    }

    .badge-soft-success {
        background-color: rgba(52, 195, 143, 0.15);
        color: #34c38f;
    }

    .badge-soft-danger {
        background-color: rgba(244, 106, 106, 0.15);
        color: #f46a6a;
    }

    .badge-soft-warning {
        background-color: rgba(241, 180, 76, 0.15);
        color: #f1b44c;
    }
</style>
@endsection

@section('content')
<div class="container-fluid targets-wrapper pb-5">

    <!-- Header Card -->
    <div class="card mb-4 header-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h3 class="text-premium font-size-18 mb-1">🎯 Daily Targets Tracker</h3>
                <p class="text-muted font-size-12 mb-0">Track and monitor target progress of team members across all departments.</p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-10">
                <a href="{{ route('daily-targets.configure') }}" class="btn btn-primary btn-sm shadow-sm mr-2" style="background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%); border: none;">
                    <i class="mdi mdi-cog mr-1"></i> Set Target
                </a>
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item active text-muted">Daily Targets</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border shadow-sm mb-4">
        <div class="card-body py-3 px-4" style="background: #f8fafc; border-radius: 12px;">
            <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-filter-variant text-primary font-size-24 mr-2"></i>
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark">Filter Records</h6>
                            <small class="text-muted">Narrow down by employee or submission date.</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-row justify-content-md-end">
                        <div class="form-group col-sm-5 mb-0">
                            <select id="filter-employee" class="form-control form-control-sm border select2-filter" style="height: 36px; border-radius: 8px;">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-4 mb-0">
                            <input type="text" id="filter-date-range" class="form-control form-control-sm border" style="height: 36px; border-radius: 8px; cursor: pointer; text-align: center; background: white;" readonly placeholder="Select Date Range">
                            <input type="hidden" id="start-date" value="">
                            <input type="hidden" id="end-date" value="">
                        </div>
                        <div class="form-group col-sm-2 mb-0">
                            <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-sm w-100" style="height: 36px; border-radius: 8px;">
                                <i class="mdi mdi-refresh mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Achievements Table Card -->
    <div class="card border shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="daily-targets-table" class="table table-premium table-centered table-striped mb-0 w-100">
                    <thead class="thead-custom-teal">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Department & Role</th>
                            <th>Target Parameters</th>
                            <th>Target Status</th>
                            <th>Closing Status</th>
                            <th>Executive Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
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
        var table = $('#daily-targets-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [25, 50, 100, 250, 500],
            ajax: {
                url: "{{ route('daily-targets.data') }}",
                data: function(d) {
                    d.employee_id = $('#filter-employee').val();
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                }
            },
            columns: [{
                    data: 'date',
                    name: 'closing_date'
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
                    name: 'target_status'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'remarks',
                    name: 'remarks',
                    orderable: false
                }
            ],
            order: [
                [0, 'desc']
            ],
            language: {
                processing: '<div class="text-center py-4"><i class="bx bx-loader-circle bx-spin font-size-24 text-primary"></i> <span class="d-block mt-2 text-muted">Loading Records...</span></div>'
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        // Date Range Picker Initialization
        $('#filter-date-range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
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
        });

        $('#filter-date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#start-date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end-date').val(picker.endDate.format('YYYY-MM-DD'));
            table.draw();
        });

        $('#filter-date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start-date').val('');
            $('#end-date').val('');
            table.draw();
        });

        // Trigger filter on employee change
        $('#filter-employee').change(function() {
            table.draw();
        });

        // Reset filter
        $('#btn-reset').click(function() {
            $('#filter-employee').val('').trigger('change');
            $('#filter-date-range').val('');
            $('#start-date').val('');
            $('#end-date').val('');
            table.draw();
        });
    });
</script>
@endsection
