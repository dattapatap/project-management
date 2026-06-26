@extends('layouts.app')

@section('styles')
<style>
    /* Styling System Custom Tokens */
    .teams-dashboard-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, sans-serif;
        padding-bottom: 2rem;
    }

    .btn-back-premium {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #4b5563;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
    }

    .btn-back-premium:hover {
        background: #f8fafc;
        color: #4f46e5;
        border-color: #cbd5e1;
        transform: translateX(-4px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);
    }

    /* Tabs Styling */
    .nav-tabs-premium {
        border-bottom: 2px solid #f1f5f9 !important;
        gap: 8px;
        margin-bottom: 24px;
        background: transparent;
    }

    .nav-tabs-premium .nav-item {
        margin-bottom: -2px;
    }

    .nav-tabs-premium .nav-link {
        border: none !important;
        font-size: 14px;
        font-weight: 600;
        color: #64748b !important;
        padding: 12px 24px !important;
        border-radius: 0 !important;
        border-bottom: 2px solid transparent !important;
        transition: all 0.25s ease-in-out;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .nav-tabs-premium .nav-link.active {
        color: #4f46e5 !important;
        background: transparent !important;
        border-bottom-color: #4f46e5 !important;
    }

    .nav-tabs-premium .nav-link:hover:not(.active) {
        color: #0f172a !important;
        border-bottom-color: #cbd5e1 !important;
    }

    /* Card Details Container */
    .details-card-premium {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        padding: 24px;
        margin-bottom: 24px;
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

    /* Action Buttons */
    .btn-create-premium {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #ffffff !important;
        font-weight: 600;
        padding: 8px 18px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        font-size: 13px;
    }

    .btn-create-premium:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
        filter: brightness(1.05);
    }

    /* Card Item Grid */
    .team-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .team-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
        border-color: rgba(99, 102, 241, 0.2);
    }

    .team-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--team-stripe, #6366f1);
    }

    .team-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 20px 10px;
    }

    .team-title {
        font-weight: 700;
        font-size: 15px;
        color: #0f172a;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .team-members-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 3px 6px;
        border-radius: 6px;
        background: rgba(99, 102, 241, 0.08);
        color: #4f46e5;
    }

    .team-card-body {
        padding: 6px 20px 18px;
    }

    .team-desc {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 14px;
        line-height: 1.5;
        height: 38px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .team-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }

    /* Overlapping Avatars */
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
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        object-fit: cover;
    }

    .avatar-stack .stack-count {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Actions UI Elements */
    .btn-circle-action {
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

    .btn-circle-action:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    /* Statuses Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
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

    /* Custom Glassmorphic Modal */
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

    /* Drag and Drop Lists styling */
    .list-group-premium {
        border: 1px dashed #cbd5e1 !important;
        border-radius: 12px !important;
        background-color: #f8fafc !important;
        padding: 12px !important;
        min-height: 380px !important;
        max-height: 480px !important;
        overflow-y: auto !important;
    }

    .list-group-header {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Draggable Item Card overrides */
    #userslist .list-group-item, #userslist-unassigned .list-group-item {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        margin-bottom: 8px !important;
        color: #334155 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        cursor: grab;
        display: flex;
        align-items: center;
        justify-content: space-between !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.01) !important;
        transition: all 0.2s ease !important;
    }

    #userslist .list-group-item:hover, #userslist-unassigned .list-group-item:hover {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.03) !important;
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

    /* Modal Submit Button */
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
</style>
@endsection

@section('content')
<div class="container-fluid teams-dashboard-wrapper">
    <!-- Header Page Title Banner -->
    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <a href="{{ url('/departments') }}" class="btn-back-premium mr-3" data-toggle="tooltip" title="Back to Departments">
                    <i class="mdi mdi-keyboard-backspace"></i>
                </a>
                <div>
                    <h4 class="mb-0 font-size-18 font-weight-bold text-dark">{{ $department->name }} Teams</h4>
                    <span class="text-muted font-size-13">Configure and align working teams under this department structure.</span>
                </div>
            </div>

            <div class="page-title-right d-none d-sm-block">
                <ol class="breadcrumb m-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-muted">{{ env('APP_NAME') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/departments') }}" class="text-muted">Departments</a></li>
                    <li class="breadcrumb-item active font-weight-semibold text-primary">{{ $department->name }} Teams</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Workspace Card Container -->
    <div class="row">
        <div class="col-12">
            <div class="details-card-premium">
                <!-- Navigation Tabs Strip -->
                <ul class="nav nav-tabs nav-tabs-premium" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('departments/'. $department->name .'/') }}" role="tab">
                            <i class="mdi mdi-account-multiple-outline"></i>
                            <span>Department Members</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#teams" role="tab">
                            <i class="mdi mdi-account-group-outline"></i>
                            <span>Associated Teams</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content Panel -->
                <div class="tab-content">
                    <div class="tab-pane active" id="teams" role="tabpanel">
                        <!-- Actions & Search Filters Row -->
                        <div class="row align-items-center mb-4">
                            <div class="col-md-8 col-sm-7">
                                <div class="input-group search-group mb-3 mb-sm-0">
                                    <span class="input-group-text search-icon-addon">
                                        <i class="mdi mdi-magnify"></i>
                                    </span>
                                    <input type="text" id="teamSearchInput" class="form-control search-input" placeholder="Filter teams instantly by name or description...">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-5 text-sm-right">
                                <button type="button" class="btn-create-premium btnAddTeam">
                                    <i class="mdi mdi-plus-circle-outline mr-2"></i>Manage Teams
                                </button>
                            </div>
                        </div>

                        <!-- Teams Cards Grid -->
                        <div class="row teams-grid-row">
                            @forelse ($teams as $item)
                            <div class="col-lg-4 col-md-6 team-card-col">
                                <div class="team-card team-card-clickable"
                                     style="--team-stripe: #4f46e5;"
                                     data-team-id="{{ $item->id }}"
                                     data-department-id="{{ $department->id }}"
                                     role="button"
                                     tabindex="0"
                                     title="Manage {{ $item->name }}">
                                    <!-- Card Header -->
                                    <div class="team-card-header">
                                        <h5 class="team-title">
                                            {{ $item->name }}
                                            <span class="team-members-badge">{{ $item->teammembers->count() }} Members</span>
                                        </h5>
                                        <div class="d-flex align-items-center gap-2 team-card-actions">
                                            <!-- Manage Team Members (Sortable list) -->
                                            <a href="javascript:void(0);" team_id="{{ $item->id }}" departmentid="{{ $department->id }}" class="btn-circle-action btnAddMembers mr-1" data-toggle="tooltip" title="Manage Team Members">
                                                <i class="mdi mdi-plus-outline"></i>
                                            </a>
                                            <!-- Options menu -->
                                            <div class="dropdown">
                                                <a href="#" class="btn-circle-action dropdown-toggle arrow-none" data-toggle="dropdown" aria-expanded="false">
                                                    <i class="mdi mdi-dots-vertical"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item btn_edit_team font-size-13" teamid="{{ $item->id }}" href="javascript:void(0);">
                                                        <i class="mdi mdi-pencil mr-1 text-warning"></i> Edit Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="team-card-body">
                                        <p class="team-desc">{{ $item->description ?? 'No group descriptions configured.' }}</p>

                                        <!-- Footer strip: Members and status -->
                                        <div class="team-card-footer">
                                            <!-- Overlapping Avatars -->
                                            <div>
                                                @if(!$item->teammembers->isEmpty())
                                                <ul class="avatar-stack">
                                                    @php $stackedCount = 0; @endphp
                                                    @foreach ($item->teammembers as $members)
                                                        @if($stackedCount < 6)
                                                        <li>
                                                            @if ($members->users->profile)
                                                                <img title="{{ $members->users->name }}" src="{{ asset('storage/'. $members->users->profile )}}">
                                                            @else
                                                                <img title="{{ $members->users->name }}" src="{{ Avatar::create($members->users->name)->toBase64() }}">
                                                            @endif
                                                        </li>
                                                        @php $stackedCount++; @endphp
                                                        @endif
                                                    @endforeach
                                                    
                                                    @php $totalMembers = $item->teammembers()->count(); @endphp
                                                    @if($totalMembers > 6)
                                                        <li>
                                                            <span class="stack-count">+{{ $totalMembers - 6 }}</span>
                                                        </li>
                                                    @endif
                                                </ul>
                                                @else
                                                <span class="text-muted font-size-12"><i class="mdi mdi-account-off mr-1"></i>No Members Aligned</span>
                                                @endif
                                            </div>

                                            <!-- Active Badge Indicator -->
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
                            <!-- Empty State -->
                            <div class="col-12 text-center py-5">
                                <div class="empty-state-card">
                                    <div class="empty-state-icon">
                                        <i class="mdi mdi-account-group-outline"></i>
                                    </div>
                                    <h3 class="font-weight-bold text-dark mb-2">No Active Teams Found</h3>
                                    <p class="text-muted mb-4 fs-14" style="max-width: 420px; margin: 0 auto;">Organize working pods, assign team leadership boundaries, and allocate resources easily under this department division.</p>
                                    <button href="javascript:void(0);" class="btn-create-premium btnAddTeam">
                                        <i class="mdi mdi-plus-circle-outline mr-2"></i>Create First Team
                                    </button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Drag-and-Drop Drag Members Modal -->
<div id="mdlDeptUsers" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-content-premium">
            <div class="modal-header modal-header-premium">
                <h5 class="modal-title modal-title-premium">Manage Team Members</h5>
                <button type="button" class="modal-close-premium btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-premium">
                <p class="text-muted font-size-13 mb-4">Drag and drop workspace profiles from the <strong>Un-Assigned</strong> bucket directly into the <strong>Team Members</strong> column to allocate them to this working pod.</p>
                <form id="frm_department_member" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="teamid" value="-1" id="teamid">
                    <input type="hidden" name="departmentid" value="{{ $department->id }}" id="departmentid">
                    
                    <div class="row">
                        <!-- Column: Team Members (Active) -->
                        <div class="col-md-6 pr-md-3">
                            <span class="list-group-header text-primary">
                                <i class="mdi mdi-checkbox-marked-circle-outline"></i>Aligned Team Members
                            </span>
                            <ul id="userslist" class="list-group list-group-premium">
                                <!-- Dynamic JS contents -->
                            </ul>
                        </div>
                        
                        <!-- Column: Un-Assigned Department Members -->
                        <div class="col-md-6 pl-md-3 mt-4 mt-md-0">
                            <span class="list-group-header text-danger">
                                <i class="mdi mdi-alert-circle-outline"></i>Un-Assigned Members
                            </span>
                            <ul id="userslist-unassigned" class="list-group list-group-premium">
                                <!-- Dynamic JS contents -->
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create / Edit Team Modal -->
<div id="mdlTeams" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-premium">
            <div class="modal-header modal-header-premium">
                <h5 class="modal-title modal-title-premium"></h5>
                <button type="button" class="modal-close-premium btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-premium">
                <form id="frm_teams" class="custom-validation" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="team_id" value="-1" id="team_id">
                    <input type="hidden" name="department" value="{{ $department->id }}" id="department">
                    
                    <!-- Field: Team Name -->
                    <div class="form-group form-group-premium">
                        <label for="name">
                            <i class="mdi mdi-account-group"></i>Team Name <span class="text_required">*</span>
                        </label>
                        <input type="text" name="name" id="name" class="form-control input-premium" placeholder="e.g., frontend-engineers" required>
                        <span class="invalid-feedback" id="name-input-error" role="alert">
                            <strong></strong>
                        </span>
                    </div>

                    <!-- Field: Description -->
                    <div class="form-group form-group-premium">
                        <label for="description">
                            <i class="mdi mdi-text-box-outline"></i>Description <span class="text_required">*</span>
                        </label>
                        <input type="text" name="description" id="description" class="form-control input-premium" placeholder="e.g., Visual asset creations & layouts updates" required>
                        <span class="invalid-feedback" id="description-input-error" role="alert">
                            <strong></strong>
                        </span>
                    </div>

                    <!-- Footer actions -->
                    <div class="row mt-4 pt-3 border-top border-light">
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
<script src="{{ asset('assets/libs/draggable/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/libs/draggable/jquery-sortable.js') }}"></script>
<script src="{{ asset('js/teams.js') }}"></script>
<script>
    $(document).ready(function() {
        // Trigger Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Open team members modal on card click
        $(document).on('click', '.team-card-clickable', function(e) {
            if ($(e.target).closest('.team-card-actions, .dropdown, .dropdown-menu').length) {
                return;
            }
            $(this).find('.btnAddMembers').first().trigger('click');
        });

        $(document).on('keydown', '.team-card-clickable', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).find('.btnAddMembers').first().trigger('click');
            }
        });

        // Trigger New Team Modal
        $('.btnAddTeam').click(function(){
            $('#frm_teams')[0].reset();
            $('#team_id').val('-1');
            $('#mdlTeams').modal('show');
            $('.modal-title').text('Configure Working Pod Team');
            $('.creatBtn').text('Create Team');
            $(".invalid-feedback").children("strong").text("");
            $(".input-premium").removeClass("is-invalid");
        });

        // Close Modals
        $('.btnmdlclose').click(function(){
            $('#mdlTeams').modal('hide');
            $('#mdlDeptUsers').modal('hide');
        });

        // Live card filtering
        $('#teamSearchInput').on('keyup', function() {
            let value = $(this).val().toLowerCase().trim();
            
            $('.teams-grid-row .team-card-col').filter(function() {
                let text = $(this).find('.team-card').text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1);
            });
            
            let visibleCards = $('.teams-grid-row .team-card-col:visible').length;
            let noMatchCard = $('#noMatchTeamCard');
            
            if (visibleCards === 0 && $('.teams-grid-row').children('.team-card-col').length > 0) {
                if (noMatchCard.length === 0) {
                    $('.teams-grid-row').append(`
                        <div id="noMatchTeamCard" class="col-12 text-center py-5 text-muted">
                            <i class="mdi mdi-account-search-outline font-size-24 d-block mb-2"></i>
                            No teams found matching "${$(this).val()}"
                        </div>
                    `);
                }
            } else {
                noMatchCard.remove();
            }
        });
    });
</script>
@endsection
