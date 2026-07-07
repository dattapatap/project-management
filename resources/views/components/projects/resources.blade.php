@extends('layouts.app')
@section('title', 'Resource Allocation')
@section('styles')
<style>
    /* ── Layout ─────────────────────────────────────────────────── */
    .res-page { padding:24px; }
    .res-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .res-title { font-size:1.5rem; font-weight:700; color:#2d3748; display:flex; align-items:center; gap:10px; }
    .res-title i { color:#667eea; font-size:1.6rem; }

    /* ── Stats Row ──────────────────────────────────────────────── */
    .stats-row { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:22px; }
    .stat-card {
        flex:1; min-width:130px; padding:16px 20px;
        border-radius:12px; background:#fff;
        box-shadow:0 2px 10px rgba(0,0,0,.05); text-align:center;
    }
    .stat-card .stat-num { font-size:1.8rem; font-weight:800; line-height:1; }
    .stat-card .stat-lbl { font-size:.75rem; color:#718096; font-weight:500; margin-top:4px; }
    .stat-card.blue .stat-num  { color:#4299e1; }
    .stat-card.amber .stat-num { color:#ed8936; }
    .stat-card.green .stat-num { color:#38a169; }
    .stat-card.red .stat-num   { color:#e53e3e; }

    /* ── Workload Heatmap ───────────────────────────────────────── */
    .section-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        padding: 24px;
        margin-bottom: 24px;
    }
    .section-card h5 { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .section-card h5 i { color: #3b82f6; }

    /* ── Heatmap grid ───────────────────────────────────────────── */
    .heatmap-grid {
        display: grid;
        grid-template-columns: 240px repeat(5, 1fr);
        gap: 8px;
        font-size: .85rem;
        overflow-x: auto;
        min-width: 780px;
    }
    .hm-cell {
        padding: 12px 10px;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-width: 90px;
    }
    .hm-header { background: #f8fafc; font-weight: 700; color: #475569; font-size: .8rem; border: 1px solid #e2e8f0; }
    .hm-name { background: #f8fafc; text-align: left; align-items: flex-start; gap: 4px; border: 1px solid #e2e8f0; }
    .hm-name .avatar {
        width:30px; height:30px; border-radius:50%;
        background:linear-gradient(135deg,#667eea,#764ba2);
        color:#fff; font-weight:700; font-size:.75rem;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
    .hm-name .name-text { font-weight:600; color:#2d3748; font-size:.8125rem; line-height:1.2; }
    .hm-name .role-text { font-size:.7rem; color:#a0aec0; }
    .hm-name { flex-direction:row; gap:8px; }
    .hm-count { font-size:1rem; font-weight:800; }
    .hm-sub { font-size:.7rem; color:#718096; }

    .load-0   { background:#f0fff4; }
    .load-low { background:#c6f6d5; }
    .load-med { background:#fefcbf; }
    .load-hi  { background:#fed7aa; }
    .load-max { background:#feb2b2; }

    /* ── Matrix Table ───────────────────────────────────────────── */
    .matrix-wrap { overflow-x:auto; }
    table.matrix { width:100%; border-collapse:separate; border-spacing:0; font-size:.8125rem; }
    table.matrix th {
        background:#f7f8fb; color:#4a5568; font-weight:700;
        padding:10px 12px; border-bottom:2px solid #e2e8f0;
        white-space:nowrap; position:sticky; top:0; z-index:1;
    }
    table.matrix td { padding:10px 12px; border-bottom:1px solid #f0f4f8; vertical-align:middle; }
    table.matrix tr:hover td { background:#fafbff; }
    table.matrix .proj-name { font-weight:600; color:#2d3748; }
    table.matrix .proj-client { font-size:.75rem; color:#a0aec0; }
    table.matrix .proj-status span {
        padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700;
    }
    .badge-todo     { background:#ebf8ff; color:#2b6cb0; }
    .badge-inprog   { background:#fffaf0; color:#c05621; }
    .badge-complete { background:#f0fff4; color:#276749; }
    .badge-overdue  { background:#fff5f5; color:#c53030; }

    .task-pill {
        display:inline-flex; align-items:center; gap:4px;
        padding:3px 10px; border-radius:20px;
        font-size:.72rem; font-weight:600;
        background:#ebf8ff; color:#2c7a7b;
        cursor:default;
    }
    .task-pill.empty { background:#f7f8fb; color:#a0aec0; }

    /* ── Legend ─────────────────────────────────────────────────── */
    .heat-legend { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
    .heat-legend-item { display:flex; align-items:center; gap:6px; font-size:.75rem; color:#718096; }
    .heat-dot { width:14px; height:14px; border-radius:4px; }
</style>
@endsection

@section('content')
<div class="container-fluid erp-page pb-5">

    <div class="erp-page-header my-4">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">
                <i class="mdi mdi-account-switch mr-2 text-primary"></i>Resource Allocation Matrix
            </h4>
            <p class="erp-page-subtitle">Track team assignments, workload balance, and availability</p>
        </div>
        <div class="erp-page-header__actions">
            @if(Auth::user()->hasRole(['Admin', 'Project-Manager', 'Branch-Manager']))
            <form method="GET" action="{{ route('projects.resources') }}" class="form-inline d-flex align-items-center" style="gap: 8px;">
                <!-- Team Selector -->
                <select name="team" class="form-control form-control-sm border" style="width: 155px; height: 35px;" onchange="this.form.submit()">
                    <option value="">All Teams</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" {{ $teamId == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
                
                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm" style="height: 35px; line-height: 23px;">
                    <i class="mdi mdi-arrow-left"></i>
                </a>
            </form>
            @else
                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left mr-1"></i>Back
                </a>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    @php
        $totalProjects = $projects->count();
        $totalMembers  = $workload->count();
        $overloaded    = $workload->filter(fn($m) => ($m->todo_count + $m->inprogress_count) > 5)->count();
        $idle          = $workload->filter(fn($m) => ($m->todo_count + $m->inprogress_count) === 0)->count();
    @endphp

    <!-- Stats Cards Grid -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border mb-3">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted font-weight-medium mb-1">Active Projects</p>
                        <h4 class="mb-0 text-dark font-weight-bold">{{ $totalProjects }}</h4>
                    </div>
                    <div class="avatar-title rounded-circle bg-soft-primary text-primary" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="mdi mdi-briefcase-outline"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border mb-3">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted font-weight-medium mb-1">Team Members</p>
                        <h4 class="mb-0 text-dark font-weight-bold">{{ $totalMembers }}</h4>
                    </div>
                    <div class="avatar-title rounded-circle bg-soft-info text-info" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="mdi mdi-account-group-outline"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border mb-3">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted font-weight-medium mb-1">Overloaded Members</p>
                        <h4 class="mb-0 text-danger font-weight-bold">{{ $overloaded }}</h4>
                    </div>
                    <div class="avatar-title rounded-circle bg-soft-danger text-danger" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border mb-3">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted font-weight-medium mb-1">Available / Idle</p>
                        <h4 class="mb-0 text-success font-weight-bold">{{ $idle }}</h4>
                    </div>
                    <div class="avatar-title rounded-circle bg-soft-success text-success" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="mdi mdi-checkbox-marked-circle-outline"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Workload Heatmap --}}
    @if($workload->count())
    <div class="section-card">
        <h5><i class="mdi mdi-chart-bar"></i> Team Workload Heatmap</h5>

        <div class="heatmap-grid">
            {{-- Row 0: headers --}}
            <div class="hm-cell hm-header">Member</div>
            <div class="hm-cell hm-header">To Do</div>
            <div class="hm-cell hm-header">In Progress</div>
            <div class="hm-cell hm-header">Completed</div>
            <div class="hm-cell hm-header">Overdue</div>
            <div class="hm-cell hm-header">Hours Logged</div>

            @foreach($workload as $member)
                @php
                    $active = $member->todo_count + $member->inprogress_count;
                    $loadClass = match(true) {
                        $active === 0         => 'load-0',
                        $active <= 2          => 'load-low',
                        $active <= 4          => 'load-med',
                        $active <= 6          => 'load-hi',
                        default               => 'load-max',
                    };
                    $initials = collect(explode(' ', $member->name))->map(fn($w)=>strtoupper($w[0]))->take(2)->implode('');
                @endphp
                {{-- Name cell --}}
                <div class="hm-cell hm-name">
                    <div class="avatar">{{ $initials }}</div>
                    <div>
                        <div class="name-text">{{ $member->name }}</div>
                    </div>
                </div>
                <div class="hm-cell {{ $member->todo_count > 0 ? 'load-low' : 'load-0' }}">
                    <div class="hm-count">{{ $member->todo_count }}</div>
                    <div class="hm-sub">tasks</div>
                </div>
                <div class="hm-cell {{ $member->inprogress_count > 0 ? 'load-med' : 'load-0' }}">
                    <div class="hm-count">{{ $member->inprogress_count }}</div>
                    <div class="hm-sub">tasks</div>
                </div>
                <div class="hm-cell load-0">
                    <div class="hm-count">{{ $member->completed_count }}</div>
                    <div class="hm-sub">tasks</div>
                </div>
                <div class="hm-cell {{ $member->overdue_count > 0 ? 'load-max' : 'load-0' }}">
                    <div class="hm-count">{{ $member->overdue_count }}</div>
                    <div class="hm-sub">tasks</div>
                </div>
                <div class="hm-cell {{ $loadClass }}">
                    <div class="hm-count">{{ number_format($member->total_hours / 60, 1) }}h</div>
                    <div class="hm-sub">logged</div>
                </div>
            @endforeach
        </div>

        <div class="heat-legend" style="margin-top:16px;">
            <div class="heat-legend-item"><div class="heat-dot" style="background:#f0fff4;border:1px solid #c6f6d5;"></div> No tasks</div>
            <div class="heat-legend-item"><div class="heat-dot" style="background:#c6f6d5;"></div> Light (1–2)</div>
            <div class="heat-legend-item"><div class="heat-dot" style="background:#fefcbf;"></div> Moderate (3–4)</div>
            <div class="heat-legend-item"><div class="heat-dot" style="background:#fed7aa;"></div> High (5–6)</div>
            <div class="heat-legend-item"><div class="heat-dot" style="background:#feb2b2;"></div> Overloaded (7+)</div>
        </div>
    </div>
    @endif

    {{-- Project × Member Matrix --}}
    <div class="section-card">
        <h5><i class="mdi mdi-table-large"></i> Project Assignment Matrix</h5>
        @if($projects->isEmpty())
            <p style="color:#a0aec0;text-align:center;padding:40px 0;">No active projects found.</p>
        @else
        <div class="matrix-wrap">
            <table class="matrix">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Category</th>
                        <th>Assigned Members</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        @php
                            $assignedUsers = $project->tasks->pluck('user')->unique('id')->filter();
                            $completedTasks = $project->tasks->where('status', 'Completed')->count();
                            $totalTasks = $project->tasks->count();
                            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

                            $isOverdue = $project->status !== 'Completed'
                                && $project->end_date
                                && \Carbon\Carbon::parse($project->end_date)->isPast();

                            $badgeClass = match(true) {
                                $isOverdue => 'badge-overdue',
                                $project->status === 'Completed' => 'badge-complete',
                                $project->status === 'InProgress' => 'badge-inprog',
                                default => 'badge-todo',
                            };
                            $badgeLabel = $isOverdue ? 'Overdue' : $project->status;
                        @endphp
                        <tr>
                            <td>
                                <div class="proj-name">
                                    <a href="{{ url('/projects/'.base64_encode($project->id).'/history') }}" target="_blank" style="color:inherit;text-decoration:none;">
                                        {{ $project->project_name }}
                                    </a>
                                </div>
                            </td>
                            <td class="proj-client">{{ $project->clients->name ?? '—' }}</td>
                            <td class="proj-status"><span class="{{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                            <td style="white-space:nowrap;color:{{ $isOverdue ? '#e53e3e' : '#4a5568' }}">
                                {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '—' }}
                            </td>
                            <td style="color:#718096;">{{ $project->projectCategory->category ?? '—' }}</td>
                            <td>
                                @forelse($assignedUsers as $u)
                                    <span class="task-pill">
                                        {{ $u->name }}
                                        ({{ $project->tasks->where('assigned_to', $u->id)->count() }})
                                    </span>
                                @empty
                                    <span class="task-pill empty">Unassigned</span>
                                @endforelse
                            </td>
                            <td style="min-width:120px;">
                                <div style="background:#e2e8f0;border-radius:20px;height:8px;overflow:hidden;">
                                    <div style="background:{{ $progress >= 100 ? '#68d391' : ($progress >= 50 ? '#f6ad55' : '#90cdf4') }};height:8px;width:{{ $progress }}%;border-radius:20px;transition:width .4s;"></div>
                                </div>
                                <div style="font-size:.7rem;color:#718096;margin-top:3px;">{{ $progress }}% ({{ $completedTasks }}/{{ $totalTasks }})</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
