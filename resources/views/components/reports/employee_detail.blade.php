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
            <form action="{{ url()->current() }}" method="GET" id="filterForm" class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                <input type="hidden" name="preset" id="presetInput" value="{{ $range['preset'] }}">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary btn-sm range-preset {{ $range['preset'] === 'daily' ? 'active' : '' }}" data-preset="daily">Today</button>
                    <button type="button" class="btn btn-outline-primary btn-sm range-preset {{ $range['preset'] === 'weekly' ? 'active' : '' }}" data-preset="weekly">Week</button>
                    <button type="button" class="btn btn-outline-primary btn-sm range-preset {{ in_array($range['preset'], ['monthly', 'yearly']) ? 'active' : '' }}" data-preset="monthly">Month</button>
                    <button type="button" class="btn btn-outline-primary btn-sm range-preset {{ $range['preset'] === 'custom' ? 'active' : '' }}" data-preset="custom">Custom</button>
                </div>
                <div id="yearMonthFilters" class="d-flex {{ $range['preset'] === 'custom' ? 'd-none' : '' }}">
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
                <div id="customRangeFields" class="d-flex align-items-center {{ $range['preset'] === 'custom' ? '' : 'd-none' }}">
                    <input type="date" name="date_from" id="dateFromInput" class="form-control form-control-sm mr-1" value="{{ request('date_from', $range['from']->toDateString()) }}">
                    <input type="date" name="date_to" id="dateToInput" class="form-control form-control-sm mr-1" value="{{ request('date_to', $range['to']->toDateString()) }}">
                    <button type="submit" class="btn btn-primary btn-sm" id="applyCustomRange">Apply</button>
                </div>
            </form>
            <small class="text-muted d-block mt-2">{{ $range['label'] }}</small>
        </div>
        <div class="col-lg-3 text-right">
            @if(Auth::id() != $employee->id)
            <a href="{{ route('reports.employee.pdf', array_merge(['id' => base64_encode($employee->id)], request()->all())) }}" class="btn btn-primary btn-rounded px-4 mr-2 font-weight-bold">
                <i class="mdi mdi-download mr-1"></i> Download PDF
            </a>
            <a href="{{ route('reports.employees') }}" class="btn btn-outline-primary btn-rounded px-4 font-weight-bold">
                <i class="mdi mdi-arrow-left mr-1"></i> Back
            </a>
            @else
            <div class="d-flex justify-content-end align-items-center">
                <a href="{{ route('reports.employee.pdf', array_merge(['id' => base64_encode($employee->id)], request()->all())) }}" class="btn btn-primary btn-rounded px-4 mr-3 font-weight-bold">
                    <i class="mdi mdi-download mr-1"></i> Download PDF
                </a>
                <a href="{{ url('/') }}" class="btn btn-outline-primary btn-rounded px-4 mr-3 font-weight-bold">
                    <i class="mdi mdi-arrow-left mr-1"></i> Back
                </a>
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
        <div class="col-xl-4">
            <!-- 📂 Current Active Deliverables / Leads -->
            <div class="modern-card p-4 mb-4 bg-white shadow-sm border border-light" style="border-radius: 24px;">
                <h6 class="font-weight-bold text-dark mb-3">
                    <i class="mdi mdi-folder-clock-outline text-primary mr-2"></i> 
                    @if($isSales) Active Working Leads @elseif($isCsd) Active Care Assignments @else Current Active Projects @endif
                </h6>
                <div class="active-deliverables-list" style="max-height: 250px; overflow-y: auto;">
                    @forelse($currentProjects as $p)
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-lg border border-light">
                        <div class="flex-grow-1">
                            <span class="font-weight-bold text-dark d-block" style="font-size: 13px;">{{ $p->name }}</span>
                            <small class="text-muted">{{ $p->status }}</small>
                        </div>
                        <span class="badge badge-soft-info px-2 py-1 rounded-pill" style="font-size: 10px;">Active</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4 small">No active items in progress.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ⚡ Comprehensive Performance Metrics -->
        <!-- ⚡ Comprehensive Performance Metrics -->
        <div class="col-xl-8">
            <!-- Summary Row 1 -->
            <div class="row">
                @if($isSales)
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 mesh-gradient-primary">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Assigned Leads</h6>
                        <h2 class="font-weight-bold text-primary mb-0">{{ $stats['total_leads'] }}</h2>
                        <div class="mt-2 small text-muted"><i class="mdi mdi-account-plus"></i> Lead pipeline size</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 bg-white shadow-sm border border-light">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Active Followups</h6>
                        <h2 class="font-weight-bold text-warning mb-0">{{ $stats['active_followups'] }}</h2>
                        <div class="mt-2 small text-muted"><i class="mdi mdi-phone-in-talk"></i> Active negotiations</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 mesh-gradient-success">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Matured Clients</h6>
                        <h2 class="font-weight-bold text-success mb-0">{{ $stats['matured'] }}</h2>
                        <div class="mt-2 small font-weight-bold text-success-50"><i class="mdi mdi-shield-check"></i> Sales conversions</div>
                    </div>
                </div>
                @elseif($isCsd)
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 mesh-gradient-primary">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Active Clients</h6>
                        <h2 class="font-weight-bold text-primary mb-0">{{ $stats['active_clients'] }}</h2>
                        <div class="mt-2 small text-muted"><i class="mdi mdi-account-heart"></i> Under your care</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 bg-white shadow-sm border border-light">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Communications</h6>
                        <h2 class="font-weight-bold text-info mb-0">{{ $stats['communications'] }}</h2>
                        <div class="mt-2 small text-muted"><i class="mdi mdi-message-text"></i> Client touchpoints</div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 mesh-gradient-success">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Opportunities Won</h6>
                        <h2 class="font-weight-bold text-success mb-0">{{ $stats['opportunities_won'] }}</h2>
                        <div class="mt-2 small font-weight-bold text-success-50"><i class="mdi mdi-trending-up"></i> Upsell / cross-sell</div>
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
                <div class="col-md-4 mb-4">
                    <div class="modern-card p-4 h-100 bg-white shadow-sm">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Productivity Index</h6>
                        <h2 class="font-weight-bold text-dark mb-0">{{ $stats['completed_projects'] }} <small>Projects</small></h2>
                        <div class="mt-2 small text-warning font-weight-bold"><i class="mdi mdi-rocket-launch"></i> Output Velocity</div>
                    </div>
                </div>
                @endif
            </div>

            @if($isOd)
            <!-- Summary Row 2 (OD) -->
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Resolved Tasks</h6>
                        <h4 class="font-weight-bold text-primary mb-0">{{ $stats['completed_tasks'] }}</h4>
                    </div>
                </div>
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
            </div>
            @elseif($isCsd)
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Tickets Resolved</h6>
                        <h4 class="font-weight-bold text-success mb-0">{{ $stats['tickets_resolved'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">Collections Paid</h6>
                        <h4 class="font-weight-bold text-primary mb-0">{{ $stats['collections_paid'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">CR Completed</h6>
                        <h4 class="font-weight-bold text-dark mb-0">{{ $stats['change_requests_completed'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="modern-card p-4 h-100 bg-white border border-light text-center">
                        <h6 class="text-muted font-weight-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 1px;">At-Risk Clients</h6>
                        <h4 class="font-weight-bold text-danger mb-0">{{ $stats['at_risk_clients'] }}</h4>
                    </div>
                </div>
            </div>
            @endif

            <!-- 📈 Multi-Track Trend (Tasks vs Clients) -->
            <div class="modern-card p-4 mb-4 border-0">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="font-weight-bold text-dark mb-0">Performance Trend</h5>
                    <div class="d-flex align-items-center">
                        @if($isSales)
                        <div class="mr-3 d-flex align-items-center"><span class="badge-dot bg-primary mr-1"></span> <small class="font-weight-bold">Assigned Leads</small></div>
                        <div class="d-flex align-items-center"><span class="badge-dot bg-success mr-1"></span> <small class="font-weight-bold">Matured Clients</small></div>
                        @elseif($isCsd)
                        <div class="mr-3 d-flex align-items-center"><span class="badge-dot bg-primary mr-1"></span> <small class="font-weight-bold">Communications</small></div>
                        <div class="d-flex align-items-center"><span class="badge-dot bg-success mr-1"></span> <small class="font-weight-bold">Opportunities Won</small></div>
                        @else
                        <div class="mr-3 d-flex align-items-center"><span class="badge-dot bg-primary mr-1"></span> <small class="font-weight-bold">Tasks</small></div>
                        <div class="d-flex align-items-center"><span class="badge-dot bg-warning mr-1"></span> <small class="font-weight-bold">Input Hours</small></div>
                        @endif
                    </div>
                </div>
                <div id="individual-delivery-trend" style="height: 280px;"></div>
            </div>
        </div>
    </div>

    @if($isOd)
    <!-- 📋 Operations Work Detail -->
    <div class="row mt-4">
        <div class="col-lg-5 mb-4">
            <div class="modern-card p-4 h-100">
                <h5 class="font-weight-bold text-dark mb-3">Period Summary</h5>
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block text-uppercase">Tasks Completed</small>
                            <h3 class="font-weight-bold text-success mb-0">{{ $odSummary['completed_tasks'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block text-uppercase">Total Hours</small>
                            <h3 class="font-weight-bold text-primary mb-0">{{ $odSummary['total_hours'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block text-uppercase">Days Worked</small>
                            <h3 class="font-weight-bold text-dark mb-0">{{ $odSummary['days_worked'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block text-uppercase">Avg Hrs / Day</small>
                            <h3 class="font-weight-bold text-warning mb-0">{{ $odSummary['avg_hours_per_day'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7 mb-4">
            <div class="modern-card p-4 h-100">
                <h5 class="font-weight-bold text-dark mb-3">Hours per Task</h5>
                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table table-sm modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th class="text-right">Hours</th>
                                <th class="text-right">Logs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($odTaskBreakdown as $taskRow)
                            <tr>
                                <td class="font-weight-bold">{{ Str::limit($taskRow->task_title, 35) }}</td>
                                <td><small class="text-muted">{{ Str::limit($taskRow->project_name, 25) }}</small></td>
                                <td><span class="badge badge-soft-secondary">{{ $taskRow->status }}</span></td>
                                <td class="text-right font-weight-bold text-primary">{{ $taskRow->total_hours }} hrs</td>
                                <td class="text-right">{{ $taskRow->log_count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No task logs in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @endif

    <div class="row">
        <div class="col-12 mb-4">
            <div class="modern-card p-4 text-left">
                <h5 class="font-weight-bold text-dark mb-3">Daily Breakdown</h5>
                <div class="table-responsive">
                    <table id="daily-breakdown-table" class="table table-sm modern-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-left">Date</th>
                                <th class="text-center">@if($isSales) Matured Convs @elseif($isCsd) Tickets Resolved @else Completed Tasks @endif</th>
                                <th class="text-center">@if($isSales) Callbacks Logged @elseif($isCsd) Communications @else Log Entries @endif</th>
                                <th class="text-right">@if($isSales) Estimated Effort @elseif($isCsd) Calculated Effort @else Total Hours @endif</th>
                                <th class="text-left">@if($isSales || $isCsd) Client Activities @else Tasks Worked (hours) @endif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($odDailyBreakdown as $day)
                            @php
                                if (\Carbon\Carbon::parse($day->date)->isSunday()) {
                                    continue;
                                }
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-left">{{ $day->label }}</td>
                                <td class="text-center">{{ $day->completed_tasks }}</td>
                                <td class="text-center">{{ $day->log_entries }}</td>
                                <td class="text-right font-weight-bold text-primary">
                                    @if($isSales || $isCsd)
                                    {{ $day->total_hours }} pts
                                    @else
                                    {{ $day->total_hours }} hrs
                                    @endif
                                </td>
                                <td class="text-left">
                                    @if($day->tasks->isEmpty())
                                    <span class="text-muted">—</span>
                                    @else
                                    <div class="d-flex flex-wrap justify-content-start">
                                        @foreach($day->tasks as $t)
                                        <span class="badge badge-soft-info mr-1 mb-1" title="{{ $t->task_title }}" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ Str::limit($t->task_title, 25) }}
                                            @if($isOd)
                                            · {{ $t->hours }}h
                                            @endif
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No daily activity in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ☀️ Morning to Evening Activity (Daily Work Rhythm) -->
    <div class="row mt-4">
        <div class="col-12 text-left">
            <div class="modern-card p-5">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-1">Daily Work Rhythm</h4>
                        <p class="text-muted small mb-0">{{ $range['label'] }}</p>
                    </div>
                    <div class="text-right">
                        @if($isOd)
                            <span class="badge badge-soft-primary px-3 py-1 rounded-pill">Avg. {{ $odSummary['avg_hours_per_day'] ?? 0 }} Hrs / Day</span>
                        @elseif($isSales)
                            <span class="badge badge-soft-primary px-3 py-1 rounded-pill">Avg. {{ $stats['avg_callbacks_per_day'] ?? 0 }} Callbacks / Day</span>
                        @elseif($isCsd)
                            <span class="badge badge-soft-primary px-3 py-1 rounded-pill">Avg. {{ $stats['avg_comms_per_day'] ?? 0 }} Comms / Day</span>
                        @endif
                    </div>
                </div>

                <div class="daily-rhythm-timeline-wrapper" style="max-height: 550px; overflow-y: auto; padding-right: 15px; border-radius: 12px;">
                    <div class="daily-rhythm-timeline">
                        @forelse($dailyLogs as $date => $dayLogs)
                        <div class="day-group mb-5 rhythm-day-item">
                            <div class="d-flex align-items-center mb-4">
                                <h5 class="font-weight-bold text-primary mb-0 mr-3">{{ $date }}</h5>
                                <hr class="flex-grow-1 border-light">
                                <span class="ml-3 badge badge-soft-secondary">
                                    @if($isOd)
                                        {{ round($dayLogs->sum('time_spend'), 2) }} Working Hours
                                    @elseif($isSales)
                                        {{ $dayLogs->count() }} Callbacks logged
                                    @elseif($isCsd)
                                        {{ $dayLogs->count() }} Actions logged
                                    @endif
                                </span>
                            </div>

                            <div class="timeline-items ml-4 border-left border-light pl-4">
                                @foreach($dayLogs->sortBy('created_at') as $log)
                                <div class="timeline-item position-relative mb-4">
                                    <div class="timeline-dot position-absolute" style="left: -29px; top: 5px; width: 10px; height: 10px; background: #6366f1; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px #e2e8f0;"></div>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="font-size-13 text-muted font-weight-bold">{{ Carbon\Carbon::parse($log->created_at)->format('h:i A') }}</span>
                                            @if($isOd)
                                                <h6 class="font-weight-bold text-dark mt-1 mb-1">{{ $log->task->title ?? 'Untitled Task' }}</h6>
                                                <p class="text-muted small mb-0">{{ $log->log_description }}</p>
                                            @elseif($isSales)
                                                <h6 class="font-weight-bold text-dark mt-1 mb-1">Callback Status: <span class="badge badge-soft-info">{{ $log->status }}</span></h6>
                                                <p class="text-muted small mb-0">{{ $log->remarks }}</p>
                                            @elseif($isCsd)
                                                <h6 class="font-weight-bold text-dark mt-1 mb-1">Comm Channel: <span class="badge badge-soft-info">{{ ucfirst($log->type ?? 'Note') }}</span></h6>
                                                <p class="text-muted small mb-0">{{ $log->subject ?? $log->remarks }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            @if($isOd)
                                                <span class="badge badge-soft-info rounded-pill px-3">{{ $log->time_spend }} hrs</span>
                                                <div class="small text-muted mt-1">{{ $log->task->project->clients->name ?? ($log->task->project->project_name ?? 'Internal') }}</div>
                                            @elseif($isSales)
                                                <span class="badge badge-soft-success rounded-pill px-3">Sales Action</span>
                                                <div class="small text-muted mt-1">{{ $log->client->name ?? 'Client' }}</div>
                                            @elseif($isCsd)
                                                <span class="badge badge-soft-success rounded-pill px-3">CSD Log</span>
                                                <div class="small text-muted mt-1">{{ $log->client->name ?? 'Client' }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center py-4">No activity entries in this period.</p>
                        @endforelse
                    </div>
                </div>
                @if($dailyLogs->count() > 0)
                <div id="rhythm-pagination-container" class="mt-4 d-flex justify-content-end">
                    <!-- Pagination will be dynamically generated by JS -->
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- 📜 Recent Activity/Followup Logs -->
        <div class="col-xl-8">
            <div class="modern-card p-5 h-100">
                @if($isSales)
                <h5 class="font-weight-bold text-dark mb-5">Recent Followup & Callback Logs</h5>
                <div class="table-responsive px-1" style="max-height: 480px; overflow-y: auto;">
                    <table class="table modern-table mb-0" style="border: none !important;">
                        <thead>
                            <tr style="background: transparent !important; border: none !important; position: sticky; top: 0; background-color: #ffffff; z-index: 10;">
                                <th style="text-align: left; padding-top: 0;">Timestamp</th>
                                <th style="text-align: left; padding-top: 0;">Client Name</th>
                                <th style="text-align: left; padding-top: 0;">Status / Remarks</th>
                                <th style="padding-top: 0;">Next Followup</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesLogs as $sLog)
                            <tr>
                                <td class="text-left"><span class="badge badge-soft-secondary">{{ Carbon\Carbon::parse($sLog->created_at)->format('d M, H:i') }}</span></td>
                                <td class="text-left">
                                    <span class="font-weight-bold text-dark d-block">{{ $sLog->client()->first()->name ?? 'N/A' }}</span>
                                    <small class="text-muted">STS Callback History</small>
                                </td>
                                <td class="text-left">
                                    <span class="badge {{ $sLog->status == 'Matured' ? 'badge-success' : ($sLog->status == 'Followup' ? 'badge-info' : 'badge-warning') }} mb-1">{{ $sLog->status }}</span>
                                    <p class="text-muted small mb-0">{{ Str::limit($sLog->remarks, 75) }}</p>
                                </td>
                                <td>
                                    @if($sLog->tbro)
                                    <span class="text-primary font-weight-bold">{{ Carbon\Carbon::parse($sLog->tbro)->format('d M, Y') }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/gray/fogg-searching.png" style="width: 120px;" class="mb-3 opacity-50">
                                    <p class="text-muted mb-0">No followups logged for the selected period.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @elseif($isCsd)
                <h5 class="font-weight-bold text-dark mb-5">Recent Client Communications</h5>
                <div class="table-responsive px-1" style="max-height: 480px; overflow-y: auto;">
                    <table class="table modern-table mb-0" style="border: none !important;">
                        <thead>
                            <tr style="background: transparent !important; border: none !important; position: sticky; top: 0; background-color: #ffffff; z-index: 10;">
                                <th style="text-align: left; padding-top: 0;">Timestamp</th>
                                <th style="text-align: left; padding-top: 0;">Client</th>
                                <th style="text-align: left; padding-top: 0;">Channel / Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCsdComms as $comm)
                            <tr>
                                <td class="text-left"><span class="badge badge-soft-secondary">{{ $comm->created_at->format('d M, H:i') }}</span></td>
                                <td class="text-left"><span class="font-weight-bold text-dark">{{ $comm->client->name ?? 'N/A' }}</span></td>
                                <td class="text-left">
                                    <span class="badge badge-soft-info mb-1">{{ ucfirst($comm->type ?? 'note') }}</span>
                                    <p class="text-muted small mb-0">{{ Str::limit($comm->subject ?? $comm->remarks, 75) }}</p>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <p class="text-muted mb-0">No communications logged for the selected period.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
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
                                <td><span class="creator-identity">{{ $log->time_spend }} hrs</span></td>
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
                @endif
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
                @elseif($isCsd)
                <h6 class="font-weight-bold text-dark mb-4"><i class="mdi mdi-trending-up text-success mr-2"></i> Won Opportunities</h6>
                <div class="recent-list">
                    @forelse($recentWonOpps as $opp)
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-lg border border-light">
                        <div class="flex-grow-1">
                            <span class="font-weight-bold text-dark d-block" style="font-size: 13px;">{{ Str::limit($opp->title, 30) }}</span>
                            <small class="text-muted">{{ $opp->clients?->name ?? 'Client' }} · {{ $opp->updated_at->format('d M, Y') }}</small>
                        </div>
                        <span class="badge badge-success">Won</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">No won opportunities yet.</p>
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


</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    $(document).ready(function() {
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

        $('#applyCustomRange').on('click', function(e) {
            e.preventDefault();
            $('#presetInput').val('custom');
            $('#filterForm').submit();
        });

        $('#dateFromInput, #dateToInput').on('change', function() {
            if ($('#presetInput').val() === 'custom') {
                $('#filterForm').submit();
            }
        });

        // Dual-Track Trend Chart for Individual
        var trendOptions = {
            series: [
                @if($isSales)
                {
                    name: 'Assigned Leads',
                    data: [@foreach($monthlyTrend as $m) {{ $m->clients }}, @endforeach]
                },
                {
                    name: 'Matured Clients',
                    data: [@foreach($monthlyTrend as $m) {{ $m->matured }}, @endforeach]
                }
                @elseif($isCsd)
                {
                    name: 'Communications',
                    data: [@foreach($monthlyTrend as $m) {{ $m->communications }}, @endforeach]
                },
                {
                    name: 'Opportunities Won',
                    data: [@foreach($monthlyTrend as $m) {{ $m->opportunities_won }}, @endforeach]
                }
                @else
                {
                    name: 'Tasks',
                    data: [@foreach($monthlyTrend as $m) {{ $m->tasks }}, @endforeach]
                },
                {
                    name: 'Input Hours',
                    data: [@foreach($monthlyTrend as $m) {{ $m->hours }}, @endforeach]
                }
                @endif
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
            colors: ['#6366f1', @if($isSales || $isCsd)
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

        // Daily Breakdown DataTable Pagination
        $('#daily-breakdown-table').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            ordering: false,
            dom: 'rtip',
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            }
        });

        // Daily Work Rhythm Custom Day-wise Pagination
        const itemsPerPage = 3;
        const $rhythmItems = $('.rhythm-day-item');
        const numItems = $rhythmItems.length;
        
        if (numItems > itemsPerPage) {
            const numPages = Math.ceil(numItems / itemsPerPage);
            const $paginationContainer = $('#rhythm-pagination-container');
            
            let paginationHtml = '<ul class="pagination pagination-rounded mb-0">';
            paginationHtml += '<li class="page-item disabled" id="rhythm-prev"><a class="page-link" href="javascript:void(0);"><i class="mdi mdi-chevron-left"></i></a></li>';
            for (let i = 1; i <= numPages; i++) {
                paginationHtml += `<li class="page-item ${i === 1 ? 'active' : ''} rhythm-page-link" data-page="${i}"><a class="page-link" href="javascript:void(0);">${i}</a></li>`;
            }
            paginationHtml += `<li class="page-item" id="rhythm-next"><a class="page-link" href="javascript:void(0);"><i class="mdi mdi-chevron-right"></i></a></li>`;
            paginationHtml += '</ul>';
            $paginationContainer.html(paginationHtml);
            
            let currentPage = 1;
            
            function showPage(page) {
                currentPage = page;
                $rhythmItems.hide();
                $rhythmItems.slice((page - 1) * itemsPerPage, page * itemsPerPage).fadeIn(200);
                
                $('.daily-rhythm-timeline-wrapper').animate({ scrollTop: 0 }, 100);
                $('.rhythm-page-link').removeClass('active');
                $(`.rhythm-page-link[data-page="${page}"]`).addClass('active');
                
                if (page === 1) {
                    $('#rhythm-prev').addClass('disabled');
                } else {
                    $('#rhythm-prev').removeClass('disabled');
                }
                
                if (page === numPages) {
                    $('#rhythm-next').addClass('disabled');
                } else {
                    $('#rhythm-next').removeClass('disabled');
                }
            }
            
            showPage(1);
            
            $(document).on('click', '.rhythm-page-link', function() {
                const page = parseInt($(this).data('page'));
                showPage(page);
            });
            
            $(document).on('click', '#rhythm-prev', function() {
                if (currentPage > 1) {
                    showPage(currentPage - 1);
                }
            });
            
            $(document).on('click', '#rhythm-next', function() {
                if (currentPage < numPages) {
                    showPage(currentPage + 1);
                }
            });
        }
    });
</script>

<style>
    .badge-dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    /* Sleek custom scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endsection
