@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div class="pb-2 d-flex align-items-center justify-content-between">
                        <a href="{{ url('/projects') }}" class="btn-back" >
                            <i class="mdi mdi-keyboard-backspace fs-20"></i>
                        </a>
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
                                <span projectid="{{ $project->id }}" class="todos-task-add add-task"><i class="mdi mdi-plus-outline"></i></span>
                            </div>
                        </header>

                        <main class="kanban-drag">

                            @foreach ($todo as $items)
                                <div class="kanban-item task-body">
                                    <div class="kanban-item-title">
                                        <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                            <h6 class="title c-p">
                                                @if( $items->priority == "Low")
                                                    <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority" ></i>
                                                @elseif( $items->priority == "Medium")
                                                    <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority" ></i>
                                                @else
                                                    <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority" ></i>
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
                                                <a taskid="{{ $items->id }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                                    <i class="mdi mdi-arrow-left-right-bold"></i>
                                                </a>
                                            </li>
                                            <li class="task-edit">
                                                <a taskid="{{ $items->id }}" class="edittask" href="javascript:void(0);" title="Change Task Status">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                </a>
                                            </li>
                                            <li class="task-edit">
                                                <form method="post" action="{{ route('tasks.destroy',[$items->id]) }}" onsubmit="return confirmation();"  style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" href="javascript:void(0)" title="Delete Task">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>


                                    <div class="kanban-item-text c-m">
                                        {!! Str::limit($items->description, 120) !!}

                                        <div class="task-schedule-time" >
                                            <span class="">
                                                <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time" ></i>
                                                {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                            </span>
                                            @if($items->status != 'COMPLETED')
                                            <span class="small light-danger-bg  p-1 rounded">
                                                    <i class="mdi mdi-clock-outline"></i>
                                                        @if(  \Carbon\Carbon::parse($items->enddate)->gt(\Carbon\Carbon::now()))
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
                                            <div class="project-matrix-group-divs" >
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
                        <main class="kanban-drag">
                            @foreach ($inprocess as $items)
                                <div class="kanban-item task-body">
                                    <div class="kanban-item-title">
                                        <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                            <h6 class="title c-p">
                                                @if( $items->priority == "Low")
                                                    <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority" ></i>
                                                @elseif( $items->priority == "Medium")
                                                    <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority" ></i>
                                                @else
                                                    <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority" ></i>
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
                                                <a taskid="{{ $items->id }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                                    <i class="mdi mdi-arrow-left-right-bold"></i>
                                                </a>
                                            </li>
                                            <li class="task-edit">
                                                <a taskid="{{ $items->id }}" class="edittask" href="javascript:void(0);" title="Change Task Status">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                </a>
                                            </li>
                                            <li class="task-edit">
                                                <form method="post" action="{{ route('tasks.destroy',[$items->id]) }}" onsubmit="return confirmation();"  style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" href="javascript:void(0)" title="Delete Task">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>


                                    <div class="kanban-item-text c-m">
                                        {!! Str::limit($items->description, 120) !!}

                                        <div class="task-schedule-time" >
                                            <span class="">
                                                <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time" ></i>
                                                {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                            </span>
                                            <span class="small bg-info p-1 rounded text-white">
                                                {{ $items->status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-card-footer">
                                        <div class="project-members">
                                            <div class="project-matrix-group-divs" >
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
                        <main class="kanban-drag">
                            @foreach ($completed as $items)
                                <div class="kanban-item task-body">
                                    <div class="kanban-item-title">
                                        <a href="{{ url('projects/task/'. base64_encode($items->id) .'/history')}}">
                                            <h6 class="title c-p">
                                                @if( $items->priority == "Low")
                                                    <i class="mdi mdi-flag-variant text-success" title="{{ $items->priority }} Priority" ></i>
                                                @elseif( $items->priority == "Medium")
                                                    <i class="mdi mdi-flag-variant text-warning" title="{{ $items->priority }} Priority" ></i>
                                                @else
                                                    <i class="mdi mdi-flag-variant text-danger" title="{{ $items->priority }} Priority" ></i>
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
                                                <a taskid="{{ $items->id }}" href="javascript:void(0);" class="changeStatus" title="Change Task Status">
                                                    <i class="mdi mdi-arrow-left-right-bold"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="kanban-item-text c-m">
                                        {!! Str::limit($items->description, 120) !!}
                                        <div class="task-schedule-time" >
                                            <span class="">
                                                <i class="mdi mdi-calendar-month-outline" title="Task Scheduled Time" ></i>
                                                {{ \Carbon\Carbon::parse($items->startdate)->format('d M y') }} To {{ \Carbon\Carbon::parse($items->enddate)->format('d M y') }}
                                            </span>
                                            @if($items->status != 'Completed')
                                            <span class="small light-danger-bg  p-1 rounded">
                                                    <i class="mdi mdi-clock-outline"></i>
                                                        @if(  \Carbon\Carbon::parse($items->enddate)->gt(\Carbon\Carbon::now()))
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
                                            <div class="project-matrix-group-divs" >
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

@endsection

@section('scripts')
    <script>
        function confirmation(){
            if(confirm('Do you want to delete this task? it will not revert once deleted!')){
               return true;
            }else{
                return false;
            }
        }
    </script>
@endsection
