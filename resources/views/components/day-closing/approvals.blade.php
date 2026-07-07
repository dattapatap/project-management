@extends('layouts.app')

@section('styles')
<style>
    .approvals-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    .header-card-glass {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 255, 0.95) 100%);
        border-left: 5px solid #FF4D4F;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .text-premium-dark {
        color: #2b3a4a;
        font-weight: 700;
    }

    .custom-table th {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.8px;
        color: #5c6a7a !important;
        font-weight: 700 !important;
        background-color: #f8fafc;
        border-bottom: 2px solid #edf2f7 !important;
    }

    .custom-table td {
        vertical-align: middle !important;
        color: #2d3748;
        font-size: 13.5px;
    }

    .badge-dept {
        font-size: 11px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 6px;
    }

    .badge-nsd {
        background-color: rgba(246, 173, 85, 0.15);
        color: #dd6b20;
    }

    .badge-csd {
        background-color: rgba(79, 209, 197, 0.15);
        color: #319795;
    }

    .badge-od {
        background-color: rgba(159, 122, 234, 0.15);
        color: #805ad5;
    }
</style>
@endsection

@section('content')
<div class="container-fluid approvals-wrapper pb-5">

    <!-- Header Block -->
    <div class="card mb-4 header-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h3 class="text-premium-dark font-size-18 mb-1">📋 Day Closing Approvals</h3>
                <p class="text-muted font-size-12 mb-0">Review and approve daily work closings submitted by executives.</p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-3">
                <button type="button" class="btn btn-danger btn-sm shadow-sm" data-toggle="modal" data-target="#recordLeaveModal">
                    <i class="mdi mdi-airplane-takeoff mr-1"></i> Record Employee Leave
                </button>
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item active text-muted">Approvals</li>
                </ol>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
        <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
        <i class="mdi mdi-alert-circle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Date Filter Bar -->
    <div class="card border shadow-sm mb-4">
        <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: #f8fafc; border-radius: 12px;">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-calendar-search text-primary font-size-24 mr-2"></i>
                <div>
                    <h6 class="mb-0 font-weight-bold text-dark">Audit Date Selection</h6>
                    <small class="text-muted">Select a date to audit work-closing statuses of all employees.</small>
                </div>
            </div>
            <form method="GET" action="{{ route('day-closing.approvals') }}" class="form-inline">
                <div class="input-group">
                    <input type="date" name="date" class="form-control form-control-sm border" value="{{ $selectedDate }}" max="{{ date('Y-m-d') }}" style="width: 160px; height: 36px; border-radius: 8px 0 0 8px;" onchange="this.form.submit()">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 0 8px 8px 0;">
                            <i class="mdi mdi-magnify mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daily Closing Completion Checklist -->
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title text-premium-dark mb-0 font-size-14">
                <i class="mdi mdi-playlist-check text-success mr-1"></i> Daily Audit Checklist (Date: {{ \Carbon\Carbon::parse($selectedDate)->format('d-M-Y') }})
            </h5>
            <span class="badge badge-soft-primary px-3 py-1 font-size-11">
                Total Subordinates: {{ $auditList->count() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($auditList->isEmpty())
            <div class="text-center py-5 text-muted">
                <p class="font-size-12 mb-0">No subordinate employees registered in your team or branch.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px;">Subordinate</th>
                            <th>Department / Role</th>
                            <th>Target Parameters</th>
                            <th>Submission Status</th>
                            <th>Target Progress</th>
                            <th class="text-right" style="padding-right: 24px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditList as $audit)
                        <tr>
                            <td style="padding-left: 24px;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center font-weight-bold text-uppercase border mr-3" style="width: 28px; height: 28px; font-size: 11px;">
                                        {{ substr($audit->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold font-size-13">{{ $audit->name }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-dept text-uppercase badge-{{ strtolower($audit->dept_type ?? '') }}">
                                    {{ $audit->dept_type ?? '-' }}
                                </span>
                                <small class="text-muted d-block mt-0.5">{{ $audit->getRoleNames()->first() ?? '-' }}</small>
                            </td>
                            <td>
                                @if($audit->submission)
                                @if($audit->submission->department === 'NSD')
                                <span class="text-muted d-block font-size-12">STS: <strong>{{ $audit->submission->achieved_metrics['sts'] ?? 0 }}</strong></span>
                                <span class="text-muted d-block font-size-12">DSR: <strong>{{ $audit->submission->achieved_metrics['dsr'] ?? 0 }}</strong></span>
                                @elseif($audit->submission->department === 'CSD')
                                <span class="text-muted d-block font-size-12">Comms: <strong>{{ $audit->submission->achieved_metrics['communications'] ?? 0 }}</strong></span>
                                @else
                                <span class="text-muted d-block font-size-12">Hours: <strong>{{ $audit->submission->achieved_metrics['hours'] ?? 0 }}</strong></span>
                                <span class="text-muted d-block font-size-12">Tasks: <strong>{{ $audit->submission->achieved_metrics['tasks'] ?? 0 }}</strong></span>
                                @endif
                                @else
                                <span class="text-muted font-italic font-size-12">No metrics recorded</span>
                                @endif
                            </td>
                            <td>
                                @if($audit->submission)
                                @if($audit->submission->status === 'Pending')
                                <span class="badge badge-soft-warning font-size-11 px-2.5 py-0.5">Pending Approval</span>
                                @elseif($audit->submission->status === 'Approved')
                                <span class="badge badge-soft-success font-size-11 px-2.5 py-0.5">Submitted & Approved</span>
                                @else
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-0.5">Submitted & Rejected</span>
                                @endif
                                @else
                                <span class="badge badge-soft-secondary font-size-11 px-2.5 py-0.5">Not Submitted</span>
                                @endif
                            </td>
                            <td>
                                @if($audit->submission)
                                @if($audit->submission->target_status === 'Met')
                                <span class="badge badge-soft-success font-size-11 px-2.5 py-0.5">Target Met</span>
                                @elseif($audit->submission->target_status === 'On Leave')
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-0.5">On Leave</span>
                                @else
                                <span class="badge badge-soft-warning font-size-11 px-2.5 py-0.5">Target Not Met</span>
                                @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-right" style="padding-right: 24px;">
                                @if($audit->submission && $audit->submission->status === 'Pending')
                                <button class="btn btn-success btn-sm mr-1 shadow-sm px-3" onclick="openApprovalModal('approve', {{ $audit->submission->id }}, '{{ $audit->name }}')">
                                    <i class="mdi mdi-check mr-1"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-sm shadow-sm px-3" onclick="openApprovalModal('reject', {{ $audit->submission->id }}, '{{ $audit->name }}')">
                                    <i class="mdi mdi-close mr-1"></i> Reject
                                </button>
                                @elseif(!$audit->submission)
                                <button class="btn btn-soft-danger btn-sm px-3" onclick="openLeaveModalForUser({{ $audit->id }}, '{{ $selectedDate }}')">
                                    <i class="mdi mdi-airplane-takeoff mr-1"></i> Record Leave
                                </button>
                                @else
                                <span class="text-muted font-size-12"><i class="mdi mdi-check-all mr-1 text-success"></i> Audited</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <!-- Pending Approvals Board -->
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title text-premium-dark mb-0 font-size-14"><i class="mdi mdi-clock-alert text-warning mr-1"></i> Pending Submissions</h5>
        </div>
        <div class="card-body p-0">
            @if($pending->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="mdi mdi-check-circle-outline display-4 text-success d-block mb-2"></i>
                <h6 class="font-weight-bold">All caught up!</h6>
                <p class="font-size-12 mb-0">No pending day closing submissions to review.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px;">Executive</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Metrics</th>
                            <th>Target Status</th>
                            <th>Executive Remarks</th>
                            <th class="text-right" style="padding-right: 24px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending as $item)
                        <tr>
                            <td style="padding-left: 24px;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center font-weight-bold text-uppercase border mr-3" style="width: 32px; height: 32px;">
                                        {{ substr($item->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">{{ $item->user->name }}</h6>
                                        <small class="text-muted">{{ $item->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>{{ $item->closing_date->format('d-M-Y') }}</strong></td>
                            <td>
                                <span class="badge badge-dept text-uppercase badge-{{ strtolower($item->department) }}">
                                    {{ $item->department }}
                                </span>
                            </td>
                            <td>
                                @if($item->department === 'NSD')
                                <span class="text-dark d-block">STS Logged: <strong>{{ $item->achieved_metrics['sts'] ?? 0 }}</strong></span>
                                <span class="text-dark d-block">DSR Logged: <strong>{{ $item->achieved_metrics['dsr'] ?? 0 }}</strong></span>
                                @else
                                @php
                                // Global hours formatting
                                $ghVal = $item->achieved_metrics['global_hours'] ?? 0;
                                $ghMin = round($ghVal * 60);
                                $ghH = floor($ghMin / 60);
                                $ghM = $ghMin % 60;
                                $ghFormatted = $ghH > 0 ? sprintf('%02d:%02d Hrs', $ghH, $ghM) : sprintf('%02d:%02d min', $ghH, $ghM);

                                // Break hours formatting
                                $bhVal = $item->achieved_metrics['break_hours'] ?? 0;
                                $bhMin = round($bhVal * 60);
                                $bhH = floor($bhMin / 60);
                                $bhM = $bhMin % 60;
                                $bhFormatted = $bhH > 0 ? sprintf('%02d:%02d Hrs', $bhH, $bhM) : sprintf('%02d:%02d min', $bhH, $bhM);

                                // Task hours formatting
                                $thVal = $item->achieved_metrics['hours'] ?? 0;
                                $thMin = round($thVal * 60);
                                $thH = floor($thMin / 60);
                                $thM = $thMin % 60;
                                $thFormatted = $thH > 0 ? sprintf('%02d:%02d Hrs', $thH, $thM) : sprintf('%02d:%02d min', $thH, $thM);
                                @endphp

                                @if(isset($item->achieved_metrics['global_hours']))
                                <span class="text-dark d-block">Global Shift Time: <strong>{{ $ghFormatted }}</strong></span>
                                <span class="text-dark d-block">Break Time Spent: <strong>{{ $bhFormatted }}</strong></span>
                                @endif

                                @if($item->department === 'CSD')
                                @if(isset($item->achieved_metrics['communications']))
                                <span class="text-dark d-block">Comms: <strong>{{ $item->achieved_metrics['communications'] ?? 0 }}</strong></span>
                                @endif
                                @else
                                @if(isset($item->achieved_metrics['hours']))
                                <span class="text-dark d-block">Task Work Time: <strong>{{ $thFormatted }}</strong></span>
                                <span class="text-dark d-block">Tasks Completed: <strong>{{ $item->achieved_metrics['tasks'] ?? 0 }}</strong></span>
                                @endif
                                @endif
                                @endif
                            </td>
                            <td>
                                @if($item->target_status === 'Met')
                                <span class="badge badge-soft-success font-size-11 px-2.5 py-0.5">Target Met</span>
                                @elseif($item->target_status === 'On Leave')
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-0.5">On Leave</span>
                                @else
                                <span class="badge badge-soft-warning font-size-11 px-2.5 py-0.5">Not Met</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted font-italic">"{{ $item->executive_remarks ?? '-' }}"</span>
                            </td>
                            <td class="text-right" style="padding-right: 24px;">
                                <button class="btn btn-success btn-sm mr-1 shadow-sm" onclick="openApprovalModal('approve', {{ $item->id }}, '{{ $item->user->name }}')">
                                    <i class="mdi mdi-check mr-1"></i>Approve
                                </button>
                                <button class="btn btn-danger btn-sm shadow-sm" onclick="openApprovalModal('reject', {{ $item->id }}, '{{ $item->user->name }}')">
                                    <i class="mdi mdi-close mr-1"></i>Reject
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Approval/Rejection Modal -->
<div class="modal fade" id="decisionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalTitle">Day Closing Action</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="decisionForm" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <div class="modal-body text-dark">
                    <p class="font-size-14 mb-3" id="modalText"></p>
                    <div class="form-group mb-0">
                        <label class="font-weight-semibold mb-2">Remarks / Feedback:</label>
                        <textarea name="remarks" class="form-control border" rows="3" style="border-radius: 8px;" placeholder="Optional remarks for the executive..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn" id="modalSubmitBtn"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Employee Leave Modal -->
<div class="modal fade" id="recordLeaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="mdi mdi-airplane-takeoff mr-1 text-danger"></i> Record Employee Leave</h5>
                <button type="button" class="close text-dark btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('day-closing.submit-leave-on-behalf') }}" method="POST">
                @csrf
                <div class="modal-body text-dark">
                    <div class="form-group mb-3">
                        <label class="font-weight-semibold mb-2">Select Employee(s): <span class="text-danger">*</span></label>
                        <select name="user_ids[]" class="form-control border select2" style="width: 100%;" multiple="multiple" data-placeholder="Choose Subordinate(s)..." required>
                            @foreach($subordinates as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->getRoleNames()->first() ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-semibold mb-2">Leave Date: <span class="text-danger">*</span></label>
                        <input type="date" name="leave_date" class="form-control border" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-semibold mb-2">Reason for Leave: <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control border" rows="3" style="border-radius: 8px;" placeholder="Explain the reason (e.g., Casual Leave, Medical Leave)..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary btnmdlclose" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger shadow-sm">Submit Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openApprovalModal(action, id, executiveName) {
        var actionUrl = '';
        var title = '';
        var text = '';
        var submitBtnText = '';
        var submitBtnClass = '';

        if (action === 'approve') {
            actionUrl = "{{ url('/day-closing') }}/" + id + "/approve";
            title = '✔️ Approve Day Closing';
            text = 'Are you sure you want to approve the day closing submission for ' + executiveName + '?';
            submitBtnText = 'Approve';
            submitBtnClass = 'btn-success';
        } else {
            actionUrl = "{{ url('/day-closing') }}/" + id + "/reject";
            title = '❌ Reject Day Closing';
            text = 'Are you sure you want to reject the day closing submission for ' + executiveName + '?';
            submitBtnText = 'Reject';
            submitBtnClass = 'btn-danger';
        }

        $('#decisionForm').attr('action', actionUrl);
        $('#modalTitle').text(title);
        $('#modalText').text(text);
        $('#modalSubmitBtn').text(submitBtnText).removeClass('btn-success btn-danger').addClass(submitBtnClass);
        $('#decisionModal').modal('show');
    }

    function openLeaveModalForUser(userId, date) {
        $('#recordLeaveModal select[name="user_ids[]"]').val([userId]).trigger('change');
        $('#recordLeaveModal input[name="leave_date"]').val(date);
        $('#recordLeaveModal').modal('show');
    }
</script>
@endsection
