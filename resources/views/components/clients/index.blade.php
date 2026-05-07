@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">

<style>
    /* Premium Page Typography & Variables */
    .companies-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    /* Breathtaking Title/Welcome Card */
    .title-card-glass {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 255, 0.95) 100%);
        border-left: 5px solid #7F00FF;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .text-premium-dark {
        color: #343a40;
        font-weight: 700;
    }

    /* Main Repository Card Styling */
    .repo-card {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(230, 230, 240, 0.8);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.02);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    /* Beautiful Pill-Style Glass Toggle Bar */
    .nav-dept {
        border-bottom: none !important;
        background: #f1f2f7;
        padding: 6px;
        border-radius: 14px;
        display: inline-flex;
        gap: 6px;
    }

    .nav-dept .nav-item {
        margin-bottom: 0;
    }

    .nav-dept .nav-link {
        border: none !important;
        border-radius: 10px !important;
        padding: 10px 22px !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        color: #74788d !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        background: transparent !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
    }

    .nav-dept .nav-link i {
        font-size: 1.05rem;
        transition: transform 0.25s ease;
    }

    .nav-dept .nav-link.active {
        background: #ffffff !important;
        color: #7F00FF !important;
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.05);
    }

    .nav-dept .nav-link.active i {
        transform: scale(1.1);
        color: #7F00FF;
    }

    .nav-dept .nav-link:hover:not(.active) {
        color: #343a40 !important;
        background: rgba(255, 255, 255, 0.5) !important;
    }

    /* Datatable Layout & Element Styling Overrides */
    .dataTables_wrapper {
        padding: 10px 0;
    }

    /* Search Input Styling */
    .dataTables_wrapper .dataTables_filter {
        position: relative !important;
        bottom: auto !important;
        right: auto !important;
        float: right;
        margin-bottom: 20px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid rgba(210, 210, 225, 0.7) !important;
        border-radius: 12px !important;
        padding: 8px 16px 8px 16px !important;
        font-size: 0.85rem !important;
        background-color: #ffffff !important;
        transition: all 0.25s ease;
        width: 250px !important;
        height: 40px !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.015);
        color: #495057;
        font-family: 'Outfit', sans-serif;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #7F00FF !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.15) !important;
    }

    /* Page Length Dropdown Styling */
    .dataTables_wrapper .dataTables_length {
        float: left;
        margin-bottom: 20px;
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid rgba(210, 210, 225, 0.7) !important;
        border-radius: 10px !important;
        padding: 6px 12px !important;
        height: 40px !important;
        outline: none;
        transition: all 0.2s ease;
        color: #495057;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #7F00FF !important;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.15) !important;
    }

    /* Clean Corporate Enterprise Table Design */
    #datatable {
        border-collapse: collapse !important;
        width: 100% !important;
        margin-top: 15px !important;
        background-color: #ffffff;
    }

    #datatable thead th {
        background: #f8f9fc !important;
        border-top: none !important;
        border-bottom: 2px solid #edf2f9 !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11.5px;
        letter-spacing: 0.6px;
        color: #495057;
        padding: 14px 16px !important;
    }

    #datatable tbody tr {
        background: #ffffff;
        transition: background-color 0.2s ease;
    }

    #datatable tbody tr:hover {
        background-color: #f4f6fc !important;
    }

    #datatable tbody td {
        padding: 13px 16px !important;
        vertical-align: middle !important;
        border: none !important;
        border-bottom: 1px solid #edf2f9 !important;
        color: #495057;
        font-size: 13.5px;
    }

    /* circular paginator controls */
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 20px !important;
    }

    .dataTables_wrapper .paginate_button {
        padding: 0 !important;
        margin: 0 4px !important;
        border: none !important;
        background: transparent !important;
    }

    .dataTables_wrapper .paginate_button.page-item .page-link {
        border-radius: 10px !important;
        border: 1px solid rgba(215, 215, 230, 0.6) !important;
        color: #64748b !important;
        padding: 8px 16px !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .dataTables_wrapper .paginate_button.page-item.active .page-link {
        background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%) !important;
        border-color: transparent !important;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(127, 0, 255, 0.3) !important;
    }

    .dataTables_wrapper .paginate_button.page-item:hover:not(.active) .page-link {
        background-color: #f1f2f7 !important;
        color: #343a40 !important;
        border-color: rgba(200, 200, 220, 0.8) !important;
    }

    .dataTables_wrapper .dataTables_info {
        margin-top: 20px !important;
        color: #64748b !important;
        font-size: 0.85rem !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 500;
    }

    /* Modal Design Polishes */
    .modal-content-glass {
        border-radius: 20px;
        overflow: hidden;
        border: none;
        background: #ffffff;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header-gradient {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        padding: 18px 24px;
        border-bottom: none;
    }

    .modal-title-premium {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: #ffffff !important;
        letter-spacing: 0.5px;
    }

    .modal-body-premium {
        padding: 24px 30px;
    }

    .modal-footer-premium {
        background-color: #f8f9fc;
        padding: 16px 30px;
        border-top: none;
    }

    .input-premium {
        border-radius: 10px !important;
        border: 1px solid rgba(210, 210, 225, 0.7);
        padding: 10px 14px;
        height: 44px;
        font-family: 'Outfit', sans-serif;
        font-size: 13.5px;
        color: #333333;
    }

    .input-premium:focus {
        border-color: #7F00FF;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.15);
    }

    .select-premium {
        height: 44px !important;
    }

    .label-premium {
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        color: #343a40;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .text_required {
        color: #ff4d4f;
    }

    /* Responsive adjustment for search alignment */
    @media (max-width: 767px) {

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length {
            float: none !important;
            text-align: left !important;
            width: 100% !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid companies-wrapper">

    <!-- 🗓 Beautiful Title/Welcome Card -->
    <div class="card mb-4 title-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h3 class="text-premium-dark font-size-20 mb-1">🏢 Companies Directory</h3>
                <p class="text-muted font-size-13 mb-0">Centralized database of leads, touchpoint stages, and conversion structures.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item active text-muted">Companies</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Repository Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card repo-card">
                <div class="card-body p-4">

                    <!-- Unified Navigation Tab & Add Action Row -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-4" style="gap: 15px;">

                        <ul class="nav nav-tabs nav-dept mb-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link @if(request()->segment(2) == 'Fresh') active @endif" href="{{ url('client/Fresh')}}">
                                    <i class="bx bx-bookmark-plus"></i>
                                    <span>Fresh</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link @if(request()->segment(2) != 'Matured' && request()->segment(2) != 'Fresh' && request()->segment(2) != 'Not Interested') active @endif"
                                    href="{{ url('client/Folloup') }}">
                                    <i class="bx bx-redo"></i>
                                    <span>Follow-up Queue</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link @if(request()->segment(2) == 'Matured') active @endif" href="{{ url('client/Matured') }}">
                                    <i class="bx bx-trophy"></i>
                                    <span>Matured</span>
                                </a>
                            </li>

                            @if($user->hasRole(['Admin', 'Team-Leader']))
                            <li class="nav-item">
                                <a class="nav-link @if(request()->segment(2) == 'Not Interested') active @endif" href="{{ url('client/Not Interested') }}">
                                    <i class="bx bx-block"></i>
                                    <span>Not Interested</span>
                                </a>
                            </li>
                            @endif
                        </ul>

                        @if($user->hasRole([ 'Admin', 'Sales-Executive', 'Team-Leader']))
                        <div class="d-flex" style="gap: 12px; flex-wrap: wrap;">
                            <a href="{{ route('clients.bulkupload') }}" class="btn d-inline-flex align-items-center font-weight-bold" style="border-radius: 12px; height: 44px; padding: 0 20px; border: 1px solid rgba(127, 0, 255, 0.4); color: #7F00FF; background: rgba(127, 0, 255, 0.05); font-size: 13px; transition: all 0.2s ease;" onmouseover="this.style.background='#7F00FF'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(127, 0, 255, 0.05)'; this.style.color='#7F00FF';">
                                <i class="mdi mdi-cloud-upload-outline font-size-16 mr-1"></i> Bulk Upload
                            </a>
                            <a href="{{ route('clients.create') }}" class="btn btn-primary d-inline-flex align-items-center font-weight-bold" style="border-radius: 12px; height: 44px; padding: 0 20px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none; box-shadow: 0 4px 15px rgba(127,0,255,0.25); font-size: 13px;">
                                <i class="mdi mdi-plus-circle font-size-16 mr-1"></i> Add New Company
                            </a>
                        </div>
                        @endif
                    </div>

                    <input type="hidden" id="category_name" value="{{ request()->segment(2) }}">
                     <script>
                        var isPM = "{{ $user->hasRole('Project-Manager') ? 'true' : 'false' }}";
                        var isAuthority = "{{ $user->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']) ? 'true' : 'false' }}";
                    </script>

                    <!-- Table Pane -->
                    <div class="tab-content">
                        <div class="tab-pane active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-centered mb-0" id="datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center" style="width: 5%">Sl No</th>
                                            <th scope="col">Name</th>
                                            @if(!$user->hasRole('Project-Manager'))
                                            <th scope="col" class="text-center" style="width: 15%">Contact Info</th>
                                            @endif
                                            <th scope="col" class="text-center"> Mobile</th>
                                            <th scope="col" class="text-center"> City </th>
                                            @if($user->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']))
                                            <th scope="col" class="text-center">Created By</th>
                                            <th scope="col" class="text-center">Following By</th>
                                            @endif
                                            <th scope="col" class="text-center"> Status</th>
                                            <th scope="col" class="text-center"> Created Date </th>
                                            <th scope="col" class="text-center" style="width: 8%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Server-side Rendered Content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS SECTION (HIGH AESTHETICS OVERLAYS WITH data-dismiss="modal" TRIGGERS) --}}

{{-- 1. ASSIGN TO CLIENT MODAL --}}
<div id="mdlAssignTo" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-glass">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title modal-title-premium" id="allocModalTitle">👥 Assign Sales Executive</h5>
                <button type="button" class="close text-white font-size-20" data-dismiss="modal" aria-label="Close" style="opacity: 0.8; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frm_asssign_to_opther" class="custom-validation" method="POST" novalidate>
                @csrf
                <div class="modal-body modal-body-premium">
                    <input type="hidden" name="clientid" id="clientid" value="">

                    <div class="form-group mb-0">
                        <label class="label-premium">Select Executive</label>
                        <select class="form-control select2 select-premium" name="sales_executive" id="sales_executive" style="width: 100%;">
                            <option value="" selected>-- Select Executive --</option>
                        </select>
                        <span class="invalid-feedback" id="sales_executive-input-error" role="alert">
                            <strong></strong>
                        </span>
                    </div>
                </div>
                <div class="modal-footer modal-footer-premium">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-submit creatBtn font-weight-bold" style="border-radius: 10px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <i class="bx bx-check-shield mr-1"></i> Assign Executive
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. CREATE NEW PROJECT MODAL --}}
<div id="mdlNewProject" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modal-content-glass">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title modal-title-premium">🚀 Create New Client Project</h5>
                <button type="button" class="close text-white font-size-20" data-dismiss="modal" aria-label="Close" style="opacity: 0.8; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frm_create_new_project" class="custom-validation" method="POST">
                @csrf
                <div class="modal-body modal-body-premium">
                    <input type="hidden" value="" name="clientsid" id="clientsid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="label-premium"> Client Name <span class="text_required">*</span></label>
                                <input type="text" class="form-control input-premium" name="client_name" id="client_name" readonly>
                                <span class="invalid-feedback" id="client_name-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="label-premium"> Project Department <span class="text_required">*</span></label>
                                @php
                                $departments = DB::table('project_category')->where('deleted_at', null)->orderBy('id', 'asc')->get();
                                @endphp
                                <select class="form-control select2 select-premium" name="{{ $user->hasRole('Team-Leader') ? '' : 'department' }}" id="department" width="100%" {{ $user->hasRole('Team-Leader') ? 'disabled' : '' }}>
                                    @foreach ($departments as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == 1 ? 'selected' : '' }}> {{ $item->category }}</option>
                                    @endforeach
                                </select>
                                @if($user->hasRole('Team-Leader'))
                                <input type="hidden" name="department" value="1">
                                @endif
                                <span class="invalid-feedback" id="department-input-error" role="alert"><strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="label-premium"> Category </label>
                                <select class="form-control select2 select-premium" name="category" id="category" style="width:100%">
                                    <option selected value=""> Select Category</option>
                                </select>
                                <span class="invalid-feedback" id="category-input-error" role="alert"><strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Team Leader </label>
                                <select class="form-control select2 select-premium" name="{{ $user->hasRole('Team-Leader') ? '' : 'team_leader' }}" id="team_leader" style="width:100%" {{ $user->hasRole('Team-Leader') ? 'disabled' : '' }}>
                                    @if($user->hasRole('Team-Leader'))
                                    <option value="{{ $user->id }}" selected>{{ $user->name }}</option>
                                    @else
                                    <option selected value=""> Select Team Leader </option>
                                    @endif
                                </select>
                                @if($user->hasRole('Team-Leader'))
                                <input type="hidden" name="team_leader" value="{{ $user->id }}">
                                @endif
                                <span class="invalid-feedback" id="team_leader-input-error" role="alert"><strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Contract Package Value ($) </label>
                                <input type="number" class="form-control input-premium" name="package" id="package" placeholder="Contract Package" onKeyPress="return isNumberKey(event);">
                                <span class="invalid-feedback" id="package-input-error" role="alert"><strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Estimate Start Date <span class="text_required">*</span></label>
                                <input type="date" class="form-control input-premium" name="start_date" id="start_date" max="<?= date('Y-m-d', strtotime(date('Y-m-d') . ' +10 days')); ?>">
                                <span class="invalid-feedback" id="start_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Estimate End Date </label>
                                <input type="date" class="form-control input-premium" name="end_date" id="end_date" min="<?= date('Y-m-d'); ?>">
                                <span class="invalid-feedback" id="end_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-8 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Reference Website / Design Links </label>
                                <input type="url" class="form-control input-premium" name="referel_link" id="referel_link" placeholder="https://example.com/mockups">
                                <span class="invalid-feedback" id="referel_link-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="label-premium"> Project Description / Scope <span class="text_required">*</span></label>
                            <textarea class="form-control" name="description" id="description"></textarea>
                            <span class="invalid-feedback" id="description-input-error" role="alert"> <strong></strong></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-premium">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-submit creatBtn font-weight-bold" style="border-radius: 10px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none;">
                        <i class="bx bx-check-shield mr-1"></i> Register Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. REGISTER NEW DOMAIN MODAL --}}
