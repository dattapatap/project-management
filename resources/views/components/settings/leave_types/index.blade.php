@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    <!-- Header Card -->
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px;">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 12px; width: 44px; height: 44px; font-size: 22px;">
                    <i class="mdi mdi-clipboard-list-outline"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-17">Leave Types & HR Quotas</h4>
                    <span class="text-muted font-size-12">Configure annual quotas, monthly limits (e.g. 2 EL / month), accrual policies, and paid/unpaid statuses</span>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold mt-2 mt-md-0" data-toggle="modal" data-target="#addLeaveTypeModal" style="height: 36px; border-radius: 8px;">
                <i class="mdi mdi-plus mr-1"></i> Add Leave Type
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
        <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
        <i class="mdi mdi-alert-circle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Leave Types Table Card -->
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4 text-muted font-size-11 text-uppercase font-weight-bold">Leave Name</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Code</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Annual Quota</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Monthly Limit</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Paid / Unpaid</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Status</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Description</th>
                            <th class="py-3 px-4 text-right text-muted font-size-11 text-uppercase font-weight-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveTypes as $type)
                        <tr>
                            <td class="px-4">
                                <span class="font-weight-bold text-dark font-size-13">{{ $type->name }}</span>
                                @if($type->is_monthly_accrual)
                                    <span class="badge badge-soft-info px-2 py-0.5 rounded font-size-10 ml-1">Monthly Accrual</span>
                                @endif
                            </td>
                            <td class="px-3">
                                <span class="badge badge-light border px-2 py-1 font-size-11 font-weight-bold">{{ $type->code }}</span>
                            </td>
                            <td class="px-3 text-center font-weight-bold text-dark font-size-13">
                                {{ $type->days_allowed_per_year }} days
                            </td>
                            <td class="px-3 text-center">
                                @if($type->monthly_limit)
                                    <span class="badge badge-soft-primary px-2 py-1 font-size-11 font-weight-bold">Max {{ $type->monthly_limit }} days / mo</span>
                                @else
                                    <span class="text-muted font-size-12">No Monthly Cap</span>
                                @endif
                            </td>
                            <td class="px-3 text-center">
                                @if($type->is_paid)
                                    <span class="badge badge-soft-success px-2 py-0.5 rounded font-size-11 font-weight-bold">Paid</span>
                                @else
                                    <span class="badge badge-soft-secondary px-2 py-0.5 rounded font-size-11">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-3 text-center">
                                @if($type->status)
                                    <span class="badge badge-soft-success px-2 py-0.5 rounded font-size-11 font-weight-bold">Active</span>
                                @else
                                    <span class="badge badge-soft-danger px-2 py-0.5 rounded font-size-11">Inactive</span>
                                @endif
                            </td>
                            <td class="px-3 font-size-12 text-muted">
                                {{ $type->description ?: '—' }}
                            </td>
                            <td class="px-4 text-right">
                                <button type="button" class="btn btn-light border btn-sm px-2 py-1 mr-1 btn-edit-leavetype" 
                                    data-id="{{ $type->id }}" 
                                    data-name="{{ $type->name }}" 
                                    data-code="{{ $type->code }}" 
                                    data-days="{{ $type->days_allowed_per_year }}" 
                                    data-monthly="{{ $type->monthly_limit }}"
                                    data-accrual="{{ $type->is_monthly_accrual ? '1' : '0' }}"
                                    data-paid="{{ $type->is_paid ? '1' : '0' }}" 
                                    data-status="{{ $type->status ? '1' : '0' }}" 
                                    data-desc="{{ $type->description }}" 
                                    style="border-radius: 6px;">
                                    <i class="mdi mdi-pencil text-muted font-size-13"></i>
                                </button>
                                <form action="{{ route('settings.leave-types.destroy', $type->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this leave type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light border btn-sm px-2 py-1 text-danger" style="border-radius: 6px;">
                                        <i class="mdi mdi-trash-can-outline font-size-13"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No leave types configured. Click "Add Leave Type" to set up HR leave categories.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Leave Type Modal --}}
