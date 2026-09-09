@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    <!-- Header Card -->
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px;">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-success" style="background: #ecfdf5; border-radius: 12px; width: 44px; height: 44px; font-size: 22px;">
                    <i class="mdi mdi-file-excel-box"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-17">Date-Range Attendance & Payroll Export</h4>
                    <span class="text-muted font-size-12">Export comprehensive attendance audit, shift timings, verified task hours, and day closing status to Excel</span>
                </div>
            </div>
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-primary btn-sm px-3 font-weight-semibold shadow-sm" style="height: 36px; border-radius: 8px;">
                <i class="mdi mdi-eye mr-1"></i> Live Attendance Monitor
            </a>
        </div>
    </div>

    <!-- Export Configuration Card -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="card-title text-dark mb-0 font-size-15 font-weight-bold">
                        <i class="mdi mdi-tune-vertical text-primary mr-1"></i> Export Parameters
                    </h5>
                    <small class="text-muted font-size-12">Select your desired date interval and filters for the Excel (.xlsx) file</small>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.attendance-export.download') }}" method="GET">
                        {{-- Quick Range Presets --}}
                        <div class="mb-4">
                            <label class="font-size-12 font-weight-bold text-dark mb-2 d-block">Quick Presets:</label>
                            <div class="btn-group btn-group-sm w-100 flex-wrap" role="group" style="gap: 4px;">
                                <button type="button" class="btn btn-light border font-weight-medium btn-preset" data-preset="this_month" style="border-radius: 6px;">This Month</button>
                                <button type="button" class="btn btn-light border font-weight-medium btn-preset" data-preset="last_month" style="border-radius: 6px;">Last Month</button>
                                <button type="button" class="btn btn-light border font-weight-medium btn-preset" data-preset="last_30" style="border-radius: 6px;">Last 30 Days</button>
                                <button type="button" class="btn btn-light border font-weight-medium btn-preset" data-preset="last_7" style="border-radius: 6px;">Last 7 Days</button>
                            </div>
                        </div>

                        {{-- Date Range Inputs --}}
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6 mb-3 mb-md-0">
                                <label class="font-size-12 font-weight-bold text-dark">Start Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="mdi mdi-calendar text-primary"></i></span>
                                    </div>
                                    <input type="date" name="start_date" id="export-start-date" class="form-control font-size-13 font-weight-medium" value="{{ $startDate }}" required style="border-radius: 0 8px 8px 0;">
                                </div>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <label class="font-size-12 font-weight-bold text-dark">End Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="mdi mdi-calendar text-primary"></i></span>
                                    </div>
                                    <input type="date" name="end_date" id="export-end-date" class="form-control font-size-13 font-weight-medium" value="{{ $endDate }}" required style="border-radius: 0 8px 8px 0;">
                                </div>
                            </div>
                        </div>

                        {{-- Department Filter --}}
                        <div class="form-group mb-3">
                            <label class="font-size-12 font-weight-bold text-dark">Filter by Department</label>
                            <select name="department" class="form-control font-size-13" style="border-radius: 8px;">
                                @foreach($departments as $dId => $dName)
                                    <option value="{{ $dId }}">{{ $dName }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Employee Filter --}}
                        <div class="form-group mb-4">
                            <label class="font-size-12 font-weight-bold text-dark">Filter by Specific Employee (Optional)</label>
                            <select name="user_id" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="">All Staff Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} (#EMP-{{ $emp->id + 1000 }}) - {{ $emp->departments->dept->name ?? 'General' }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Summary Info Box --}}
                        <div class="p-3 mb-4 bg-light rounded-lg border">
                            <h6 class="font-size-12 font-weight-bold text-dark mb-1"><i class="mdi mdi-information-outline text-primary mr-1"></i> Excel (.xlsx) Report Includes:</h6>
                            <ul class="mb-0 pl-3 font-size-11 text-muted" style="line-height: 1.6;">
                                <li>Employee Code, Full Name, Department & Role</li>
                                <li>Daily Shift Start Time & End Time, Shift Hours Logged, Verified Task Hours Logged</li>
                                <li>Total Break Duration, Efficiency Productivity Ratio (%)</li>
                                <li>Statuses: <strong>Present</strong>, <strong>Absent</strong>, <strong>Missed Day Closing</strong>, <strong>Half Day</strong>, <strong>Approved Leave</strong>, <strong>Company Holiday</strong>, <strong>Sunday Weekend</strong></li>
                                <li>Projects & Task details worked on each date</li>
                            </ul>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-success btn-lg px-4 shadow-sm font-weight-bold" style="border-radius: 10px;">
                                <i class="mdi mdi-download mr-1"></i> Generate & Download Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.btn-preset').on('click', function(e) {
            e.preventDefault();
            var preset = $(this).data('preset');
            var start = '';
            var end = moment().format('YYYY-MM-DD');

            if (preset === 'this_month') {
                start = moment().startOf('month').format('YYYY-MM-DD');
                end = moment().format('YYYY-MM-DD');
            } else if (preset === 'last_month') {
                start = moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
                end = moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
            } else if (preset === 'last_30') {
                start = moment().subtract(29, 'days').format('YYYY-MM-DD');
                end = moment().format('YYYY-MM-DD');
            } else if (preset === 'last_7') {
                start = moment().subtract(6, 'days').format('YYYY-MM-DD');
                end = moment().format('YYYY-MM-DD');
            }

            $('#export-start-date').val(start);
            $('#export-end-date').val(end);
        });
    });
</script>
@endsection
