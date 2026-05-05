@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background: #f1f5f9; min-height: 100vh;">
    <!-- 🚀 Premium Employee Dossier Header -->
    <div class="row mb-5 align-items-center">
        <div class="col-lg-4">
            <h1 class="header-glow mb-2">{{ Auth::id() == $employee->id ? 'My Insights' : 'Employee Report' }}</h1>
            <p class="text-muted font-size-15 font-weight-medium">{{ Auth::id() == $employee->id ? 'Track your professional growth and performance metrics.' : 'Deep-dive intelligence for ' . $employee->name . '.' }}</p>
        </div>
        <div class="col-lg-5 text-center">
            <form action="{{ url()->current() }}" method="GET" id="filterForm" class="d-flex justify-content-center">
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
            </form>
        </div>
        <div class="col-lg-3 text-right">
            @if(Auth::id() != $employee->id)
            <a href="{{ route('reports.employees') }}" class="btn btn-outline-primary btn-rounded px-4 font-weight-bold">
                <i class="mdi mdi-arrow-left mr-1"></i> Back
            </a>
            @else
            <div class="d-flex justify-content-end align-items-center">
                <div class="px-3 py-2 bg-white rounded shadow-sm border border-light text-center mr-3">
                    <p class="text-muted font-weight-bold mb-0 text-uppercase font-size-10">Last Task Log</p>
                    <span class="font-weight-bold text-primary">{{ $logs->first() ? $logs->first()->created_at->diffForHumans() : 'No Logs' }}</span>
                </div>
                <div class="px-3 py-2 bg-white rounded shadow-sm border border-light text-center">
                    <p class="text-muted font-weight-bold mb-0 text-uppercase font-size-10">System Access</p>
                    <span class="font-weight-bold text-success">{{ $employee->last_login_at ? $employee->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- 🎭 Professional Identity Card -->
        <div class="col-xl-4">
            <div class="modern-card p-0 mb-4 h-100 shadow-lg">
                <div class="mesh-gradient-primary p-5 text-center" style="border-radius: 24px 24px 0 0;">
                    <img src="{{ Avatar::create($employee->name)->toBase64() }}" class="rounded-circle avatar-lg border border-white border-4 shadow-lg mb-3" style="width: 100px; height: 100px;">
                    <h3 class="font-weight-bold text-dark mb-1">{{ $employee->name }}</h3>
                    <div class="d-flex justify-content-center mt-2">
                        @foreach($employee->roles as $role)
                        <span class="badge badge-soft-primary px-3 py-1 rounded-pill mx-1" style="font-weight: 700;">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="p-4 bg-light rounded-xl mb-4 border border-light">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-primary text-primary mr-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="mdi mdi-briefcase-variant-outline"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px; letter-spacing: 1px;">Designation & Dept</small>
                                <span class="font-weight-bold text-dark">{{ $employee->emp->designation ?? 'Team Member' }}</span>
                                <small class="text-muted d-block">{{ $employee->departments->dept->name ?? 'OD Department' }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-info text-info mr-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="mdi mdi-calendar-check"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px; letter-spacing: 1px;">Joining & DOB</small>
                                <span class="font-weight-bold text-dark">{{ $employee->emp->joining_dt ? Carbon\Carbon::parse($employee->emp->joining_dt)->format('d M, Y') : 'N/A' }}</span>
                                <small class="text-muted d-block">Born: {{ $employee->emp->dob ? Carbon\Carbon::parse($employee->emp->dob)->format('d M, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-success text-success mr-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="mdi mdi-email-outline"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px; letter-spacing: 1px;">Contact Details</small>
                                <span class="font-weight-bold text-dark">{{ $employee->email }}</span>
                                <small class="text-muted d-block">{{ $employee->emp->alt_number ?? 'No Contact' }}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-warning text-warning mr-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="mdi mdi-identifier"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px; letter-spacing: 1px;">Employee Code</small>
                                <span class="font-weight-bold text-dark">#{{ $employee->emp->mem_code ?? 'EMP-'.$employee->id }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-0 border-top pt-3">
                            <div class="kpi-icon-box bg-soft-secondary text-secondary mr-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="mdi mdi-clock-check-outline"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px; letter-spacing: 1px;">System Access</small>
                                <span class="font-weight-bold text-dark">Logged in {{ $employee->last_login_at ? $employee->last_login_at->diffForHumans() : 'Never' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ⚡ Comprehensive Performance Metrics -->
        <div class="col-xl-8">
            <!-- Summary Row 1 -->
            <div class="row">
                @if($isSales)
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 mesh-gradient-success">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Matured Clients</h6>
                        <h2 class="font-weight-bold text-success mb-0">{{ $stats['matured'] }}</h2>
                        <div class="mt-2 small font-weight-bold text-success-50"><i class="mdi mdi-shield-check"></i> High Conversion</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Sales Volume</h6>
                        <h2 class="font-weight-bold text-primary mb-0">₹{{ number_format($stats['total_sales'], 2) }}</h2>
                        <div class="mt-2 small text-muted"><i class="mdi mdi-cash mr-1"></i> Revenue Impact</div>
                    </div>
                </div>
                @else
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 mesh-gradient-primary">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Resolved Tasks</h6>
                        <h2 class="font-weight-bold text-dark mb-0">{{ $stats['completed_tasks'] }}</h2>
                        <div class="mt-2 small text-muted-50"><i class="mdi mdi-check-all"></i> Total Deliveries</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 bg-white">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Avg Daily Input</h6>
                        <h2 class="font-weight-bold text-primary mb-0">{{ $stats['avg_daily_hours'] }} <small>Hrs</small></h2>
                        <div class="mt-2 small text-muted"><i class="mdi mdi-timer-outline mr-1"></i> Working Rhythm</div>
                    </div>
                </div>
                @endif
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 bg-white shadow-sm">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Productivity Index</h6>
                        <h2 class="font-weight-bold text-dark mb-0">{{ $stats['completed_projects'] }} <small>Projects</small></h2>
                        <div class="mt-2 small text-warning font-weight-bold"><i class="mdi mdi-rocket-launch"></i> Output Velocity</div>
                    </div>
                </div>
            </div>

            <!-- Summary Row 2 -->
            <div class="row">
                <div class="{{ $isSales ? 'col-md-6' : 'col-md-3' }} mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        @if($isSales)
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Followups</h6>
                        <h4 class="font-weight-bold text-info mb-0">{{ $stats['followup'] }}</h4>
                        @else
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Resolved Tasks</h6>
                        <h4 class="font-weight-bold text-primary mb-0">{{ $stats['completed_tasks'] }}</h4>
                        @endif
                    </div>
                </div>
                @if(!$isSales)
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Taken Time</h6>
                        <h4 class="font-weight-bold text-dark mb-0">{{ $stats['total_hours'] }} <small>Hrs</small></h4>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Pending Tasks</h6>
                        <h4 class="font-weight-bold text-danger mb-0">{{ $stats['pending_tasks'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Compl. Projects</h6>
                        <h4 class="font-weight-bold text-success mb-0">{{ $stats['completed_projects'] }}</h4>
                    </div>
                </div>
                @else
                <div class="col-md-6 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Active Lead Engagement</h6>
                        <h4 class="font-weight-bold text-success mb-0">{{ $stats['matured'] + $stats['followup'] }} <small>Interactions</small></h4>
                    </div>
                </div>
                @endif
            </div>

            <!-- 📈 Multi-Track Trend (Tasks vs Clients) -->
            <div class="modern-card p-4 mb-4 border-0">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="font-weight-bold text-dark mb-0">Performance Trend</h5>
                    <div class="d-flex align-items-center">
                        <div class="mr-3 d-flex align-items-center"><span class="badge-dot bg-primary mr-1"></span> <small class="font-weight-bold">Tasks</small></div>
                        @if($isSales)
                        <div class="d-flex align-items-center"><span class="badge-dot bg-success mr-1"></span> <small class="font-weight-bold">New Clients</small></div>
                        @else
                        <div class="d-flex align-items-center"><span class="badge-dot bg-warning mr-1"></span> <small class="font-weight-bold">Input Hours</small></div>
                        @endif
                    </div>
                </div>
                <div id="individual-delivery-trend" style="height: 280px;"></div>
            </div>
        </div>
    </div>

    @if(!$isSales)
    <!-- ☀️ Morning to Evening Activity (Daily Work Rhythm) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="modern-card p-5">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-1">Daily Work Rhythm</h4>
                        <p class="text-muted small mb-0">{{ $selectedMonth == 'All' ? 'Last 30 Days Activity' : 'Activity for ' . $selectedMonth . ' ' . $selectedYear }}</p>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-soft-primary px-3 py-1 rounded-pill">Avg. {{ $stats['avg_task_delivery_time'] }} Hrs / Task</span>
                    </div>
                </div>

                <div class="daily-rhythm-timeline">
                    @foreach($dailyLogs as $date => $dayLogs)
                    <div class="day-group mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <h5 class="font-weight-bold text-primary mb-0 mr-3">{{ $date }}</h5>
                            <hr class="flex-grow-1 border-light">
                            <span class="ml-3 badge badge-soft-secondary">{{ round($dayLogs->sum('time_spend')/60, 1) }} Working Hours</span>
                        </div>

                        <div class="timeline-items ml-4 border-left border-light pl-4">
                            @foreach($dayLogs->sortBy('created_at') as $log)
                            <div class="timeline-item position-relative mb-4">
                                <div class="timeline-dot position-absolute" style="left: -29px; top: 5px; width: 10px; height: 10px; background: #6366f1; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px #e2e8f0;"></div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="font-size-13 text-muted font-weight-bold">{{ Carbon\Carbon::parse($log->created_at)->format('h:i A') }}</span>
                                        <h6 class="font-weight-bold text-dark mt-1 mb-1">{{ $log->task->title ?? 'Untitled Task' }}</h6>
                                        <p class="text-muted small mb-0">{{ $log->log_description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-soft-info rounded-pill px-3">{{ $log->time_spend }}m Logged</span>
                                        <div class="small text-muted mt-1">{{ $log->task->project->clients->name ?? ($log->task->project->project_name ?? 'Internal') }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-4">
        <!-- 📜 Recent Activity Logs -->
        <div class="col-xl-8">
            <div class="modern-card p-5 h-100">
                <h5 class="font-weight-bold text-dark mb-5">Recent Activity Logs</h5>
                <div class="table-responsive">
                    <table class="table modern-table mb-0" style="border: none !important;">
                        <thead>
                            <tr style="background: transparent !important; border: none !important;">
                                <th style="text-align: left;">Timestamp</th>
                                <th style="text-align: left;">Activity</th>
                                <th>Yield</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="text-left"><span class="badge badge-soft-secondary">{{ Carbon\Carbon::parse($log->created_at)->format('d M, H:i') }}</span></td>
                                <td class="text-left">
                                    <span class="font-weight-bold text-dark d-block">{{ $log->task->title ?? 'Activity' }}</span>
                                    <small class="text-muted">{{ Str::limit($log->log_description, 45) }}</small>
                                </td>
                                <td><span class="creator-identity">{{ $log->time_spend }}m</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/gray/fogg-searching.png" style="width: 120px;" class="mb-3 opacity-50">
                                    <p class="text-muted mb-0">No activity logs found for the selected period.</p>
                                    <small class="text-muted-50">Try selecting a different year or month from the filters above.</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 🎯 Role-Specific Recent Achievements/Assignments -->
        <div class="col-xl-4">
            <div class="modern-card p-4 h-100 bg-white shadow-sm border border-light">
                @if($isSales)
                <h6 class="font-weight-bold text-dark mb-4"><i class="mdi mdi-crown text-warning mr-2"></i> Recent Matured Clients</h6>
                <div class="recent-list">
                    @forelse($recentMatured as $client)
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-lg border border-light">
                        <div class="avatar-xs mr-3">
                            <span class="avatar-title rounded-circle bg-soft-success text-success font-weight-bold">{{ substr($client->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <span class="font-weight-bold text-dark d-block" style="font-size: 13px;">{{ $client->name }}</span>
                            <small class="text-muted">{{ Carbon\Carbon::parse($client->created_at)->format('d M, Y') }}</small>
                        </div>
                        <span class="badge badge-success">Matured</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">No recent matured clients found.</p>
                    @endforelse
                </div>
                @else
                <h6 class="font-weight-bold text-dark mb-4"><i class="mdi mdi-clipboard-text-clock text-primary mr-2"></i> Recent Task Assignments</h6>
                <div class="recent-list">
                    @forelse($tasks as $task)
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-lg border border-light transition-hover">
                        <div class="flex-grow-1 mr-2">
                            <span class="font-weight-bold text-dark d-block" style="font-size: 13px;">{{ Str::limit($task->title, 25) }}</span>
                            <small class="text-primary font-weight-bold">{{ $task->project->clients->name ?? ($task->project->project_name ?? 'Internal') }}</small>
                        </div>
                        <span class="badge {{ $task->status == 'Completed' ? 'badge-soft-success' : 'badge-soft-warning' }} px-3 rounded-pill">
                            {{ $task->status }}
                        </span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">No recent tasks assigned.</p>
                    @endforelse
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 🛡️ System Access & Activity Logs -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="modern-card p-5 bg-white shadow-sm border-0">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="font-weight-bold text-dark mb-1">System Activity Log</h5>
                        <p class="text-muted small mb-0">Chronological history of system access and security events.</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-soft-success mr-2"><span class="badge-dot bg-success mr-1"></span> Login</span>
                        <span class="badge badge-soft-danger mr-2"><span class="badge-dot bg-danger mr-1"></span> Logout</span>
                        <span class="badge badge-soft-primary"><span class="badge-dot bg-primary mr-1"></span> Other</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table modern-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Device / Browser</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $act)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="activity-icon mr-3 bg-soft-{{ $act->activity == 'Login' ? 'success' : ($act->activity == 'Logout' ? 'danger' : 'primary') }} text-{{ $act->activity == 'Login' ? 'success' : ($act->activity == 'Logout' ? 'danger' : 'primary') }} rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="mdi {{ $act->activity == 'Login' ? 'mdi-login-variant' : ($act->activity == 'Logout' ? 'mdi-logout-variant' : 'mdi-cog-outline') }}"></i>
                                        </div>
                                        <span class="font-weight-bold text-dark">{{ $act->activity }}</span>
                                    </div>
                                </td>
                                <td><span class="text-muted">{{ $act->details }}</span></td>
                                <td><code class="text-primary">{{ $act->ip_address }}</code></td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $act->user_agent }}">
                                        {{ Str::limit($act->user_agent, 45) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark">{{ $act->created_at->format('d M, Y') }}</span>
                                        <small class="text-muted">{{ $act->created_at->format('h:i A') }} ({{ $act->created_at->diffForHumans() }})</small>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="p-4 bg-light rounded-circle d-inline-block mb-3">
                                        <i class="mdi mdi-history text-muted" style="font-size: 40px;"></i>
                                    </div>
                                    <p class="text-muted">No system activity has been recorded yet.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
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
        // 🔄 Filter Change Handlers
        $('#yearSelect').on('change', function() {
            $('#monthSelect').val('All');
            $('#filterForm').submit();
        });

        $('#monthSelect').on('change', function() {
            $('#filterForm').submit();
        });

        // Dual-Track Trend Chart for Individual
        var trendOptions = {
            series: [{
                    name: 'Tasks',
                    data: [@foreach($monthlyTrend as $m) {
                        {
                            $m - > tasks
                        }
                    }, @endforeach]
                },
                {
                    @if($isSales)
                    name: 'New Clients',
                    data: [@foreach($monthlyTrend as $m) {
                        {
                            $m - > clients
                        }
                    }, @endforeach]
                    @else
                    name: 'Input Hours',
                    data: [@foreach($monthlyTrend as $m) {
                        {
                            $m - > hours
                        }
                    }, @endforeach]
                    @endif
                }
            ],
            chart: {
                height: 280,
                type: 'line',
                toolbar: {
                    show: false
                }
            },
            stroke: {
                curve: 'smooth',
                width: 4
            },
            colors: ['#6366f1', @if($isSales)
                '#34c38f'
                @else '#f1b44c'
                @endif
            ],
            xaxis: {
                categories: [@foreach($monthlyTrend as $m)
                    "{{ $m->month }}", @endforeach
                ],
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontWeight: 600
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontWeight: 600
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 5
            },
            markers: {
                size: 5,
                strokeWidth: 3,
                hover: {
                    size: 8
                }
            }
        };
        new ApexCharts(document.querySelector("#individual-delivery-trend"), trendOptions).render();
    });
</script>

<style>
    .badge-dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endsection
