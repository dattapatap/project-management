@extends('layouts.app')

@php
    $user = Auth::user();
    $isSalesTL = $user->hasRole('Team-Leader') && ($user->departments && $user->departments->department == 1);
@endphp

@section('content')
<div class="container-fluid py-4" style="background: #f1f5f9; min-height: 100vh;">
    <!-- 🚀 Professional Employee Report Header -->
    <div class="row mb-5 align-items-center">
        <div class="col-lg-6">
            <div class="d-flex align-items-center mb-2">
                <a href="{{ url('/') }}" class="btn btn-outline-primary btn-sm mr-3 d-inline-flex align-items-center">
                    <i class="mdi mdi-arrow-left mr-1"></i> Back
                </a>
                <h1 class="header-glow mb-0">{{ $isSalesTL ? 'Team Sales Report' : (Auth::user()->hasRole('Team-Leader') ? 'Team Report' : 'Employee Report') }}</h1>
            </div>
            <p class="text-muted font-size-15 font-weight-medium">{{ $isSalesTL ? 'Strategic team sales conversions and pipeline intelligence.' : (Auth::user()->hasRole('Team-Leader') ? 'Strategic productivity tracking and team member insights.' : 'Strategic productivity tracking and departmental efficiency trends.') }}</p>
        </div>
        <div class="col-lg-6">
            <div class="d-flex align-items-center justify-content-lg-end flex-wrap gap-2">
                <form action="{{ route('reports.employees') }}" method="GET" id="filterForm" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="hidden" name="preset" id="presetInput" value="{{ $range['preset'] ?? 'monthly' }}">
                    <div class="btn-group btn-group-sm mr-2">
                        <button type="button" class="btn btn-outline-primary range-preset {{ ($range['preset'] ?? '') === 'daily' ? 'active' : '' }}" data-preset="daily">Today</button>
                        <button type="button" class="btn btn-outline-primary range-preset {{ ($range['preset'] ?? '') === 'weekly' ? 'active' : '' }}" data-preset="weekly">Week</button>
                        <button type="button" class="btn btn-outline-primary range-preset {{ in_array($range['preset'] ?? 'monthly', ['monthly', 'yearly']) ? 'active' : '' }}" data-preset="monthly">Month</button>
                        <button type="button" class="btn btn-outline-primary range-preset {{ ($range['preset'] ?? '') === 'custom' ? 'active' : '' }}" data-preset="custom">Custom</button>
                    </div>
                    <div id="yearMonthFilters" class="d-flex {{ ($range['preset'] ?? '') === 'custom' ? 'd-none' : '' }}">
                        <select name="year" id="yearSelect" class="year-select mr-2">
                            @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>FY {{ $y }}</option>
                            @endfor
                        </select>
                        <select name="month" id="monthSelect" class="year-select">
                            @foreach($months as $m)
                                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="customRangeFields" class="d-flex align-items-center gap-2 {{ ($range['preset'] ?? '') === 'custom' ? '' : 'd-none' }}">
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', ($range['from'] ?? now())->toDateString()) }}">
                        <span class="text-muted">to</span>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', ($range['to'] ?? now())->toDateString()) }}">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 📊 Integrated Trends & KPI Insight Layer -->
    <div class="row mb-4">
        <!-- Organizational Velocity Trend (50%) -->
        <div class="col-lg-6 mb-4">
            <div class="modern-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-1">{{ $isSalesTL ? 'Sales Pipeline Trend' : ($showSales ? 'Organizational Velocity' : 'Operations Velocity') }}</h4>
                        <p class="text-muted small mb-0">{{ $isSalesTL ? 'Monthly client lead acquisitions and maturity tracking' : ($showSales ? 'Ops Delivery vs Sales Maturity Trend' : 'Monthly Project Delivery Performance') }}</p>
                    </div>
                </div>
                <div id="efficiency-trend-chart" style="height: 350px;"></div>
            </div>
        </div>

        <!-- Strategic Summary Metrics (50%) -->
        <div class="col-lg-6">
            <div class="row h-100">
                <div class="col-12 mb-3">
                    <div class="modern-card p-4 mesh-gradient-primary">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon-box bg-primary text-white mr-3">
                                <i class="mdi mdi-account-group"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">{{ $isSalesTL ? 'Sales Force Size' : 'Workforce Size' }}</h6>
                                <h2 class="font-weight-bold mb-0 text-dark">{{ $employeesCount }} <small class="text-muted" style="font-size: 14px;">{{ $isSalesTL ? 'Specialist(s)' : 'Talents' }}</small></h2>
                            </div>
                        </div>
                    </div>
                </div>
                @if($showSales)
                <div class="col-12 mb-3">
                    <div class="modern-card p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-success text-white mr-3">
                                <i class="mdi mdi-currency-inr"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">Avg Sales Conversion</h6>
                                <h2 class="font-weight-bold mb-0 text-dark">{{ $salesRate }}%</h2>
                            </div>
                        </div>
                        <div class="progress-modern">
                            <div class="progress-bar bg-success" style="width: {{ $salesRate }}%"></div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-12">
                    @if($isSalesTL)
                    <div class="modern-card p-4 h-100 shadow-sm border border-light">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-purple text-white mr-3" style="background-color: #6366f1 !important;">
                                <i class="mdi mdi-shield-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">Team Matured Sales</h6>
                                <h2 class="font-weight-bold mb-0 text-dark">{{ $maturedCount }} <small class="text-muted" style="font-size: 14px;">Matured Clients</small></h2>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted"><i class="mdi mdi-trophy-outline mr-1 text-warning"></i> Converted Leads pipeline</div>
                    </div>
                    @else
                    <div class="modern-card p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-warning text-white mr-3">
                                <i class="mdi mdi-check-all"></i>
                            </div>
                            <div>
                                <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">Ops Completion Rate</h6>
                                <h2 class="font-weight-bold mb-0 text-dark">{{ $opsRate }}%</h2>
                            </div>
                        </div>
                        <div class="progress-modern">
                            <div class="progress-bar bg-warning" style="width: {{ $opsRate }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 performance matrix -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="modern-card p-5">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div>
                        <h3 class="font-weight-bold text-dark mb-1">{{ Auth::user()->hasRole('Team-Leader') ? 'Team Performance Matrix' : 'Performance Matrix' }}</h3>
                        <p class="text-muted small mb-0">{{ $isSalesTL ? 'Team sales conversions and callback tracking metrics' : (Auth::user()->hasRole('Team-Leader') ? 'Team performance and client maturity tracking metrics' : 'Role-aware efficiency tracking and task delivery logs') }}</p>
                    </div>
                    <div class="neon-toggle">
                        <button class="btn btn-sm range-btn" data-range="daily">Today</button>
                        <button class="btn btn-sm range-btn" data-range="weekly">Weekly</button>
                        <button class="btn btn-sm range-btn active" data-range="monthly">Monthly</button>
                        <button class="btn btn-sm range-btn" data-range="yearly">Yearly</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="modern-employees-table" class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Professional Identity</th>
                                <th>Department & Role</th>
                                @if($isSalesTL)
                                <th>Assigned Leads</th>
                                <th>Active Followups</th>
                                <th>Matured Clients</th>
                                @else
                                <th>Load (Tasks/Clients)</th>
                                <th>Logged Time</th>
                                <th>Productivity Index</th>
                                @endif
                                <th>Review</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    $(document).ready(function() {
        let currentRange = $('#presetInput').val() || 'monthly';

        $('.range-preset').on('click', function() {
            $('.range-preset').removeClass('active');
            $(this).addClass('active');
            const preset = $(this).data('preset');
            $('#presetInput').val(preset);
            $('#customRangeFields').toggleClass('d-none', preset !== 'custom');
            $('#yearMonthFilters').toggleClass('d-none', preset === 'custom');
            if (preset !== 'custom') {
                $('#filterForm').submit();
            }
        });

        // 🔄 Filter Change Handlers
        $('#yearSelect').on('change', function() {
            $('#monthSelect').val('All');
            $('#filterForm').submit();
        });

        $('#monthSelect').on('change', function() {
            $('#filterForm').submit();
        });

        const table = $('#modern-employees-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.employees.data') }}",
                data: function(d) {
                    d.preset = $('#presetInput').val() || currentRange;
                    d.range = d.preset;
                    d.year = "{{ $selectedYear }}";
                    d.month = "{{ $selectedMonth }}";
                    d.date_from = $('input[name=date_from]').val();
                    d.date_to = $('input[name=date_to]').val();
                }
            },
            columns: [
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data, type, row) {
                        return `<div class="d-flex align-items-center">
                                    <div class="avatar-xs mr-3">
                                        <div class="kpi-icon-box bg-soft-primary text-primary font-weight-bold" style="width: 40px; height: 40px; border-radius: 12px; font-size: 14px;">${data.charAt(0)}</div>
                                    </div>
                                    <div class="text-left">
                                        <span class="font-weight-bold text-dark d-block font-size-15">${data}</span>
                                        <small class="text-muted">UID: #EMP-${row.id + 1000}</small>
                                    </div>
                                </div>`;
                    }
                },
                { 
                    data: 'departments', 
                    name: 'departments.dept.name',
                    render: function(data, type, row) {
                        const dept = data && data.dept ? data.dept.name : 'Sales';
                        const role = row.roles && row.roles[0] ? row.roles[0].name : 'Specialist';
                        return `<div>
                                    <span class="badge badge-soft-info px-3 rounded-pill d-block mb-1">${dept}</span>
                                    <small class="text-muted font-weight-bold">${role}</small>
                                </div>`;
                    }
                },
                @if($isSalesTL)
                { 
                    data: 'total_leads', 
                    name: 'total_leads',
                    render: function(data) {
                        return `<span class="font-weight-bold text-primary font-size-14"><i class="mdi mdi-account-plus mr-1"></i>${data} Leads</span>`;
                    }
                },
                { 
                    data: 'active_followups', 
                    name: 'active_followups',
                    render: function(data) {
                        return `<span class="font-weight-bold text-warning font-size-14"><i class="mdi mdi-phone-in-talk mr-1"></i>${data} Followups</span>`;
                    }
                },
                { 
                    data: 'matured_clients', 
                    name: 'matured_clients',
                    render: function(data) {
                        return `<span class="badge badge-soft-success px-3 py-1 rounded-pill font-size-13 font-weight-bold">${data} Matured</span>`;
                    }
                },
                @else
                { 
                    data: 'active_tasks', 
                    name: 'active_tasks',
                    render: function(data, type, row) {
                        const isSales = row.roles.some(r => r.name === 'Sales-Executive');
                        if (isSales) {
                            return `<div>
                                        <span class="font-weight-bold text-dark d-block">${row.matured_clients} Matured</span>
                                        <small class="text-muted">Lead Conversion</small>
                                    </div>`;
                        }
                        return `<div>
                                    <span class="font-weight-bold text-dark d-block">${data} Active Tasks</span>
                                    <small class="text-success">${row.completed_tasks} Delivered</small>
                                </div>`;
                    }
                },
                { 
                    data: 'total_hours', 
                    name: 'total_hours',
                    render: function(data) {
                        return `<span class="creator-identity"><i class="mdi mdi-timer-outline mr-2"></i>${data} Hrs</span>`;
                    }
                },
                { 
                    data: 'performance', 
                    name: 'productivity',
                    render: function(data, type, row) {
                        let color = row.productivity > 75 ? 'bg-success' : (row.productivity > 40 ? 'bg-primary' : 'bg-danger');
                        return `<div class="d-flex align-items-center justify-content-center">
                                    <div class="progress-modern flex-grow-1 mr-3" style="width: 80px;">
                                        <div class="progress-bar ${color}" style="width: ${row.productivity}%;"></div>
                                    </div>
                                    <span class="font-weight-bold ${color.replace('bg-', 'text-')}">${row.productivity}%</span>
                                </div>`;
                    }
                },
                @endif
                { 
                    data: 'action', 
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<a href="${row.action_link}" class="btn btn-sm btn-soft-primary px-3 rounded-pill">Report</a>`;
                    }
                }
            ],
            dom: 'rt<"d-flex justify-content-between align-items-center mt-5"ip>',
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            }
        });

        $('.range-btn').on('click', function() {
            $('.range-btn').removeClass('active');
            $(this).addClass('active');
            currentRange = $(this).data('range');
            $('#presetInput').val(currentRange);
            table.ajax.reload();
        });

        // 📈 Dual-Track Efficiency Trend Chart
        var efficiencyOptions = {
            series: [
                @if(!$isSalesTL)
                {
                    name: 'Ops Delivery (Tasks)',
                    data: [ @foreach($performanceTrend as $p) {{ $p->ops }}, @endforeach ]
                }
                @endif
                @if($showSales)
                @if(!$isSalesTL),@endif
                {
                    name: 'Sales Maturity (Clients)',
                    data: [ @foreach($performanceTrend as $p) {{ $p->sales }}, @endforeach ]
                }
                @endif
            ],
            chart: { 
                height: 320, 
                type: 'area', 
                toolbar: { show: false },
                dropShadow: { enabled: true, top: 10, blur: 15, opacity: 0.1 }
            },
            stroke: { curve: 'smooth', width: 4 },
            colors: [@if($isSalesTL) '#34c38f' @else '#6366f1', '#34c38f' @endif],
            fill: { 
                type: 'gradient', 
                gradient: { 
                    shadeIntensity: 1, 
                    opacityFrom: 0.4, 
                    opacityTo: 0.0, 
                    stops: [0, 90, 100]
                } 
            },
            xaxis: {
                categories: [ @foreach($performanceTrend as $p) "{{ $p->month }}", @endforeach ],
                axisBorder: { show: false },
                labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
            },
            yaxis: { labels: { style: { colors: '#94a3b8', fontWeight: 600 } } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 5 },
            markers: { size: 4, strokeWidth: 3, hover: { size: 7 } },
            legend: { show: false }
        };
        new ApexCharts(document.querySelector("#efficiency-trend-chart"), efficiencyOptions).render();
    });
</script>

<style>
.badge-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; }
</style>
@endsection
