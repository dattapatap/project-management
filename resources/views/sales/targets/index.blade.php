@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page pb-5">
    <div class="erp-page-header my-4">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">
                <i class="mdi mdi-trophy-outline mr-2 text-warning"></i>Targets & Leaderboard
            </h4>
            <p class="erp-page-subtitle">Track performance targets and sales standings</p>
        </div>
        <div class="erp-page-header__actions d-flex align-items-center">
            <form method="GET" action="{{ route('sales.targets.index') }}" class="form-inline mr-2">
                <select name="month" class="form-control form-control-sm border mr-2" onchange="this.form.submit()">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $m == $selectedMonth ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                        @endfor
                </select>
                <select name="year" class="form-control form-control-sm border mr-2" onchange="this.form.submit()">
                    @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                        <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                </select>
            </form>

            @if(Auth::user()->hasRole(['Admin', 'Branch-Manager']))
            <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#setTargetModal">
                <i class="mdi mdi-plus mr-1"></i>Set Target
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- My Targets Segment -->
        <div class="col-lg-6">
            <div class="card bg-white border shadow-sm mb-4 h-100">
                <div class="card-body">
                    @if(Auth::user()->hasRole(['Admin', 'Branch-Manager']))
                    {{-- Admin / Branch Manager: Team Targets only --}}
                    <h5 class="text-dark font-weight-semibold mb-4 d-flex align-items-center">
                        <i class="mdi mdi-account-group mr-2 text-primary"></i>Team Targets ({{ $selectedYear }})
                    </h5>

                    @if(empty($subordinateTargets) || count($subordinateTargets) == 0)
                    <div class="text-center py-5 text-muted">
                        <i class="mdi mdi-alert-circle-outline display-4 text-warning"></i>
                        <p class="mt-2 font-weight-semibold font-size-13">No team member targets configured for {{ $selectedYear }} yet.</p>
                    </div>
                    @else
                    <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-centered table-hover mb-0">
                            <thead class="thead-light" style="position: sticky; top: 0; z-index: 1; background: #f8fafc;">
                                <tr>
                                    <th>Member</th>
                                    <th>Type</th>
                                    <th>Month</th>
                                    <th>Target</th>
                                    <th>Achieved</th>
                                    <th class="text-center">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($subordinateTargets as $subTgt)
                                @php
                                $percent = $subTgt->target_value > 0 ? min(100, round(($subTgt->achieved_value / $subTgt->target_value) * 100)) : 0;
                                $monthName = date('F', mktime(0, 0, 0, $subTgt->period_month, 1));
                                $typeLabel = [
                                    'revenue' => 'Revenue (₹)',
                                    'conversions' => 'Conversions',
                                    'meetings' => 'Meetings'
                                ][$subTgt->target_type] ?? $subTgt->target_type;

                                $progressColor = 'bg-primary';
                                if ($percent >= 100) $progressColor = 'bg-success';
                                elseif ($percent >= 50) $progressColor = 'bg-warning';
                                else $progressColor = 'bg-danger';
                                @endphp
                                <tr>
                                    <td style="padding: 10px;">
                                        <h6 class="font-size-13 font-weight-bold mb-0">{{ optional($subTgt->user)->name }}</h6>
                                    </td>
                                    <td style="padding: 10px;">
                                        <span class="badge badge-soft-info">{{ $typeLabel }}</span>
                                    </td>
                                    <td style="padding: 10px;">
                                        <span class="text-muted font-weight-semibold">{{ $monthName }}</span>
                                    </td>
                                    <td style="padding: 10px;" class="font-weight-semibold">
                                        {{ $subTgt->target_type == 'revenue' ? '₹' : '' }}{{ number_format($subTgt->target_value) }}
                                    </td>
                                    <td style="padding: 10px;" class="font-weight-semibold text-success">
                                        {{ $subTgt->target_type == 'revenue' ? '₹' : '' }}{{ number_format($subTgt->achieved_value) }}
                                    </td>
                                    <td style="padding: 10px; min-width: 120px;">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2 font-weight-bold text-dark font-size-11">{{ $percent }}%</span>
                                            <div class="progress flex-grow-1" style="height: 6px; background: #e2e8f0; border-radius: 3px;">
                                                <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @else
                    {{-- Team Leader / Sales Executive: My Targets only --}}
                    <h5 class="text-dark font-weight-semibold mb-4 d-flex align-items-center">
                        <i class="mdi mdi-target mr-2 text-primary"></i>My Performance Targets ({{ $selectedYear }})
                    </h5>

                    @if(empty($targets))
                    <div class="text-center py-5 text-muted">
                        <i class="mdi mdi-alert-circle-outline display-4 text-warning"></i>
                        <p class="mt-2 font-weight-semibold font-size-13">No targets set for you this year yet.</p>
                    </div>
                    @else
                    <div class="target-list" style="max-height: 450px; overflow-y: auto; padding-right: 5px;">
                        @foreach($targets as $tgt)
                        @php
                        $percent = $tgt['target_value'] > 0 ? min(100, round(($tgt['achieved_value'] / $tgt['target_value']) * 100)) : 0;
                        $monthName = date('F', mktime(0, 0, 0, $tgt['period_month'], 1));

                        $typeLabels = [
                        'revenue' => 'Revenue Target (₹)',
                        'conversions' => 'Matured Conversions',
                        'meetings' => 'Meetings Fixed'
                        ];

                        $progressColor = 'bg-primary';
                        if ($percent >= 100) $progressColor = 'bg-success';
                        elseif ($percent >= 50) $progressColor = 'bg-warning';
                        else $progressColor = 'bg-danger';
                        @endphp

                        <div class="target-item p-3 mb-3 rounded border shadow-sm" style="background: #f8fafc; border-left: 4px solid #3b82f6 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="text-muted font-weight-bold font-size-11">{{ $monthName }}</span>
                                    <h6 class="text-dark mb-0 font-weight-bold mt-1">
                                        {{ $typeLabels[$tgt['target_type']] ?? $tgt['target_type'] }}
                                    </h6>
                                </div>
                                <div class="text-right">
                                    <span class="text-dark font-weight-bold">{{ $tgt['target_type'] == 'revenue' ? '₹' : '' }}{{ number_format($tgt['achieved_value']) }}</span>
                                    <span class="text-muted">/ {{ $tgt['target_type'] == 'revenue' ? '₹' : '' }}{{ number_format($tgt['target_value']) }}</span>
                                </div>
                            </div>

                            <div class="progress progress-sm" style="height: 8px; background: #e2e8f0; border-radius: 4px;">
                                <div class="progress-bar {{ $progressColor }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 font-size-11">
                                <span class="text-muted font-weight-semibold">{{ $percent }}% Completed</span>
                                <span class="text-muted">Realtime sync</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Leaderboard Widget Segment -->
        <div class="col-lg-6">
            <div class="card bg-white border shadow-sm mb-4 h-100">
                <div class="card-body">
                    <h5 class="text-dark font-weight-semibold mb-4 d-flex align-items-center justify-content-between">
                        <span><i class="mdi mdi-medal mr-2 text-warning"></i>Sales Leaderboard</span>
                        <span class="badge badge-pill badge-soft-warning font-size-12 px-2.5 py-1">
                            {{ date('F', mktime(0, 0, 0, $selectedMonth, 1)) }} {{ $selectedYear }}
                        </span>
                    </h5>

                    <div class="leaderboard-list">
                        @foreach($leaderboard as $index => $rep)
                        @php
                        $rank = $index + 1;
                        $rankBadge = '';
                        $cardStyle = 'background: #f8fafc; border: 1px solid #e2e8f0 !important;';

                        if ($rank === 1) {
                        $rankBadge = '<span class="rank-icon-wrapper"><i class="mdi mdi-crown text-warning font-size-24"></i></span>';
                        $cardStyle = 'background: linear-gradient(90deg, rgba(241, 180, 76, 0.08), #ffffff); border: 1px solid rgba(241, 180, 76, 0.3) !important;';
                        } elseif ($rank === 2) {
                        $rankBadge = '<span class="rank-icon-wrapper"><i class="mdi mdi-medal text-secondary font-size-22"></i></span>';
                        $cardStyle = 'background: linear-gradient(90deg, rgba(148, 163, 184, 0.08), #ffffff); border: 1px solid rgba(148, 163, 184, 0.2) !important;';
                        } elseif ($rank === 3) {
                        $rankBadge = '<span class="rank-icon-wrapper"><i class="mdi mdi-medal text-orange font-size-20"></i></span>';
                        $cardStyle = 'background: linear-gradient(90deg, rgba(249, 115, 22, 0.08), #ffffff); border: 1px solid rgba(249, 115, 22, 0.2) !important;';
                        } else {
                        $rankBadge = '<span class="rank-number-wrapper font-weight-bold text-muted">' . $rank . '</span>';
                        }
                        @endphp

                        <div class="leaderboard-item d-flex align-items-center p-3 mb-2 rounded text-dark" style="{{ $cardStyle }}">
                            <div class="mr-3 text-center" style="width: 30px;">
                                {!! $rankBadge !!}
                            </div>
                            <div class="mr-3">
                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center bg-light font-weight-bold text-uppercase border" style="width: 38px; height: 38px; color: #475569;">
                                    @if($rep['avatar'])
                                    <img src="{{ asset('storage/' . $rep['avatar']) }}" class="rounded-circle img-fluid" style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                    {{ substr($rep['name'], 0, 2) }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-dark mb-0 font-weight-bold">{{ $rep['name'] }}</h6>
                                <div class="d-flex align-items-center mt-1 font-size-12 text-muted">
                                    <span class="mr-3"><i class="mdi mdi-sync-circle mr-1"></i>{{ $rep['conversions'] }} Convs</span>
                                    <span><i class="mdi mdi-calendar-check mr-1"></i>{{ $rep['meetings'] }} Mtgs</span>
                                </div>
                            </div>
                            <div class="text-right ml-2">
                                <h6 class="text-success font-weight-bold mb-0">₹{{ number_format($rep['revenue']) }}</h6>
                                @if($rep['target'] > 0)
                                <span class="text-muted font-size-11 font-weight-semibold">{{ $rep['progress_percent'] }}% of Tgt</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Set Target Modal -->
@if(Auth::user()->hasRole(['Admin', 'Branch-Manager']))
<div class="modal fade" id="setTargetModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="mdi mdi-target mr-2 text-primary"></i>Set Monthly Sales Target</h5>
                <button type="button" class="close text-dark btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="setTargetForm">
                @csrf
                <div class="modal-body text-dark">
                    <div class="form-group">
                        <label class="font-weight-semibold">Select Sales Representative <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control border" required>
                            @foreach($subordinates as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Target KPI Type <span class="text-danger">*</span></label>
                        <select name="target_type" class="form-control border" required>
                            <option value="revenue">Revenue (Amount Collected - ₹)</option>
                            <option value="conversions">Conversions (Matured Leads Count)</option>
                            <option value="meetings">Meetings (Meeting Fixed Count)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Target Value <span class="text-danger">*</span></label>
                        <input type="number" name="target_value" class="form-control border" required min="1" placeholder="e.g. 500000 or 10">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Month</label>
                                <select name="period_month" class="form-control border">
                                    @for($m=1; $m<=12; $m++)
                                        <option value="{{ $m }}" {{ $m == $selectedMonth ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Year</label>
                                <select name="period_year" class="form-control border">
                                    @for($y=date('Y'); $y<=date('Y')+2; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary btnmdlclose" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary shadow-sm">Assign Target</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#setTargetForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('sales.targets.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        alertify.success(response.message);
                        setTimeout(() => window.location.reload(), 800);
                    }
                },
                error: function(xhr) {
                    alertify.error(xhr.responseJSON?.message || "Validation or server error occurred.");
                }
            });
        });
    });
</script>
@endsection
