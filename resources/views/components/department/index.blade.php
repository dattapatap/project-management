@extends('layouts.app')

@section('styles')
<style>
    /* Styling System Custom Tokens */
    .dept-dashboard-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        padding-bottom: 2rem;
    }

    .dept-header-gradient-card {
        background: linear-gradient(135deg, #1e1e38 0%, #0f172a 100%);
        border-radius: 18px;
        padding: 30px;
        color: #ffffff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .dept-header-gradient-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .dept-dashboard-title {
        font-weight: 700;
        font-size: 26px;
        letter-spacing: -0.5px;
        margin-bottom: 6px;
        color: #ffffff;
    }

    .dept-dashboard-subtitle {
        font-size: 14px;
        color: #94a3b8;
        max-width: 600px;
        margin-bottom: 0;
    }

    /* Premium Stats Grid */
    .stat-card-premium {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .stat-card-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.01);
        border-color: rgba(99, 102, 241, 0.25);
    }

    .stat-card-premium::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--card-accent, #6366f1);
    }

    .stat-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        background: var(--stat-bg, rgba(99, 102, 241, 0.08));
        color: var(--stat-color, #4f46e5);
    }

    .stat-icon-wrapper i {
        font-size: 20px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin-top: 4px;
    }

    /* Action Buttons */
    .btn-create-dept {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #ffffff !important;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        font-size: 14px;
    }

    .btn-create-dept:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        filter: brightness(1.05);
    }

    /* Search Container */
    .search-group {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        transition: all 0.25s ease;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.01);
    }

    .search-group:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        background: #ffffff;
    }

    .search-icon-addon {
        background: transparent !important;
        border: none !important;
        color: #94a3b8;
        padding-left: 16px;
    }

    .search-input {
        border: none !important;
        background: transparent !important;
        font-size: 14px;
        padding: 12px 12px 12px 4px;
        height: 46px;
        box-shadow: none !important;
    }

    /* Department Cards */
    .dept-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .dept-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        border-color: rgba(99, 102, 241, 0.2);
    }

    .dept-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--dept-stripe, #4f46e5);
    }

    .dept-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px 12px;
        border-bottom: none;
        background: transparent;
    }

    .dept-title-link {
        text-decoration: none !important;
        color: #0f172a !important;
    }

    .dept-title {
        font-weight: 700;
        font-size: 16px;
        letter-spacing: -0.3px;
        margin-bottom: 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: color 0.2s ease;
    }

    .dept-card:hover .dept-title {
        color: #4f46e5 !important;
    }

    .branch-badge {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 8px;
        border-radius: 6px;
        background: rgba(99, 102, 241, 0.08);
        color: #4f46e5;
        border: 1px solid rgba(99, 102, 241, 0.15);
    }

    .dept-card-body {
        padding: 8px 24px 20px;
    }

    .dept-desc {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 16px;
        line-height: 1.5;
        height: 38px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .dept-footer-strip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }

    /* Stacked Avatars */
    .avatar-stack {
        display: inline-flex;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .avatar-stack li {
        margin-left: -10px;
        position: relative;
        transition: transform 0.2s ease;
    }

    .avatar-stack li:first-child {
        margin-left: 0;
    }

    .avatar-stack li:hover {
        transform: translateY(-4px);
        z-index: 5;
    }

    .avatar-stack img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        object-fit: cover;
    }

    .avatar-stack .stack-count {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.1px;
    }

    .status-active {
        background-color: rgba(16, 185, 129, 0.08);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.15);
    }

    .status-inactive {
        background-color: rgba(239, 68, 68, 0.08);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.15);
    }

    /* Actions UI */
    .btn-card-dots {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .btn-card-dots:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    /* Glassmorphic Modal */
    .modal-content-premium {
        border-radius: 20px !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.1) !important;
        overflow: hidden;
    }

    .modal-header-premium {
        background: linear-gradient(135deg, #1e1e38 0%, #0f172a 100%);
        color: #ffffff !important;
        border-bottom: none !important;
        padding: 20px 28px !important;
    }

    .modal-title-premium {
        font-weight: 700 !important;
        font-size: 18px !important;
        letter-spacing: -0.3px;
        color: #ffffff !important;
    }

    .modal-close-premium {
        color: #94a3b8 !important;
        opacity: 0.8 !important;
        transition: all 0.2s ease;
        background: transparent !important;
        border: none !important;
        font-size: 24px;
        line-height: 1;
    }

    .modal-close-premium:hover {
        color: #ffffff !important;
        transform: scale(1.1);
    }

    .modal-body-premium {
        padding: 28px !important;
    }

    /* Form Fields */
    .form-group-premium {
        margin-bottom: 20px;
    }

    .form-group-premium label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .form-group-premium label i {
        font-size: 16px;
        margin-right: 6px;
        color: #94a3b8;
    }

    .text_required {
        color: #ef4444;
        margin-left: 3px;
    }

    .input-premium {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 10px 14px !important;
        height: auto !important;
        font-size: 14px !important;
        color: #0f172a !important;
        background-color: #ffffff !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.01) !important;
    }

    .input-premium:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12), inset 0 1px 2px rgba(0,0,0,0) !important;
        outline: none !important;
    }

    .input-premium::placeholder {
        color: #94a3b8 !important;
    }

    /* Custom Submit Button */
    .btn-submit-premium {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #ffffff !important;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 10px;
        border: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        cursor: pointer;
    }

    .btn-submit-premium:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
    }

    /* Beautiful Empty State */
    .empty-state-card {
        padding: 60px 20px;
        text-align: center;
        width: 100%;
    }

    .empty-state-icon {
        font-size: 64px;
        color: #94a3b8;
        animation: floatAnimation 3s ease-in-out infinite;
        display: inline-block;
        margin-bottom: 20px;
    }

    @keyframes floatAnimation {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }

    .empty-state-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff !important;
        font-weight: 600;
        padding: 8px 18px;
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        transition: all 0.2s ease;
    }

    .empty-state-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
    }
