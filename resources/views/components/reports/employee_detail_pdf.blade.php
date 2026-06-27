<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Performance Dossier - {{ $employee->name }}</title>
    <style>
        @page {
            margin: 40px 40px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .employee-name {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }
        .employee-role {
            font-size: 13px;
            color: #64748b;
            margin: 2px 0 0 0;
            font-weight: 500;
        }
        .dossier-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6366f1;
            font-weight: bold;
            margin: 0;
        }
        .timeframe-badge {
            background-color: #f1f5f9;
            color: #334155;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .score-box {
            border: 1px solid #e2e8f0;
            background: #fafafa;
            border-radius: 12px;
            padding: 10px 15px;
            text-align: center;
        }
        .score-value {
            font-size: 22px;
            font-weight: bold;
            color: #6366f1;
            margin: 0;
            line-height: 1;
        }
        .score-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: bold;
            margin-top: 3px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 25px;
            margin-bottom: 10px;
            border-left: 3px solid #6366f1;
            padding-left: 8px;
        }
        /* Grid Tables */
        .kpi-table {
            width: 100%;
            border-spacing: 10px;
            margin-bottom: 15px;
            margin-left: -10px;
            margin-right: -10px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            vertical-align: top;
        }
        .kpi-title {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .kpi-sub {
            font-size: 9px;
            color: #94a3b8;
            margin: 3px 0 0 0;
        }
        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
        }
        .data-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 8px 10px;
            vertical-align: middle;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-soft-info {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .badge-soft-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-soft-secondary {
            background-color: #f1f5f9;
            color: #475569;
        }
        .badge-soft-warning {
            background-color: #fef9c3;
            color: #a16207;
        }
        .badge-soft-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        /* Timeline styles */
        .timeline-day {
            page-break-inside: avoid;
            margin-bottom: 20px;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 12px;
            background: #fafafa;
        }
        .timeline-day-header {
            font-size: 12px;
            font-weight: bold;
            color: #6366f1;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .timeline-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .timeline-time {
            font-weight: bold;
            color: #94a3b8;
            font-size: 10px;
            display: inline-block;
            width: 70px;
        }
        .timeline-content {
            display: inline-block;
            vertical-align: top;
            width: calc(100% - 80px);
        }
        .timeline-title {
            font-weight: bold;
            font-size: 11px;
            margin: 0;
            color: #1e293b;
        }
        .timeline-desc {
            font-size: 10px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="footer">
        WMS Intelligence Report &bull; Generated on {{ now()->format('d M, Y h:i A') }} &bull; Page [page]
    </div>

    <!-- Header -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top;">
                <p class="dossier-label">Employee Dossier</p>
                <h1 class="employee-name">{{ $employee->name }}</h1>
                <p class="employee-role">
                    {{ $employee->emp->designation ?? 'Team Member' }} &bull; 
                    {{ $employee->departments->dept->name ?? 'WMS' }} Department
                </p>
                <div style="margin-top: 10px;">
                    <span class="timeframe-badge">Timeframe: {{ $range['label'] }}</span>
                </div>
            </td>
            <td style="width: 130px; text-align: right; vertical-align: top;">
                <div class="score-box">
                    <p class="score-value">{{ $performanceScore }}%</p>
                    <p class="score-title">Productivity</p>
                </div>
            </td>
        </tr>
    </table>

    <!-- KPIs Row 1 -->
    <h2 class="section-title">Key Performance Indicators</h2>
    <table class="kpi-table" cellpadding="0" cellspacing="0">
        <tr>
            @if($isSales)
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Assigned Leads</p>
                    <p class="kpi-value">{{ $stats['total_leads'] }}</p>
                    <p class="kpi-sub">Total lead pipeline size</p>
                </td>
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Active Followups</p>
                    <p class="kpi-value">{{ $stats['active_followups'] }}</p>
                    <p class="kpi-sub">Ongoing negotiations</p>
                </td>
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Matured Clients</p>
                    <p class="kpi-value">{{ $stats['matured'] }}</p>
                    <p class="kpi-sub">Sales conversions</p>
                </td>
            @elseif($isCsd)
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Active Clients</p>
                    <p class="kpi-value">{{ $stats['active_clients'] }}</p>
                    <p class="kpi-sub">Client care accounts</p>
                </td>
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Communications</p>
                    <p class="kpi-value">{{ $stats['communications'] }}</p>
                    <p class="kpi-sub">Client touchpoints</p>
                </td>
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Opportunities Won</p>
                    <p class="kpi-value">{{ $stats['opportunities_won'] }}</p>
                    <p class="kpi-sub">Won upsell / cross-sell</p>
                </td>
            @else
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Resolved Tasks</p>
                    <p class="kpi-value">{{ $stats['completed_tasks'] }}</p>
                    <p class="kpi-sub">Completed task deliverables</p>
                </td>
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Avg Daily Input</p>
                    <p class="kpi-value">{{ $stats['avg_daily_hours'] }} hrs</p>
                    <p class="kpi-sub">Average logged time per day</p>
                </td>
                <td class="kpi-card" style="width: 33%;">
                    <p class="kpi-title">Completed Projects</p>
                    <p class="kpi-value">{{ $stats['completed_projects'] }}</p>
                    <p class="kpi-sub">Projects wrapped in period</p>
                </td>
            @endif
        </tr>
    </table>

    <!-- Secondary KPIs / Context Row -->
    @if($isOd)
        <table class="kpi-table" cellpadding="0" cellspacing="0" style="margin-top: -10px;">
            <tr>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">Taken Time</p>
                    <p class="kpi-value" style="font-size: 16px;">{{ $stats['total_hours'] }} Hrs</p>
                </td>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">Pending Tasks</p>
                    <p class="kpi-value" style="font-size: 16px; color: #ef4444;">{{ $stats['pending_tasks'] }}</p>
                </td>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">Days Worked</p>
                    <p class="kpi-value" style="font-size: 16px;">{{ $odSummary['days_worked'] ?? 0 }}</p>
                </td>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">Active Projects</p>
                    <p class="kpi-value" style="font-size: 16px; color: #10b981;">{{ $currentProjects->count() }}</p>
                </td>
            </tr>
        </table>
    @elseif($isCsd)
        <table class="kpi-table" cellpadding="0" cellspacing="0" style="margin-top: -10px;">
            <tr>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">Tickets Resolved</p>
                    <p class="kpi-value" style="font-size: 16px; color: #10b981;">{{ $stats['tickets_resolved'] }}</p>
                </td>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">Collections Paid</p>
                    <p class="kpi-value" style="font-size: 16px;">{{ $stats['collections_paid'] }}</p>
                </td>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">CR Completed</p>
                    <p class="kpi-value" style="font-size: 16px;">{{ $stats['change_requests_completed'] }}</p>
                </td>
                <td class="kpi-card" style="width: 25%; padding: 10px;">
                    <p class="kpi-title">At-Risk Clients</p>
                    <p class="kpi-value" style="font-size: 16px; color: #ef4444;">{{ $stats['at_risk_clients'] }}</p>
                </td>
            </tr>
        </table>
    @endif

    <!-- Active Projects/Deliverables section -->
    <h2 class="section-title">
        @if($isSales) Active Working Leads @elseif($isCsd) Active Care Assignments @else Current Active Projects @endif
    </h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Name</th>
                <th style="width: 30%; text-align: right;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($currentProjects as $p)
            <tr>
                <td style="font-weight: bold; color: #1e293b;">{{ $p->name }}</td>
                <td style="text-align: right;"><span class="badge badge-soft-info">Active ({{ $p->status }})</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center" style="color: #94a3b8; padding: 15px;">No active projects or deliverables in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>

    <!-- Daily Breakdown -->
    <h2 class="section-title">Daily Performance Breakdown</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Date</th>
                <th class="text-center" style="width: 15%;">@if($isSales) Matured Convs @elseif($isCsd) Tickets Resolved @else Completed Tasks @endif</th>
                <th class="text-center" style="width: 15%;">@if($isSales) Callbacks Logged @elseif($isCsd) Communications @else Log Entries @endif</th>
                <th class="text-right" style="width: 15%;">@if($isSales || $isCsd) Estimated Effort @else Total Hours @endif</th>
                <th style="width: 35%;">@if($isSales || $isCsd) Client Activities @else Tasks Worked @endif</th>
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
                    <td style="font-weight: bold;">{{ $day->label }}</td>
                    <td class="text-center">{{ $day->completed_tasks }}</td>
                    <td class="text-center">{{ $day->log_entries }}</td>
                    <td class="text-right font-weight-bold" style="color: #6366f1;">
                        @if($isSales || $isCsd)
                            {{ $day->total_hours }} pts
                        @else
                            {{ $day->total_hours }} hrs
                        @endif
                    </td>
                    <td>
                        @if($day->tasks->isEmpty())
                            <span style="color: #94a3b8;">—</span>
                        @else
                            @foreach($day->tasks as $t)
                                <span class="badge badge-soft-secondary" style="margin-right: 2px; margin-bottom: 2px;">
                                    {{ Str::limit($t->task_title, 20) }}
                                    @if($isOd)
                                        · {{ $t->hours }}h
                                    @endif
                                </span>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 20px;">No daily breakdown activity found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Daily Work Rhythm -->
    <h2 class="section-title">Daily Work Rhythm (Activity logs)</h2>
    @forelse($dailyLogs as $date => $dayLogs)
        @php
            if (\Carbon\Carbon::parse($date)->isSunday()) {
                continue;
            }
        @endphp
        <div class="timeline-day">
            <div class="timeline-day-header">
                {{ $date }} 
                <span style="float: right; font-weight: normal; font-size: 10px; color: #64748b;">
                    @if($isOd)
                        {{ round($dayLogs->sum('time_spend'), 2) }} Working Hours
                    @elseif($isSales)
                        {{ $dayLogs->count() }} Callbacks logged
                    @elseif($isCsd)
                        {{ $dayLogs->count() }} Actions logged
                    @endif
                </span>
                <div style="clear: both;"></div>
            </div>
            
            @foreach($dayLogs->sortBy('created_at') as $log)
                <div class="timeline-item">
                    <span class="timeline-time">{{ Carbon\Carbon::parse($log->created_at)->format('h:i A') }}</span>
                    <div class="timeline-content">
                        @if($isOd)
                            <p class="timeline-title">
                                {{ $log->task->title ?? 'Untitled Task' }}
                                <span class="badge badge-soft-info" style="float: right;">{{ $log->time_spend }} hrs</span>
                            </p>
                            <p class="timeline-desc">{{ $log->log_description }}</p>
                            <p style="font-size: 9px; color: #94a3b8; margin: 2px 0 0 0;">
                                Project: {{ $log->task->project->clients->name ?? ($log->task->project->project_name ?? 'Internal') }}
                            </p>
                        @elseif($isSales)
                            <p class="timeline-title">
                                Callback Status: <span class="badge badge-soft-info">{{ $log->status }}</span>
                                <span class="badge badge-soft-success" style="float: right;">Sales Action</span>
                            </p>
                            <p class="timeline-desc">{{ $log->remarks }}</p>
                            <p style="font-size: 9px; color: #94a3b8; margin: 2px 0 0 0;">
                                Client: {{ $log->client->name ?? 'Client' }}
                            </p>
                        @elseif($isCsd)
                            <p class="timeline-title">
                                Comm Channel: <span class="badge badge-soft-info">{{ ucfirst($log->type ?? 'Note') }}</span>
                                <span class="badge badge-soft-success" style="float: right;">CSD Log</span>
                            </p>
                            <p class="timeline-desc">{{ $log->subject ?? $log->remarks }}</p>
                            <p style="font-size: 9px; color: #94a3b8; margin: 2px 0 0 0;">
                                Client: {{ $log->client->name ?? 'Client' }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <p class="text-center" style="color: #94a3b8; padding: 20px;">No detailed work rhythm logs found.</p>
    @endforelse

</body>
</html>
