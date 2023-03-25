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
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Domains</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Domains</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="">
                                {{-- <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <strong>Warning!</strong> Better check yourself, you're not looking too good.
                                </div> --}}
                                @if($expired > 0)
                                    <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                        <strong>{{ $expired }}</strong> Domains has been expired.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <a href="javascript:void(0)" type="button" class="btn btn-primary btn-sm createNewDomain float-right">
                                <i class="mdi mdi-plus"></i>
                                New Domain
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Company</th>
                                        <th>Domain</th>
                                        <th>Contact Info</th>
                                        <th>Mobile</th>
                                        <th> Registered Date </th>
                                        <th> Expiry Date </th>
                                        <th> Renew </th>
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
    </div>
    <!-- end row -->
</div>

<div id="mdlUpdateDomain" class="modal fade bs-example-modal-center " role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mdldomain mt-0">Update Domain:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_update_domain" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                            <input type="hidden" name="client_id" id="client_id" value="">
                            <input type="hidden" name="domain_id" id="domain_id" value="">
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
                                    <input type="date" class="form-control" name="reg_date" id="reg_date" placeholder="Client" tabindex="3" readonly/>
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
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit editBtn"
                                > UPDATE </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<div id="mdlNewDomain" class="modal fade bs-example-modal-center " role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mdldomain mt-0">Add Domain:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_new_domain" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                            <input type="hidden" name="domain_id" id="domain_id" value="">
                            <div class="col-6">
                                @php
                                    $clients = DB::table('clients')->select('id', 'name')
                                                ->where('is_active', true)->orderBy('name', 'asc')->get();
                                @endphp

                                <div class="form-group">
                                    <label> Client Name <span class="text_required">*</span></label>
                                    <select class="form-control select2" name="clientid" id="clientid" tabindex="1">
                                        <option selected value="">Select Client</option>
                                        @foreach ($clients as $item)
                                            <option value="{{$item->id}}"> {{$item->name}} </option>
                                        @endforeach
                                    </select>
                                    </select>
                                    <span class="invalid-feedback" id="clientid-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Domain <span class="text_required">*</span></label>
                                    <input type="text" class="form-control" name="domain_name" id="domain_name" placeholder="Domain Name"  tabindex="2">
                                    <span class="invalid-feedback" id="domain_name-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Registered Date <span class="text_required">*</span></label>
                                    <input type="date" class="form-control" name="registered_date" id="registered_date" placeholder="Regidter Date" tabindex="3"
                                    min="<?= date('Y-m-d', strtotime(date('Y-m-d').' +11 months')); ?>" />
                                    <span class="invalid-feedback" id="registered_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Expiry Date <span class="text_required">*</span></label>
                                    <input type="date" class="form-control" name="expiry_date" id="expiry_date" placeholder="Expiry Date" tabindex="4"
                                    min="<?= date('Y-m-d', strtotime(date('Y-m-d').' +11 months')); ?>"
                                    >
                                    <span class="invalid-feedback" id="expiry_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                > Submit </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<div id="mdlRenewDomain" class="modal fade bs-example-modal-center " role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mdldomain mt-0">Renew Domain:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_renew_domain" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <input type="hidden" name="client" id="client" value="">
                        <input type="hidden" name="domainid" id="domainid" value="">
                        <div class="col-6">
                            <div class="form-group">
                                <label> Client Name <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="clientnm" id="clientnm" placeholder="Client" readonly tabindex="1">
                                <span class="invalid-feedback" id="clientnm-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label> Domain <span class="text_required">*</span></label>
                                <input type="text" class="form-control" name="domain_nm" id="domain_nm" placeholder="Domain Name"  tabindex="2">
                                <span class="invalid-feedback" id="domain_nm-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label> Renew Date <span class="text_required">*</span></label>
                                <input type="date" class="form-control" name="renew_date" id="renew_date" placeholder="Renew Date"
                                max="<?= date('Y-m-d'); ?>"  tabindex="3" >
                                <span class="invalid-feedback" id="renew_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label> Expiry Date <span class="text_required">*</span></label>
                                <input type="date" class="form-control" name="expirydate" id="expirydate" placeholder="Expiry Date" tabindex="4"
                                    min="<?= date('Y-m-d', strtotime(date('Y-m-d').' +11 months')); ?>">
                                <span class="invalid-feedback" id="expirydate-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-renew"
                                > Renew Domain</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>




@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{ asset('js/domains.js')}}"></script>
@endsection