<div class="modal fade" id="addLeaveTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form action="{{ route('settings.leave-types.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0">Add Leave Type</h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-size-12 font-weight-bold text-dark">Leave Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control font-size-13" placeholder="e.g. Earned Leave (EL)" required style="border-radius: 8px;">
                    </div>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control font-size-13" placeholder="e.g. EL" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Annual Quota (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="days_allowed_per_year" class="form-control font-size-13" value="24" min="0" max="365" required style="border-radius: 8px;">
                        </div>
                    </div>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Monthly Limit (Days)</label>
                            <input type="number" step="0.5" name="monthly_limit" class="form-control font-size-13" value="2.0" placeholder="e.g. 2.0 (blank for no limit)" style="border-radius: 8px;">
                            <small class="text-muted font-size-11">Max days employee can apply in 1 month</small>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Paid Status <span class="text-danger">*</span></label>
                            <select name="is_paid" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="1">Paid Leave</option>
                                <option value="0">Unpaid Leave (LWP)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="is_monthly_accrual" value="1" class="custom-control-input" id="addAccrualCheck" checked>
                            <label class="custom-control-label font-size-12 font-weight-semibold text-dark" for="addAccrualCheck">
                                Enable Monthly Accrual (Leaves unlock month by month, e.g. 2 EL/mo)
                            </label>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-size-12 font-weight-bold text-dark">Description (Optional)</label>
                        <textarea name="description" rows="3" class="form-control font-size-13" placeholder="Notes for employees..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Save Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Leave Type Modal --}}
<div class="modal fade" id="editLeaveTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form id="editLeaveTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0">Edit Leave Type</h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-size-12 font-weight-bold text-dark">Leave Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-leavetype-name" class="form-control font-size-13" required style="border-radius: 8px;">
                    </div>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit-leavetype-code" class="form-control font-size-13" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Annual Quota (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="days_allowed_per_year" id="edit-leavetype-days" class="form-control font-size-13" min="0" max="365" required style="border-radius: 8px;">
                        </div>
                    </div>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Monthly Limit (Days)</label>
                            <input type="number" step="0.5" name="monthly_limit" id="edit-leavetype-monthly" class="form-control font-size-13" placeholder="e.g. 2.0" style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Paid Status <span class="text-danger">*</span></label>
                            <select name="is_paid" id="edit-leavetype-paid" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="1">Paid Leave</option>
                                <option value="0">Unpaid Leave</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Active Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit-leavetype-status" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-0 d-flex align-items-center mt-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_monthly_accrual" value="1" class="custom-control-input" id="editAccrualCheck">
                                <label class="custom-control-label font-size-12 font-weight-semibold text-dark" for="editAccrualCheck">Monthly Accrual</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-size-12 font-weight-bold text-dark">Description (Optional)</label>
                        <textarea name="description" id="edit-leavetype-desc" rows="3" class="form-control font-size-13" style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Update Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.btn-edit-leavetype').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var code = $(this).data('code');
            var days = $(this).data('days');
            var monthly = $(this).data('monthly');
            var accrual = $(this).data('accrual') == '1';
            var paid = $(this).data('paid');
            var status = $(this).data('status');
            var desc = $(this).data('desc');

            $('#edit-leavetype-name').val(name);
            $('#edit-leavetype-code').val(code);
            $('#edit-leavetype-days').val(days);
            $('#edit-leavetype-monthly').val(monthly);
            $('#editAccrualCheck').prop('checked', accrual);
            $('#edit-leavetype-paid').val(paid);
            $('#edit-leavetype-status').val(status);
            $('#edit-leavetype-desc').val(desc);

            var actionUrl = "{{ url('settings/leave-types') }}/" + id;
            $('#editLeaveTypeForm').attr('action', actionUrl);

            $('#editLeaveTypeModal').modal('show');
        });
    });
</script>
@endsection
