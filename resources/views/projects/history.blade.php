@extends('layouts.app')

@section('styles')
<style>
    /* Premium Stabilized Design for History View */
    .sticky-header-fallback {
        position: sticky;
        top: 70px !important;
        /* Offset for fixed navbar */
        z-index: 999 !important;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .animate-slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .timeline-scroll {
        max-height: 700px;
        overflow-y: auto;
        padding-right: 10px;
        scrollbar-width: thin;
        scrollbar-color: #556ee6 rgba(85, 110, 230, 0.05);
    }

    .timeline-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .timeline-scroll::-webkit-scrollbar-track {
        background: rgba(85, 110, 230, 0.05);
        border-radius: 10px;
    }

    .timeline-scroll::-webkit-scrollbar-thumb {
        background: #556ee6;
        border-radius: 10px;
    }

    .timeline-scroll::-webkit-scrollbar-thumb:hover {
        background: #344ec5;
    }

    .card {
        border-radius: 12px !important;
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #556ee6 0%, #344ec5 100%);
    }

    .bg-light-soft {
        background-color: #f8f9fa;
    }

    .text-primary {
        color: #556ee6 !important;
    }

    .bg-soft-primary {
        background-color: rgba(85, 110, 230, 0.1);
        color: #556ee6;
    }

    .bg-soft-success {
        background-color: rgba(52, 195, 143, 0.1);
        color: #34c38f;
    }

    .bg-soft-danger {
        background-color: rgba(244, 106, 106, 0.1);
        color: #f46a6a;
    }

    .bg-soft-warning {
        background-color: rgba(241, 180, 76, 0.1);
        color: #f1b44c;
    }

    .timeline-dot.pulse {
        animation: dot-pulse 2s infinite;
    }

    @keyframes dot-pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(85, 110, 230, 0.4);
        }

        70% {
            transform: scale(1.1);
            box-shadow: 0 0 0 10px rgba(85, 110, 230, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(85, 110, 230, 0);
        }
    }
</style>
@endsection

