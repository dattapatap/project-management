@extends('layouts.app')

@section('content')


    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Projects</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME') }}</a></li>
                            <li class="breadcrumb-item active">Project List</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mt-2 section-search-project">
                <div class="first-child">

                </div>
                <form class="form-inline" method="GET" action="{{ url('projects/search') }}">
                    <div class="input-group mt-3 mt-sm-0 mr-sm-3">
                        <input type="text" id="filter" class="form-control" name="search" style="width: 270px;"
                            placeholder="Project/Client" @if(isset($search)) value="{{ $search }}" @endif>
                        <div class="input-group-prepend">
                            <button type="submit" class="btn btn-primary mb-2">Search</button>
                        </div>
                    </div>
                </form>
                <a href="{{ url('projects/create') }}" type="button" class="btn btn-primary btn-md btn-rounded">
                    <i class="mdi mdi-plus"></i>
                </a>
            </div>
        </div>
        <hr>

        {{-- New Projects --}}
        <div class="row">
            @if (!$projects->isEmpty())
                @foreach ($projects as $item)
                    <div class="col-3">
                        <div class="card project-card">
                            <div class="card-body">
                                <div class="department">
                                    <div class="project-card-header">
                                        <a class="project-title-header"
                                            href="{{ url('projects/taskboard/' . base64_encode($item->id)) }}">
                                            <h5 class="project-title mt-1">
                                                {{ $item->project_name }}
                                            </h5>
                                            <span
                                                class="badge badge-pill
                                            @if($item->status == 'ToDo') badge-danger
                                            @elseif($item->status == 'InProgress') badge-info
                                            @else badge-success @endif">
                                                {{ $item->status }}
                                            </span>
                                        </a>
                                        <div class="btn-group float-right">
                                            <a href="#" class="dropdown-toggle arrow-none" data-bs-toggle="dropdown"
                                                aria-expanded="false" style="font-size: 21px;">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-start">

                                                <a href="{{ url('projects/taskboard/' . base64_encode($item->id)) }}"
                                                    class="dropdown-item btn_edit_department" dept_id="{{ $item->id }}">
                                                    <i class="mdi mdi-rocket"></i>Taskbar
                                                </a>

                                                <a class="dropdown-item btn_project_status" projectid="{{ $item->id }}">
                                                    <i class="mdi mdi-folder-outline"></i> Change Status
                                                </a>

                                                <a class="dropdown-item btn_assign_project"
                                                    projectid="{{ $item->id }}">
                                                    <i class="mdi mdi-account"></i> Assign To
                                                </a>

                                                <a class="dropdown-item btn_project_update"
                                                    projectid="{{ $item->id }}">
                                                    <i class="mdi mdi-update"></i> Add Update
                                                </a>
                                                <a class="dropdown-item btn_add_task" projectid="{{ $item->id }}">
                                                    <i class="mdi mdi-checkbox-marked-circle-outline"></i> Add Task
                                                </a>
                                                <a class="dropdown-item btn_edit_project"
                                                    projectid="{{ $item->id }}">
                                                    <i class="mdi mdi-pencil"></i> Edit
                                                </a>

                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="project-card-content">
                                        <a href="{{ url('clients/'. base64_encode($item->clients->id) .'/contacts')}}" target="_new">
                                            <h6 class="text-center">{{ $item->clients->name }}</h6>
                                        </a>
                                        <hr>
                                        <h6 class="mb-3">Total Tasks <span
                                                class="badge badge-pill badge-info">{{ $item->tasks->count() }}</span>
                                        </h6>
                                        <div class="project-matrix-group">

                                            <div class="project-matrix-group-divs">
                                                <span class="project-metrics__metric-group-item__title project-matrix-group-items">
                                                    Progress
                                                </span>
                                                @php
                                                    $totTasks = $item->tasks->count();
                                                    if($totTasks > 0){
                                                        $completedTask = $item->tasks->SUM('progress');
                                                        $compPerc = round(($completedTask / ($totTasks *100) ) * 100);
                                                    }else{
                                                        $compPerc = '0';
                                                    }
                                                @endphp

                                                <div
                                                    class="project-matrix-group-items project-metrics__metric-group-item__chart progress progress-sm">
                                                    <div class="progress-bar @if($compPerc > 50) bg-success @else bg-danger  @endif" role="progressbar"
                                                        style="width: {{ $compPerc }}%"
                                                        aria-valuenow="{{ $compPerc }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span
                                                    class="project-matrix-group-items project-metrics__metric-group-item__value">
                                                    {{ $compPerc }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="project-card-footer">
                                        <div class="project-members">

                                            @if($item->project_team)

                                                @if (!$item->tasks->isEmpty())
                                                    <ul class="project-users">
                                                        <?php $totCount = 0; ?>
                                                        @foreach ($item->tasks as $members)
                                                            @if ($totCount < 10)
                                                                <li>
                                                                    @if ($members->user->profile)
                                                                        <img title="{{ $members->user->name }}"
                                                                            src="{{ asset('storage/' . $members->user->profile) }}">
                                                                    @else
                                                                        <img title="{{$members->user->name}}"
                                                                            src="{{ Avatar::create($members->user->name)->toBase64() }}">
                                                                    @endif

                                                                </li>
                                                            @endif
                                                        @endforeach
                                                        @php
                                                            $count = $item->tasks->count();
                                                        @endphp
                                                        @if ($count > 10)
                                                            <li class="count">{{ $count - 10 }}+</li>
                                                        @endif
                                                    </ul>
                                                @else
                                                    <ul class="project-users">
                                                        <li class="cursor">
                                                            <img data-toggle="tooltip" data-placement="top"
                                                                aria-label="Assigned"
                                                                data-bs-original-title="Assigned"
                                                                src="{{ asset('img/users.png') }}">
                                                        </li>
                                                    </ul>
                                                @endif
                                            @else
                                                <ul class="project-users">
                                                    <span class="badge badge-danger">Not Assigned</span>
                                                </ul>
                                            @endif


                                            @if ($item->status != 'Completed')
                                                <span class="small light-danger-bg  p-1 rounded">
                                                    <i class="mdi mdi-clock-outline"></i>
                                                    @if (\Carbon\Carbon::parse($item->end_date)->gt(\Carbon\Carbon::now()))
                                                        {{ \Carbon\Carbon::parse($item->end_date)->diffForhumans(null, true) }}
                                                        Left
                                                    @else
                                                        {{ \Carbon\Carbon::parse($item->end_date)->diffForhumans(null, true) }}
                                                        Over
                                                    @endif
                                                </span>
                                            @else
                                                <span class="small bg-success p-1 rounded">
                                                    {{ $item->status }}
                                                </span>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-md-12">
                    <div class="text-center">
                        <div class="mb-3" style="position: relative;">
                            <img src="{{ asset('img/projects.jpg') }}" style="height: 100%;width: 25%;"
                                class="img-fluid rounded-circle" alt="">
                        </div>
                        <h3 class="text-truncate mb-2">You don't have any Projects.</h3> <br>
                        <h6 class="fs-15">
                            <a href="#" class="btnAddDepartment text-success"> Click </a>
                            to create new Project
                        </h6>
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('component')
    @include('components.projects.components.assigntoteam')
    @include('components.projects.components.editproject')
    @include('components.projects.components.projectupdate')
    @include('components.projects.components.projecttask')
    @include('components.projects.components.projectstatus')
@endsection

