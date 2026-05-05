@extends('layouts.app')
@section('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 8px 16px rgba(0, 0, 0, 0.1);
        --primary-gradient: linear-gradient(135deg, #556ee6 0%, #7b91ff 100%);
    }

    .task-details-container {
        padding: 20px 0;
    }

    /* Sidebar Cards */
    .sidebar-card {
        background: #ffffff;
        border-radius: 15px;
        border: 1px solid #f0f0f0;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .sidebar-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .sidebar-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #74788d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .sidebar-card-title i {
        margin-right: 8px;
        font-size: 16px;
        color: #556ee6;
    }

    /* Modern Progress Bar */
    .progress-premium {
        height: 10px;
        border-radius: 10px;
        background-color: #f0f2f8;
        overflow: visible;
        position: relative;
    }

    .progress-premium .progress-bar {
        border-radius: 10px;
        background: var(--primary-gradient);
        box-shadow: 0 2px 5px rgba(85, 110, 230, 0.3);
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -34px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #556ee6;
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px rgba(85, 110, 230, 0.1);
        z-index: 1;
    }

    .timeline-content {
        background: #fff;
        padding: 15px 20px;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        box-shadow: var(--shadow-sm);
    }

    /* Comment Styles */
    .comment-bubble {
        display: flex;
        margin-bottom: 25px;
    }

    .comment-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        margin-right: 15px;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }

    .comment-body {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 0 15px 15px 15px;
        flex-grow: 1;
        position: relative;
        border: 1px solid #edf2f9;
    }

    .comment-author {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        display: block;
    }

    /* Badges */
    .badge-soft-primary {
        background: rgba(85, 110, 230, 0.1);
        color: #556ee6;
    }

    .badge-soft-success {
        background: rgba(52, 195, 143, 0.1);
        color: #34c38f;
    }

    .badge-soft-danger {
        background: rgba(244, 106, 106, 0.1);
        color: #f46a6a;
    }

    .badge-soft-warning {
        background: rgba(241, 180, 76, 0.1);
        color: #f1b44c;
    }

    /* Main Content Card */
    .main-content-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #f0f0f0;
        padding: 0;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .card-premium-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        padding: 25px 30px;
        border-bottom: 1px solid #f0f0f0;
    }

    .card-premium-body {
        padding: 30px;
    }

    .stats-card-premium {
        background: #fcfdfe;
        border: 1px solid #edf2f9;
        border-radius: 15px;
        padding: 20px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .stats-card-premium:hover {
        border-color: #556ee6;
        background: #fff;
    }

    .stats-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 15px;
    }

    .task-description-box {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        border: 1px solid #edf2f9;
        color: #495057;
        font-size: 14.5px;
        line-height: 1.7;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center justify-content-between">
                    <a href="{{ url('/projects/taskboard/'.base64_encode($task->project->id)) }}" class="btn-back">
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/projects/taskboard/'.base64_encode($task->project->id)) }}">Taskboard</a></li>
                        <li class="breadcrumb-item active">Task History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="sidebar-card">
                <div class="sidebar-card-title mb-4">
                    <i class="mdi mdi-information-outline"></i> Task Information
                </div>

                <ul class="list-unstyled mb-0">
                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-calendar-range mr-1"></i> Timeline
                        </p>
                        <div class="font-weight-medium">
                            {{ \Carbon\Carbon::parse($task->startdate)->format('d M y') }} - {{ \Carbon\Carbon::parse($task->enddate)->format('d M y') }}
                        </div>
                    </li>

                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-account-star mr-1"></i> Assigned To
                        </p>
                        <div class="d-flex align-items-center">
                            @if ($task->user->profile)
                            <img class="rounded-circle mr-2" style="width: 30px; height: 30px;" src="{{ asset('storage/' . $task->user->profile) }}">
                            @else
                            <img class="rounded-circle mr-2" style="width: 30px; height: 30px;" src="{{ Avatar::create($task->user->name)->toBase64() }}">
                            @endif
                            <span class="font-weight-bold">{{ $task->user->name }}</span>
                        </div>
                    </li>

                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-flag-variant mr-1"></i> Priority
                        </p>
                        <span class="badge @if($task->priority == 'Low') badge-soft-success @elseif($task->priority == 'Medium') badge-soft-warning @else badge-soft-danger @endif px-3 py-2">
                            {{ $task->priority }}
                        </span>
                    </li>

                    @if ($task->act_startdate)
                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-calendar-check mr-1"></i> Actual Start
                        </p>
                        <div class="text-success font-weight-medium">
                            {{ \Carbon\Carbon::parse($task->act_startdate)->format('d M y') }}
                        </div>
                    </li>
                    @endif

                    <li class="mb-4">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-trending-up mr-1"></i> Task Progress
                        </p>
                        <div class="progress progress-premium mb-2">
                            <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Overall completion</span>
                            <span class="font-weight-bold text-primary">{{ $task->progress }}%</span>
                        </div>
                    </li>

                    @if ($task->status == 'Completed')
                    <li class="mb-4 p-3 rounded" style="background: rgba(52, 195, 143, 0.05); border-left: 3px solid #34c38f;">
                        <p class="text-success mb-1 small uppercase font-weight-bold">
                            <i class="mdi mdi-check-decagram mr-1"></i> Completed On
                        </p>
                        <div class="font-weight-bold text-success">
                            {{ \Carbon\Carbon::parse($task->act_enddate)->format('d M y') }}
                        </div>
                    </li>
                    @endif

                    <li class="mt-4 pt-4 border-top">
                        <p class="text-muted mb-2 small uppercase font-weight-bold">
                            <i class="mdi mdi-account-edit mr-1"></i> Created By
                        </p>
                        <div class="d-flex align-items-center">
                            @if ($task->createdby->profile)
                            <img class="rounded-circle mr-2" style="width: 24px; height: 24px;" src="{{ asset('storage/' . $task->createdby->profile) }}">
                            @else
                            <img class="rounded-circle mr-2" style="width: 24px; height: 24px;" src="{{ Avatar::create($task->createdby->name)->toBase64() }}">
                            @endif
                            <span class="text-muted small">{{ $task->createdby->name }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>


        <div class="col-md-9">
            <div class="main-content-card">
                <div class="card-premium-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-1 font-weight-bold text-dark">Task Details</h3>
                            <p class="text-muted mb-0 d-flex align-items-center">
                                <i class="mdi mdi-folder-outline mr-1"></i> {{ $task->project->project_name }}
                                <span class="mx-2 text-light">|</span>
                                <span class="text-primary font-weight-medium">#TASK-{{ $task->id }}</span>
                            </p>
                        </div>
                        <div>
                            @if ($task->status == 'ToDo')
                            <span class="badge badge-soft-danger px-3 py-2 font-size-13"><i class="mdi mdi-circle-outline mr-1"></i> {{ $task->status }}</span>
                            @elseif($task->status == 'InProgress')
                            <span class="badge badge-soft-warning px-3 py-2 font-size-13"><i class="mdi mdi-loading mdi-spin mr-1"></i> {{ $task->status }}</span>
                            @else
                            <span class="badge badge-soft-success px-3 py-2 font-size-13"><i class="mdi mdi-check-circle-outline mr-1"></i> {{ $task->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-premium-body">
                    <h4 class="mb-4 font-weight-bold">{{ $task->title }}</h4>

                    <div class="row mb-5">
                        <div class="col-md-4">
                            <div class="stats-card-premium">
                                <div class="stats-icon bg-soft-primary">
                                    <i class="mdi mdi-timer-outline text-primary"></i>
                                </div>
                                <p class="text-muted mb-1 small uppercase font-weight-bold">Total Time Spent</p>
                                <h3 class="mb-0 font-weight-bold text-dark">{{ $task->logs->sum('time_spend') }} <span class="font-size-14 text-muted font-weight-normal">hours</span></h3>
                            </div>
                        </div>
                        <div class="col-md-8">
                            @if ($task->status != 'Completed')
                            <div class="stats-card-premium">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <p class="text-muted mb-0 small uppercase font-weight-bold">Task Progress</p>
                                        <h5 class="mb-0 font-weight-bold text-primary task-progress-val">{{ $task->progress }}%</h5>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-primary shadow-sm tasklog px-3" taskid="{{ $task->id }}">
                                            <i class="mdi mdi-clock-outline mr-1"></i> Log Time
                                        </button>
                                        <button class="btn btn-sm btn-soft-primary changeStatus px-3" taskid="{{ $task->id }}">
                                            <i class="mdi mdi-arrow-left-right-bold mr-1"></i> Status
                                        </button>
                                    </div>
                                </div>
                                <input type="range" class="custom-range w-100" id="progress-range" min="0" max="100" value="{{ $task->progress }}" step="2">
                            </div>
                            @else
                            <div class="stats-card-premium" style="background: rgba(52, 195, 143, 0.05);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-soft-success mb-0 mr-3">
                                            <i class="mdi mdi-trophy-outline text-success"></i>
                                        </div>
                                        <div>
                                            <p class="text-success mb-0 small uppercase font-weight-bold">Task Status</p>
                                            <h5 class="mb-0 font-weight-bold text-success">Mission Accomplished!</h5>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-soft-success tasklog px-3" taskid="{{ $task->id }}">
                                        <i class="mdi mdi-clock-outline mr-1"></i> Add Final Log
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="task-description mb-5">
                        <h5 class="font-size-15 mb-3 d-flex align-items-center">
                            <i class="mdi mdi-text-subject mr-2 text-primary"></i>
                            <span class="font-weight-bold">Description</span>
                        </h5>
                        <div class="task-description-box">
                            {!! $task->description !!}
                        </div>
                    </div>

                    <div class="task-timeline-section mb-5">
                        <h5 class="font-size-15 mb-4 d-flex align-items-center">
                            <i class="mdi mdi-history mr-2 text-primary"></i>
                            <span class="font-weight-bold">Activity Log</span>
                        </h5>
                        <div class="timeline">
                            @forelse ($task->logs as $items)
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            @if ($items->user->profile)
                                            <img class="rounded-circle mr-2 border" style="width: 28px; height: 28px;" src="{{ asset('storage/' . $items->user->profile) }}">
                                            @else
                                            <img class="rounded-circle mr-2 border" style="width: 28px; height: 28px;" src="{{ Avatar::create($items->user->name)->toBase64() }}">
                                            @endif
                                            <span class="font-weight-bold text-dark">{{ $items->user->name }}</span>
                                        </div>
                                        <span class="badge badge-soft-light text-muted">{{ \Carbon\Carbon::parse($items->log_date)->format('d M, Y') }}</span>
                                    </div>
                                    <p class="mb-3 text-muted" style="font-size: 14px;">{{ $items->log_description }}</p>
                                    <div class="d-flex align-items-center bg-light p-2 rounded small text-muted">
                                        <i class="mdi mdi-clock-check-outline mr-1 text-primary"></i>
                                        {{ \Carbon\Carbon::parse($items->starttime)->format('h:i A') }} - {{ \Carbon\Carbon::parse($items->endtime)->format('h:i A') }}
                                        <span class="mx-3 text-light">|</span>
                                        <i class="mdi mdi-av-timer mr-1 text-success"></i>
                                        <span class="text-dark font-weight-bold">{{ intval($items->time_spend) .'h '.intval( ($items->time_spend - intval($items->time_spend)) * 60 ).'m' }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center p-5 bg-light rounded-xl">
                                <i class="mdi mdi-clipboard-text-outline display-4 text-muted d-block mb-3"></i>
                                <p class="text-muted mb-0">No activities recorded yet.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="task-comments-section pt-4 border-top">
                        <h5 class="font-size-15 mb-4 d-flex align-items-center">
                            <i class="mdi mdi-comment-multiple-outline mr-2 text-primary"></i>
                            <span class="font-weight-bold">Discussion</span>
                        </h5>

                        <div class="comments-list mb-4">
                            @foreach ($task->comments as $item)
                            <div class="comment-bubble">
                                @if ($item->user->profile)
                                <img class="comment-avatar border" src="{{ asset('storage/' . $item->user->profile) }}">
                                @else
                                <img class="comment-avatar border" src="{{ Avatar::create($item->user->name)->toBase64() }}">
                                @endif
                                <div class="comment-body shadow-none border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="comment-author text-dark">{{ $item->user->name }}</span>
                                        <span class="text-muted small"><i class="mdi mdi-clock-outline mr-1"></i>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 14px;">
                                        {{ $item->comment }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="comment-form bg-light p-4 rounded-xl border">
                            <div class="d-flex align-items-start">
                                @php $user = Auth::user(); @endphp
                                @if ($user->profile)
                                <img class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px;" src="{{ asset('storage/' . $user->profile) }}">
                                @else
                                <img class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px;" src="{{ Avatar::create($user->name)->toBase64() }}">
                                @endif
                                <div class="flex-grow-1">
                                    <form action="{{ url('projects/task/comment') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="task_id" value="{{ $task->id }}">
                                        <textarea name="task_comment" class="form-control border-0 bg-white shadow-sm" placeholder="Type your message here..." rows="3" required style="resize: none; border-radius: 12px;"></textarea>
                                        <div class="text-right mt-3">
                                            <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                                                Post Comment <i class="mdi mdi-send ml-1"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>
    <!-- end row -->
</div>
@endsection
@section('component')
@include('components.projects.components.changestatus')
@include('components.projects.components.tasklog')
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        $('#progress-range').change(function() {
            $('.task-progress-val').text($(this).val());
            $('.progress-bar').css('width', $(this).val() + '%');
            $('.project-metrics__metric-group-item__value').text($(this).val() + '%');

            $.ajax({
                type: 'post',
                url: base_url + '/projects/task/progress',
                data: {
                    'task_id': {
                        {
                            $task - > id
                        }
                    },
                    'progerss': $(this).val()
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success == true)
                        alertify.success(res.message);
                    else
                        alertify.error(res.message);
                },
                error: function(err) {
                    console.log(err);
                },
            })





        })


    })
</script>
@endsection
