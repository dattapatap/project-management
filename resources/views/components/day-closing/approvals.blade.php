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

    .col-remarks {
        width: 30% !important;
        max-width: 300px !important;
        white-space: normal !important;
        word-break: break-word !important;
    }

    .col-actions {
        width: 180px !important;
        white-space: nowrap !important;
        text-align: right !important;
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
                <div>
                    <div class="input-group">
                        <input type="date" name="date" class="form-control form-control-sm border" value="{{ $selectedDate }}" @if(!empty($minDate)) min="{{ $minDate }}" @endif max="{{ date('Y-m-d') }}" style="width: 160px; height: 36px; border-radius: 8px 0 0 8px;" onchange="this.form.submit()">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 0 8px 8px 0;">
                                <i class="mdi mdi-magnify mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                    @if(!empty($isTeamLeaderOnly))
                    <small class="text-muted d-block mt-1 font-size-11"><i class="mdi mdi-shield-account mr-0.5 text-primary"></i> TL limit: up to 2 days back</small>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- 1. Daily Closing Completion Checklist (Submitted Only) -->
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title text-premium-dark mb-0 font-size-14">
                <i class="mdi mdi-playlist-check text-success mr-1"></i> Daily Audit Checklist (Submitted Closings) - {{ \Carbon\Carbon::parse($selectedDate)->format('d-M-Y') }}
            </h5>
            <span class="badge badge-soft-success px-3 py-1 font-size-11 font-weight-bold">
                Submitted: {{ $submittedList->count() }} of {{ $subordinates->count() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($submittedList->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="mdi mdi-clipboard-text-outline display-4 text-muted d-block mb-2" style="opacity: 0.4;"></i>
                <h6 class="font-weight-bold text-dark">No Submissions Recorded</h6>
                <p class="font-size-12 mb-0">No subordinate employees have submitted day closing for {{ \Carbon\Carbon::parse($selectedDate)->format('d-M-Y') }} yet.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-premium table-centered table-striped table-hover mb-0">
                    <thead class="thead-custom-teal">
                        <tr>
                            <th style="padding-left: 24px;">Subordinate</th>
                            <th>Department / Role</th>
                            <th>Target Parameters</th>
                            <th>Submission Status</th>
                            <th>Target Progress</th>
                            <th class="col-remarks">Executive Remarks</th>
                            <th class="col-actions" style="padding-right: 24px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submittedList as $audit)
                        <tr>
                            <td style="padding-left: 24px;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center font-weight-bold text-uppercase border mr-3" style="width: 32px; height: 32px;">
                                        {{ substr($audit->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold font-size-13">{{ $audit->name }}</h6>
                                        <small class="text-muted">{{ $audit->email }}</small>
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
                                @if($audit->submission->department === 'NSD')
                                <span class="text-dark d-block font-size-12">STS Logged: <strong>{{ $audit->submission->achieved_metrics['sts'] ?? 0 }}</strong></span>
                                <span class="text-dark d-block font-size-12">DSR Logged: <strong>{{ $audit->submission->achieved_metrics['dsr'] ?? 0 }}</strong></span>
                                @else
                                @php
                                // Global hours formatting
                                $ghVal = $audit->submission->achieved_metrics['global_hours'] ?? 0;
                                $ghMin = round($ghVal * 60);
                                $ghH = floor($ghMin / 60);
                                $ghM = $ghMin % 60;
                                $ghFormatted = $ghH > 0 ? sprintf('%02d:%02d Hrs', $ghH, $ghM) : sprintf('%02d:%02d min', $ghH, $ghM);

                                // Break hours formatting
                                $bhVal = $audit->submission->achieved_metrics['break_hours'] ?? 0;
                                $bhMin = round($bhVal * 60);
                                $bhH = floor($bhMin / 60);
                                $bhM = $bhMin % 60;
                                $bhFormatted = $bhH > 0 ? sprintf('%02d:%02d Hrs', $bhH, $bhM) : sprintf('%02d:%02d min', $bhH, $bhM);

                                // Task hours formatting
                                $thVal = $audit->submission->achieved_metrics['hours'] ?? 0;
                                $thMin = round($thVal * 60);
                                $thH = floor($thMin / 60);
                                $thM = $thMin % 60;
                                $thFormatted = $thH > 0 ? sprintf('%02d:%02d Hrs', $thH, $thM) : sprintf('%02d:%02d min', $thH, $thM);
                                @endphp

                                @if(isset($audit->submission->achieved_metrics['global_hours']))
                                <span class="text-dark d-block font-size-12">Global Shift: <strong>{{ $ghFormatted }}</strong></span>
                                <span class="text-dark d-block font-size-12">Break Time: <strong>{{ $bhFormatted }}</strong></span>
                                @endif

                                @if($audit->submission->department === 'CSD')
                                @if(isset($audit->submission->achieved_metrics['communications']))
                                <span class="text-dark d-block font-size-12">Comms: <strong>{{ $audit->submission->achieved_metrics['communications'] ?? 0 }}</strong></span>
                                @endif
                                @else
                                @if(isset($audit->submission->achieved_metrics['hours']))
                                <span class="text-dark d-block font-size-12">Task Work: <strong>{{ $thFormatted }}</strong></span>
                                <span class="text-dark d-block font-size-12">Tasks Done: <strong>{{ $audit->submission->achieved_metrics['tasks'] ?? 0 }}</strong></span>
                                @endif
                                @endif
                                @endif
                            </td>
                            <td>
                                @if($audit->submission->status === 'Pending')
                                <span class="badge badge-soft-warning font-size-11 px-2.5 py-0.5 font-weight-bold">Pending Approval</span>
                                @elseif($audit->submission->status === 'Approved')
                                <span class="badge badge-soft-success font-size-11 px-2.5 py-0.5 font-weight-bold"><i class="mdi mdi-check mr-0.5"></i> Approved</span>
                                @if($audit->submission->approver)
                                <small class="text-muted d-block mt-0.5 font-size-11"><i class="mdi mdi-account-check mr-0.5 text-success"></i> by {{ $audit->submission->approver->name }}</small>
                                @endif
                                @else
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-0.5 font-weight-bold"><i class="mdi mdi-close mr-0.5"></i> Rejected</span>
                                @if($audit->submission->approver)
                                <small class="text-muted d-block mt-0.5 font-size-11"><i class="mdi mdi-account-cancel mr-0.5 text-danger"></i> by {{ $audit->submission->approver->name }}</small>
                                @endif
                                @endif
                            </td>
                            <td>
                                @if($audit->submission->target_status === 'Met')
                                <span class="badge badge-soft-success font-size-11 px-2.5 py-0.5 font-weight-bold">Target Met</span>
                                @elseif($audit->submission->target_status === 'On Leave')
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-0.5 font-weight-bold">On Leave</span>
                                @else
                                <span class="badge badge-soft-warning font-size-11 px-2.5 py-0.5 font-weight-bold">Target Not Met</span>
                                @endif
                            </td>
                            <td class="col-remarks">
                                <span class="text-muted font-italic font-size-12">"{{ $audit->submission->executive_remarks ?? '-' }}"</span>
                            </td>
                            <td class="col-actions" style="padding-right: 24px;">
                                @if($audit->submission->status === 'Pending')
                                <button class="btn btn-success btn-sm mr-1 shadow-sm px-2.5" onclick="openApprovalModal('approve', {{ $audit->submission->id }}, '{{ $audit->name }}')">
                                    <i class="mdi mdi-check mr-1"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-sm shadow-sm px-2.5" onclick="openApprovalModal('reject', {{ $audit->submission->id }}, '{{ $audit->name }}')">
                                    <i class="mdi mdi-close mr-1"></i> Reject
                                </button>
                                @else
                                <div>
                                    <span class="text-muted font-size-12 font-weight-semibold">
                                        <i class="mdi mdi-check-all mr-1 text-success font-size-14"></i> Audited
                                    </span>
                                    @if($audit->submission->approver)
                                    <small class="text-muted d-block font-size-11">by {{ $audit->submission->approver->name }}</small>
                                    @endif
                                </div>
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

    <!-- 2. Pending Submissions (Active Employees NOT Submitted) -->
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title text-premium-dark mb-0 font-size-14">
                <i class="mdi mdi-clock-alert text-warning mr-1"></i> Pending Submissions (Not Submitted Yet)
            </h5>
            <span class="badge badge-soft-danger px-3 py-1 font-size-11 font-weight-bold">
                Not Submitted: {{ $notSubmittedList->count() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($notSubmittedList->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="mdi mdi-check-circle-outline display-4 text-success d-block mb-2"></i>
                <h6 class="font-weight-bold text-dark">All Caught Up!</h6>
                <p class="font-size-12 mb-0">All active team members have submitted their day closing for {{ \Carbon\Carbon::parse($selectedDate)->format('d-M-Y') }}.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-premium table-centered table-striped table-hover mb-0">
                    <thead class="thead-custom-teal">
                        <tr>
                            <th style="padding-left: 24px;">Executive</th>
                            <th>Date</th>
                            <th>Department & Role</th>
                            <th>Current Activity Logged</th>
                            <th>Submission Status</th>
                            <th class="col-actions" style="padding-right: 24px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notSubmittedList as $emp)
                        <tr>
                            <td style="padding-left: 24px;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center font-weight-bold text-uppercase border mr-3" style="width: 32px; height: 32px;">
                                        {{ substr($emp->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold font-size-13">{{ $emp->name }}</h6>
                                        <small class="text-muted">{{ $emp->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d-M-Y') }}</strong></td>
                            <td>
                                <span class="badge badge-dept text-uppercase badge-{{ strtolower($emp->dept_type ?? '') }}">
                                    {{ $emp->dept_type ?? '-' }}
                                </span>
                                <small class="text-muted d-block mt-0.5">{{ $emp->getRoleNames()->first() ?? '-' }}</small>
                            </td>
                            <td>
                                @if($emp->dept_type === 'nsd')
                                <span class="text-dark d-block font-size-12">STS Today: <strong>{{ $emp->currentMetrics['sts'] ?? 0 }}</strong></span>
                                <span class="text-dark d-block font-size-12">DSR Today: <strong>{{ $emp->currentMetrics['dsr'] ?? 0 }}</strong></span>
                                @else
                                @php
                                $ghVal = $emp->currentMetrics['global_hours'] ?? 0;
                                $ghMin = round($ghVal * 60);
                                $ghH = floor($ghMin / 60);
                                $ghM = $ghMin % 60;
                                $ghFormatted = $ghH > 0 ? sprintf('%02d:%02d Hrs', $ghH, $ghM) : sprintf('%02d:%02d min', $ghH, $ghM);
                                @endphp
                                <span class="text-dark d-block font-size-12">Shift Time: <strong>{{ $ghFormatted }}</strong></span>
                                @if($emp->dept_type === 'csd')
                                <span class="text-dark d-block font-size-12">Comms Today: <strong>{{ $emp->currentMetrics['communications'] ?? 0 }}</strong></span>
                                @else
                                @php
                                $thVal = $emp->currentMetrics['hours'] ?? 0;
                                $thMin = round($thVal * 60);
                                $thH = floor($thMin / 60);
                                $thM = $thMin % 60;
                                $thFormatted = $thH > 0 ? sprintf('%02d:%02d Hrs', $thH, $thM) : sprintf('%02d:%02d min', $thH, $thM);
                                @endphp
                                <span class="text-dark d-block font-size-12">Task Work: <strong>{{ $thFormatted }}</strong></span>
                                <span class="text-dark d-block font-size-12">Tasks Done: <strong>{{ $emp->currentMetrics['tasks'] ?? 0 }}</strong></span>
                                @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-0.5 font-weight-bold">Not Submitted</span>
                            </td>
                            <td class="col-actions" style="padding-right: 24px;">
                                <button class="btn btn-soft-danger btn-sm px-3 shadow-sm font-weight-semibold" onclick="openLeaveModalForUser({{ $emp->id }}, '{{ $selectedDate }}')">
                                    <i class="mdi mdi-airplane-takeoff mr-1"></i> Record Leave
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