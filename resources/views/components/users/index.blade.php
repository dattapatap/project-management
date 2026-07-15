@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page erp-page--admin user-dashboard-wrapper">
    <div class="erp-page-header">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">Team Management</h4>
            <p class="erp-page-subtitle">Manage roles, departments, logins, and credentials for workspace members.</p>
        </div>
        <div class="erp-page-header__actions">
            <a href="{{ url('/') }}" class="btn btn-outline-primary btn-sm mr-2">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus"></i> New Member
            </a>
        </div>
    </div>

    <!-- Interactive Stats Strip -->
    <div class="row mb-3">
        <!-- Card 1: Total Team -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium stat-card-premium--indigo">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-group-outline"></i>
                </div>
                <div class="stat-value">{{ $users->total() }}</div>
                <div class="stat-label">Total Team Members</div>
            </div>
        </div>

        <!-- Card 2: Active Accounts -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-premium stat-card-premium--success">
                <div class="stat-icon-wrapper">
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
            <div class="stat-card-premium stat-card-premium--danger">
                <div class="stat-icon-wrapper">
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
            <div class="stat-card-premium stat-card-premium--warning">
                <div class="stat-icon-wrapper">
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
                    <table class="table table-striped mb-0 table-hover table-premium erp-table--users" id="userListingTable">
                        <thead class="thead-custom-teal">
                            <tr>
                                <th scope="col" class="col-sl">Sl No</th>
                                <th scope="col" class="col-name">Name</th>
                                <th scope="col" class="col-email">Email</th>
                                <th scope="col" class="col-code">Member Code</th>
                                <th scope="col" class="col-role">System Role</th>
                                <th scope="col" class="col-dept text-center">Department</th>
                                <th scope="col" class="col-status">Status</th>
                                @if($user->hasBranchWideAccess())
                                <th scope="col" class="col-action text-center">Action</th>
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
                                    <code class="erp-mem-code text-uppercase">{{ $items->emp->mem_code ?? 'N/A' }}</code>
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
                                @if($user->hasBranchWideAccess())
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
                    <p class="text-muted mb-4 erp-empty-state__text erp-empty-state__text--narrow">It looks like there are no accounts inside your workspace yet. Create your very first team member account with customized permissions.</p>
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
