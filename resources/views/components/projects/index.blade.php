@extends('layouts.app')

@section('content')

<div class="container-fluid erp-page erp-page--od">


    @if(isset($stats))
    <div class="row mb-4">
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ url('projects/search?search=Near+Deadline' . (isset($department) ? '&department='.$department : '')) }}" class="text-decoration-none">
                <div class="card project-kpi-card gradient-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="kpi-label mb-1">Deadlines Near (7 Days)</p>
                                <h4 class="kpi-value mb-0">{{ $stats['near_deadline'] }}</h4>
                            </div>
                            <div class="project-kpi-icon-box">
                                <i class="mdi mdi-alert-decagram text-white font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ url('projects?status=ToDo' . (isset($department) ? '&department='.$department : '')) }}" class="text-decoration-none">
                <div class="card project-kpi-card gradient-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="kpi-label mb-1">Not Started</p>
                                <h4 class="kpi-value mb-0">{{ $stats['not_started'] }}</h4>
                            </div>
                            <div class="project-kpi-icon-box">
                                <i class="mdi mdi-playlist-plus text-white font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ url('projects?status=InProgress' . (isset($department) ? '&department='.$department : '')) }}" class="text-decoration-none">
                <div class="card project-kpi-card gradient-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="kpi-label mb-1">In Progress</p>
                                <h4 class="kpi-value mb-0">{{ $stats['in_progress'] }}</h4>
                            </div>
                            <div class="project-kpi-icon-box">
                                <i class="mdi mdi-progress-clock text-white font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ url('projects?status=Completed' . (isset($department) ? '&department='.$department : '')) }}" class="text-decoration-none">
                <div class="card project-kpi-card gradient-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="kpi-label mb-1">Completed</p>
                                <h4 class="kpi-value mb-0">{{ $stats['completed'] }}</h4>
                            </div>
                            <div class="project-kpi-icon-box">
                                <i class="mdi mdi-check-decagram text-white font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ url('projects?status=all' . (isset($department) ? '&department='.$department : '')) }}" class="text-decoration-none">
                <div class="card project-kpi-card gradient-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="kpi-label mb-1">Total Projects</p>
                                <h4 class="kpi-value mb-0">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="project-kpi-icon-box">
                                <i class="mdi mdi-folder-multiple-outline text-white font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif

    <div class="project-header-section">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="d-flex align-items-center mb-1">
                    <a href="{{ url('/') }}" class="btn btn-outline-primary btn-sm mr-2 d-inline-flex align-items-center">
                        <i class="mdi mdi-arrow-left mr-1"></i> Back
                    </a>
                    <h4 class="erp-page-title mb-0">Explore Projects</h4>
                </div>
                <p class="text-muted mb-0 font-size-12">Manage and track active workflows</p>
            </div>
            <div class="col-md-5">
                <form class="m-0" method="GET" action="{{ url('projects/search') }}">
                    <input type="hidden" name="status" value="{{ request('status', 'Pending') }}">
                    <div class="d-flex">
                        @if(Auth::user()->hasRole(['Admin', 'Branch-Manager']))
                        <select name="department" class="form-control mr-2" onchange="this.form.submit()" style="height: 38px; border-radius: 8px; border: 1px solid #e2e8f0; font-weight: 500; font-size: 13px; color: #4a5568; width: 170px;">
                            <option value="">All Departments</option>
                            <option value="1" {{ (isset($department) && $department == 1) ? 'selected' : '' }}>NSD (Sales)</option>
                            <option value="2" {{ (isset($department) && $department == 2) ? 'selected' : '' }}>OD (Operations)</option>
                            <option value="3" {{ (isset($department) && $department == 3) ? 'selected' : '' }}>CSD (Customer Service)</option>
                        </select>
                        @endif
                        <div class="input-group">
                            <input type="text" id="filter" class="form-control search-input-trendy" name="search"
                                placeholder="Search by project or client..." @if(isset($search)) value="{{ $search }}" @endif>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary px-4 erp-btn-search">
                                    <i class="mdi mdi-magnify mr-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-right">
                <div class="btn-group mr-3">
                    <button type="button" id="btn-grid-view" class="btn btn-outline-primary btn-sm active"><i class="mdi mdi-view-grid mr-1"></i> Grid</button>
                    <button type="button" id="btn-list-view" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-view-list mr-1"></i> List</button>
                </div>
                <a href="{{ url('projects/create') }}" class="btn btn-primary btn-md">
                    <i class="mdi mdi-plus-circle mr-1"></i> Create Project
                </a>
            </div>
        </div>
    </div>
    <hr>

    {{-- New Projects --}}
    <div id="project-grid-view" class="row">
        @if (!$projects->isEmpty())
        @foreach ($projects as $item)
        @php
        $isNearDeadline = false;
        if ($item->status != 'Completed' && $item->end_date) {
        $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($item->end_date), false);
        if ($daysLeft <= 7) $isNearDeadline=true;
            }
            $statusKey=strtolower(trim($item->status));
            $statusBorderClass = 'erp-status-border-default';
            if ($statusKey == 'todo') $statusBorderClass = 'erp-status-border-todo';
            elseif ($statusKey == 'inprogress') $statusBorderClass = 'erp-status-border-inprogress';
            elseif ($statusKey == 'completed') $statusBorderClass = 'erp-status-border-completed';
            @endphp
            <div class="col-3">
                <div class="card project-card erp-project-card {{ $statusBorderClass }} @if($isNearDeadline) erp-project-card--urgent shadow-lg @endif">
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
                                    <a href="#" class="dropdown-toggle arrow-none erp-dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-start">

                                        <a href="{{ url('projects/taskboard/' . base64_encode($item->id)) }}"
                                            class="dropdown-item">
                                            <i class="mdi mdi-rocket mr-2"></i>Taskboard
                                        </a>

                                        <a href="javascript:void(0);" class="dropdown-item btn_project_status" projectid="{{ $item->id }}" status="{{ $item->status }}">
                                            <i class="mdi mdi-folder-outline mr-2"></i>Change Status
                                        </a>

                                        @if($item->status != 'Completed' && !$item->assigned_to)
                                        @if(!$item->project_team)
                                        <a href="javascript:void(0);" class="dropdown-item btn_assign_project"
                                            projectid="{{ $item->id }}">
                                            <i class="mdi mdi-account-group mr-2"></i>Assign To Team
                                        </a>
                                        @endif

                                        @if($item->project_team)
                                        @if($user->hasRole('Team-Leader'))
                                        <a href="javascript:void(0);" class="dropdown-item btn_assign_to_me"
                                            projectid="{{ $item->id }}">
                                            <i class="mdi mdi-account-check mr-2"></i>Assign to Me
                                        </a>
                                        @endif

                                        @if($user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager']))
                                        <a href="javascript:void(0);" class="dropdown-item btn_assign_to_tl"
                                            projectid="{{ $item->id }}"
                                            categoryid="{{ $item->category }}">
                                            <i class="mdi mdi-account-star mr-2"></i>Assign to TL
                                        </a>
                                        @endif
                                        @endif
                                        @endif

                                        @if($item->status != 'Completed')
                                        <a href="javascript:void(0);" class="dropdown-item btn_project_update"
                                            projectid="{{ $item->id }}">
                                            <i class="mdi mdi-update mr-2"></i>Add Update
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item btn_add_task" projectid="{{ $item->id }}">
                                            <i class="mdi mdi-checkbox-marked-circle-outline mr-2"></i>Add Task
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item btn_edit_project"
                                            projectid="{{ $item->id }}">
                                            <i class="mdi mdi-pencil mr-2"></i>Edit
                                        </a>
                                        @endif
                                        <a class="dropdown-item" href="{{ url('projects/' . base64_encode($item->id) . '/history') }}">
                                            <i class="mdi mdi-history mr-2"></i>History
                                        </a>

                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="project-card-content">
                                @if($user->hasRole(['Admin', 'Project-Manager']))
                                <a href="{{ url('clients/'. base64_encode($item->clients->id) .'/contacts')}}" target="_new">
                                    <h6 class="text-center">{{ $item->clients->name }}</h6>
                                </a>
                                @else
                                <h6 class="text-center text-muted">{{ $item->clients->name }}</h6>
                                @endif
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
                                <div class="mt-3 text-center">
                                    @if($item->status == 'Completed')
                                    <small class="text-success font-weight-bold"><i class="mdi mdi-calendar-check"></i> Completed: {{ $item->act_end_date ? \Carbon\Carbon::parse($item->act_end_date)->format('d M Y') : \Carbon\Carbon::parse($item->updated_at)->format('d M Y') }}</small>
                                    @elseif($item->end_date)
                                    <small class="text-danger font-weight-bold"><i class="mdi mdi-calendar-clock"></i> Deadline: {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</small>
                                    @else
                                    <small class="text-muted"><i class="mdi mdi-calendar-blank"></i> No deadline set</small>
                                    @endif
                                </div>
                            </div>
                            <hr>
                            <div class="project-card-footer">
                                <div class="project-members">

                                    @if($item->project_team && $item->project_team->team)
                                    <div class="mt-2 text-center w-100">
                                        <small class="text-muted"><i class="mdi mdi-account-group"></i> Team: <strong>{{ $item->project_team->team->name }}</strong> ({{ $item->project_team->team->teammembers->count() }} Members)</small>
                                    </div>
                                    <ul class="project-users mt-2">
                                        <?php $totCount = 0; ?>
                                        @foreach ($item->tasks->unique('assigned_to') as $taskMember)
                                        @if ($totCount < 10)
                                            <li>
                                            @if ($taskMember->user->profile)
                                            <img title="{{ $taskMember->user->name }}" src="{{ asset('storage/' . $taskMember->user->profile) }}">
                                            @else
                                            <img title="{{$taskMember->user->name}}" src="{{ Avatar::create($taskMember->user->name)->toBase64() }}">
                                            @endif
                                            </li>
                                            <?php $totCount++; ?>
                                            @endif
                                            @endforeach
                                            @php $count = $item->tasks->count(); @endphp
                                            @if ($count > 10)
                                            <li class="count">{{ $count - 10 }}+</li>
                                            @endif
                                    </ul>
                                    @else
                                    <ul class="project-users">
                                        <span class="badge badge-danger">Not Assigned</span>
                                    </ul>
                                    @endif


                                    @if ($item->status != 'Completed')
                                    <span class="small light-danger-bg p-1 rounded" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="mdi mdi-clock-outline"></i>
                                        @if (\Carbon\Carbon::parse($item->end_date)->gt(\Carbon\Carbon::now()))
                                        {{ \Carbon\Carbon::parse($item->end_date)->diffForhumans(null, true) }} Left
                                        @else
                                        {{ \Carbon\Carbon::parse($item->end_date)->diffForhumans(null, true) }} Over
                                        @endif
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

    {{-- List View Projects --}}
    <div id="project-list-view" class="row" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Project Name</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Timeline</th>
                                    <th>Team</th>
                                    <th>Tasks</th>
                                    <th>Progress</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projects as $item)
                                @php
                                $isNearDeadline = false;
                                if ($item->status != 'Completed' && $item->end_date) {
                                $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($item->end_date), false);
                                if ($daysLeft <= 7) $isNearDeadline=true;
                                    }
                                    @endphp
                                    <tr @if($isNearDeadline) class="table-danger" @endif style="border-left: 5px solid @if(strtolower(trim($item->status)) == 'todo') #f46a6a @elseif(strtolower(trim($item->status)) == 'inprogress') #50a5f1 @elseif(strtolower(trim($item->status)) == 'completed') #34c38f @else #556ee6 @endif !important;">
                                    <td>
                                        <a href="{{ url('projects/taskboard/' . base64_encode($item->id)) }}" class="text-body font-weight-bold">{{ $item->project_name }}</a>
                                    </td>
                                    <td>{{ $item->clients->name }}</td>
                                    <td>
                                        <span class="badge badge-pill @if($item->status == 'ToDo') badge-danger @elseif($item->status == 'InProgress') badge-info @else badge-success @endif">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->status == 'Completed')
                                        <span class="text-success font-weight-bold" style="font-size: 12px;"><i class="mdi mdi-calendar-check"></i> {{ $item->act_end_date ? \Carbon\Carbon::parse($item->act_end_date)->format('d M Y') : \Carbon\Carbon::parse($item->updated_at)->format('d M Y') }}</span>
                                        @elseif($item->end_date)
                                        <span class="text-danger font-weight-bold" style="font-size: 12px;"><i class="mdi mdi-calendar-clock"></i> {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</span>
                                        @else
                                        <span class="text-muted" style="font-size: 12px;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->project_team && $item->project_team->team)
                                        {{ $item->project_team->team->name }} <br><small class="text-muted">({{ $item->project_team->team->teammembers->count() }} Members)</small>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>{{ $item->tasks->count() }} Tasks</td>
                                    <td>
                                        @php
                                        $totTasks = $item->tasks->count();
                                        $compPerc = 0;
                                        if($totTasks > 0){
                                        $completedTask = $item->tasks->SUM('progress');
                                        $compPerc = round(($completedTask / ($totTasks *100) ) * 100);
                                        }
                                        @endphp
                                        <div class="progress progress-sm" style="width: 100px;">
                                            <div class="progress-bar @if($compPerc > 50) bg-success @else bg-danger @endif" role="progressbar" style="width: {{ $compPerc }}%" aria-valuenow="{{ $compPerc }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small>{{ $compPerc }}% Completed</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="#" class="dropdown-toggle arrow-none" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 18px;">
                                                <i class="mdi mdi-dots-horizontal"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ url('projects/taskboard/' . base64_encode($item->id)) }}" class="dropdown-item"><i class="mdi mdi-rocket mr-2"></i>Taskboard</a>
                                                <a class="dropdown-item btn_project_status" projectid="{{ $item->id }}" status="{{ $item->status }}"><i class="mdi mdi-folder-outline mr-2"></i>Change Status</a>
                                                @if($item->status != 'Completed' && !$item->assigned_to)
                                                @if(!$item->project_team)
                                                <a class="dropdown-item btn_assign_project" projectid="{{ $item->id }}"><i class="mdi mdi-account-group mr-2"></i>Assign To Team</a>
                                                @endif

                                                @if($item->project_team)
                                                @if($user->hasRole('Team-Leader'))
                                                <a class="dropdown-item btn_assign_to_me" projectid="{{ $item->id }}"><i class="mdi mdi-account-check mr-2"></i>Assign to Me</a>
                                                @endif

                                                @if($user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager']))
                                                <a class="dropdown-item btn_assign_to_tl" projectid="{{ $item->id }}" categoryid="{{ $item->category }}"><i class="mdi mdi-account-star mr-2"></i>Assign to TL</a>
                                                @endif
                                                @endif
                                                @endif
                                                @if($item->status != 'Completed')
                                                <a class="dropdown-item btn_project_update" projectid="{{ $item->id }}"><i class="mdi mdi-update mr-2"></i>Add Update</a>
                                                <a class="dropdown-item btn_add_task" projectid="{{ $item->id }}"><i class="mdi mdi-checkbox-marked-circle-outline mr-2"></i>Add Task</a>
                                                <a class="dropdown-item btn_edit_project" projectid="{{ $item->id }}"><i class="mdi mdi-pencil mr-2"></i>Edit</a>
                                                @endif
                                                <a class="dropdown-item" href="{{ url('projects/' . base64_encode($item->id) . '/history') }}"><i class="mdi mdi-history mr-2"></i>History</a>
                                            </div>
                                        </div>
                                    </td>
                                    </tr>
                                    @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 d-flex justify-content-center">
            {{ $projects->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>
@endsection

@section('component')
@include('components.projects.components.assigntoteam')
@include('components.projects.components.assigntotl')
@include('components.projects.components.editproject')
@include('components.projects.components.projectupdate')
@include('components.projects.components.projectstatus')
@include('components.projects.components.projecthistory')
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Load saved view preference from localStorage
        var savedView = localStorage.getItem('project_view_preference');
        if (savedView === 'list') {
            $('#btn-list-view').addClass('active btn-primary').removeClass('btn-outline-primary');
            $('#btn-grid-view').removeClass('active btn-primary').addClass('btn-outline-primary');
            $('#project-grid-view').hide();
            $('#project-list-view').show();
        }

        $('#btn-grid-view').click(function() {
            localStorage.setItem('project_view_preference', 'grid');
            $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btn-list-view').removeClass('active btn-primary').addClass('btn-outline-primary');
            $('#project-list-view').hide();
            $('#project-grid-view').show();
        });

        $('#btn-list-view').click(function() {
            localStorage.setItem('project_view_preference', 'list');
            $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btn-grid-view').removeClass('active btn-primary').addClass('btn-outline-primary');
            $('#project-grid-view').hide();
            $('#project-list-view').show();
        });

        // Assign to Me (Team Leader)
        $(document).on('click', '.btn_assign_to_me', function() {
            let projectid = $(this).attr('projectid');
            alertify.confirm("Are you sure you want to assign this project to yourself?", function() {
                $.ajax({
                    type: 'POST',
                    url: base_url + '/projects/assign-to-tl',
                    data: {
                        _token: '{{ csrf_token() }}',
                        projectid: projectid
                    },
                    success: function(response) {
                        if (response.status == true) {
                            alertify.success(response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            alertify.error(response.message);
                        }
                    }
                });
            });
        });

        // Open Assign to TL Modal (Manager/Admin)
        $(document).on('click', '.btn_assign_to_tl', function() {
            let projectid = $(this).attr('projectid');
            let categoryid = $(this).attr('categoryid');
            $('#assign_tl_project_id').val(projectid);

            // Populate Team Leaders
            $('#assigned_to_tl').empty().append('<option value="">Select Team Leader</option>');
            $.ajax({
                type: 'GET',
                url: base_url + "/projects/get-team-leaders",
                data: {
                    category_id: categoryid
                },
                success: function(response) {
                    if (response.status == true) {
                        response.data.forEach(function(item) {
                            $('#assigned_to_tl').append(new Option(item.name, item.id));
                        });
                        $('#assignToTLModal').modal('show');
                    }
                }
            });
        });

        // Submit Assign to TL
        $('#frm_assign_to_tl').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: base_url + '/projects/assign-to-tl',
                data: $(this).serialize(),
                beforeSend: function() {
                    $(".btn_submit_assign_tl").html('Assigning...').prop('disabled', true);
                },
                success: function(response) {
                    if (response.status == true) {
                        alertify.success(response.message);
                        $('#assignToTLModal').modal('hide');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        alertify.error(response.message);
                        $(".btn_submit_assign_tl").html('Assign Project').prop('disabled', false);
                    }
                }
            });
        });
    });
</script>
@endsection
