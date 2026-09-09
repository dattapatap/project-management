@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    <!-- Header Card -->
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px;">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 12px; width: 44px; height: 44px; font-size: 22px;">
                    <i class="mdi mdi-calendar-account-outline"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-17">My Leaves & HR Portal</h4>
                    <span class="text-muted font-size-12">View your annual & monthly leave balances (2 EL / month), apply for leave, and track approval status</span>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm px-3.5 py-1.5 shadow-sm font-weight-bold mt-2 mt-md-0" data-toggle="modal" data-target="#applyLeaveModal" style="border-radius: 8px;">
                <i class="mdi mdi-plus mr-1"></i> Apply For Leave
            </button>
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

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
        <div class="d-flex align-items-center mb-1">
            <i class="mdi mdi-alert-circle mr-1 font-size-16"></i>
            <span class="font-weight-bold">Leave Application Notice:</span>
        </div>
        <ul class="mb-0 pl-3 font-size-13">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    {{-- Leave Balances Cards --}}
    <div class="row mb-4">
        @foreach($balances as $bal)
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #4f46e5 !important;">
                <div class="card-body py-3 px-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="font-weight-bold text-dark font-size-13">{{ $bal['name'] }}</span>
                            <span class="badge badge-light border font-size-11 font-weight-bold">{{ $bal['code'] }}</span>
                        </div>
                        <div class="d-flex align-items-baseline justify-content-between mb-2">
                            <div>
                                <span class="font-size-22 font-weight-bold text-primary">{{ $bal['remaining'] }}</span>
                                <span class="text-muted font-size-11">/ {{ $bal['accrued_till_now'] }} accrued left</span>
                            </div>
                            <span class="badge {{ $bal['is_paid'] ? 'badge-soft-success' : 'badge-soft-secondary' }} px-2 py-0.5 rounded font-size-10 font-weight-bold">
                                {{ $bal['is_paid'] ? 'Paid' : 'Unpaid' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="pt-2 border-top font-size-11 text-muted d-flex align-items-center justify-content-between flex-wrap">
                        @if($bal['monthly_limit'])
                            <span><i class="mdi mdi-calendar-clock mr-0.5 text-primary"></i>This Mo: <strong>{{ $bal['taken_this_month'] }}/{{ $bal['monthly_limit'] }}d</strong></span>
                        @else
                            <span>Annual Quota: <strong>{{ $bal['allowed'] }}d</strong></span>
                        @endif
                        <span>Total Used: <strong>{{ $bal['taken'] }}d</strong></span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- My Leave Applications History -->
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="card-title text-dark mb-0 font-size-14 font-weight-bold">
                <i class="mdi mdi-history text-primary mr-1"></i> Leave Applications History
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4 text-muted font-size-11 text-uppercase font-weight-bold">Leave Type</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Dates</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Duration</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Reason</th>
                            <th class="py-3 px-3 text-center text-muted font-size-11 text-uppercase font-weight-bold">Status</th>
                            <th class="py-3 px-4 text-muted font-size-11 text-uppercase font-weight-bold">Admin Remarks</th>
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
                                <span class="font-weight-bold text-dark font-size-13">{{ $leave->leaveType->name ?? 'Leave' }}</span>
                                <small class="text-muted d-block font-size-11">{{ $leave->leaveType->code ?? '' }}</small>
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
                            <td class="px-3 font-size-12 text-dark">
                                {{ $leave->reason }}
                            </td>
                            <td class="px-3 text-center">
                                <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded font-size-11 font-weight-bold text-capitalize">
                                    {{ $leave->status }}
                                </span>
                            </td>
                            <td class="px-4 font-size-12 text-muted">
                                @if($leave->admin_remarks)
                                    <span class="text-dark">{{ $leave->admin_remarks }}</span>
                                    @if($leave->approver)
                                        <small class="text-muted d-block font-size-10">By: {{ $leave->approver->name }} on {{ $leave->approved_at?->format('d M') }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="mdi mdi-calendar-blank-outline font-size-24 text-muted d-block mb-1"></i>
                                You have not submitted any leave applications yet. Click "Apply For Leave" to submit.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Apply Leave Modal --}}
<div class="modal fade" id="applyLeaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form action="{{ route('hrms.my-leaves.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0">Apply For Leave</h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    {{-- Leave Type Select --}}
                    <div class="form-group mb-3">
                        <label class="font-size-12 font-weight-bold text-dark">Select Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-control font-size-13" required style="border-radius: 8px;">
                            <option value="">-- Choose Leave Category --</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name }} ({{ $type->code }}) &bull; {{ $type->monthly_limit ? 'Max ' . $type->monthly_limit . ' days/mo' : 'Annual: ' . $type->days_allowed_per_year . 'd' }} - {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Full Day / Half Day Toggle --}}
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="is_half_day" value="1" class="custom-control-input" id="isHalfDayCheck">
                            <label class="custom-control-label font-size-12 font-weight-semibold text-dark" for="isHalfDayCheck">This is a Half-Day Leave (0.5 Day)</label>
                        </div>
                    </div>

                    {{-- Half Day Session Option (Hidden by default) --}}
                    <div class="form-group mb-3 d-none" id="halfDayTypeWrapper">
                        <label class="font-size-12 font-weight-bold text-dark">Half-Day Session <span class="text-danger">*</span></label>
                        <select name="half_day_type" class="form-control font-size-13" style="border-radius: 8px;">
                            <option value="first_half">First Half (Morning Session)</option>
                            <option value="second_half">Second Half (Afternoon Session)</option>
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="leaveStartDate" class="form-control font-size-13" required value="{{ date('Y-m-d') }}" style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-6 mb-0" id="leaveEndDateWrapper">
                            <label class="font-size-12 font-weight-bold text-dark">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="leaveEndDate" class="form-control font-size-13" value="{{ date('Y-m-d') }}" style="border-radius: 8px;">
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div class="form-group mb-0">
                        <label class="font-size-12 font-weight-bold text-dark">Reason for Leave <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" class="form-control font-size-13" placeholder="Please provide specific reason..." required style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Submit Leave Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#isHalfDayCheck').on('change', function() {
            if ($(this).is(':checked')) {
                $('#halfDayTypeWrapper').removeClass('d-none');
                $('#leaveEndDateWrapper').addClass('d-none');
                $('#leaveEndDate').val($('#leaveStartDate').val());
            } else {
                $('#halfDayTypeWrapper').addClass('d-none');
                $('#leaveEndDateWrapper').removeClass('d-none');
            }
        });

        $('#leaveStartDate').on('change', function() {
            if ($('#isHalfDayCheck').is(':checked')) {
                $('#leaveEndDate').val($(this).val());
            }
        });
    });
</script>
@endsection
