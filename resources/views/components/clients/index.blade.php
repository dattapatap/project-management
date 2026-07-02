@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page erp-page--nsd companies-wrapper">
    @include('layouts.partials.erp-page-header', [
    'title' => 'Companies Directory',
    'subtitle' => 'Leads, follow-ups, and conversion pipeline.',
    'actions' => '<a href="' . url('/') . '" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-arrow-left mr-1"></i> Back</a>'
    ])
    <!-- Main Repository Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card repo-card">
                <div class="card-body p-3">

                    <!-- Unified Navigation Tab & Add Action Row -->
                    <div class="erp-flex-toolbar mb-4">

                        <ul class="nav nav-tabs nav-dept mb-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link @if(client_category_active('fresh')) active @endif" href="{{ client_list_url('Fresh') }}">
                                    <i class="bx bx-bookmark-plus"></i>
                                    <span>Fresh</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link @if(client_category_active('followup')) active @endif"
                                    href="{{ client_list_url('followup') }}">
                                    <i class="bx bx-redo"></i>
                                    <span>Follow-up Queue</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link @if(client_category_active('matured')) active @endif" href="{{ client_list_url('Matured') }}">
                                    <i class="bx bx-trophy"></i>
                                    <span>Matured</span>
                                </a>
                            </li>

                            @if($user->hasBranchWideAccess() || $user->hasRole(['Team-Leader', 'Sales-Executive']))
                            <li class="nav-item">
                                <a class="nav-link @if(client_category_active('not-interested')) active @endif" href="{{ client_list_url('Not Interested') }}">
                                    <i class="bx bx-block"></i>
                                    <span>Not Interested</span>
                                </a>
                            </li>
                            @endif
                        </ul>

                        @if($user->hasRole(['Admin', 'Sales-Executive', 'Team-Leader', 'Branch-Manager']))
                        <div class="erp-action-group">
                            <a href="{{ route('clients.bulkupload') }}" class="btn erp-btn-nsd-outline">
                                <i class="mdi mdi-cloud-upload-outline font-size-16 mr-1"></i> Bulk Upload
                            </a>
                            <a href="{{ route('clients.create') }}" class="btn erp-btn-nsd-gradient">
                                <i class="mdi mdi-plus-circle font-size-16 mr-1"></i> Add New Company
                            </a>
                        </div>
                        @endif
                    </div>

                    <input type="hidden" id="category_name" value="{{ normalize_client_category(request()->segment(2)) }}">
                    <script>
                        var isPM = "{{ $user->hasRole('Project-Manager') ? 'true' : 'false' }}";
                        var isAuthority = "{{ $user->hasBranchWideAccess() || $user->hasRole('Team-Leader') ? 'true' : 'false' }}";
                    </script>

                    <!-- Table Pane -->
                    <div class="tab-content">
                        <div class="tab-pane active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-centered mb-0 erp-table--companies" id="datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center col-sl">Sl No</th>
                                            <th scope="col">Name</th>
                                            @if(!$user->hasRole('Project-Manager'))
                                            <th scope="col" class="text-center col-contact">Contact Info</th>
                                            @endif
                                            <th scope="col" class="text-center"> Mobile</th>
                                            <th scope="col" class="text-center"> City </th>
                                            @if($user->hasRole(['Admin', 'Team-Leader', 'Branch-Manager']))
                                            <th scope="col" class="text-center">Created By</th>
                                            <th scope="col" class="text-center">Following By</th>
                                            @endif
                                            <th scope="col" class="text-center"> Status</th>
                                            <th scope="col" class="text-center"> Created Date </th>
                                            <th scope="col" class="text-center col-action">Action</th>
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
                <button type="button" class="close text-white font-size-20 erp-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frm_asssign_to_opther" class="custom-validation" method="POST" novalidate>
                @csrf
                <div class="modal-body modal-body-premium">
                    <input type="hidden" name="clientid" id="clientid" value="">

                    <div class="form-group mb-0">
                        <label class="label-premium">Select Executive</label>
                        <select class="form-control select2 select-premium erp-select-full" name="sales_executive" id="sales_executive">
                            <option value="" selected>-- Select Executive --</option>
                        </select>
                        <span class="invalid-feedback" id="sales_executive-input-error" role="alert">
                            <strong></strong>
                        </span>
                    </div>
                </div>
                <div class="modal-footer modal-footer-premium">
                    <button type="button" class="btn btn-secondary waves-effect erp-btn-modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-submit creatBtn font-weight-bold erp-btn-modal erp-btn-modal--nsd">
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
                <button type="button" class="close text-white font-size-20 erp-modal-close" data-dismiss="modal" aria-label="Close">
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
                                <select class="form-control select2 select-premium erp-select-full" name="category" id="category">
                                    <option selected value=""> Select Category</option>
                                </select>
                                <span class="invalid-feedback" id="category-input-error" role="alert"><strong></strong></span>
                            </div>
                        </div>

                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label class="label-premium"> Team Leader </label>
                                <select class="form-control select2 select-premium erp-select-full" name="{{ $user->hasRole('Team-Leader') ? '' : 'team_leader' }}" id="team_leader" {{ $user->hasRole('Team-Leader') ? 'disabled' : '' }}>
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
                    <button type="button" class="btn btn-secondary waves-effect erp-btn-modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-submit creatBtn font-weight-bold erp-btn-modal erp-btn-modal--nsd">
                        <i class="bx bx-check-shield mr-1"></i> Register Project
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