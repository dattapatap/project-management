@extends('layouts.app')

@section('styles')
<style>
    .dayclosing-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    .header-card-glass {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 255, 0.95) 100%);
        border-left: 5px solid #7F00FF;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .metric-card-premium {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
        transition: all 0.25s ease;
    }

    .metric-card-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(127, 0, 255, 0.06);
    }

    .text-premium-dark {
        color: #2b3a4a;
        font-weight: 700;
    }

    .status-badge-lg {
        font-size: 15px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 20px;
    }

    .btn-submit-closing {
        background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);
        border: none;
        color: white;
        font-weight: 700;
        padding: 12px 28px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(127, 0, 255, 0.25);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
    }

    .btn-submit-closing:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(127, 0, 255, 0.35);
        color: white;
    }

    .btn-submit-closing:disabled {
        background: #cbd5e1;
        box-shadow: none;
        transform: none;
        cursor: not-allowed;
    }

    .history-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #5c6a7a;
        letter-spacing: 0.8px;
        background-color: #f8fafc;
        border-bottom: 2px solid #edf2f7;
    }

    .history-table td {
        vertical-align: middle;
        font-size: 13px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid dayclosing-wrapper pb-5">

    <!-- Header Block -->
    <div class="card mb-4 header-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h3 class="text-premium-dark font-size-18 mb-1">🌅 Day Closing Submission</h3>
                <p class="text-muted font-size-12 mb-0">Review today's achievements and submit your final work summary for approvals.</p>
            </div>
            <div>
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item active text-muted">Day Closing</li>
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

    <div class="row">
        <!-- Prefilled Metrics & Form -->
        <div class="col-lg-7">
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <h4 class="text-premium-dark font-size-16 mb-0">📊 Today's Progress Report ({{ date('d-M-Y') }})</h4>
                        @if($targetStatus === 'Met')
                        <span class="badge badge-soft-success status-badge-lg">
                            <i class="mdi mdi-check-decagram mr-1"></i> Target Met
                        </span>
                        @else
                        <span class="badge badge-soft-warning status-badge-lg">
                            <i class="mdi mdi-alert-circle-outline mr-1"></i> Target Not Met
                        </span>
                        @endif
                    </div>

                    <!-- Realtime parameters listing -->
                    <div class="row mb-5">
                        @if($deptType === 'nsd')
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-4">
                                    <i class="mdi mdi-phone-classic text-primary font-size-32 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-12 mb-1">STS Touchpoints Done</h6>
                                    <h3 class="text-premium-dark mb-2">{{ $metrics['sts'] }}</h3>
                                    <span class="badge badge-soft-primary px-3 py-1 font-size-11">Target: {{ $targets['sts_updates'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-4">
                                    <i class="mdi mdi-shield-account-outline text-success font-size-32 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-12 mb-1">DSR Updates Done</h6>
                                    <h3 class="text-premium-dark mb-2">{{ $metrics['dsr'] }}</h3>
                                    <span class="badge badge-soft-success px-3 py-1 font-size-11">Target: {{ $targets['dsr_updates'] }}</span>
                                </div>
                            </div>
                        </div>
                        @elseif(Auth::user()->hasRole('Branch-Manager'))
                        @php
                        $gMinutes = round(($metrics['global_hours'] ?? 0) * 60);
                        $gH = floor($gMinutes / 60);
                        $gM = $gMinutes % 60;
                        $gFormatted = $gH > 0 ? sprintf('%02d:%02d Hrs', $gH, $gM) : sprintf('%02d:%02d min', $gH, $gM);

                        $bMinutes = round(($metrics['break_hours'] ?? 0) * 60);
                        $bH = floor($bMinutes / 60);
                        $bM = $bMinutes % 60;
                        $bFormatted = $bH > 0 ? sprintf('%02d:%02d Hrs', $bH, $bM) : sprintf('%02d:%02d min', $bH, $bM);
                        @endphp
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-3">
                                    <i class="mdi mdi-briefcase-clock text-primary font-size-28 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-11 mb-1">Global Shift Time</h6>
                                    <h4 class="text-premium-dark font-size-16 mb-2">{{ $gFormatted }}</h4>
                                    <span class="badge badge-soft-primary px-3 py-1 font-size-10">Attendance</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-3">
                                    <i class="mdi mdi-coffee text-warning font-size-28 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-11 mb-1">Break Time Spent</h6>
                                    <h4 class="text-premium-dark font-size-16 mb-2">{{ $bFormatted }}</h4>
                                    <span class="badge badge-soft-warning px-3 py-1 font-size-10">Lunch/Breaks</span>
                                </div>
                            </div>
                        </div>
                        @elseif($deptType === 'csd')
                        @php
                        $gMinutes = round(($metrics['global_hours'] ?? 0) * 60);
                        $gH = floor($gMinutes / 60);
                        $gM = $gMinutes % 60;
                        $gFormatted = $gH > 0 ? sprintf('%02d:%02d Hrs', $gH, $gM) : sprintf('%02d:%02d min', $gH, $gM);

                        $bMinutes = round(($metrics['break_hours'] ?? 0) * 60);
                        $bH = floor($bMinutes / 60);
                        $bM = $bMinutes % 60;
                        $bFormatted = $bH > 0 ? sprintf('%02d:%02d Hrs', $bH, $bM) : sprintf('%02d:%02d min', $bH, $bM);
                        @endphp
                        <div class="col-sm-4 mb-3 mb-sm-0">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-3">
                                    <i class="mdi mdi-briefcase-clock text-primary font-size-28 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-11 mb-1">Global Shift Time</h6>
                                    <h4 class="text-premium-dark font-size-16 mb-2">{{ $gFormatted }}</h4>
                                    <span class="badge badge-soft-primary px-3 py-1 font-size-10">Attendance</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 mb-3 mb-sm-0">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-3">
                                    <i class="mdi mdi-coffee text-warning font-size-28 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-11 mb-1">Break Time Spent</h6>
                                    <h4 class="text-premium-dark font-size-16 mb-2">{{ $bFormatted }}</h4>
                                    <span class="badge badge-soft-warning px-3 py-1 font-size-10">Lunch/Breaks</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-3">
                                    <i class="mdi mdi-message-text-outline text-info font-size-28 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-11 mb-1">Client Communications Logged</h6>
                                    <h3 class="text-premium-dark mb-2">{{ $metrics['communications'] }}</h3>
                                    <span class="badge badge-soft-info px-3 py-1 font-size-10">Target: {{ $targets['communications'] }}</span>
                                </div>
                            </div>
                        </div>
                        @elseif($deptType === 'od')
                        @php
                        $gMinutes = round(($metrics['global_hours'] ?? 0) * 60);
                        $gH = floor($gMinutes / 60);
                        $gM = $gMinutes % 60;
                        $gFormatted = $gH > 0 ? sprintf('%02d:%02d Hrs', $gH, $gM) : sprintf('%02d:%02d min', $gH, $gM);

                        $bMinutes = round(($metrics['break_hours'] ?? 0) * 60);
                        $bH = floor($bMinutes / 60);
                        $bM = $bMinutes % 60;
                        $bFormatted = $bH > 0 ? sprintf('%02d:%02d Hrs', $bH, $bM) : sprintf('%02d:%02d min', $bH, $bM);

                        $tMinutes = round(($metrics['hours'] ?? 0) * 60);
                        $tH = floor($tMinutes / 60);
                        $tM = $tMinutes % 60;
                        $tFormatted = $tH > 0 ? sprintf('%02d:%02d Hrs', $tH, $tM) : sprintf('%02d:%02d min', $tH, $tM);
                        @endphp
                        <div class="col-sm-3 mb-3 mb-sm-0">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-2.5">
                                    <i class="mdi mdi-briefcase-clock text-primary font-size-26 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-10 mb-1">Global Shift Time</h6>
                                    <h4 class="text-premium-dark font-size-14 mb-2">{{ $gFormatted }}</h4>
                                    <span class="badge badge-soft-primary px-2.5 py-0.5 font-size-9">Attendance</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 mb-3 mb-sm-0">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-2.5">
                                    <i class="mdi mdi-coffee text-warning font-size-26 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-10 mb-1">Break Time Spent</h6>
                                    <h4 class="text-premium-dark font-size-14 mb-2">{{ $bFormatted }}</h4>
                                    <span class="badge badge-soft-warning px-2.5 py-0.5 font-size-9">Lunch/Breaks</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 mb-3 mb-sm-0">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-2.5">
                                    <i class="mdi mdi-clock-outline text-purple font-size-26 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-10 mb-1">Task Work Time</h6>
                                    <h4 class="text-premium-dark font-size-14 mb-2">{{ $tFormatted }}</h4>
                                    <span class="badge badge-soft-purple px-2.5 py-0.5 font-size-9">Target: {{ $targets['hours_logged'] }}h</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="card metric-card-premium h-100">
                                <div class="card-body text-center p-2.5">
                                    <i class="mdi mdi-checkbox-marked-circle-outline text-success font-size-26 mb-2 d-block"></i>
                                    <h6 class="text-muted font-size-10 mb-1">Tasks Completed</h6>
                                    <h4 class="text-premium-dark font-size-14 mb-2">{{ $metrics['tasks'] }}</h4>
                                    <span class="badge badge-soft-success px-2.5 py-0.5 font-size-9">Target: {{ $targets['tasks_completed'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Submission Form -->
                    @if($todaySubmission)
                    <div class="alert alert-soft-success p-4 border" style="border-radius: 12px; background-color: rgba(79, 209, 197, 0.08); border-color: rgba(79, 209, 197, 0.25);">
                        <h5 class="text-success font-weight-bold mb-2"><i class="mdi mdi-check-all mr-1"></i> Already Submitted</h5>
                        <p class="mb-2 font-size-13 text-muted">You have successfully submitted your day closing for today.</p>
                        <p class="mb-0 font-size-13"><strong>Status:</strong>
                            @if($todaySubmission->status === 'Pending')
                            <span class="badge badge-soft-warning px-2 py-1 font-size-11">Pending Approval</span>
                            @elseif($todaySubmission->status === 'Approved')
                            <span class="badge badge-soft-success px-2 py-1 font-size-11">Approved</span>
                            @else
                            <span class="badge badge-soft-danger px-2 py-1 font-size-11">Rejected</span>
                            @endif
                        </p>
                        @if($todaySubmission->executive_remarks)
                        <p class="mt-3 mb-0 font-size-13 text-muted"><strong>Your Remarks:</strong> "{{ $todaySubmission->executive_remarks }}"</p>
                        @endif
                        @if($todaySubmission->tl_remarks)
                        <p class="mt-2 mb-0 font-size-13 text-muted"><strong>TL/Manager Remarks:</strong> "{{ $todaySubmission->tl_remarks }}"</p>
                        @endif
                    </div>
                    @else
                    <form id="dayClosingForm" action="{{ route('day-closing.submit') }}" method="POST">
                        @csrf

                        <div class="custom-control custom-checkbox mb-4">
                            <input type="checkbox" class="custom-control-input" id="onLeaveCheckbox" name="on_leave" value="1" onchange="toggleLeaveState(this)">
                            <label class="custom-control-label font-weight-semibold text-danger" style="cursor: pointer;" for="onLeaveCheckbox">
                                <i class="mdi mdi-airplane-takeoff mr-1"></i> I was on leave today
                            </label>
                        </div>

                        @if(isset($todayActivities) && !empty($todayActivities))
                        <div class="card bg-light border-0 mb-4" style="border-radius: 12px;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 font-weight-bold text-dark font-size-12">
                                        <i class="mdi mdi-checkbox-multiple-marked text-primary mr-1"></i> Today's Logged Activities
                                    </h6>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-0.5 px-2 rounded-pill font-size-10" onclick="copyActivitiesToRemarks()">
                                        <i class="mdi mdi-content-copy mr-1"></i> Auto-fill Remarks
                                    </button>
                                </div>
                                <div style="max-height: 120px; overflow-y: auto; font-size: 12px; color: #5c6a7a;">
                                    <ul class="list-unstyled mb-0 pl-1" id="loggedActivitiesList">
                                        @foreach($todayActivities as $act)
                                        <li class="mb-1"><i class="mdi mdi-circle-small text-primary mr-1"></i> <span class="activity-text">{{ $act }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="form-group mb-4">
                            <label id="remarksLabel" class="font-weight-600 mb-2">Executive Daily Remarks / Summary:</label>
                            <textarea name="remarks" id="remarksTextarea" class="form-control border" rows="4" style="border-radius: 10px;" placeholder="Summarize your work done today, and explain if targets were not met..." required></textarea>
                        </div>

                        <div id="targetWarningAlert" style="display: {{ $targetStatus === 'Not Met' ? 'block' : 'none' }};">
                            @if($targetStatus === 'Not Met')
                            <div class="alert alert-soft-warning font-size-12 mb-4 p-3 border" style="border-radius: 8px;">
                                <i class="mdi mdi-alert-circle mr-1"></i> <strong>Note:</strong> Your target is not met today. Please justify the reason in your remarks block above for quick approval.
                            </div>
                            @endif
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-submit-closing btn-md shadow-sm">
                                Submit Day Closing
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- History Sidebar -->
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="text-premium-dark font-size-15 mb-3"><i class="mdi mdi-history mr-1"></i> Submission History</h5>

                    @if($history->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="mdi mdi-folder-open-outline font-size-36 d-block mb-2"></i>
                        <p class="font-size-12 mb-0">No past submissions found.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table history-table mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Metrics</th>
                                    <th>Target</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $item)
                                <tr>
                                    <td><strong>{{ $item->closing_date->format('d M') }}</strong></td>
                                    <td>
                                        @if($item->department === 'NSD')
                                        <small class="text-muted d-block">STS: {{ $item->achieved_metrics['sts'] ?? 0 }}</small>
                                        <small class="text-muted d-block">DSR: {{ $item->achieved_metrics['dsr'] ?? 0 }}</small>
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
                                        <small class="text-muted d-block">Global: {{ $ghFormatted }}</small>
                                        <small class="text-muted d-block">Break: {{ $bhFormatted }}</small>
                                        @endif

                                        @if($item->department === 'CSD')
                                        @if(isset($item->achieved_metrics['communications']))
                                        <small class="text-muted d-block">Comms: {{ $item->achieved_metrics['communications'] ?? 0 }}</small>
                                        @endif
                                        @else
                                        @if(isset($item->achieved_metrics['hours']))
                                        <small class="text-muted d-block">Tasks: {{ $thFormatted }}</small>
                                        <small class="text-muted d-block">Done: {{ $item->achieved_metrics['tasks'] ?? 0 }}</small>
                                        @endif
                                        @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->target_status === 'Met')
                                        <span class="badge badge-soft-success px-2 py-0.5" style="font-size: 10px;">Met</span>
                                        @elseif($item->target_status === 'On Leave')
                                        <span class="badge badge-soft-danger px-2 py-0.5" style="font-size: 10px;">On Leave</span>
                                        @else
                                        <span class="badge badge-soft-warning px-2 py-0.5" style="font-size: 10px;">Not Met</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status === 'Pending')
                                        <span class="badge badge-soft-warning px-2 py-0.5" style="font-size: 10px;">Pending</span>
                                        @elseif($item->status === 'Approved')
                                        <span class="badge badge-soft-success px-2 py-0.5" style="font-size: 10px;">Approved</span>
                                        @else
                                        <span class="badge badge-soft-danger px-2 py-0.5" style="font-size: 10px;">Rejected</span>
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
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleLeaveState(checkbox) {
        var remarksLabel = document.getElementById('remarksLabel');
        var remarksTextarea = document.getElementById('remarksTextarea');
        var warningAlert = document.getElementById('targetWarningAlert');
        var statusBadge = document.querySelector('.status-badge-lg');

        if (checkbox.checked) {
            remarksLabel.textContent = 'Reason for Leave / Details (Optional):';
            remarksTextarea.placeholder = 'Provide details about your leave (e.g. sick leave, casual leave)...';
            remarksTextarea.required = false;
            if (warningAlert) warningAlert.style.display = 'none';
            if (statusBadge) {
                statusBadge.className = 'badge badge-soft-danger status-badge-lg';
                statusBadge.innerHTML = '<i class="mdi mdi-airplane-takeoff mr-1"></i> On Leave';
            }
        } else {
            remarksLabel.textContent = 'Executive Daily Remarks / Summary:';
            remarksTextarea.placeholder = 'Summarize your work done today, and explain if targets were not met...';
            remarksTextarea.required = true;

            var targetStatus = "{{ $targetStatus }}";
            if (warningAlert && targetStatus === 'Not Met') {
                warningAlert.style.display = 'block';
            }
            if (statusBadge) {
                if (targetStatus === 'Met') {
                    statusBadge.className = 'badge badge-soft-success status-badge-lg';
                    statusBadge.innerHTML = '<i class="mdi mdi-check-decagram mr-1"></i> Target Met';
                } else {
                    statusBadge.className = 'badge badge-soft-warning status-badge-lg';
                    statusBadge.innerHTML = '<i class="mdi mdi-alert-circle-outline mr-1"></i> Target Not Met';
                }
            }
        }
    }

    function copyActivitiesToRemarks() {
        var remarksTextarea = document.getElementById('remarksTextarea');
        var activityElms = document.querySelectorAll('#loggedActivitiesList .activity-text');
        var text = '';
        activityElms.forEach(function(el) {
            text += '- ' + el.textContent.trim() + '\n';
        });
        if (text) {
            remarksTextarea.value = "Today's summary:\n" + text;
        }
    }

    $(document).ready(function() {
        $('#dayClosingForm').on('submit', function(e) {
            var onLeave = $('#onLeaveCheckbox').is(':checked');
            if (!onLeave) {
                var remarks = $('#remarksTextarea').val().trim();
                if (remarks === '') {
                    alertify.alert('Validation Error', 'Executive Remarks are required.');
                    e.preventDefault();
                    return false;
                }
                
                // Count words
                var words = remarks.split(/\s+/).filter(function(word) {
                    return word.length > 0;
                });
                var count = words.length;

                if (count < 3 || count > 50) {
                    alertify.alert('Validation Error', 'Executive Remarks must be between 3 and 50 words. Current count: ' + count + ' words.');
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
@endsection
