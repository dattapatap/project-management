@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    <!-- Header Card -->
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px;">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 12px; width: 44px; height: 44px; font-size: 22px;">
                    <i class="mdi mdi-account-check-outline"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-17">Employee Leave Approvals</h4>
                    <span class="text-muted font-size-12">Review, approve, or reject employee leave requests</span>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0" style="gap: 8px;">
                <a href="{{ route('hrms.my-leaves.approvals', ['status' => 'pending']) }}" class="btn btn-sm px-3 font-weight-bold {{ $statusFilter === 'pending' ? 'btn-primary' : 'btn-light border' }}" style="border-radius: 8px;">
                    Pending
                </a>
                <a href="{{ route('hrms.my-leaves.approvals', ['status' => 'approved']) }}" class="btn btn-sm px-3 font-weight-bold {{ $statusFilter === 'approved' ? 'btn-success' : 'btn-light border' }}" style="border-radius: 8px;">
                    Approved
                </a>
                <a href="{{ route('hrms.my-leaves.approvals', ['status' => 'rejected']) }}" class="btn btn-sm px-3 font-weight-bold {{ $statusFilter === 'rejected' ? 'btn-danger' : 'btn-light border' }}" style="border-radius: 8px;">
                    Rejected
                </a>
                <a href="{{ route('hrms.my-leaves.approvals', ['status' => 'all']) }}" class="btn btn-sm px-3 font-weight-bold {{ $statusFilter === 'all' ? 'btn-dark' : 'btn-light border' }}" style="border-radius: 8px;">
                    All
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
        <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Leaves Table Card -->
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4 text-muted font-size-11 text-uppercase font-weight-bold">Employee</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Leave Type</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Dates</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Duration</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold" style="max-width: 250px;">Reason</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Status</th>
                            <th class="py-3 px-4 text-right text-muted font-size-11 text-uppercase font-weight-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                        @php
                            $sDate = Carbon\Carbon::parse($leave->start_date);
                            $eDate = Carbon\Carbon::parse($leave->end_date);
                            $badgeClass = 'badge-soft-warning';
                            if ($leave->status === 'approved') $badgeClass = 'badge-soft-success';
                            elseif ($leave->status === 'rejected') $badgeClass = 'badge-soft-danger';
                        @endphp
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-2 font-weight-bold text-primary" style="width: 32px; height: 32px; background: #eef2ff; font-size: 11px;">
                                        {{ strtoupper(substr($leave->user->name ?? 'E', 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-weight-bold text-dark font-size-13 d-block">{{ $leave->user->name ?? 'Employee' }}</span>
                                        <small class="text-muted font-size-11">#EMP-{{ ($leave->user_id + 1000) }} &bull; {{ $leave->user->departments->dept->name ?? 'General' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3">
                                <span class="font-weight-semibold text-dark font-size-12">{{ $leave->leaveType->name ?? 'Leave' }}</span>
                                <span class="badge badge-light border font-size-10 ml-1">{{ $leave->leaveType->code ?? '' }}</span>
                            </td>
                            <td class="px-3">
                                <span class="font-weight-semibold text-dark font-size-12">{{ $sDate->format('d M, Y') }}</span>
                                @if($sDate->ne($eDate))
                                    <span class="text-muted font-size-11">&rarr;</span>
                                    <span class="font-weight-semibold text-dark font-size-12">{{ $eDate->format('d M, Y') }}</span>
                                @endif
                            </td>
                            <td class="px-3 text-center">
                                @if($leave->is_half_day)
                                    <span class="badge badge-light border px-2 py-0.5 font-size-11 font-weight-bold">0.5 Day ({{ $leave->half_day_type === 'second_half' ? '2nd Half' : '1st Half' }})</span>
                                @else
                                    <span class="badge badge-light border px-2 py-0.5 font-size-11 font-weight-bold">{{ $leave->total_days }} {{ $leave->total_days == 1 ? 'Day' : 'Days' }}</span>
                                @endif
                            </td>
                            <td class="px-3 font-size-12 text-dark" style="max-width: 250px;">
                                <div class="text-truncate" title="{{ $leave->reason }}">{{ $leave->reason }}</div>
                                @if($leave->admin_remarks)
                                    <small class="text-muted font-italic d-block mt-0.5">Note: {{ $leave->admin_remarks }}</small>
                                @endif
                            </td>
                            <td class="px-3 text-center">
                                <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded font-size-11 font-weight-bold text-capitalize">
                                    {{ $leave->status }}
                                </span>
                            </td>
                            <td class="px-4 text-right">
                                @if($leave->status === 'pending')
                                    <button type="button" class="btn btn-success btn-sm px-2.5 py-1 font-size-11 font-weight-bold shadow-sm btn-action-leave mr-1" data-id="{{ $leave->id }}" data-action="approved" data-name="{{ $leave->user->name }}" style="border-radius: 6px;">
                                        <i class="mdi mdi-check mr-0.5"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm px-2.5 py-1 font-size-11 font-weight-bold shadow-sm btn-action-leave" data-id="{{ $leave->id }}" data-action="rejected" data-name="{{ $leave->user->name }}" style="border-radius: 6px;">
                                        <i class="mdi mdi-close mr-0.5"></i> Reject
                                    </button>
                                @else
                                    <span class="text-muted font-size-11 font-italic">Processed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="mdi mdi-checkbox-marked-circle-outline font-size-24 text-success d-block mb-1"></i>
                                No {{ $statusFilter === 'all' ? '' : $statusFilter }} leave applications found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leaves->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $leaves->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Leave Action Modal (Approve / Reject with remarks) --}}
<div class="modal fade" id="leaveActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form id="leaveActionForm" method="POST">
                @csrf
                <input type="hidden" name="status" id="leaveActionStatus">
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0" id="leaveActionTitle">Process Leave Application</h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="font-size-13 text-dark mb-3" id="leaveActionText"></p>
                    <div class="form-group mb-0">
                        <label class="font-size-12 font-weight-bold text-dark">Remarks / Note (Optional)</label>
                        <textarea name="admin_remarks" rows="3" class="form-control font-size-13" placeholder="Add any comments or conditions..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" id="leaveActionSubmitBtn" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.btn-action-leave').on('click', function() {
            var id = $(this).data('id');
            var action = $(this).data('action');
            var empName = $(this).data('name');

            $('#leaveActionStatus').val(action);
            var actionUrl = "{{ url('hrms/leaves') }}/" + id + "/status";
            $('#leaveActionForm').attr('action', actionUrl);

            if (action === 'approved') {
                $('#leaveActionTitle').text('Approve Leave Request');
                $('#leaveActionText').html('Are you sure you want to <strong>Approve</strong> the leave application for <strong>' + empName + '</strong>?');
                $('#leaveActionSubmitBtn').removeClass('btn-danger').addClass('btn-success').text('Approve Leave');
            } else {
                $('#leaveActionTitle').text('Reject Leave Request');
                $('#leaveActionText').html('Are you sure you want to <strong>Reject</strong> the leave application for <strong>' + empName + '</strong>?');
                $('#leaveActionSubmitBtn').removeClass('btn-success').addClass('btn-danger').text('Reject Leave');
            }

            $('#leaveActionModal').modal('show');
        });
    });
</script>
@endsection
