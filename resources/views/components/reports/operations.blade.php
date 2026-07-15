@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page erp-page--od">
    <div class="erp-page-header">
        <div class="erp-page-header__main">
            <h1 class="erp-page-title">Department Work Report</h1>
            <p class="erp-page-subtitle text-muted mb-0">
                {{ $branchLabel }} · {{ $range['label'] }} · Track daily tasks, leads, client communications, and hours per employee across departments.
            </p>
        </div>
        <div class="erp-page-header__actions">
            <form action="{{ route('reports.operations') }}" method="GET" id="opsReportFilter" class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center mr-2">
                    <i class="mdi mdi-arrow-left mr-1"></i> Back
                </a>
                @if(Auth::user()->hasBranchWideAccess())
                <select name="department" class="form-control form-control-sm" style="width: 190px;" onchange="this.form.submit()">
                    @foreach($departments as $id => $label)
                    <option value="{{ $id }}" {{ $departmentId == $id ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @endif
                <input type="hidden" name="preset" id="presetInput" value="{{ $range['preset'] }}">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary range-preset {{ $range['preset'] === 'daily' ? 'active' : '' }}" data-preset="daily">Today</button>
                    <button type="button" class="btn btn-outline-primary range-preset {{ $range['preset'] === 'weekly' ? 'active' : '' }}" data-preset="weekly">This Week</button>
                    <button type="button" class="btn btn-outline-primary range-preset {{ in_array($range['preset'], ['monthly', 'yearly']) ? 'active' : '' }}" data-preset="monthly">This Month</button>
                    <button type="button" class="btn btn-outline-primary range-preset {{ $range['preset'] === 'custom' ? 'active' : '' }}" data-preset="custom">Custom</button>
                </div>
                <div id="customRangeFields" class="d-flex align-items-center gap-2 {{ $range['preset'] === 'custom' ? '' : 'd-none' }}">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', $range['from']->toDateString()) }}">
                    <span class="text-muted">to</span>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', $range['to']->toDateString()) }}">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="erp-kpi-card erp-gradient-primary p-4">
                <div class="erp-kpi-card__label">Employees</div>
                <div class="erp-kpi-card__value">{{ $employeeCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="erp-kpi-card erp-gradient-info p-4">
                <div class="erp-kpi-card__label">Period</div>
                <div class="erp-kpi-card__value" style="font-size: 1.15rem;">{{ $range['label'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="erp-kpi-card erp-gradient-nsd p-4">
                <div class="erp-kpi-card__label">Report Focus</div>
                <div class="erp-kpi-card__value" style="font-size: 0.95rem; font-weight: 500; line-height: 1.45;">
                    @if($departmentId == 1)
                    Leads assigned · Active followups · Matured conversions · Callbacks logged
                    @elseif($departmentId == 3)
                    Active clients · Customer communications · Support tickets resolved · Opportunities won
                    @else
                    Completed tasks · Total hours logged · Per-task hours · Daily breakdown
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="erp-table-card">
        <div class="erp-table-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 font-weight-bold">Individual Work Summary</h5>
                <small class="text-muted">Click <strong>Full Report</strong> for daily logs and detailed workflow metrics.</small>
            </div>
        </div>
        <div class="table-responsive p-3">
            <table id="operations-report-table" class="table table-premium table-centered table-striped mb-0">
                <thead class="thead-custom-teal">
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        @if($departmentId == 1)
                        <th>Leads Assigned</th>
                        <th>Active Followups</th>
                        <th>Matured Clients</th>
                        <th>Sales Volume</th>
                        <th>Callbacks Logged</th>
                        @elseif($departmentId == 3)
                        <th>Active Clients</th>
                        <th>Communications</th>
                        <th>Tickets Resolved</th>
                        <th>Opps Won</th>
                        <th>Collections Paid</th>
                        @else
                        <th>Days Worked</th>
                        <th>Tasks Completed</th>
                        <th>Total Hours</th>
                        <th>Avg Hrs/Day</th>
                        <th>Log Entries</th>
                        @endif
                        <th>Productivity Score</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        const presetInput = $('#presetInput');
        const customFields = $('#customRangeFields');

        $('.range-preset').on('click', function() {
            $('.range-preset').removeClass('active');
            $(this).addClass('active');
            const preset = $(this).data('preset');
            presetInput.val(preset);
            customFields.toggleClass('d-none', preset !== 'custom');
            if (preset !== 'custom') {
                $('#opsReportFilter').submit();
            }
        });

        $('#operations-report-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.operations.data') }}",
                data: function(d) {
                    d.preset = presetInput.val();
                    d.department = $('select[name=department]').val();
                    d.date_from = $('input[name=date_from]').val();
                    d.date_to = $('input[name=date_to]').val();
                }
            },
            columns: [{
                    data: 'name',
                    render: function(data, type, row) {
                        return `<div class="font-weight-bold">${data}</div><small class="text-muted">#EMP-${row.id}</small>`;
                    }
                },
                {
                    data: 'departments',
                    render: function(data) {
                        return data && data.dept ? data.dept.name : '—';
                    }
                },
                @if($departmentId == 1) {
                    data: 'leads_count',
                    className: 'text-center'
                },
                {
                    data: 'followups_count',
                    className: 'text-center'
                },
                {
                    data: 'matured_count',
                    className: 'text-center'
                },
                {
                    data: 'sales_amount',
                    render: function(data) {
                        return `<span class="font-weight-bold text-success">₹${parseFloat(data).toLocaleString()}</span>`;
                    }
                },
                {
                    data: 'callback_logs_count',
                    className: 'text-center'
                },
                @elseif($departmentId == 3) {
                    data: 'active_clients',
                    className: 'text-center'
                },
                {
                    data: 'comms_count',
                    className: 'text-center'
                },
                {
                    data: 'tickets_resolved',
                    className: 'text-center'
                },
                {
                    data: 'opportunities_won',
                    className: 'text-center'
                },
                {
                    data: 'collections_paid',
                    className: 'text-center'
                },
                @else {
                    data: 'days_worked',
                    className: 'text-center'
                },
                {
                    data: 'completed_tasks',
                    className: 'text-center'
                },
                {
                    data: 'total_hours',
                    render: function(data) {
                        return `<span class="font-weight-bold text-primary">${data} hrs</span>`;
                    }
                },
                {
                    data: 'avg_hours_per_day',
                    className: 'text-center'
                },
                {
                    data: 'log_entries',
                    className: 'text-center'
                },
                @endif {
                    data: 'productivity',
                    render: function(data) {
                        const color = data > 75 ? 'success' : (data > 40 ? 'primary' : 'danger');
                        return `<span class="badge badge-${color}">${data}%</span>`;
                    }
                },
                {
                    data: 'action_link',
                    orderable: false,
                    searchable: false,
                    render: function(url) {
                        return `<a href="${url}" class="btn btn-sm btn-soft-primary">Full Report</a>`;
                    }
                }
            ],
            order: [
                @if($departmentId == 1)[4, 'desc']
                @elseif($departmentId == 3)[2, 'desc']
                @else[4, 'desc']
                @endif
            ],
            dom: 'frtip'
        });
    });
</script>
@endsection
