@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div class="pb-2 d-flex align-items-center justify-content-between">
                        <a href="{{ url('/projects/taskboard/'.base64_encode($task->project->id)) }}" class="btn-back" >
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
            <div class="col-md-3 sidetask-board">
                <div class="sidebar__block">
                    <div class="sidebar__block-header">
                        <h3 class="sidebar__block-title"> Task Timeline </h3>
                    </div>
                    <div class="sidebar__block-content">
                        <i class="mdi mdi-clock-outline"></i>
                        {{ \Carbon\Carbon::parse($task->startdate)->format('d M y') }} To
                        {{ \Carbon\Carbon::parse($task->enddate)->format('d M y') }}
                    </div>
                </div>

                <div class="sidebar__block">
                    <div class="sidebar__block-header">
                        <h3 class="sidebar__block-title"> Assigned To </h3>
                    </div>
                    <div class="sidebar__block-content">
                        @if ($task->user->profile)
                            <img class="project-member" title="{{ $task->user->name }}"
                                src="{{ asset('storage/' . $task->user->profile) }}">
                        @else
                            <img class="project-member" title="{{ $task->user->name }}"
                                src="{{ Avatar::create($task->user->name)->toBase64() }}">
                        @endif
                        <span>
                            {{ $task->user->name }}
                        </span>
                    </div>
                </div>
                <div class="sidebar__block">
                    <div class="sidebar__block-header">
                        <h3 class="sidebar__block-title"> Task Priority </h3>
                    </div>
                    <div class="sidebar__block-content">
                        @if ($task->priority == 'Low')
                            <i class="mdi mdi-flag-variant text-success" title="{{ $task->priority }} Priority"></i>
                        @elseif($items->priority == 'Medium')
                            <i class="mdi mdi-flag-variant text-warning" title="{{ $task->priority }} Priority"></i>
                        @else
                            <i class="mdi mdi-flag-variant text-danger" title="{{ $task->priority }} Priority"></i>
                        @endif
                        <span> {{ $task->priority }} </span>
                    </div>
                </div>



                @if ($task->act_startdate)
                    <div class="sidebar__block">
                        <div class="sidebar__block-header">
                            <h3 class="sidebar__block-title"> Start Date </h3>
                        </div>
                        <div class="sidebar__block-content">
                            <i class="mdi mdi-calendar-month-outline"></i>
                            {{ \Carbon\Carbon::parse($task->act_startdate)->format('d M y') }}
                        </div>
                    </div>
                @endif

                <div class="sidebar__block">
                    <div class="sidebar__block-header">
                        <h3 class="sidebar__block-title"> Progress </h3>
                    </div>
                    <div class="sidebar__block-content">
                        <div
                            class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $task->progress }}%"
                                aria-valuemax="100">
                            </div>
                        </div>
                        <span class="project-matrix-group-items project-metrics__metric-group-item__value">
                            {{ $task->progress }}%
                        </span>
                    </div>
                </div>

                @if ($task->status == 'Completed')
                    <div class="sidebar__block">
                        <div class="sidebar__block-header">
                            <h3 class="sidebar__block-title"> Completed Date </h3>
                        </div>
                        <div class="sidebar__block-content">
                            <i class="mdi mdi-calendar-month-outline"></i>
                            {{ \Carbon\Carbon::parse($task->act_enddate)->format('d M y') }}
                        </div>
                    </div>
                @endif
                <div class="sidebar__block">
                    <div class="sidebar__block-header">
                        <h3 class="sidebar__block-title"> Created By </h3>
                    </div>
                    <div class="sidebar__block-content">
                        @if ($task->createdby->profile)
                            <img class="project-member" title="{{ $task->createdby->name }}"
                                src="{{ asset('storage/' . $task->createdby->profile) }}">
                        @else
                            <img class="project-member" title="{{ $task->createdby->name }}"
                                src="{{ Avatar::create($task->createdby->name)->toBase64() }}">
                        @endif
                        <span>
                            {{ $task->createdby->name }}
                        </span>
                    </div>
                </div>
            </div>


            <div class="col-md-9 taskhistory-board">
                <div class="task-header">
                    <div class="task-header">
                        <h4>Task : {{ $task->title }}</h4>
                    </div>
                    <hr>
                    <div class="task-detail">
                        <div class="task-detail-items">
                            <div class="task-items">
                                @if ($task->user->profile)
                                    <img class="" title="{{ $task->user->name }}"
                                        src="{{ asset('storage/' . $task->user->profile) }}">
                                @else
                                    <img class="" title="{{ $task->user->name }}"
                                        src="{{ Avatar::create($task->user->name)->toBase64() }}">
                                @endif
                                <span>
                                    {{ $task->user->name }}
                                </span>
                            </div>
                            <div class="task-items">
                                @if ($task->status == 'ToDo')
                                    Status : <span class="small badge badge-danger p-1"> {{ $task->status }}</span>
                                @elseif($task->status == 'InProgess')
                                    Status : <span class="small badge badge-warning p-1"> {{ $task->status }}</span>
                                @else
                                    Status : <span class="small badge badge-success p-1"> {{ $task->status }}</span>
                                @endif
                            </div>

                            <div class="task-items">
                                <span>Logs : {{ $task->logs->sum('time_spend') . 'h' }} </span>
                            </div>
                            @if ($task->status != 'Completed')
                                <div class="task-items c-p task-progress-change">
                                    <span class="task-progress-val">{{ $task->progress }} </span> %
                                    <ul class="task-progress-action">
                                        <input type="range" id="progress-range" name="progress-range" min="0"
                                            max="100" value="{{ $task->progress }}" step="2">
                                    </ul>
                                </div>
                            @endif

                        </div>

                    </div>
                    <hr>
                    <div class="task-logs">
                        <h4> Task Descriptions : </h4>
                        <span> {!! $task->description !!} </span>
                    </div>
                    <hr>
                    <div class="task-logs">
                        <h4> Task Logs : </h4>
                        <div class="logs">
                            <table class="table table-bordered table-striped mb-0">
                                <thead>
                                    <th>Log User</th>
                                    <th>Log Description</th>
                                    <th>Log Date</th>
                                    <th>From From </th>
                                    <th>Log To</th>
                                    <th>Time Spend</th>
                                </thead>
                                <tbody>
                                    @forelse ($task->logs as $items )
                                        <tr>
                                            <td>{{ $items->user->name }}</td>
                                            <td>{{ $items->log_description }}</td>
                                            <td>{{ \Carbon\Carbon::parse($items->log_date)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($items->starttime)->format('h:i A') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($items->endtime)->format('h:i A') }}</td>
                                            <td>{{ intval($items->time_spend) .'h '.intval( ($items->time_spend - intval($items->time_spend)) * 60 ).'m' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6"> Logs not added </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="task-comments">
                        <h4> Comments : </h4>
                        <br>
                        <div class="w-comment-sections">
                            @foreach ($task->comments as $item)
                                <div class="w-comment">
                                    @if ($item->user->profile)
                                        <img class="w-comment__avatar" title="{{ $item->user->name }}" src="{{ asset('storage/' . $item->user->profile) }}">
                                    @else
                                        <img class="w-comment__avatar" title="{{ $item->user->name }}" src="{{ Avatar::create($item->user->name)->toBase64() }}">
                                    @endif
                                    <div class="w-comment__details">
                                        <div class="w-comment__details-author">
                                            <a> {{ $item->user->name }}</a>
                                        </div>
                                        <div class="w-comment__details-content">
                                            <span>
                                                <div style="" class="h-typo-formatting ">
                                                    {{ $item->comment }}
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-comment__info">
                                        <div class="w-comment__info-group w-comment__info-group--actions">
                                            <div class="w-comment__info-date">
                                                <time title="Wed, Apr 12th 2023 10:32AM">
                                                    {{ \Carbon\Carbon::parse($item->created_at)->format('D, M d Y h:i A') }}
                                                </time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="w-comment">

                                @if ($task->user->profile)
                                    <img class="w-comment__avatar" title="{{ $user->name }}"
                                        src="{{ asset('storage/' . $user->profile) }}">
                                @else
                                    <img class="w-comment__avatar" title="{{ $user->name }}"
                                        src="{{ Avatar::create($user->name)->toBase64() }}">
                                @endif

                                <div class="w-comment__details">
                                    <form action="{{ url('projects/task/comment') }}" method="post">
                                        @csrf
                                        <input type="hidden" id="task_id" name="task_id" value="{{ $task->id }}">
                                        <textarea name="task_comment" id="task_comment" cols="30" rows="3"style="width:100%" required></textarea>
                                        <div class="w-comment__info mt-3">
                                            <button type="submit" class="btn btn-primary">Send</button>
                                        </div>
                                    </form>
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
                        'task_id': {{ $task->id }},
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
