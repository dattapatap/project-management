@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page erp-page--admin dept-dashboard-wrapper">
    <div class="erp-page-header">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">Departments</h4>
            <p class="erp-page-subtitle">Configure business units, branches, and team organization.</p>
        </div>
        <div class="erp-page-header__actions">
            <a href="{{ url('/') }}" class="btn btn-outline-primary btn-sm mr-2">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-primary btn-sm btnAddDepartment">
                <i class="mdi mdi-plus"></i> New Department
            </button>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="row mb-2">
        <!-- Metric 1: Total Groups -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium stat-card-premium--indigo">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-office-building"></i>
                </div>
                <div class="stat-value">{{ $departments->total() }}</div>
                <div class="stat-label">Total Departments</div>
            </div>
        </div>

        <!-- Metric 2: Active Groups -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium stat-card-premium--success">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-checkbox-marked-circle-outline"></i>
                </div>
                <div class="stat-value">
                    {{ \App\Models\Department::where('status', true)->where('deleted_at', null)->count() }}
                </div>
                <div class="stat-label">Active Groups</div>
            </div>
        </div>

        <!-- Metric 3: Inactive Groups -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium stat-card-premium--danger">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-alert-circle-outline"></i>
                </div>
                <div class="stat-value">
                    {{ \App\Models\Department::where('status', false)->where('deleted_at', null)->count() }}
                </div>
                <div class="stat-label">Inactive Groups</div>
            </div>
        </div>

        <!-- Metric 4: Total Aligned Staff -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium stat-card-premium--warning">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-multiple"></i>
                </div>
                <div class="stat-value">
                    {{ \App\Models\User::where('deleted_at', null)->where('id', '!=', '1')->count() }}
                </div>
                <div class="stat-label">Aligned Workspace Staff</div>
            </div>
        </div>
    </div>

    <!-- List & Cards Container -->
    @if(!$departments->isEmpty())
    <!-- Search Bar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="input-group search-group">
                <span class="input-group-text search-icon-addon">
                    <i class="mdi mdi-magnify"></i>
                </span>
                <input type="text" id="deptSearchInput" class="form-control search-input" placeholder="Filter departments instantly by group name, branch label, or description...">
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="row departments-grid-row">
        @forelse ($departments as $item)
        @php
        // Assign custom accent stripes based on department name
        $stripeColor = '#4f46e5';
        $lowerName = strtolower($item->name);
        if (str_contains($lowerName, 'sales')) $stripeColor = '#3b82f6';
        else if (str_contains($lowerName, 'design')) $stripeColor = '#a855f7';
        else if (str_contains($lowerName, 'dev') || str_contains($lowerName, 'tech')) $stripeColor = '#6366f1';
        else if (str_contains($lowerName, 'seo')) $stripeColor = '#10b981';
        else if (str_contains($lowerName, 'account') || str_contains($lowerName, 'finance')) $stripeColor = '#06b6d4';
        @endphp
        <div class="col-lg-4 col-md-6 dept-card-col">
            <div class="dept-card dept-card-clickable"
                style="--dept-stripe: {{ $stripeColor }};"
                data-dept-url="{{ route('departments.show', $item->name) }}"
                data-teams-url="{{ url('departments/'.$item->name.'/teams') }}"
                role="button"
                tabindex="0"
                title="Open {{ $item->name }}">
                <!-- Header -->
                <div class="dept-card-header">
                    <div class="dept-title-link">
                        <h5 class="dept-title">
                            {{ $item->name }}
                            <span class="branch-badge">{{ $item->branch->code }}</span>
                        </h5>
                    </div>
                    <div class="dropdown dept-card-actions">
                        <a href="#" class="btn-card-dots dropdown-toggle arrow-none" data-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-dots-vertical"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item btn_edit_department font-size-13" dept_name="{{ $item->name }}" href="javascript:void(0);">
                                <i class="mdi mdi-pencil mr-1 text-warning"></i> Edit Details
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="dept-card-body">
                    <p class="dept-desc">{{ $item->description ?? 'No group descriptions configured.' }}</p>

                    <!-- Footer strip: Members and status -->
                    <div class="dept-footer-strip">
                        <!-- Overlapping Avatars -->
                        <div>
                            @if(!$item->users->isEmpty())
                            <ul class="avatar-stack">
                                @php $stackedCount = 0; @endphp
                                @foreach ($item->users as $members)
                                @if($stackedCount < 6)
                                    <li>
                                    @if ($members->userdetail->profile)
                                    <img title="{{ $members->userdetail->name }}" src="{{ asset('storage/'. $members->userdetail->profile )}}">
                                    @else
                                    <img title="{{ $members->userdetail->name }}" src="{{ Avatar::create($members->userdetail->name)->toBase64() }}">
                                    @endif
                                    </li>
                                    @php $stackedCount++; @endphp
                                    @endif
                                    @endforeach

                                    @php $totalMembers = $item->users()->count(); @endphp
                                    @if($totalMembers > 6)
                                    <li>
                                        <span class="stack-count">+{{ $totalMembers - 6 }}</span>
                                    </li>
                                    @endif
                            </ul>
                            @else
                            <span class="text-muted font-size-12"><i class="mdi mdi-account-off mr-1"></i>No Members</span>
                            @endif
                        </div>

                        <!-- Active indicator -->
                        <div class="d-flex align-items-center gap-2 dept-card-actions">
                            <a href="{{ url('departments/'.$item->name.'/teams') }}" class="btn btn-sm btn-soft-primary dept-teams-link" title="View teams">
                                <i class="mdi mdi-account-group-outline"></i> Teams
                            </a>
                            @if ($item->status == true)
                            <span class="status-badge status-active">Active</span>
                            @else
                            <span class="status-badge status-inactive">In Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        @endforelse
    </div>

    <!-- Pagination row -->
    <div class="row mt-3">
        <div class="col-md-6 d-flex align-items-center">
            <span class="text-muted font-size-13">Showing groups {{ $departments->firstItem() }} to {{ $departments->lastItem() }} of {{ $departments->total() }}</span>
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            {{ $departments->links("pagination::bootstrap-4") }}
        </div>
    </div>

    @else
    <!-- Empty State -->
    <div class="row">
        <div class="col-12 text-center py-5">
            <div class="empty-state-card">
                <div class="empty-state-icon">
                    <i class="mdi mdi-office-building"></i>
                </div>
                <h3 class="font-weight-bold text-dark mb-2">No Departments Registered</h3>
                <p class="text-muted mb-4 font-size-13 erp-page-subtitle" style="max-width: 420px; margin: 0 auto;">Setup your company divisions and assign branches to establish a clear hierarchy structure across your workspace.</p>
                <button href="javascript:void(0);" class="empty-state-btn btnAddDepartment">
                    <i class="mdi mdi-plus-circle-outline mr-2"></i>Create Department
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- AJAX Create / Edit Modal -->
<div id="mdlDepartment" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-premium">
            <div class="modal-header modal-header-premium">
                <h5 class="modal-title modal-title-premium"></h5>
                <button type="button" class="modal-close-premium btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-premium">
                <form id="frm_department" class="custom-validation" method="POST" novalidate>
                    @csrf
                    <input type="hidden" id="department_id" name="department_id" value="">

                    <!-- Field: Name -->
                    <div class="form-group form-group-premium">
                        <label for="name">
                            <i class="mdi mdi-tag-outline"></i>Department Name <span class="text_required">*</span>
                        </label>
                        <input type="text" name="name" id="name" class="form-control input-premium" placeholder="e.g., Creative Design" required>
                        <span class="invalid-feedback" id="name-input-error" role="alert">
                            <strong></strong>
                        </span>
                    </div>

                    <!-- Field: Branch -->
                    <div class="form-group form-group-premium">
                        <label for="branch">
                            <i class="mdi mdi-office-building-marker"></i>Allocated Branch <span class="text_required">*</span>
                        </label>
                        <select name="branch" id="branch" class="form-control select-premium" required>
                            <option value selected>Select Branch</option>
                            @foreach ($branches as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback" id="branch-input-error" role="alert">
                            <strong></strong>
                        </span>
                    </div>

                    <!-- Field: Description -->
                    <div class="form-group form-group-premium">
                        <label for="description">
                            <i class="mdi mdi-text-box-outline"></i>Description <span class="text_required">*</span>
                        </label>
                        <input type="text" name="description" id="description" class="form-control input-premium" placeholder="Configure group purpose, role boundaries..." required>
                        <span class="invalid-feedback" id="description-input-error" role="alert">
                            <strong></strong>
                        </span>
                    </div>

                    <!-- Footer actions -->
                    <div class="row pt-3 mt-4 border-top border-light">
                        <div class="col-12 text-right">
                            <button type="submit" class="btn-submit-premium creatBtn">
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
<script>
    $(document).ready(function() {
        // Open department on card click (exclude action controls)
        $(document).on('click', '.dept-card-clickable', function(e) {
            if ($(e.target).closest('.dept-card-actions, .dropdown, .dropdown-menu').length) {
                return;
            }
            window.location.href = $(this).data('dept-url');
        });

        $(document).on('keydown', '.dept-card-clickable', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.location.href = $(this).data('dept-url');
            }
        });

        // Trigger modal on add department click
        $('.btnAddDepartment').click(function() {
            $('#frm_department')[0].reset();
            $('#department_id').val('');
            $('#mdlDepartment').modal('show');
            $('.modal-title').text('New Department');
            $('.creatBtn').text('Create Department');
            $(".invalid-feedback").children("strong").text("");
            $(".input-premium, .select-premium").removeClass("is-invalid");
        });

        // Close modal
        $('.btnmdlclose').click(function() {
            $('#mdlDepartment').modal('hide');
        });

        // Live Grid Search Filter on cards
        $('#deptSearchInput').on('keyup', function() {
            let value = $(this).val().toLowerCase().trim();

            $('.departments-grid-row .dept-card-col').filter(function() {
                let text = $(this).find('.dept-card').text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1);
            });

            // Displays empty display filter search is too matching
            let visibleCards = $('.departments-grid-row .dept-card-col:visible').length;
            let noMatchCard = $('#noMatchCard');

            if (visibleCards === 0 && $('.departments-grid-row').children('.dept-card-col').length > 0) {
                if (noMatchCard.length === 0) {
                    $('.departments-grid-row').append(`
                        <div id="noMatchCard" class="col-12 text-center py-5 text-muted">
                            <i class="mdi mdi-office-building-marker-outline font-size-24 d-block mb-2"></i>
                            No departments found matching "${$(this).val()}"
                        </div>
                    `);
                }
            } else {
                noMatchCard.remove();
            }
        });

        // AJAX submit form
        $('#frm_department').submit(function(e) {
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");
            $(".input-premium, .select-premium").removeClass("is-invalid");

            $.ajax({
                type: 'POST',
                url: "{{ route('departments.store') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".creatBtn").html('<i class="mdi mdi-loading mdi-spin mr-1"></i> Saving..');
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    if (response.status == true) {
                        $('#frm_department')[0].reset();
                        $('#mdlDepartment').modal('hide');
                        alertify.success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 800);
                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html($('#department_id').val() ? 'Update Details' : 'Create Department');
                    }
                },
                error: function(response) {
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html($('#department_id').val() ? 'Update Details' : 'Create Department');
                    if (response.responseJSON && response.responseJSON.status === 400) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                        });
                    } else {
                        alertify.error("An error occurred. Please try again.");
                    }
                }
            });
        });

        // Fetch department and trigger edit
        $(document).on('click', '.btn_edit_department', function(e) {
            let dept_name = $(this).attr('dept_name');
            $.ajax({
                type: 'GET',
                url: 'departments/' + encodeURIComponent(dept_name) + '/edit',
                beforeSend: function() {
                    $(".invalid-feedback").children("strong").text("");
                    $(".input-premium, .select-premium").removeClass("is-invalid");
                },
                success: function(response) {
                    if (response.status == true) {
                        let dept = response.data;
                        $('.modal-title').text('Edit Department Details');
                        $('.creatBtn').text('Update Details');

                        $('#department_id').val(dept.id);
                        $('#name').val(dept.name);
                        $('#description').val(dept.description);
                        $('#branch').val(dept.branchid);
                        $('#mdlDepartment').modal('show');
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function(response) {
                    alertify.error("Failed to fetch department details.");
                }
            });
        });
    });
</script>
@endsection
