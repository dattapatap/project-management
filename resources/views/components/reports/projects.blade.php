@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background: #f1f5f9; min-height: 100vh;">
    <!-- 🚀 Stellar Header -->
    <div class="row mb-5 align-items-center">
        <div class="col-lg-6">
            <h1 class="header-glow mb-2">Project Reports</h1>
            <p class="text-muted font-size-15 font-weight-medium">Strategic intelligence dashboard for system-wide project lifecycles.</p>
        </div>
        <div class="col-lg-6">
            <div class="d-flex align-items-center justify-content-lg-end">
                <div class="mr-3">
                    <form action="{{ route('reports.projects') }}" method="GET" id="yearForm">
                        <select name="year" class="year-select" onchange="document.getElementById('yearForm').submit()">
                            @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                <option value="{{ $y }}" {{ $metrics['selected_year'] == $y ? 'selected' : '' }}>Fiscal Year {{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 💎 High-Impact KPI Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="modern-card p-4 h-100 mesh-gradient-primary">
                <div class="d-flex align-items-center mb-4">
                    <div class="kpi-icon-box bg-primary text-white mr-3">
                        <i class="mdi mdi-rocket-launch"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">Projects</h6>
                        <h2 class="font-weight-bold mb-0">{{ $metrics['total'] }}</h2>
                    </div>
                </div>
                <div class="progress-modern">
                    <div class="progress-bar bg-primary" style="width: 70%"></div>
                </div>
                <div class="mt-2 text-right">
                    <span class="text-primary font-weight-bold small">+12.5% Growth</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="modern-card p-4 h-100">
                <div class="d-flex align-items-center mb-4">
                    <div class="kpi-icon-box bg-warning text-white mr-3">
                        <i class="mdi mdi-clock-check"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">In Operations</h6>
                        <h2 class="font-weight-bold mb-0">{{ $metrics['in_progress'] }}</h2>
                    </div>
                </div>
                <div class="progress-modern">
                    <div class="progress-bar bg-warning" style="width: 45%"></div>
                </div>
                <div class="mt-2 text-right text-warning small font-weight-bold">Live Execution</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="modern-card p-4 h-100 mesh-gradient-success">
                <div class="d-flex align-items-center mb-4">
                    <div class="kpi-icon-box bg-success text-white mr-3">
                        <i class="mdi mdi-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">Avg Cycle Time</h6>
                        <h2 class="font-weight-bold mb-0">{{ $metrics['avg_days'] }} <small class="text-muted font-size-14 font-weight-normal">Days</small></h2>
                    </div>
                </div>
                <div class="progress-modern">
                    <div class="progress-bar bg-success" style="width: 85%"></div>
                </div>
                <div class="mt-2 text-right text-success small font-weight-bold">Velocity: Optimal</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="modern-card p-4 h-100">
                <div class="d-flex align-items-center mb-4">
                    <div class="kpi-icon-box bg-info text-white mr-3">
                        <i class="mdi mdi-target-variant"></i>
                    </div>
                    <div>
                        <h6 class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 10px; letter-spacing: 1.5px;">Success Rate</h6>
                        <h2 class="font-weight-bold mb-0">{{ $metrics['total'] > 0 ? round(($metrics['completed'] / $metrics['total']) * 100) : 0 }}%</h2>
                    </div>
                </div>
                <div class="progress-modern">
                    <div class="progress-bar bg-info" style="width: {{ $metrics['total'] > 0 ? round(($metrics['completed'] / $metrics['total']) * 100) : 0 }}%"></div>
                </div>
                <div class="mt-2 text-right text-info small font-weight-bold">Benchmark: 80%</div>
            </div>
        </div>
    </div>

    <!-- 📈 Deep Analytics Layer -->
    <div class="row">
        <!-- Delivery Velocity Area Chart -->
        <div class="col-xl-8 mb-4">
            <div class="modern-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-1">Delivery Velocity</h4>
                        <p class="text-muted small mb-0">Monthly completion trend for FY {{ $metrics['selected_year'] }}</p>
                    </div>
                </div>
                <div id="modern-growth-chart" style="min-height: 250px;"></div>
            </div>
        </div>

        <!-- Completed by Sub-Category -->
        <div class="col-xl-4 mb-4">
            <div class="modern-card p-4 h-100">
                <h4 class="font-weight-bold text-dark mb-4">Completed Projects</h4>
                <div id="modern-completed-categories-chart" style="min-height: 250px;"></div>
                <div class="mt-4">
                    <h6 class="text-muted font-weight-bold text-uppercase mb-3" style="font-size: 10px; letter-spacing: 1px;">Completed Deliverables</h6>
                    @foreach($completedCategories->take(4) as $ccat)
                    <div class="d-flex align-items-center mb-3">
                        <span class="flex-grow-1 text-dark font-weight-medium">{{ $ccat->name }}</span>
                        <span class="badge badge-soft-success px-3 rounded-pill">{{ $ccat->total }} Completed</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 Performance Matrix (Table) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="modern-card p-5">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <h3 class="font-weight-bold text-dark mb-0">System Performance Matrix</h3>
                    <div class="neon-toggle">
                        <button class="btn btn-sm active">Global View</button>
                        <button class="btn btn-sm">Critical Priority</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="modern-projects-table" class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Project Identity</th>
                                <th>Project Team</th>
                                <th>Task Yield</th>
                                <th>Execution Timeframe</th>
                                <th>Operational Status</th>
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
        // ✨ Modern DataTable Initialization
        let currentView = 'global';
        const table = $('#modern-projects-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.projects.data') }}",
                data: function(d) {
                    d.year = "{{ $metrics['selected_year'] }}";
                    d.view = currentView;
                }
            },
            columns: [
                { 
                    data: 'project_name', 
                    name: 'project_name',
                    render: function(data) {
                        return `<div>
                                    <span class="font-weight-bold text-dark d-block font-size-15">${data}</span>
                                    <small class="text-muted font-weight-medium">UID: #PRJ-${Math.floor(Math.random() * 10000)}</small>
                                </div>`;
                    }
                },
                { 
                    data: 'team_count', 
                    name: 'team_count'
                },
                { 
                    data: 'task_yield', 
                    name: 'task_yield'
                },
                { 
                    data: 'duration', 
                    name: 'duration',
                    className: 'font-weight-bold text-dark'
                },
                { data: 'status', name: 'status' }
            ],
            dom: 'rt<"d-flex justify-content-between align-items-center mt-5"ip>',
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            }
        });

        $('.neon-toggle .btn').on('click', function() {
            $('.neon-toggle .btn').removeClass('active');
            $(this).addClass('active');
            currentView = $(this).text().toLowerCase().includes('critical') ? 'critical' : 'global';
            table.ajax.reload();
        });

        // 📈 Lush Delivery Velocity Area Chart
        var growthOptions = {
            series: [{
                name: 'Completed Projects',
                data: [ @foreach($growthData as $growth) {{ $growth->count }}, @endforeach ]
            }],
            chart: { 
                height: 250, 
                type: 'area', 
                toolbar: { show: false },
                dropShadow: {
                    enabled: true,
                    top: 10,
                    left: 0,
                    blur: 15,
                    opacity: 0.1
                }
            },
            stroke: { curve: 'smooth', width: 4 },
            fill: { 
                type: 'gradient', 
                gradient: { 
                    shadeIntensity: 1, 
                    opacityFrom: 0.5, 
                    opacityTo: 0.0, 
                    stops: [0, 90, 100],
                    colorStops: [
                        { offset: 0, color: "#6366f1", opacity: 0.5 },
                        { offset: 100, color: "#6366f1", opacity: 0 }
                    ]
                } 
            },
            colors: ['#6366f1'],
            xaxis: {
                categories: [ @foreach($growthData as $growth) "{{ $growth->month }}", @endforeach ],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 5,
                xaxis: { lines: { show: true } }
            },
            markers: { 
                size: 6, 
                colors: ['#6366f1'], 
                strokeColors: '#fff', 
                strokeWidth: 3,
                hover: { size: 9 }
            },
            tooltip: {
                theme: 'dark',
                x: { show: false },
                style: { fontSize: '13px', fontFamily: 'Inter' }
            }
        };
        new ApexCharts(document.querySelector("#modern-growth-chart"), growthOptions).render();

        // 📊 Completed by Sub-Category Bar Chart
        var completedCatOptions = {
            series: [{
                name: 'Completed',
                data: [ @foreach($completedCategories as $ccat) {{ $ccat->total }}, @endforeach ]
            }],
            chart: { height: 250, type: 'bar', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, horizontal: true, distributed: true } },
            colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: [ @foreach($completedCategories as $ccat) "{{ $ccat->name }}", @endforeach ],
            },
            legend: { show: false }
        };
        new ApexCharts(document.querySelector("#modern-completed-categories-chart"), completedCatOptions).render();
    });
</script>
@endsection