</style>
@endsection

@section('content')
<div class="container-fluid dept-dashboard-wrapper">
    <!-- Header Gradient Banner -->
    <div class="dept-header-gradient-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="dept-dashboard-title">Departments Workspace</h2>
                <p class="dept-dashboard-subtitle">Configure system business units, allocate branches, manage department workflows, and team organization.</p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <button type="button" class="btn-create-dept btnAddDepartment">
                    <i class="mdi mdi-plus-circle-outline mr-2"></i>New Department
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="row">
        <!-- Metric 1: Total Groups -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium" style="--card-accent: #6366f1;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(99, 102, 241, 0.08); --stat-color: #6366f1;">
                    <i class="mdi mdi-office-building"></i>
                </div>
                <div class="stat-value">{{ $departments->total() }}</div>
                <div class="stat-label">Total Departments</div>
            </div>
        </div>

        <!-- Metric 2: Active Groups -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium" style="--card-accent: #10b981;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(16, 185, 129, 0.08); --stat-color: #10b981;">
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
            <div class="stat-card-premium" style="--card-accent: #ef4444;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(239, 68, 68, 0.08); --stat-color: #ef4444;">
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
            <div class="stat-card-premium" style="--card-accent: #f59e0b;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(245, 158, 11, 0.08); --stat-color: #f59e0b;">
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
    <div class="row mb-4">
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
            <div class="dept-card" style="--dept-stripe: {{ $stripeColor }};">
                <!-- Header -->
                <div class="dept-card-header">
                    <a href="{{ route('departments.show', $item->name) }}" class="dept-title-link">
                        <h5 class="dept-title">
                            {{ $item->name }} 
                            <span class="branch-badge">{{ $item->branch->code }}</span>
                        </h5>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="btn-card-dots dropdown-toggle arrow-none" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <div>
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
                <p class="text-muted mb-4 fs-14" style="max-width: 420px; margin: 0 auto;">Setup your company divisions and assign branches to establish a clear hierarchy structure across your workspace.</p>
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
    $(document).ready(function(){
        // Trigger modal on add department click
        $('.btnAddDepartment').click(function(){
            $('#frm_department')[0].reset();
            $('#department_id').val('');
            $('#mdlDepartment').modal('show');
            $('.modal-title').text('New Department');
            $('.creatBtn').text('Create Department');
            $(".invalid-feedback").children("strong").text("");
            $(".input-premium, .select-premium").removeClass("is-invalid");
        });

        // Close modal
        $('.btnmdlclose').click(function(){
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
        $('#frm_department').submit(function(e){
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");
            $(".input-premium, .select-premium").removeClass("is-invalid");

            $.ajax({
                type: 'POST',
                url: '{{ route('departments.store') }}',
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
        $(document).on('click', '.btn_edit_department', function(e){
            let dept_name = $(this).attr('dept_name');
            $.ajax({
                type: 'GET',
                url: 'departments/' + encodeURIComponent(dept_name) + '/edit',
                beforeSend: function() {
                    $(".invalid-feedback").children("strong").text("");
                    $(".input-premium, .select-premium").removeClass("is-invalid");
                },
                success: function(response) {
                    if(response.status == true){
                        let dept = response.data;
                        $('.modal-title').text('Edit Department Details');
                        $('.creatBtn').text('Update Details');

                        $('#department_id').val(dept.id);
                        $('#name').val(dept.name);
                        $('#description').val(dept.description);
                        $('#branch').val(dept.branchid);
                        $('#mdlDepartment').modal('show');
                    }else{
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
