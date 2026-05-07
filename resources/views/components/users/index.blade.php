@extends('layouts.app')

@section('styles')
<style>
    /* Styling System Custom Tokens */
    .user-dashboard-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding-bottom: 2rem;
    }

    .user-header-gradient-card {
        background: linear-gradient(135deg, #1e1e38 0%, #0f172a 100%);
        border-radius: 18px;
        padding: 30px;
        color: #ffffff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .user-header-gradient-card::after {
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

    .user-dashboard-title {
        font-weight: 700;
        font-size: 26px;
        letter-spacing: -0.5px;
        margin-bottom: 6px;
        color: #ffffff;
    }

    .user-dashboard-subtitle {
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
    .btn-create-member {
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

    .btn-create-member:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        filter: brightness(1.05);
    }

    .btn-create-member i {
        font-size: 16px;
    }

    /* Table Card */
    .table-card-premium {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        padding: 24px;
        margin-bottom: 24px;
    }

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

    .table-premium {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0 6px;
    }

    .table-premium thead th {
        border: none;
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        background: #f1f5f9;
        border-radius: 6px;
    }

    .table-premium tbody tr {
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .table-premium tbody tr:hover {
        background: #f8fafc !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }

    .table-premium tbody td {
        padding: 14px 16px;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 14px;
        vertical-align: middle;
    }

    .table-premium tbody tr td:first-child {
        border-left: 1px solid #f1f5f9;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        font-weight: 600;
        color: #64748b;
    }

    .table-premium tbody tr td:last-child {
        border-right: 1px solid #f1f5f9;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .user-avatar-text {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #ffffff;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        font-size: 14px;
        margin-right: 12px;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.15);
    }

    .user-meta-name {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 1px;
    }

    .user-meta-sub {
        font-size: 12px;
        color: #64748b;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.1px;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .status-active {
        background-color: rgba(16, 185, 129, 0.08);
        color: #059669 !important;
        border: 1px solid rgba(16, 185, 129, 0.15);
    }

    .status-active:hover {
        background-color: rgba(16, 185, 129, 0.15);
        transform: translateY(-1px);
    }

    .status-inactive {
        background-color: rgba(239, 68, 68, 0.08);
        color: #dc2626 !important;
        border: 1px solid rgba(239, 68, 68, 0.15);
    }

    .status-inactive:hover {
        background-color: rgba(239, 68, 68, 0.15);
        transform: translateY(-1px);
    }

    .status-dot-active {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #10b981;
        margin-right: 6px;
        display: inline-block;
        animation: pulseActive 2s infinite;
    }

    .status-dot-inactive {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #ef4444;
        margin-right: 6px;
        display: inline-block;
        animation: pulseInactive 2s infinite;
    }

    @keyframes pulseActive {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    @keyframes pulseInactive {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    /* Custom Role Badges */
    .badge-role {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .role-admin {
        background-color: rgba(239, 68, 68, 0.08);
        color: #b91c1c;
        border: 1px solid rgba(239, 68, 68, 0.15);
    }

    .role-leader {
        background-color: rgba(245, 158, 11, 0.08);
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.15);
    }

    .role-sales {
        background-color: rgba(59, 130, 246, 0.08);
        color: #1d4ed8;
        border: 1px solid rgba(59, 130, 246, 0.15);
    }

    .role-developer {
        background-color: rgba(99, 102, 241, 0.08);
        color: #4338ca;
        border: 1px solid rgba(99, 102, 241, 0.15);
    }

    .role-designer {
        background-color: rgba(168, 85, 247, 0.08);
        color: #7e22ce;
        border: 1px solid rgba(168, 85, 247, 0.15);
    }

    .role-seo {
        background-color: rgba(16, 185, 129, 0.08);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.15);
    }

    .role-accountant {
        background-color: rgba(14, 165, 233, 0.08);
        color: #0369a1;
        border: 1px solid rgba(14, 165, 233, 0.15);
    }

    .role-default {
        background-color: rgba(107, 114, 128, 0.08);
        color: #374151;
        border: 1px solid rgba(107, 114, 128, 0.15);
    }

    /* Actions UI */
    .btn-edit-member {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .btn-edit-member:hover {
        background: #fef3c7;
        color: #d97706;
        border-color: #fde68a;
        transform: scale(1.1);
    }

    /* Beautiful Empty State */
    .empty-state-card {
        padding: 60px 20px;
        text-align: center;
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
<div class="container-fluid user-dashboard-wrapper">
    <!-- Header Gradient Banner -->
    <div class="user-header-gradient-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="user-dashboard-title">Team Management Workspace</h2>
                <p class="user-dashboard-subtitle">Manage roles, departments, system logins, and professional credentials for your workspace members.</p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <a href="{{ route('users.create') }}" class="btn-create-member">
                    <i class="mdi mdi-plus-circle-outline mr-2"></i>New Member
                </a>
            </div>
        </div>
    </div>

    <!-- Interactive Stats Strip -->
    <div class="row">
        <!-- Card 1: Total Team -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium" style="--card-accent: #6366f1;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(99, 102, 241, 0.08); --stat-color: #6366f1;">
                    <i class="mdi mdi-account-group-outline"></i>
                </div>
                <div class="stat-value">{{ $users->total() }}</div>
                <div class="stat-label">Total Team Members</div>
            </div>
        </div>

        <!-- Card 2: Active Accounts -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium" style="--card-accent: #10b981;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(16, 185, 129, 0.08); --stat-color: #10b981;">
                    <i class="mdi mdi-account-check-outline"></i>
                </div>
                <div class="stat-value">
                    {{ \App\Models\User::where('deleted_at', null)->where('id', '!=', '1')->where('status', 'Active')->count() }}
                </div>
                <div class="stat-label">Active Team Accounts</div>
            </div>
        </div>

        <!-- Card 3: In-Active Accounts -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium" style="--card-accent: #ef4444;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(239, 68, 68, 0.08); --stat-color: #ef4444;">
                    <i class="mdi mdi-account-off-outline"></i>
                </div>
                <div class="stat-value">
                    {{ \App\Models\User::where('deleted_at', null)->where('id', '!=', '1')->where('status', 'Inactive')->count() }}
                </div>
                <div class="stat-label">Inactive Team Accounts</div>
            </div>
        </div>

        <!-- Card 4: Departments Divisions -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium" style="--card-accent: #f59e0b;">
                <div class="stat-icon-wrapper" style="--stat-bg: rgba(245, 158, 11, 0.08); --stat-color: #f59e0b;">
                    <i class="mdi mdi-domain"></i>
                </div>
                <div class="stat-value">
                    {{ \App\Models\Department::where('status', true)->count() }}
                </div>
                <div class="stat-label">Active Departments</div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="row">
        <div class="col-12">
            <div class="table-card-premium">
                @if(!$users->isEmpty())
                <!-- Search Filter Bar -->
                <div class="search-container mb-4">
                    <div class="input-group search-group">
                        <span class="input-group-text search-icon-addon">
                            <i class="mdi mdi-magnify"></i>
                        </span>
                        <input type="text" id="userSearchInput" class="form-control search-input" placeholder="Quick search by name, email, member code, department, or role...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-centered mb-0 table-hover table-premium" id="userListingTable">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 8%">Sl No</th>
                                <th scope="col" style="width: 20%;">Name</th>
                                <th scope="col" style="width: 22%;">Email</th>
                                <th scope="col" style="width: 12%;">Member Code</th>
                                <th scope="col" style="width: 12%;">System Role</th>
                                <th scope="col" style="width: 13%;" class="text-center">Department</th>
                                <th scope="col" style="width: 8%;">Status</th>
                                @if($user->hasRole("Admin"))
                                <th scope="col" style="width: 5%;" class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $items)
                            @php
                                $roleName = $items->roles->pluck('name')->first() ?? 'No Role';
                                $roleClass = 'role-default';
                                if (str_contains(strtolower($roleName), 'admin')) $roleClass = 'role-admin';
                                else if (str_contains(strtolower($roleName), 'leader')) $roleClass = 'role-leader';
                                else if (str_contains(strtolower($roleName), 'sales')) $roleClass = 'role-sales';
                                else if (str_contains(strtolower($roleName), 'developer')) $roleClass = 'role-developer';
                                else if (str_contains(strtolower($roleName), 'designer')) $roleClass = 'role-designer';
                                else if (str_contains(strtolower($roleName), 'seo')) $roleClass = 'role-seo';
                                else if (str_contains(strtolower($roleName), 'accountant')) $roleClass = 'role-accountant';
                            @endphp
                            <tr class="user-row">
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-text">
                                            {{ strtoupper(substr($items->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="user-meta-name">{{ $items->name }}</div>
                                            <div class="user-meta-sub">{{ $items->emp->designation ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-medium text-muted">{{ $items->email }}</span>
                                </td>
                                <td>
                                    <code class="text-uppercase font-weight-bold" style="color: #4f46e5; font-size: 13px;">{{ $items->emp->mem_code ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    <span class="badge-role {{ $roleClass }}">{{ $roleName }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="font-weight-semibold text-dark">{{ $items->departments->dept->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($items->status == 'Active')
                                    <a href="{{ route('users.changeStatus', $items->id ) }}" class="status-badge status-active" data-toggle="tooltip" title="Click to Deactivate">
                                        <span class="status-dot-active"></span>Active
                                    </a>
                                    @else
                                    <a href="{{ route('users.changeStatus', $items->id ) }}" class="status-badge status-inactive" data-toggle="tooltip" title="Click to Activate">
                                        <span class="status-dot-inactive"></span>In-Active
                                    </a>
                                    @endif
                                </td>
                                @if($user->hasRole("Admin"))
                                <td class="text-center">
                                    <a type="button" class="btn-edit-member" href="{{ route('users.edit', $items->id ) }}"
                                        data-toggle="tooltip" data-placement="left" title="Edit Member Credentials">
                                        <i class="mdi mdi-square-edit-outline font-size-16"></i>
                                    </a>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <!-- Trigger Fallback just in case -->
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Custom Pagination -->
                <div class="row mt-4">
                    <div class="col-md-6 d-flex align-items-center">
                        <span class="text-muted font-size-13">Showing members {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }}</span>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        {{ $users->links("pagination::bootstrap-4") }}
                    </div>
                </div>

                @else
                <!-- Elegant Custom Empty State Card -->
                <div class="empty-state-card">
                    <div class="empty-state-icon">
                        <i class="mdi mdi-account-multiple-plus-outline"></i>
                    </div>
                    <h3 class="font-weight-bold text-dark mb-2">No Workspace Members Exist</h3>
                    <p class="text-muted mb-4 fs-14" style="max-width: 420px; margin: 0 auto;">It looks like there are no accounts inside your workspace yet. Create your very first team member account with customized permissions.</p>
                    <a href="{{ route('users.create') }}" class="empty-state-btn">
                        <i class="mdi mdi-plus-circle-outline mr-2"></i>Create Member Account
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Bootstrap Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // High-Speed Instant Interactive Search Filter
        $('#userSearchInput').on('keyup', function() {
            let value = $(this).val().toLowerCase().trim();
            
            $('#userListingTable tbody tr.user-row').filter(function() {
                let text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1);
            });
            
            // Handle display if no matches are found
            let visibleRows = $('#userListingTable tbody tr.user-row:visible').length;
            let noMatchRow = $('#noMatchRow');
            
            if (visibleRows === 0 && $('#userListingTable tbody').children().length > 0) {
                if (noMatchRow.length === 0) {
                    $('#userListingTable tbody').append(`
                        <tr id="noMatchRow">
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="mdi mdi-magnify-minus font-size-24 d-block mb-2"></i>
                                No workspace members found matching "${$(this).val()}"
                            </td>
                        </tr>
                    `);
                }
            } else {
                noMatchRow.remove();
            }
        });
    });
</script>
@endsection
