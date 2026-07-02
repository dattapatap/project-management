@extends('layouts.app')
@section('content')
<style>
    .highlight-task {
        border: 2px solid #556ee6 !important;
        box-shadow: 0 0 15px rgba(85, 110, 230, 0.4) !important;
        animation: highlight-pulse 2s infinite !important;
        z-index: 10;
        position: relative;
    }

    @keyframes highlight-pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 15px rgba(85, 110, 230, 0.4);
        }

        50% {
            transform: scale(1.02);
            box-shadow: 0 0 25px rgba(85, 110, 230, 0.6);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 15px rgba(85, 110, 230, 0.4);
        }
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center">
                    <a href="{{ url('/projects') }}" class="btn-back mr-3" style="text-decoration: none;">
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                    <h4 class="mb-0 font-weight-bold text-dark mr-3">{{ $project->project_name }}</h4>
                    <span class="badge badge-pill @if($project->status == 'ToDo') badge-danger @elseif($project->status == 'InProgress') badge-info @else badge-success @endif mr-3" style="padding: 6px 12px; font-size: 11px;">
                        {{ $project->status }}
                    </span>
                    @if($project->status != 'Completed' && Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']))
                    <button type="button" class="btn btn-sm btn-success btn_project_status px-3 ml-2" projectid="{{ $project->id }}" status="{{ $project->status }}">
                        <i class="mdi mdi-check-decagram mr-1"></i> Change Status
                    </button>
                    @endif
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">Projects</li>
                        <li class="breadcrumb-item active">Task Board</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>



    <div class="row">
        <div id="kanbanCustomBoard" class="js-kanban">
            <div class="kanban-container">
                <div class="kanban-board kanban-danger">

                    <header class="kanban-board-header kanban-danger">
                        <div class="kanban-title-board d-flex" style="justify-content: space-between">
                            <div class="kanban-title-content">
                                <h6 class="title">ToDo</h6>
                                <span class="count"> {{ count($todo) }}</span>
                            </div>
                            @if($project->status != 'Completed' && Auth::user()->hasRole(['Admin', 'Project-Manager', 'Team-Leader']))
                            <span projectid="{{ $project->id }}" class="todos-task-add add-task"><i class="mdi mdi-plus-outline"></i></span>
                            @endif
                        </div>
                    </header>

                    <main class="kanban-drag" data-status="ToDo">

                        @foreach ($todo as $items)
                        <div class="kanban-item task-body {{ request('task_id') == $items->id ? 'highlight-task' : '' }}" data-id="{{ $items->id }}" data-url="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}" style="cursor: pointer;">
                            <div class="kanban-item-title">
                                <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                    <h6 class="title c-p">
                                        @if( $items->priority == "Low")
                                        <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority"></i>
                                        @elseif( $items->priority == "Medium")
                                        <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority"></i>
                                        @else
                                        <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority"></i>
                                        @endif
                                        {{ Str::limit($items->title, 28) }}
                                    </h6>
                                </a>
                                @if($project->status != 'Completed')
                                <ul class="task-action">
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" class="tasklog" href="javascript:void(0);" title="Add Task Log">
                                            <i class="mdi mdi-clock-outline"></i>
                                        </a>
                                    </li>
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" currentstatus="{{ $items->status }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                            <i class="mdi mdi-arrow-left-right-bold"></i>
                                        </a>
                                    </li>
                                    @if(Auth::user()->hasRole(['Admin', 'Project-Manager', 'Team-Leader']))
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" class="edittask" href="javascript:void(0);" title="Edit Task">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                    </li>
                                    <li class="task-edit">
                                        <form method="post" action="{{ route('tasks.destroy',[$items->id]) }}" onsubmit="return confirmation();" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" href="javascript:void(0)" title="Delete Task">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                                @endif
                            </div>


                            <div class="kanban-item-text c-m">
                                {!! Str::limit($items->description, 120) !!}

                                <div class="task-schedule-time">
                                    <span class="">
                                        <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time"></i>
                                        {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                    </span>
                                    @if($items->status != 'COMPLETED')
                                    <span class="small light-danger-bg p-1 rounded" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="mdi mdi-clock-outline"></i>
                                        @if( \Carbon\Carbon::parse($items->enddate)->gt(\Carbon\Carbon::now()))
                                        {{ \Carbon\Carbon::parse($items->enddate)->diffForhumans(null, true) }} Left
                                        @else
                                        {{ \Carbon\Carbon::parse($items->enddate)->diffForhumans(null, true) }} Over
                                        @endif
                                    </span>
                                    @else
                                    <span class="small bg-success p-1 rounded">
                                        {{ $items->status }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="task-card-footer">
                                <div class="project-members">
                                    <div class="project-matrix-group-divs">
                                        <span class="project-metrics__metric-group-item__title project-matrix-group-items">
                                            Progress
                                        </span>
                                        <div class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $items->progress }}%"
                                                aria-valuenow="{{ $items->progress }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="project-matrix-group-items project-metrics__metric-group-item__value">
                                            {{ $items->progress }} %
                                        </span>
                                    </div>

                                    <ul class="project-users">
                                        <li class="cursor">
                                            @if ($items->user->profile)
                                            <img title="{{ $items->user->name }}" src="{{ asset('storage/'. $items->user->profile )}}">
                                            @else
                                            <img title="{{ $items->user->name }}" src="{{ Avatar::create($items->user->name)->toBase64()  }}">
                                            @endif

                                        </li>
                                    </ul>

                                </div>
                            </div>

                        </div>
                        @endforeach
                    </main>
                </div>
                {{-- INPROGRESS --}}
                <div class="kanban-board">
                    <header class="kanban-board-header kanban-primary">
                        <div class="kanban-title-board">
                            <div class="kanban-title-content">
                                <h6 class="title">In Progress</h6>
                                <span class="count">{{ count($inprocess) }}</span>
                            </div>
                        </div>
                    </header>
                    <main class="kanban-drag" data-status="InProgress">
                        @foreach ($inprocess as $items)
                        <div class="kanban-item task-body {{ request('task_id') == $items->id ? 'highlight-task' : '' }}" data-id="{{ $items->id }}" data-url="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}" style="cursor: pointer;">
                            <div class="kanban-item-title">
                                <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                    <h6 class="title c-p">
                                        @if( $items->priority == "Low")
                                        <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority"></i>
                                        @elseif( $items->priority == "Medium")
                                        <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority"></i>
                                        @else
                                        <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority"></i>
                                        @endif
                                        {{ Str::limit($items->title, 28) }}
                                    </h6>
                                </a>
                                <ul class="task-action">
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" class="tasklog" href="javascript:void(0);" title="Add Task Log">
                                            <i class="mdi mdi-clock-outline"></i>
                                        </a>
                                    </li>
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" currentstatus="{{ $items->status }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                            <i class="mdi mdi-arrow-left-right-bold"></i>
                                        </a>
                                    </li>
                                    @if(Auth::user()->hasRole(['Admin', 'Project-Manager', 'Team-Leader']))
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" class="edittask" href="javascript:void(0);" title="Edit Task">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </a>
                                    </li>
                                    <li class="task-edit">
                                        <form method="post" action="{{ route('tasks.destroy',[$items->id]) }}" onsubmit="return confirmation();" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" href="javascript:void(0)" title="Delete Task">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>


                            <div class="kanban-item-text c-m">
                                {!! Str::limit($items->description, 120) !!}

                                <div class="task-schedule-time">
                                    <span class="">
                                        <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time"></i>
                                        {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                    </span>
                                    <span class="small bg-info p-1 rounded text-white">
                                        {{ $items->status }}
                                    </span>
                                </div>
                            </div>
                            <div class="task-card-footer">
                                <div class="project-members">
                                    <div class="project-matrix-group-divs">
                                        <span class="project-metrics__metric-group-item__title project-matrix-group-items">
                                            Progress
                                        </span>
                                        <div class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $items->progress }}%"
                                                aria-valuenow="{{ $items->progress }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="project-matrix-group-items project-metrics__metric-group-item__value">
                                            {{ $items->progress }} %
                                        </span>
                                    </div>

                                    <ul class="project-users">
                                        <li class="cursor">
                                            @if ($items->user->profile)
                                            <img title="{{ $items->user->name }}" src="{{ asset('storage/'. $items->user->profile )}}">
                                            @else
                                            <img title="{{ $items->user->name }}" src="{{ Avatar::create($items->user->name)->toBase64()  }}">
                                            @endif

                                        </li>
                                    </ul>

                                </div>
                            </div>

                        </div>
                        @endforeach
                    </main>
                    <footer></footer>
                </div>


                {{-- COMPLETED --}}
                <div class="kanban-board">
                    <header class="kanban-board-header kanban-success">
                        <div class="kanban-title-board">
                            <div class="kanban-title-content">
                                <h6 class="title">Completed</h6>
                                <span class="count">{{ count($completed) }} </span>
                            </div>
                        </div>
                    </header>
                    <main class="kanban-drag" data-status="Completed">
                        @foreach ($completed as $items)
                        <div class="kanban-item task-body {{ request('task_id') == $items->id ? 'highlight-task' : '' }}" data-id="{{ $items->id }}" data-url="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}" style="cursor: pointer;">
                            <div class="kanban-item-title">
                                <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                    <h6 class="title c-p">
                                        @if( $items->priority == "Low")
                                        <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority"></i>
                                        @elseif( $items->priority == "Medium")
                                        <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority"></i>
                                        @else
                                        <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority"></i>
                                        @endif
                                        {{ Str::limit($items->title, 28) }}
                                    </h6>
                                </a>
                                @if($project->status != 'Completed')
                                <ul class="task-action">
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" class="tasklog" href="javascript:void(0);" title="Add Task Log">
                                            <i class="mdi mdi-clock-outline"></i>
                                        </a>
                                    </li>
                                    <li class="task-edit">
                                        <a taskid="{{ $items->id }}" currentstatus="{{ $items->status }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                            <i class="mdi mdi-arrow-left-right-bold"></i>
                                        </a>
                                    </li>
                                </ul>
                                @endif
                            </div>

                            <div class="kanban-item-text c-m">
                                {!! Str::limit($items->description, 120) !!}
                                <div class="task-schedule-time">
                                    <span class="">
                                        <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time"></i>
                                        {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                    </span>
                                    @if($items->status != 'Completed')
                                    <span class="small light-danger-bg  p-1 rounded">
                                        <i class="mdi mdi-clock-outline"></i>
                                        @if( \Carbon\Carbon::parse($items->enddate)->gt(\Carbon\Carbon::now()))
                                        {{ \Carbon\Carbon::parse($items->enddate)->diffForhumans(null, true) }} Left
                                        @else
                                        {{ \Carbon\Carbon::parse($items->enddate)->diffForhumans(null, true) }} Over
                                        @endif
                                    </span>
                                    @else
                                    <span class="small bg-success p-1 rounded text-white">
                                        {{ $items->status }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="task-card-footer">
                                <div class="project-members">
                                    <div class="project-matrix-group-divs">
                                        <span class="project-metrics__metric-group-item__title project-matrix-group-items">
                                            Progress
                                        </span>
                                        <div class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $items->progress }}%"
                                                aria-valuenow="{{ $items->progress }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="project-matrix-group-items project-metrics__metric-group-item__value">
                                            {{ $items->progress }} %
                                        </span>
                                    </div>

                                    <ul class="project-users">
                                        <li class="cursor">
                                            @if ($items->user->profile)
                                            <img title="{{ $items->user->name }}" src="{{ asset('storage/'. $items->user->profile )}}">
                                            @else
                                            <img title="{{ $items->user->name }}" src="{{ Avatar::create($items->user->name)->toBase64()  }}">
                                            @endif

                                        </li>
                                    </ul>

                                </div>
                            </div>

                        </div>
                        @endforeach
                    </main>
                    <footer></footer>
                </div>

            </div>
        </div>
    </div>
    <!-- end row -->
</div>
@endsection
@section('component')

@include('components.projects.components.projecttask')
@include('components.projects.components.changestatus')
@include('components.projects.components.tasklog')
@include('components.projects.components.edittask')
@include('components.projects.components.projectstatus')

@endsection

@section('scripts')
<script>
    window.WMS_USER = {
        is_management: {{ Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Team-Leader']) ? 'true' : 'false' }}
    };
</script>
<script src="{{ asset('assets/libs/draggable/Sortable.min.js') }}"></script>
<script src="{{ asset('js/kanban.js') }}?v={{ time() }}"></script>
<script>
    function confirmation() {
        if (confirm('Do you want to delete this task? it will not revert once deleted!')) {
            return true;
        } else {
            return false;
        }
    }

    $(document).ready(function() {
        $(document).on('click', '.kanban-item', function(e) {
            // Ignore click if user clicked on action buttons/links
            if ($(e.target).closest('.task-action, .changeStatus, .tasklog, a, button').length) {
                return;
            }
            let url = $(this).attr('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
</script>
@endsection