<div id="mdlNewDomain" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-glass">
            <div class="modal-header modal-header-gradient">
                <h5 class="modal-title modal-title-premium">🌐 Add New Client Domain</h5>
                <button type="button" class="close text-white font-size-20" data-dismiss="modal" aria-label="Close" style="opacity: 0.8; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frm_create_new_domain" class="custom-validation" method="POST" novalidate>
                @csrf
                <div class="modal-body modal-body-premium">
                    <input type="hidden" name="client_id" id="client_id" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="label-premium"> Client Name <span class="text_required">*</span></label>
                                <input type="text" class="form-control input-premium" name="client_nm" id="client_nm" readonly>
                                <span class="invalid-feedback" id="client_nm-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="label-premium"> Domain <span class="text_required">*</span></label>
                                <input type="text" class="form-control input-premium" name="domain" id="domain" placeholder="example.com">
                                <span class="invalid-feedback" id="domain-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Registered Date <span class="text_required">*</span></label>
                                <input type="date" class="form-control input-premium" name="reg_date" id="reg_date" max="<?= date('Y-m-d'); ?>" />
                                <span class="invalid-feedback" id="reg_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Expiry Date <span class="text_required">*</span></label>
                                <input type="date" class="form-control input-premium" name="exp_date" id="exp_date" min="<?= date('Y-m-d', strtotime(date('Y-m-d') . ' +11 months')); ?>">
                                <span class="invalid-feedback" id="exp_date-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-premium">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-submit creatBtn font-weight-bold" style="border-radius: 10px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none;">
                        <i class="bx bx-check-shield mr-1"></i> Register Domain
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/libs/tinymce/js/tinymce.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/clients.js') }}"></script>
@endsection
