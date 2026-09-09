@if(Auth::user()->hasRole(['Admin', 'Branch-Manager', 'Team-Leader']) && !empty($employeeTodayStatus))
<style>
    .employee-status-scrollable-container {
        max-height: 320px;
        overflow-y: auto;
        position: relative;
        border-radius: 0 0 16px 16px;
    }

    .employee-status-scrollable-container thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #48A596 !important;
        background-color: #48A596 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05) !important;
    }
</style>
<div class="col-lg-6">
    <div class="card shadow-sm border-0" style="border-radius: 16px; background: white; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02) !important;">
        <div class="card-header bg-white border-bottom py-3.5 px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="avatar-title rounded-circle bg-soft-primary mr-2.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: rgba(72, 165, 150, 0.1); color: #48A596;">
                    <i class="mdi mdi-account-clock-outline font-size-18"></i>
                </div>
                <div>
                    <h5 class="font-size-15 mb-0 text-premium-dark font-weight-bold" style="color: #2b3a4a; font-family: 'Outfit', 'Inter', sans-serif;">
                        Live Employee Activity & Work Status
                    </h5>
                    <p class="text-muted mb-0 font-size-11">Real-time status updates of active team members.</p>
                </div>
            </div>
            <span class="badge badge-soft-info px-2.5 py-1 font-size-11" style="border-radius: 6px;">
                Active Staff: {{ count($employeeTodayStatus) }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="employee-status-scrollable-container table-responsive">
                <table class="table table-premium table-centered table-striped table-hover mb-0 w-100">
                    <thead class="thead-custom-teal">
                        <tr>
                            <th style="padding-left: 24px; width: 25%;">Employee</th>
                            <th style="width: 25%;">Department & Role</th>
                            <th style="padding-right: 24px;">Live Task / Today's Updates</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employeeTodayStatus as $status)
                        @php
                        $isDanger = false;
                        if ($status['details']['type'] === 'od') {
                        $isDanger = empty($status['details']['tasks']);
                        } elseif ($status['details']['type'] === 'nsd') {
                        $isDanger = ($status['details']['sts'] == 0 && $status['details']['dsr'] == 0);
                        } elseif ($status['details']['type'] === 'csd') {
                        $isDanger = ($status['details']['comms'] == 0);
                        }
                        @endphp
                        <tr @if($isDanger) style="background-color: rgba(244, 106, 106, 0.04) !important;" @endif>
                            <td style="padding-left: 24px;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center font-weight-bold text-uppercase border mr-3" style="width: 32px; height: 32px; font-size: 11px; color: #495057;">
                                        {{ substr($status['name'], 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold font-size-13" style="color: #2a3042;">{{ $status['name'] }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-dept text-uppercase badge-{{ strtolower($status['dept']) }}">
                                    {{ $status['dept'] }}
                                </span>
                                <small class="text-muted d-block mt-0.5" style="font-size: 11px;">{{ $status['role'] }}</small>
                            </td>
                            <td style="padding-right: 24px;">
                                @if($status['details']['type'] === 'od')
                                @if($isDanger)
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-1" style="background-color: rgba(244, 106, 106, 0.15); color: #f46a6a; border-radius: 6px; font-weight: 700;">
                                    <i class="mdi mdi-alert-circle mr-1"></i> No active task
                                </span>
                                @else
                                <div class="d-flex flex-column" style="gap: 6px;">
                                    @foreach($status['details']['tasks'] as $taskHtml)
                                    <div class="font-size-13 d-flex align-items-center" style="color: #495057;">
                                        <i class="mdi mdi-play-circle text-success mr-2 font-size-16"></i>
                                        <span>{!! $taskHtml !!}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                @elseif($status['details']['type'] === 'nsd')
                                @if($isDanger)
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-1" style="background-color: rgba(244, 106, 106, 0.15); color: #f46a6a; border-radius: 6px; font-weight: 700;">
                                    <i class="mdi mdi-alert-circle mr-1"></i> No updates today
                                </span>
                                @else
                                <div class="font-size-13 d-flex align-items-center" style="gap: 16px; color: #495057;">
                                    <span>
                                        <i class="mdi mdi-file-document-edit-outline mr-1.5 font-size-16" style="color: #7F00FF;"></i>
                                        STS Updates: <strong style="color: #2a3042;">{{ $status['details']['sts'] }}</strong>
                                    </span>
                                    <span>
                                        <i class="mdi mdi-chart-bell-curve-cumulative mr-1.5 font-size-16" style="color: #7F00FF;"></i>
                                        DSR Updates: <strong style="color: #2a3042;">{{ $status['details']['dsr'] }}</strong>
                                    </span>
                                </div>
                                @endif
                                @elseif($status['details']['type'] === 'csd')
                                @if($isDanger)
                                <span class="badge badge-soft-danger font-size-11 px-2.5 py-1" style="background-color: rgba(244, 106, 106, 0.15); color: #f46a6a; border-radius: 6px; font-weight: 700;">
                                    <i class="mdi mdi-alert-circle mr-1"></i> No updates today
                                </span>
                                @else
                                <div class="font-size-13 d-flex align-items-center" style="color: #495057;">
                                    <span>
                                        <i class="mdi mdi-comment-text-multiple-outline mr-1.5 font-size-16" style="color: #10AC84;"></i>
                                        Communications: <strong style="color: #2a3042;">{{ $status['details']['comms'] }}</strong>
                                    </span>
                                </div>
                                @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