@section('content')
<div class="project-history-page min-h-screen pb-5">
    <!-- Header Summary Bar (Sticky) -->
    <!-- Sub Header Below Topbar -->
    <div class="sticky-header-fallback px-4 py-2 border-bottom">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8 d-flex align-items-center">
                    <a href="{{ url('/projects') }}" class="btn btn-sm btn-light rounded-circle mr-3">
                        <i class="mdi mdi-arrow-left font-size-18"></i>
                    </a>
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">{{ $project->project_name }}</h5>
                        <p class="mb-0 text-muted small"><i class="mdi mdi-history mr-1"></i> Project History & Audit Trail</p>
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    @php
                    $totalTasks = $project->tasks->count();
                    $completedTasksCount = $project->tasks->where('status', 'Completed')->count();
                    $progress = $totalTasks > 0 ? round(($completedTasksCount / $totalTasks) * 100) : 0;
                    @endphp
                    <div class="d-none d-md-flex align-items-center">
                        <div class="text-right mr-3">
                            <div class="text-muted small font-weight-bold">OVERALL PROGRESS</div>
                            <div class="font-weight-bold text-primary">{{ $progress }}%</div>
                        </div>
                        <div class="progress rounded-pill" style="width: 100px; height: 6px; background-color: #f1f1f1;">
                            <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">

            <!-- LEFT SECTION (70%) -->
            <div class="col-xl-8 col-lg-7">

                <!-- 1. Project Overview Card -->
                <div class="card mb-4 shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-light border-bottom-0 py-3 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark" style="letter-spacing: 1px;">
                            <i class="mdi mdi-view-dashboard-outline text-primary mr-2"></i> Project Insights
                        </h6>
                        <span class="badge badge-soft-primary px-3 py-2 rounded-pill font-weight-bold uppercase">
                            {{ $project->status }}
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Client</label>
                                <p class="mb-0 font-weight-bold text-dark">{{ $project->clients->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Category</label>
                                <p class="mb-0 font-weight-bold text-dark">{{ $project->projectCategory->category ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Start Date</label>
                                <p class="mb-0 font-weight-bold text-dark">{{ \Carbon\Carbon::parse($project->start_date)->format('d M, Y') }}</p>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Deadline</label>
                                @php $isOverdue = \Carbon\Carbon::parse($project->end_date)->isPast() && $project->status != 'Completed'; @endphp
                                <p class="mb-0 font-weight-bold {{ $isOverdue ? 'text-danger' : 'text-dark' }}">
                                    {{ \Carbon\Carbon::parse($project->end_date)->format('d M, Y') }}
                                    @if($isOverdue) <i class="mdi mdi-alert-circle ml-1"></i> @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Duration</label>
                                @php
                                $end = $project->act_end_date ? \Carbon\Carbon::parse($project->act_end_date) : now();
                                $duration = (int) \Carbon\Carbon::parse($project->start_date)->diffInDays($end);
                                @endphp
                                <p class="mb-0 font-weight-bold text-dark">{{ $duration }} Days</p>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Hours</label>
                                <p class="mb-0 font-weight-bold text-primary">{{ number_format($project->total_working_hours, 1) }}h</p>
                            </div>
                            <div class="col-md-3 col-6 mt-3">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Completed Date</label>
                                <p class="mb-0 font-weight-bold text-dark">{{ $project->act_end_date ? \Carbon\Carbon::parse($project->act_end_date)->format('d M, Y') : '-' }}</p>
                            </div>
                            <div class="col-md-3 col-6 mt-3">
                                <label class="text-muted font-weight-bold text-uppercase mb-1 d-block" style="font-size: 10px; letter-spacing: 1px;">Created By</label>
                                <p class="mb-0 font-weight-bold text-dark">{{ $project->creator->name ?? 'System' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 10px;">Milestone Progress</span>
                                <span class="font-weight-bold text-primary">{{ $progress }}%</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 12px; background-color: #f1f1f1;">
                                <div class="progress-bar bg-gradient-primary rounded-pill shadow-sm" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Task Summary Card -->
                <div class="row">
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-primary p-2 rounded text-primary mr-2">
                                    <i class="mdi mdi-checkbox-multiple-marked-outline font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Total</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ $project->tasks->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-success p-2 rounded text-success mr-2">
                                    <i class="mdi mdi-check-decagram font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Completed</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ $project->tasks->where('status', 'Completed')->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-warning p-2 rounded text-warning mr-2">
                                    <i class="mdi mdi-clock-fast font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Pending</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ $project->tasks->whereIn('status', ['Pending', 'InProgress'])->count() }}</h4>
                        </div>
                    </div>
                    @php $overdueTasks = $project->tasks->filter(fn($t) => \Carbon\Carbon::parse($t->enddate)->isPast() && $t->status != 'Completed')->count(); @endphp
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card border-0 shadow-sm p-3 h-100 {{ $overdueTasks > 0 ? 'bg-soft-danger' : '' }}" style="border-radius: 12px;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-soft-danger p-2 rounded text-danger mr-2">
                                    <i class="mdi mdi-alert-circle-outline font-size-18"></i>
                                </div>
                                <span class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Overdue</span>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-danger">{{ $overdueTasks }}</h4>
                        </div>
                    </div>
                </div>

                <!-- 3. Task Details Section -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark">
                            <i class="mdi mdi-format-list-bulleted text-primary mr-2"></i> Task Analytics
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr class="text-uppercase font-weight-bold text-muted" style="font-size: 10px;">
                                        <th class="border-0 px-4">Task Information</th>
                                        <th class="border-0">Assignee</th>
                                        <th class="border-0">Timeline</th>
                                        <th class="border-0">Time Spend on that task</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0 text-right">Progress</th>
                                        <th class="border-0 text-center pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->tasks as $task)
                                    @php $isTOverdue = \Carbon\Carbon::parse($task->enddate)->isPast() && $task->status != 'Completed'; @endphp
                                    <tr class="{{ $isTOverdue ? 'table-danger' : '' }}">
                                        <td class="px-4">
                                            <div class="font-weight-bold text-dark">{{ $task->title }}</div>
                                            @php
                                            $tpColor = 'secondary';
                                            if(strtolower($task->priority) == 'high') $tpColor = 'danger';
                                            elseif(strtolower($task->priority) == 'medium') $tpColor = 'warning';
                                            elseif(strtolower($task->priority) == 'low') $tpColor = 'success';
                                            @endphp
                                            <span class="badge badge-soft-{{ $tpColor }} font-size-10">{{ $task->priority ?? 'Medium' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($task->user->name ?? 'U') }}&background=556ee6&color=fff" class="avatar-xs rounded-circle mr-2">
                                                <span class="font-size-12 text-muted">{{ $task->user->name ?? 'Unassigned' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="font-size-11">
                                                <div>Due: <span class="font-weight-bold {{ $isTOverdue ? 'text-danger' : '' }}">{{ \Carbon\Carbon::parse($task->enddate)->format('d M') }}</span></div>
                                                @if($task->act_enddate)
                                                <div class="text-success">End: {{ \Carbon\Carbon::parse($task->act_enddate)->format('d M') }}</div>
                                                @endif
                                            </div>
                                        </td>
                                            @php
                                            $totalMinutes = round(($task->total_time ?? 0) * 60);
                                            $h = floor($totalMinutes / 60);
                                            $m = $totalMinutes % 60;
                                            $timeSpentFormatted = $h > 0 ? sprintf('%02d:%02d Hrs', $h, $m) : sprintf('%02d:%02d min', $h, $m);
                                            @endphp
                                            <div class="font-weight-bold">{{ $timeSpentFormatted }}</div>
                                        </td>
                                        <td>
                                            @php
                                            $tColor = 'secondary';
                                            if($task->status == 'Completed') $tColor = 'success';
                                            elseif($task->status == 'InProgress') $tColor = 'primary';
                                            @endphp
                                            <span class="badge badge-soft-{{ $tColor }}">{{ $task->status }}</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="font-weight-bold mr-2">{{ $task->progress }}%</span>
                                                <div class="progress progress-sm" style="width: 50px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $task->progress }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center pr-4">
                                            @if(!auth()->user()->hasRole(['Developer', 'Designer', 'Seo-Developer', 'Accountant']))
                                            <div class="btn-group">
                                                <a href="{{ url('/projects/task/'.base64_encode($task->id).'/history') }}" class="btn btn-soft-primary btn-sm rounded-pill mr-1" title="View Details">
                                                    <i class="mdi mdi-eye-outline"></i>
                                                </a>
                                                <button type="button" class="btn btn-soft-warning btn-sm rounded-pill nudge-btn" data-task-id="{{ $task->id }}" title="Request Progress Update (Nudge)">
                                                    <i class="mdi mdi-bell-ring-outline"></i>
                                                </button>
                                            </div>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No tasks found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4. Project Documents Gallery -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark">
                            <i class="mdi mdi-attachment text-success mr-2"></i> Project Documents
                        </h6>
                        <span class="badge badge-light rounded-pill">{{ $project->documents->count() }} Files</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @forelse($project->documents as $doc)
                            @php
                            $ext = strtolower($doc->file_type);
                            $icon = 'mdi-file-outline';
                            $color = 'primary';
                            if(in_array($ext, ['jpg','jpeg','png','gif'])) { $icon = 'mdi-file-image'; $color = 'info'; }
                            elseif($ext == 'pdf') { $icon = 'mdi-file-pdf-box'; $color = 'danger'; }
                            elseif(in_array($ext, ['doc','docx'])) { $icon = 'mdi-file-word'; $color = 'primary'; }
                            @endphp
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center p-2 rounded border border-light bg-light-soft">
                                    <div class="avatar-sm mr-3">
                                        <span class="avatar-title rounded bg-soft-{{ $color }} text-{{ $color }} font-size-18">
                                            <i class="mdi {{ $icon }}"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="font-size-12 mb-1 text-truncate"><a href="{{ route('documents.download', $doc->id) }}" class="text-dark">{{ $doc->original_name }}</a></h5>
                                        <p class="text-muted font-size-10 mb-0">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-4 text-muted">No documents found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION (30%) -->
            <div class="col-xl-4 col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h6 class="mb-0 font-weight-bold text-uppercase text-dark">
                            <i class="mdi mdi-history text-primary mr-2"></i> Full Activity Trace
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline-scroll pr-2">
                            @php
                            $allHistories = $project->histories->merge($project->tasks->flatMap(function($task) {
                            return $task->histories;
                            }))->sortByDesc('created_at');
                            @endphp

                            @forelse($allHistories as $history)
                            <div class="position-relative pl-4 pb-4 border-left ml-2">
                                <!-- Dot -->
                                <div class="timeline-dot pulse position-absolute" style="left: -7px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #556ee6; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 2;"></div>

                                <div class="card border-0 shadow-none bg-light-soft mb-0 p-3 hover-lift" style="border-radius: 12px;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="badge badge-soft-primary px-2 py-1 font-size-10 text-uppercase">{{ $history->status ?? 'UPDATE' }}</div>
                                        <small class="text-muted font-weight-bold" style="font-size: 10px;">{{ $history->created_at->diffForHumans() }}</small>
                                    </div>

                                    <p class="mb-1 text-dark font-size-12 font-weight-medium">{{ $history->comments }}</p>

                                    <div class="d-flex align-items-center mt-2">
                                        <div class="avatar-xs mr-2" style="width: 20px; height: 20px;">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($history->user->name ?? 'S') }}&background=f1f1f1&color=333" class="rounded-circle img-fluid">
                                        </div>
                                        <span class="text-muted font-size-10">{{ $history->user->name ?? 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="mdi mdi-history text-muted font-size-24 d-block mb-2"></i>
                                <p class="text-muted small">No history records found.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inject Project Title into Main Navbar Header
        const projectTitle = "{{ $project->project_name }}";
        const titleHtml = `
            <div class="d-none d-lg-flex align-items-center ml-3">
                <div style="height: 20px; width: 1.5px; background: rgba(0,0,0,0.08); margin: 0 15px;"></div>
                <h4 class="mb-0 font-weight-bold text-dark" style="font-size: 15px; letter-spacing: 0.5px;">
                    ${projectTitle}
                    <span class="badge badge-soft-primary ml-2 px-2" style="font-size: 9px; vertical-align: middle; background-color: rgba(85, 110, 230, 0.1); color: #556ee6;">HISTORY</span>
                </h4>
            </div>
        `;

        // Append after the vertical menu button if it exists
        if ($('#vertical-menu-btn').length) {
            $('#vertical-menu-btn').after(titleHtml);
        }

        // Nudge functionality
        $('.nudge-btn').on('click', function() {
            const btn = $(this);
            const taskId = btn.data('task-id');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i>');

            $.ajax({
                url: "{{ url('/projects/tasks/nudge') }}/" + taskId,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error("Failed to send nudge. Please try again.");
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
@endsection
