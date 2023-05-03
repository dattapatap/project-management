@extends('layouts.app')
@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endsection
@section('content')


<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between float-right">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Home</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->


    {{-- Sales Executive  --}}
    @if($user->hasRole(['Sales-Executive', 'Team-Leader']))
    <div class="row">
        <div class="col-xl-4">
            <div class="col-sm-12 col-xl-12">
                <div class="card card-top-border">
                    <div class="card-body">
                        <div class="media">
                            <div class="media-body">
                                <h5 class="font-size-14">Total Sales</h5>
                            </div>
                            <div class="avatar-xs">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="dripicons-box"></i>
                                </span>
                            </div>
                        </div>
                        <h3 class="mt-2 align-self-center">{{ getTotalSales($user, $user->role) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-xl-12">
                <div class="card card-top-border">
                    <div class="card-body">
                        <div class="media">
                            <div class="media-body">
                                <h5 class="font-size-14">Todays TBRO</h4>
                            </div>
                            <div class="avatar-xs">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="dripicons-bell"></i>
                                </span>
                            </div>
                        </div>
                        <h3 class="mt-2 align-self-center">{{ getTbrosOfToday($user) }}</h3>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-xl-8">
            <div class="card card-top-border">
                <div class="card-body">
                    <h4 class="header-title mb-4">Sales Analytics</h4>
                    <div class="row justify-content-center">
                        <div class="col-sm-4">
                            <div class="text-center">
                                <p>Sales Month Wise</p>
                            </div>
                        </div>
                    </div>
                    <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-top-border">
                <div class="card-body">
                    <h4 class="header-title mb-4" style="margin-bottom: 1.5rem!important;">Todays Follow-ups</h4>

                    <div class="table-responsive">
                        <table id="datatable"
                            class="table table-bordered dt-responsive table-centered table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Sl</th>
                                    <th>Company</th>
                                    <th>Mobile</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Remark</th>
                                    <th> Date </th>
                                    @if($user->hasRole(['Team-Leader']))
                                        <th> Sal/Exc </th>
                                    @endif
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
    @endif


    {{-- Project Manager --}}
    @if($user->hasRole(['Project-Manager']))
    <div class="row">
        <div class="col-12">
            <h5>Projects</h5>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-danger">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Total Project</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-danger">
                                <i class="dripicons-folder-open"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ projects('ALL', $user) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-warning">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">ToDo Projects</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-warning">
                                <i class="dripicons-to-do"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ projects('ToDo', $user) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-info">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Progress Projects</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-info">
                                <i class="dripicons-checklist"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ projects('InProgress', $user) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-success">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Completed Projects</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-success">
                                <i class="dripicons-checkmark"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ projects('Completed', $user) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-12">
            <h5>Tasks</h5>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-danger">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Total Tasks</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-danger">
                                <i class="dripicons-list"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ tasks('ALL', $user) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-warning">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">ToDo Tasks</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-warning">
                                <i class="dripicons-network-1"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ tasks('ToDo', $user) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-info">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Progress Tasks</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-info">
                                <i class="dripicons-graph-line"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ tasks('InProgress', $user) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-top-border-success">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Completed Tasks</h5>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-success">
                                <i class="dripicons-checkmark"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ tasks('Completed', $user) }}</h3>
                </div>
            </div>
        </div>
    </div>


    @endif
    <!-- end row -->
</div>




@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>

@if($user->hasRole(['Sales-Executive', 'Team-Leader'])  && $user->departments->department == 1)
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script src="{{ asset("assets/js/pages/dashboard.init.js")}}"></script>
<script>
$(document).ready(function(){
    $("#datatable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax :{
                type: 'GET',
                url: base_url +"/todays/tbros",
                error:function(err){ console.log(err);}
        },
        columns: [
            {data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false },
            {data: 'name', name: 'name', orderable: false, searchable: true,   },
            {data: 'mobile', name: 'mobile', orderable: false, searchable: true,   },
            {data: 'category', name: 'history.category', orderable: false, searchable: false,   },
            {data: 'status', name: 'status', orderable: false, searchable: false,   },
            {data: 'remarks', name: 'history.remarks', orderable: false, searchable: false,   },
            {data: 'tbro', name: 'history.tbro', orderable: false, searchable: false,   },
            @if($user->hasRole(['Admin', 'Team-Leader']))
            {data: 'telereferral', name: 'telereferral.name', orderable: false, searchable: true,   },
            @endif
            {data: 'action',  name: 'action', orderable: false, searchable: false ,   },
        ],
    });
});
</script>
@endif

@endsection
