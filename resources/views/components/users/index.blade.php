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
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="stat-card-premium stat-card-premium--indigo">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-group-outline"></i>
                </div>
                <div class="stat-value">{{ $statusCounts['all'] ?? $users->total() }}</div>
                <div class="stat-label">Total Team Members</div>
            </div>
        </div>

        <!-- Card 2: Active Working Accounts -->
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="stat-card-premium stat-card-premium--success">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-check-outline"></i>
                </div>
                <div class="stat-value">
                    {{ $statusCounts['working'] ?? 0 }}
                </div>
                <div class="stat-label">Active Working Staff</div>
            </div>
        </div>

        <!-- Card 3: Suspended Accounts -->
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="stat-card-premium stat-card-premium--danger">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-pause-circle-outline"></i>
                </div>
                <div class="stat-value">
                    {{ $statusCounts['suspended'] ?? 0 }}
                </div>
                <div class="stat-label">Suspended Staff</div>
            </div>
        </div>

        <!-- Card 4: Separated (Resigned/Terminated) -->
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="stat-card-premium stat-card-premium--warning">
                <div class="stat-icon-wrapper">
                    <i class="mdi mdi-account-arrow-right-outline"></i>
                </div>
                <div class="stat-value">
                    {{ $statusCounts['separated'] ?? 0 }}
                </div>
                <div class="stat-label">Separated / Ex-Staff</div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="row">
        <div class="col-12">
            <div class="table-card-premium">
                <!-- Status Filter Tabs & Search Bar -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 border-bottom pb-3" style="gap: 10px;">
                    <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                        <a href="{{ route('users.index') }}" 
                           class="btn btn-sm font-weight-bold px-2.5 py-1 {{ ($statusFilter ?? 'all') === 'all' ? 'btn-primary text-white' : 'btn-light border text-muted' }}" 
                           style="border-radius: 8px; font-size: 11.5px;">
                            All <span class="badge badge-light ml-1 text-dark">{{ $statusCounts['all'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('users.index', ['status' => 'working']) }}" 
                           class="btn btn-sm font-weight-bold px-2.5 py-1 {{ ($statusFilter ?? '') === 'working' ? 'btn-success text-white' : 'btn-light border text-muted' }}" 
                           style="border-radius: 8px; font-size: 11.5px;">
                            Working <span class="badge badge-soft-success ml-1">{{ $statusCounts['working'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('users.index', ['status' => 'active']) }}" 
                           class="btn btn-sm font-weight-bold px-2.5 py-1 {{ ($statusFilter ?? '') === 'active' ? 'btn-success text-white' : 'btn-light border text-muted' }}" 
                           style="border-radius: 8px; font-size: 11.5px;">
                            Active <span class="badge badge-soft-success ml-1">{{ $statusCounts['active'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('users.index', ['status' => 'probation']) }}" 
                           class="btn btn-sm font-weight-bold px-2.5 py-1 {{ ($statusFilter ?? '') === 'probation' ? 'btn-info text-white' : 'btn-light border text-muted' }}" 
                           style="border-radius: 8px; font-size: 11.5px;">
                            Probation <span class="badge badge-soft-info ml-1">{{ $statusCounts['probation'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('users.index', ['status' => 'notice']) }}" 
                           class="btn btn-sm font-weight-bold px-2.5 py-1 {{ ($statusFilter ?? '') === 'notice' ? 'btn-warning text-white' : 'btn-light border text-muted' }}" 
                           style="border-radius: 8px; font-size: 11.5px;">
                            Notice Period <span class="badge badge-soft-warning ml-1">{{ $statusCounts['notice'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('users.index', ['status' => 'suspended']) }}" 
                           class="btn btn-sm font-weight-bold px-2.5 py-1 {{ ($statusFilter ?? '') === 'suspended' ? 'btn-danger text-white' : 'btn-light border text-muted' }}" 
                           style="border-radius: 8px; font-size: 11.5px;">
                            Suspended <span class="badge badge-soft-danger ml-1">{{ $statusCounts['suspended'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('users.index', ['status' => 'separated']) }}" 
                           class="btn btn-sm font-weight-bold px-2.5 py-1 {{ ($statusFilter ?? '') === 'separated' ? 'btn-dark text-white' : 'btn-light border text-muted' }}" 
                           style="border-radius: 8px; font-size: 11.5px;">
                            Separated (Ex-Staff) <span class="badge badge-soft-secondary ml-1">{{ $statusCounts['separated'] ?? 0 }}</span>
                        </a>
                    </div>
                </div>

                @if(!$users->isEmpty())
                <!-- Search Filter Bar -->
                <div class="search-container mb-3">
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
                                    @php
                                        $badge = $items->status_badge;
                                        $empRecord = $items->emp;
                                        $relievingDate = $empRecord?->end_dt ? \Carbon\Carbon::parse($empRecord->end_dt)->format('d M Y') : null;
                                    @endphp
                                    <button type="button" class="btn btn-sm p-0 border-0 bg-transparent btn-trigger-status-modal"
                                            data-user-id="{{ $items->id }}"
                                            data-user-name="{{ $items->name }}"
                                            data-user-code="{{ $empRecord->mem_code ?? 'EMP-' . $items->id }}"
                                            data-user-dept="{{ $items->departments->dept->name ?? 'General' }}"
                                            data-user-status="{{ $items->status }}"
                                            data-user-end-dt="{{ $empRecord?->end_dt ? \Carbon\Carbon::parse($empRecord->end_dt)->format('Y-m-d') : '' }}"
                                            data-toggle="tooltip" data-placement="top" title="Click to Change Status (Current: {{ $badge['label'] }})">
                                        <span class="status-badge {{ $badge['class'] }}" style="border: 1px solid {{ $badge['border'] }}; color: {{ $badge['color'] }}; background: {{ $badge['bg'] }}; border-radius: 20px; padding: 4px 10px; font-weight: 600; font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; transition: transform 0.15s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            <i class="mdi {{ $badge['icon'] }}" style="font-size: 13px;"></i>
                                            {{ $badge['label'] }}
                                        </span>
                                    </button>
                                    @if($items->status === 'Notice Period' && $relievingDate)
                                    <div class="text-muted font-size-10 mt-0.5" style="letter-spacing: 0.2px;">LWD: {{ $relievingDate }}</div>
                                    @elseif(in_array($items->status, ['Resigned', 'Terminated']) && $relievingDate)
                                    <div class="text-muted font-size-10 mt-0.5" style="letter-spacing: 0.2px;">Relieved: {{ $relievingDate }}</div>
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
                    <p class="text-muted mb-4 erp-empty-state__text erp-empty-state__text--narrow">It looks like there are no accounts matching the selected filter. You can change filters or create a new team member account.</p>
                    <a href="{{ route('users.create') }}" class="empty-state-btn">
                        <i class="mdi mdi-plus-circle-outline mr-2"></i>Create Member Account
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal: Quick Update Employee Status -->
<div class="modal fade" id="modalChangeUserStatus" tabindex="-1" role="dialog" aria-labelledby="modalChangeUserStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-2.5" style="width: 36px; height: 36px; background: rgba(255,255,255,0.15); font-size: 18px;">
                        <i class="mdi mdi-account-switch-outline"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold font-size-15 mb-0 text-white" id="modalChangeUserStatusLabel">Update Employee Status</h5>
                        <span class="font-size-11 text-white-50">Change operational & login status across WMS</span>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formChangeUserStatus" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <!-- Member Details Banner -->
                    <div class="p-3 mb-3 d-flex align-items-center justify-content-between rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div>
                            <h6 class="mb-0 font-weight-bold text-dark font-size-14" id="modalTargetUserName">—</h6>
                            <span class="text-muted font-size-11" id="modalTargetUserMeta">—</span>
                        </div>
                        <div id="modalTargetCurrentBadge">—</div>
                    </div>

                    <!-- Status Selection -->
                    <div class="form-group mb-3">
                        <label for="modalSelectStatus" class="font-weight-bold text-dark font-size-12 mb-1">
                            Select New Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" name="status" id="modalSelectStatus" required style="border-radius: 10px; height: 42px; font-weight: 600;">
                            <option value="Active">🟢 Active (Normal Working Staff)</option>
                            <option value="Probation">🔵 Probation (New Hire Under Probation)</option>
                            <option value="Notice Period">🟡 Notice Period (Resigned, Serving Notice)</option>
                            <option value="Suspended">🔴 Suspended (Disciplinary / Inquiry - Login Blocked)</option>
                            <option value="Resigned">⚪ Resigned (Relieved from Company - Login Blocked)</option>
                            <option value="Terminated">⚫ Terminated (Employment Terminated - Login Blocked)</option>
                        </select>
                    </div>

                    <!-- Date Field (Relieving / Last Working Date) -->
                    <div class="form-group mb-3" id="modalDateGroup" style="display: none;">
                        <label for="modalInputEndDt" class="font-weight-bold text-dark font-size-12 mb-1" id="modalDateLabel">
                            Relieving / Last Working Date
                        </label>
                        <input type="date" class="form-control" name="end_dt" id="modalInputEndDt" style="border-radius: 10px; height: 40px;">
                        <small class="text-muted font-size-11" id="modalDateHelp">Set expected or actual last day with the organization.</small>
                    </div>

                    <!-- Remarks -->
                    <div class="form-group mb-3">
                        <label for="modalInputRemarks" class="font-weight-bold text-dark font-size-12 mb-1">
                            Remarks / Reason <span class="text-muted font-weight-normal font-size-11">(optional)</span>
                        </label>
                        <textarea class="form-control" name="remarks" id="modalInputRemarks" rows="2" placeholder="e.g. Resignation accepted, performance review, mutual consent..." style="border-radius: 10px; font-size: 12.5px;"></textarea>
                    </div>

                    <!-- Separation Notice Box -->
                    <div class="alert alert-warning py-2.5 px-3 mb-0" id="modalSeparationWarning" style="border-radius: 10px; font-size: 11.5px; display: none;">
                        <i class="mdi mdi-alert-circle mr-1"></i>
                        <strong>Important:</strong> Changing status to <strong>Suspended</strong>, <strong>Resigned</strong>, or <strong>Terminated</strong> will immediately revoke ERP login access and stop any currently active shift or task timers.
                    </div>
                </div>
                <div class="modal-footer py-2.5 px-4 bg-light border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-4" id="btnSubmitStatusChange" style="border-radius: 8px;">
                        <i class="mdi mdi-check mr-1"></i> Update Status
                    </button>
                </div>
            </form>
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

        // Quick Status Modal Trigger
        $('.btn-trigger-status-modal').on('click', function(e) {
            e.preventDefault();
            let userId = $(this).data('user-id');
            let userName = $(this).data('user-name');
            let userCode = $(this).data('user-code');
            let userDept = $(this).data('user-dept');
            let currentStatus = $(this).data('user-status');
            let endDt = $(this).data('user-end-dt');

            $('#modalTargetUserName').text(userName);
            $('#modalTargetUserMeta').text(`${userCode} • ${userDept}`);
            $('#modalSelectStatus').val(currentStatus);
            $('#modalInputEndDt').val(endDt || '');
            $('#modalInputRemarks').val('');

            let postUrl = "{{ url('/users/status') }}/" + userId;
            $('#formChangeUserStatus').attr('action', postUrl);

            handleStatusFields(currentStatus);

            $('#modalChangeUserStatus').modal('show');
        });

        function handleStatusFields(status) {
            if (status === 'Notice Period') {
                $('#modalDateGroup').slideDown(200);
                $('#modalDateLabel').html('Expected Last Working Day (LWD) <span class="text-danger">*</span>');
                $('#modalDateHelp').text('Date up to which the employee is serving their notice period.');
                $('#modalSeparationWarning').slideUp(200);
            } else if (status === 'Resigned' || status === 'Terminated') {
                $('#modalDateGroup').slideDown(200);
                $('#modalDateLabel').html('Relieving / Exit Date <span class="text-danger">*</span>');
                $('#modalDateHelp').text('Official separation date from company records.');
                $('#modalSeparationWarning').slideDown(200);
            } else if (status === 'Suspended') {
                $('#modalDateGroup').slideUp(200);
                $('#modalSeparationWarning').slideDown(200);
            } else {
                $('#modalDateGroup').slideUp(200);
                $('#modalSeparationWarning').slideUp(200);
            }
        }

        $('#modalSelectStatus').on('change', function() {
            handleStatusFields($(this).val());
        });
    });
</script>
@endsection
