@extends('layouts.app')
@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
<style>
    .dataTables_filter input { width: 250px!important;padding: 1.1rem 0.5rem;font-size: 0.8rem;}
    .dataTables_filter {position: absolute;bottom: 57px; right: 0px;}
    .table td, .table th {  padding: 0.35rem;}

</style>
@endsection
@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Companies</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Companies</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    @if($user->hasRole([ 'Admin', 'Sales-Executive', 'Team-Leader']))

                    <div class="row header-title mb-4">
                        <div class="btn-group mr-1 mt-1 mb-2 float-right">
                            <a href="{{ route('clients.create')}}" type="button" class="btn btn-primary btn-sm float-right">
                                <i class="mdi mdi-plus"></i>
                                New Company
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- <hr> --}}

                    <ul class="nav nav-tabs nav-dept mt-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link @if(request()->segment(2) == 'Fresh') active @endif" href="{{ url('client/Fresh')}}" >
                                <span class="d-none d-md-inline-block">Fresh</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link @if(request()->segment(2) != 'Matured' &&  request()->segment(2) != 'Fresh' && request()->segment(2) != 'Not Interested') active  @endif"
                                    href="{{ url('client/Folloup') }}" role="tab">
                                <span class="d-none d-md-inline-block">Followp</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link @if(request()->segment(2) == 'Matured') active  @endif" href="{{ url('client/Matured') }}" role="tab">
                                <span class="d-none d-md-inline-block">Matured</span>
                            </a>
                        </li>
                        @if($user->hasRole(['Admin', 'Team-Leader']))
                            <li class="nav-item">
                                <a class="nav-link @if(request()->segment(2) == 'Not Interested') active  @endif" href="{{ url('client/Not Interested') }}" role="tab">
                                    <span class="d-none d-md-inline-block">Not Interested</span>
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content p-3">
                        <div class="tab-pane active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-centered mb-0 table-hover" id="datatable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" style="width: 3%">Sl No</th>
                                            <th scope="col" >Name</th>
                                            <th scope="col" style="width: 15%" >Category</th>
                                            <th scope="col" > Number</th>
                                            <th scope="col" > Created Date </th>
                                            @if($user->hasRole(["Admin","Team-Leader"]))
                                            <th scope="col" > Sales Exe.</th>
                                            @endif
                                            <th scope="col"> Status</th>
                                            <th scope="col" class="text-center" style="width: 13%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>

                            {{-- <div class="col-md-12">
                                <div class="text-center">
                                    <div class="mb-3" style="position: relative;">
                                        <img src="{{ asset('img/clients.png') }}" style="height: 100%;width: 30%;"  class="img-fluid rounded-circle" alt="">
                                    </div>
                                    <h3 class="text-truncate mb-2">You don't have any companies.</h3> <br>
                                    <h6 class="fs-15">
                                        <a href="{{ route('clients.create')}}" class="btnAddDepartment text-success"> Click </a>
                                            to create new Client
                                    </h6>
                                </div>
                            </div> --}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>

{{-- Models Assign to clients --}}
<div id="mdlAssignTo" class="modal fade bs-example-modal-center " role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Assign To:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_asssign_to_opther" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <input type="hidden" name="clientid" id="clientid" value="">
                            <div class="form-group">
                                <select class="form-control select2" name="sales_executive" id="sales_executive">
                                    <option value="" selected> Select Executive</option>
                                </select>
                                <span class="invalid-feedback" id="sales_executive-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                > Assign </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
{{-- END Models Assign to clients --}}


{{-- Create New Project --}}
<div id="mdlNewProject" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Create New Project:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_create_new_project" class="custom-validation"  method="POST">
                    @csrf
                    <input type="hidden" value="" name="clientsid" id="clientsid">
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label> Client <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="client_name" id="client_name" placeholder="Client" readonly tabindex="1">
                                <span class="invalid-feedback" id="client_name-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <label> Project Department <span class="text_required">*</span></label>
                            @php
                                $departments = DB::table('project_category')->where('deleted_at', null)->orderBy('id', 'asc')->get();
                            @endphp
                            <select class="form-control select2" name="department" id="department" width="100%"  tabindex="2">
                                <option selected value> Select Department</option>
                                @foreach ($departments as $item)
                                    <option value="{{ $item->id }}"> {{ $item->category }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback" id="department-input-error" role="alert"><strong></strong></span>


                        </div>
                        <div class="col-4">
                            <label> Category </label>
                            <div class="form-group">
                                <select class="form-control select2" name="category" id="category" style="width:100%"  tabindex="3">
                                    <option selected value> Select Category</option>
                                </select>
                                <span class="invalid-feedback" id="category-input-error" role="alert"><strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Package <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="number" class="form-control" name="package" id="package" placeholder="Package" tabindex="4"
                                onKeyPress="return isNumberKey(event);">
                                <span class="invalid-feedback" id="package-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Estimate Start Date <span class="text_required">*</span></label>
                            <div class="form-group">
                                <input type="date" class="form-control" name="start_date" id="start_date" placeholder="Start Date"
                                max="<?= date('Y-m-d', strtotime(date('Y-m-d').' +10 days')); ?>" tabindex="5">
                                <span class="invalid-feedback" id="start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label>Estimate End Date </label>
                            <div class="form-group">
                                <input type="date" class="form-control" name="end_date" id="end_date" placeholder="End Date" tabindex="6"
                                min="<?= date('Y-m-d'); ?>">
                                <span class="invalid-feedback" id="end_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label> Reference Link</label>
                            <div class="form-group">
                                <input type="url" class="form-control" name="referel_link" id="referel_link" placeholder="Referel Link" tabindex="7">
                                <span class="invalid-feedback" id="referel_link-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12 float-right">
                            <label> Project Description <span class="text_required">*</span></label>
                            <textarea class="form-control" name="description" id="description"></textarea>
                            <span class="invalid-feedback" id="description-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                > Create Project </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- END Create New Project --}}



{{-- Models New Domain to clients --}}

<div id="mdlNewDomain" class="modal fade bs-example-modal-center " role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Add New Domain:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_create_new_domain" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                            <input type="hidden" name="client_id" id="client_id" value="">
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Client Name <span class="text_required">*</span></label>
                                    <input type="text" class="form-control" name="client_nm" id="client_nm" placeholder="Client" readonly tabindex="1">
                                    <span class="invalid-feedback" id="client_nm-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Domain <span class="text_required">*</span></label>
                                    <input type="text" class="form-control" name="domain" id="domain" placeholder="Domain Name"  tabindex="2">
                                    <span class="invalid-feedback" id="domain-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Registered Date <span class="text_required">*</span></label>
                                    <input type="date" class="form-control" name="reg_date" id="reg_date" placeholder="Client" tabindex="3"
                                    max="<?= date('Y-m-d'); ?>" />
                                    <span class="invalid-feedback" id="reg_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Expiry Date <span class="text_required">*</span></label>
                                    <input type="date" class="form-control" name="exp_date" id="exp_date" placeholder="Expiry Date" tabindex="4"
                                    min="<?= date('Y-m-d', strtotime(date('Y-m-d').' +11 months')); ?>"
                                    >
                                    <span class="invalid-feedback" id="exp_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                > Add Domain </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- END Models Assign to clients --}}


@endsection
@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/libs/tinymce/js/tinymce.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/clients.js') }}"></script>

<script>
    $(document).ready(function(){
        let category ="{{ request()->segment(2) }}";
        $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            bDestroy: true,
            "responsive":true,
            lengthMenu: [10, 25, 50, 100, 500, 'All'],
            ajax :{
                    type: 'GET',
                    data:{'category':category},
                    url: base_url +"/clients",
                    error:function(err){ console.log(err);}
            },
            "language": {"sSearch": "Filter :", "searchPlaceholder": "Name, Category, Number, Status, date"},
            columns: [
                {data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false},
                {data: 'name', name: 'name', orderable: false, searchable: true},
                {data: 'category', name: 'category', orderable: false, searchable: true},
                {data: 'mobile', name: 'mobile', orderable: false, searchable: true},
                {data: 'created_at', name: 'created_at', orderable: true, searchable: true},

                @if($user->hasRole(['Admin', 'Team-Leader']))
                    {data: 'telereferral', name: 'telereferral.name', orderable: false, searchable: true},
                @endif
                {data: 'status', name: 'status', orderable: false, searchable: true},
                {data: 'action',  name: 'action', orderable: false, searchable: false },
            ]
        });

    });
</script>

@endsection
