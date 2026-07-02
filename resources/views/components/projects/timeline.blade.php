@extends('layouts.app')
@section('title', 'Project Gantt Timeline')
@section('styles')
<style>
    /* ── Page Shell ────────────────────────────────────────────── */
    .timeline-page { padding: 12px; }
    
    /* ── Legend ─────────────────────────────────────────────────── */
    .legend { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 16px; }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: .85rem; color: #475569; font-weight: 600; }
    .legend-dot { width: 12px; height: 12px; border-radius: 4px; }
    .legend-dot.todo     { background: #3b82f6; }
    .legend-dot.inprog   { background: #eab308; }
    .legend-dot.complete { background: #10b981; }
    .legend-dot.overdue  { background: #ef4444; }

    /* ── Timeline Cards ─────────────────────────────────────────── */
    .timeline-project-card {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03) !important;
    }
    .timeline-project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06) !important;
        border-color: #cbd5e1 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid erp-page pb-5">

    <div class="erp-page-header my-4">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">
                <i class="mdi mdi-chart-gantt mr-2 text-primary"></i>Project Gantt Timeline
            </h4>
            <p class="erp-page-subtitle">Track project schedules, progress, and milestones</p>
        </div>
        <div class="erp-page-header__actions d-flex align-items-center" style="gap: 8px;">
            <select id="filterStatus" class="form-control form-control-sm border" style="width: 170px; height: 35px;">
                <option value="all">All Statuses</option>
                <option value="ToDo">Not Started</option>
                <option value="InProgress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm" style="height: 35px; line-height: 23px;">
                <i class="mdi mdi-arrow-left"></i>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $total     = $projects->count();
        $inProg    = $projects->where('status','InProgress')->count();
        $completed = $projects->where('status','Completed')->count();
        $overdue   = $projects->filter(fn($p) => $p->status !== 'Completed' && $p->end_date && \Carbon\Carbon::parse($p->end_date)->isPast())->count();
    @endphp

    <!-- Stats Cards Grid -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border mb-3">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted font-weight-medium mb-1">Total Projects</p>
                        <h4 class="mb-0 text-dark font-weight-bold">{{ $total }}</h4>
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
                        <p class="text-muted font-weight-medium mb-1">In Progress</p>
                        <h4 class="mb-0 text-warning font-weight-bold">{{ $inProg }}</h4>
                    </div>
                    <div class="avatar-title rounded-circle bg-soft-warning text-warning" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="mdi mdi-progress-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border mb-3">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted font-weight-medium mb-1">Completed</p>
                        <h4 class="mb-0 text-success font-weight-bold">{{ $completed }}</h4>
                    </div>
                    <div class="avatar-title rounded-circle bg-soft-success text-success" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="mdi mdi-checkbox-marked-circle-outline"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border mb-3">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted font-weight-medium mb-1">Overdue</p>
                        <h4 class="mb-0 text-danger font-weight-bold">{{ $overdue }}</h4>
                    </div>
                    <div class="avatar-title rounded-circle bg-soft-danger text-danger" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="legend mb-4">
        <div class="legend-item"><span class="legend-dot todo"></span> Not Started</div>
        <div class="legend-item"><span class="legend-dot inprog"></span> In Progress</div>
        <div class="legend-item"><span class="legend-dot complete"></span> Completed</div>
        <div class="legend-item"><span class="legend-dot overdue"></span> Overdue</div>
    </div>

    {{-- Timeline Visual List (Fully fits in screen, responsive, modern) --}}
    <div class="gantt-wrapper p-0 border-0 bg-transparent">
        @if ($projects->isEmpty())
            <div class="card bg-white shadow-sm border p-5 text-center">
                <div class="empty-state">
                    <i class="mdi mdi-chart-gantt" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <h5 class="mt-3">No Projects Found</h5>
                    <p class="text-muted">There are no active projects to display on the timeline.</p>
                </div>
            </div>
        @else
            <div id="timeline-list">
                @foreach($projects as $p)
                    @php
                        $start = \Carbon\Carbon::parse($p->act_start_date ?? $p->start_date ?? now());
                        $end = \Carbon\Carbon::parse($p->act_end_date ?? $p->end_date ?? now());
                        $isOverdue = $p->status !== 'Completed' && $end->isPast();
                        
                        $completedTasks = $p->tasks->where('status', 'Completed')->count();
                        $totalTasks = $p->tasks->count();
                        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : ($p->status === 'Completed' ? 100 : 0);
                    @endphp
                    
                    <div class="card bg-white shadow-sm border mb-3 timeline-project-card" data-status="{{ $p->status }}" onclick="window.open('{{ url('/projects/'.base64_encode($p->id).'/history') }}', '_blank')" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <!-- Project Info -->
                                <div class="col-lg-3">
                                    <h5 class="font-weight-bold text-dark mb-1">{{ $p->project_name }}</h5>
                                    <p class="text-muted small mb-0"><i class="mdi mdi-domain mr-1"></i>{{ $p->clients->name ?? 'Internal Client' }}</p>
                                </div>
                                
                                <!-- Progress Bar & Status -->
                                <div class="col-lg-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge {{ $isOverdue ? 'bg-soft-danger text-danger' : ($p->status === 'Completed' ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning') }} font-weight-bold px-2.5 py-1">
                                            {{ $isOverdue ? 'Overdue' : $p->status }}
                                        </span>
                                        <span class="font-weight-bold text-dark font-size-13">{{ $progress }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                                        <div class="progress-bar {{ $p->status === 'Completed' ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                
                                <!-- Timeline Visualization (Fits on screen) -->
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center justify-content-between text-muted small mb-1">
                                        <span><i class="mdi mdi-calendar-play mr-1"></i>{{ $start->format('d M Y') }}</span>
                                        <span><i class="mdi mdi-calendar-check mr-1"></i>{{ $end->format('d M Y') }}</span>
                                    </div>
                                    <div class="position-relative" style="height: 28px; background: #f8fafc; border-radius: 6px; border: 1px solid #f1f5f9; overflow: hidden;">
                                        <!-- Colored Duration Strip representing duration -->
                                        <div class="position-absolute h-100" style="
                                            left: 0; 
                                            width: 100%; 
                                            background: {{ $isOverdue ? 'rgba(239, 68, 68, 0.1)' : ($p->status === 'Completed' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(59, 130, 246, 0.1)') }};
                                            border-left: 4px solid {{ $isOverdue ? '#ef4444' : ($p->status === 'Completed' ? '#10b981' : '#3b82f6') }};
                                        ">
                                            <div class="d-flex align-items-center h-100 px-3">
                                                <span class="font-weight-bold text-dark font-size-11">
                                                    {{ (int) $start->diffInDays($end) }} days total duration
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
(function () {
    // 🔄 Filter Change Handlers
    document.getElementById('filterStatus').addEventListener('change', function () {
        const selectedStatus = this.value;
        const cards = document.querySelectorAll('.timeline-project-card');
        
        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            if (selectedStatus === 'all' || status === selectedStatus) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
})();
</script>
@endsection
