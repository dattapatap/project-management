@extends('layouts.erp')
@section('title', 'Project Gantt Timeline')
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
<style>
    /* ── Page Shell ────────────────────────────────────────────── */
    .timeline-page { padding: 24px; }
    .tl-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .tl-title { font-size:1.5rem; font-weight:700; color:#2d3748; display:flex; align-items:center; gap:10px; }
    .tl-title i { color:#667eea; font-size:1.6rem; }

    /* ── Filter Bar ─────────────────────────────────────────────── */
    .filter-bar { display:flex; gap:10px; flex-wrap:wrap; }
    .filter-bar select, .filter-bar input {
        padding:8px 14px; border-radius:8px;
        border:1px solid #e2e8f0; font-size:.875rem;
        color:#4a5568; background:#fff; outline:none;
        transition:border-color .2s;
    }
    .filter-bar select:focus, .filter-bar input:focus { border-color:#667eea; }
    .btn-view { padding:8px 18px; border-radius:8px; border:none; cursor:pointer; font-size:.875rem; font-weight:600; }
    .btn-view.active { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; }
    .btn-view:not(.active) { background:#f7f8fb; color:#667eea; }

    /* ── Legend ─────────────────────────────────────────────────── */
    .legend { display:flex; gap:18px; flex-wrap:wrap; margin-bottom:16px; }
    .legend-item { display:flex; align-items:center; gap:6px; font-size:.8125rem; color:#718096; font-weight:500; }
    .legend-dot { width:12px; height:12px; border-radius:3px; }
    .legend-dot.todo     { background:#90cdf4; }
    .legend-dot.inprog   { background:#f6ad55; }
    .legend-dot.complete { background:#68d391; }
    .legend-dot.overdue  { background:#fc8181; }

    /* ── Gantt wrapper ──────────────────────────────────────────── */
    .gantt-wrapper {
        background:#fff; border-radius:14px;
        box-shadow:0 4px 20px rgba(0,0,0,.07);
        padding:24px; overflow-x:auto;
        min-height:400px;
    }
    #gantt { min-width:800px; }

    /* ── Empty state ────────────────────────────────────────────── */
    .empty-state { text-align:center; padding:80px 20px; color:#a0aec0; }
    .empty-state i { font-size:3rem; margin-bottom:12px; display:block; }
    .empty-state h5 { font-size:1.1rem; font-weight:600; margin-bottom:6px; }

    /* ── Frappe Gantt colour overrides ──────────────────────────── */
    .gantt .bar-group .bar { rx:5; ry:5; }
    .gantt .bar-group .bar-progress { rx:5; ry:5; }

    /* ── Stats row ──────────────────────────────────────────────── */
    .stats-row { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:20px; }
    .stat-card {
        flex:1; min-width:130px; padding:16px 20px;
        border-radius:12px; background:#fff;
        box-shadow:0 2px 10px rgba(0,0,0,.05);
        text-align:center;
    }
    .stat-card .stat-num { font-size:1.8rem; font-weight:800; line-height:1; }
    .stat-card .stat-lbl { font-size:.75rem; color:#718096; font-weight:500; margin-top:4px; }
    .stat-card.blue .stat-num  { color:#4299e1; }
    .stat-card.amber .stat-num { color:#ed8936; }
    .stat-card.green .stat-num { color:#38a169; }
    .stat-card.red .stat-num   { color:#e53e3e; }
</style>
@endpush

@section('content')
<div class="timeline-page">

    {{-- Header --}}
    <div class="tl-header">
        <div class="tl-title">
            <i class="mdi mdi-chart-gantt"></i>
            Gantt Timeline
        </div>
        <div class="filter-bar">
            <select id="viewMode">
                <option value="Week">Week</option>
                <option value="Month" selected>Month</option>
                <option value="Quarter">Quarter</option>
            </select>
            <select id="filterStatus">
                <option value="all">All Statuses</option>
                <option value="ToDo">Not Started</option>
                <option value="InProgress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $total     = $projects->count();
        $inProg    = $projects->where('status','InProgress')->count();
        $completed = $projects->where('status','Completed')->count();
        $overdue   = $projects->filter(fn($p) => $p->status !== 'Completed' && $p->end_date && \Carbon\Carbon::parse($p->end_date)->isPast())->count();
    @endphp
    <div class="stats-row">
        <div class="stat-card blue">
            <div class="stat-num">{{ $total }}</div>
            <div class="stat-lbl">Total Projects</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-num">{{ $inProg }}</div>
            <div class="stat-lbl">In Progress</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num">{{ $completed }}</div>
            <div class="stat-lbl">Completed</div>
        </div>
        <div class="stat-card red">
            <div class="stat-num">{{ $overdue }}</div>
            <div class="stat-lbl">Overdue</div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="legend">
        <div class="legend-item"><span class="legend-dot todo"></span> Not Started</div>
        <div class="legend-item"><span class="legend-dot inprog"></span> In Progress</div>
        <div class="legend-item"><span class="legend-dot complete"></span> Completed</div>
        <div class="legend-item"><span class="legend-dot overdue"></span> Overdue</div>
    </div>

    {{-- Gantt Chart --}}
    <div class="gantt-wrapper">
        @if ($projects->isEmpty())
            <div class="empty-state">
                <i class="mdi mdi-chart-gantt"></i>
                <h5>No Projects Found</h5>
                <p>There are no active projects to display on the timeline.</p>
            </div>
        @else
            <div id="gantt"></div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<script>
(function () {
    const rawProjects = @json($projects);

    function statusColor(status, endDate) {
        const isOverdue = status !== 'Completed' && endDate && new Date(endDate) < new Date();
        if (isOverdue)       return '#fc8181';
        if (status === 'Completed')  return '#68d391';
        if (status === 'InProgress') return '#f6ad55';
        return '#90cdf4';
    }

    function buildTasks(filter) {
        return rawProjects
            .filter(p => filter === 'all' || p.status === filter)
            .map(p => {
                const start = p.act_start_date || p.start_date || new Date().toISOString().slice(0, 10);
                const end   = p.act_end_date   || p.end_date   || new Date().toISOString().slice(0, 10);
                const safeStart = start.slice(0, 10);
                const safeEnd   = end.slice(0, 10) <= safeStart
                    ? new Date(new Date(safeStart).getTime() + 86400000).toISOString().slice(0, 10)
                    : end.slice(0, 10);

                const done = p.tasks_count > 0
                    ? Math.round((p.completed_task_count / p.tasks_count) * 100)
                    : (p.status === 'Completed' ? 100 : 0);

                return {
                    id:           String(p.id),
                    name:         (p.clients?.name ? p.clients.name + ' — ' : '') + p.project_name,
                    start:        safeStart,
                    end:          safeEnd,
                    progress:     done,
                    custom_class: 'gantt-' + p.status.toLowerCase().replace(' ', '-'),
                    color:        statusColor(p.status, p.end_date),
                };
            });
    }

    let gantt = null;

    function initGantt(viewMode, filter) {
        const tasks = buildTasks(filter);
        if (!tasks.length) return;

        if (gantt) {
            gantt.change_view_mode(viewMode);
            gantt.refresh(tasks);
        } else {
            gantt = new Gantt('#gantt', tasks, {
                view_mode: viewMode,
                date_format: 'YYYY-MM-DD',
                bar_height: 30,
                bar_corner_radius: 5,
                arrow_curve: 5,
                padding: 18,
                on_click: function (task) {
                    const proj = rawProjects.find(p => String(p.id) === task.id);
                    if (proj) {
                        window.open('/projects/' + btoa(proj.id) + '/history', '_blank');
                    }
                },
                on_view_change: function (mode) {
                    document.getElementById('viewMode').value = mode;
                },
                custom_popup_html: function (task) {
                    const proj = rawProjects.find(p => String(p.id) === task.id);
                    if (!proj) return '';
                    return `
                        <div style="background:#fff;padding:12px 16px;border-radius:10px;min-width:220px;font-size:13px;">
                            <strong style="font-size:14px;">${task.name}</strong><br>
                            <span style="color:#718096;">${proj.status}</span><br><br>
                            <div>📅 ${task.start} → ${task.end}</div>
                            <div>⚡ Progress: <strong>${task.progress}%</strong></div>
                            <div>✅ Tasks: ${proj.completed_task_count ?? 0} / ${proj.tasks_count ?? 0}</div>
                        </div>`;
                }
            });
        }
    }

    // Initial render
    initGantt('Month', 'all');

    document.getElementById('viewMode').addEventListener('change', function () {
        initGantt(this.value, document.getElementById('filterStatus').value);
    });

    document.getElementById('filterStatus').addEventListener('change', function () {
        initGantt(document.getElementById('viewMode').value, this.value);
    });
})();
</script>
@endpush
