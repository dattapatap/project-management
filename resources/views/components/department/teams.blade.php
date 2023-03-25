@extends('layouts.app')

@section('content')


<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center justify-content-between">
                    <a href="{{ url()->previous()  }}" class="btn-back" >
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/departments', $department->name  ) }}">Department</a></li>
                        <li class="breadcrumb-item active">Teams</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">

        <div class="card card-top-border cw-100" style="width: 100%">
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-dept" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link " href="{{ url('departments/'. $department->name .'/') }}"  role="tab">
                            <i class="mdi mdi-account-multiple-outline mr-1 align-middle"></i> <span class="d-none d-md-inline-block">Members</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active"  data-toggle="tab" href="#teams" role="tab">
                            <i class="mdi mdi-account-group-outline mr-1 align-middle"></i> <span class="d-none d-md-inline-block">Teams</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-3">
                    <div class="tab-pane active" id="members" role="tabpanel">
                        <div class="row">
                            <div class="col-12">
                                <div class="btn-group mr-1 mt-1 mb-2 float-right">
                                    <button type="button" class="btn btn-primary btn-sm btnAddTeam">
                                        <i class="mdi mdi-plus"></i>
                                            Manage Teams
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @forelse ($teams as $item)
                                <div class="col-4">
                                    <div class="card project card-top-border" style="border-top: 2px solid #ff1d00;">
                                        <div class="card-body">
                                            <div class="department">
                                                <div class="department-header">
                                                    <a href="javascript:void(0);" class="">
                                                        <h5 class="department-title mt-1">
                                                            {{ $item->name  }} <span class="badge badge-pill badge-primary">{{ $item->teammembers->count() }}</span>
                                                        </h5>
                                                    </a>
                                                    <div class="btn-group float-right" >

                                                        <a href="javascript:void(0);" team_id="{{ $item->id }}" departmentid="{{ $department->id }}"  class="btnAddMembers mr-2"
                                                                data-toggle="tooltip" data-placement="bottom" title="Manage Team Members" style="font-size: 21px;">
                                                            <i class="mdi mdi-plus-outline"></i>
                                                        </a>

                                                        <a href="#" class="dropdown-toggle arrow-none"
                                                            data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 21px;">
                                                            <i class="mdi mdi-dots-vertical"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-start">
                                                            <a class="dropdown-item btn_edit_team" teamid="{{ $item->id }}">
                                                                    <i class="mdi mdi-pencil"></i> Edit
                                                            </a>
                                                            {{-- <form method="post" action="{{ route('departments.destroy',[ $item->id ]) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item btn_upload_student"  onclick="return confirm('Do you want to delete this item?')"
                                                                    data-toggle="tooltip" data-placement="bottom" title="Delete Department"><i class="mdi mdi-delete-outline"></i>
                                                                    Delete
                                                                </button>
                                                            </form> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="">
                                                    <div class="department-members">
                                                        @if(!$item->teammembers->isEmpty())
                                                            <ul class="department-users">
                                                                <?php  $totCount = 0; ?>
                                                                @foreach ($item->teammembers as $members)
                                                                    @if($totCount < 10)
                                                                        <li>
                                                                            @if ($members->users->profile)
                                                                                <img title="{{ $members->users->profile }}" src="{{ asset('storage/'. $members->users->profile )}}">
                                                                            @else
                                                                                <img title="" src="{{ Avatar::create($members->users->name)->toBase64()  }}">
                                                                            @endif

                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                                @php
                                                                    $count = $item->teammembers()->count();
                                                                @endphp
                                                                @if( $count > 10 )
                                                                    <li class="count">{{ $count - 10 }}+</li>
                                                                @endif
                                                            </ul>
                                                        @else
                                                            <ul class="department-users">
                                                                <li>
                                                                    <img title="Dont have members" src="{{ asset('img/users.png')}}">
                                                                </li>
                                                            </ul>
                                                        @endif
                                                    </div>

                                                    @if ($item->status == true)
                                                        <span class="badge badge-success float-right">Active</span>
                                                    @else
                                                        <span class="badge badge-danger float-right">In Active</span>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <div class="mb-3" style="position: relative;">
                                            <img src="{{ asset('img/users.png') }}"
                                                style="height: 100%;width: 20%;"
                                                class="img-fluid rounded-circle" alt="">
                                        </div>
                                        <h3 class="text-truncate mb-2">You don't have any Teams this department.</h3> <br>
                                        <h6 class="fs-15">
                                        <a href="javascripts:void(0)" class="btnAddTeam text-success"> Click </a>
                                        to add new Team
                                        </h6>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- end row -->
</div>


<div id="mdlDeptUsers" class="modal fade bs-example-modal-center" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content ">
            <div class="modal-header" style="border-bottom: 2px solid #30d1b7;">
                <h5 class="modal-title mt-0"></h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_department_member" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="teamid" value="-1" id="teamid">
                    <input type="hidden" name="departmentid" value="{{ $department->id }}" id="departmentid">
                    <div class="row">
                        <div class="col-6">
                            <span> Team Members</span>
                            <ul id="userslist" class="list-group" style="min-height: 350px;">

                            </ul>
                        </div>
                        <div class="col-6">
                            <span class="text-danger">Un Assigned Members</span>
                            <ul id="userslist-unassigned" class="list-group" style="min-height: 350px;">

                            </ul>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MDL Teams --}}

<div id="mdlTeams" class="modal fade bs-example-modal-center" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0"></h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_teams" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <input type="hidden" name="team_id" value="-1" id="team_id">
                        <input type="hidden" name="department" value="{{ $department->id }}" id="department">
                        <div class="col-12">
                            <input type="hidden" id="department_id" name="department_id" value="">
                            <div class="form-group">
                                <label>Name <span class="text_required">*</span> </label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Team Name">
                                <span class="invalid-feedback" id="name-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Description <span class="text_required">*</span></label>
                                <input type="text" name="description" id="description" class="form-control" placeholder="Description">
                                <span class="invalid-feedback" id="description-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row float-roght">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                Create
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


@endsection
@section('scripts')
<script src="{{ asset('assets/libs/draggable/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/libs/draggable/jquery-sortable.js')}}"></script>
<script src="{{ asset('js/teams.js')}}"></script>
@endsection

