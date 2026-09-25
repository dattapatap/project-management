@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    {{-- Page Header --}}
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px;">
        <div class="card-body py-3.5 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 14px; width: 48px; height: 48px; font-size: 24px;">
                    <i class="mdi mdi-calendar-star"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-18">Company Holiday Calendar ({{ $year }})</h4>
                    <span class="text-muted font-size-12">Official schedule of public holidays, festivals, and company non-working days</span>
                </div>
            </div>
            <div class="d-flex align-items-center mt-2 mt-md-0" style="gap: 10px;">
                {{-- Year Filter Form --}}
                <form method="GET" action="{{ route('hrms.holidays.index') }}" class="d-inline-flex align-items-center">
                    <label class="text-muted font-size-12 mb-0 mr-2 font-weight-bold">Year:</label>
                    <select name="year" class="form-control form-control-sm custom-select font-weight-bold" onchange="this.form.submit()" style="border-radius: 8px; width: 110px;">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>

                @if(Auth::user()->hasRole(['Admin', 'Branch-Manager']))
                <a href="{{ route('settings.holidays.index') }}" class="btn btn-sm btn-outline-primary px-3 font-weight-bold" style="border-radius: 8px;" title="Manage Holidays">
                    <i class="mdi mdi-cog-outline mr-1"></i> Manage Holidays
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Next Upcoming Holiday Banner --}}
    @if($nextHoliday)
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%); border-color: #a7f3d0 !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="avatar-md rounded-circle d-flex align-items-center justify-content-center text-success mr-3 shadow-sm" style="background: #ffffff; width: 56px; height: 56px; font-size: 26px;">
                    <i class="mdi mdi-palm-tree"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center flex-wrap mb-1" style="gap: 6px;">
                        <span class="badge badge-success px-2.5 py-0.5 font-size-11 font-weight-bold">NEXT COMPANY HOLIDAY</span>
                        <span class="badge {{ $nextHoliday->type_badge }} font-size-11">{{ $nextHoliday->type }}</span>
                        @if($nextHoliday->is_long_weekend)
                        <span class="badge badge-soft-warning font-size-11 font-weight-bold">
                            <i class="mdi mdi-beach mr-0.5"></i> Long Weekend!
                        </span>
                        @endif
                    </div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-18">{{ $nextHoliday->name }}</h4>
                    <p class="text-muted font-size-12 mb-0 mt-0.5">
                        <i class="mdi mdi-calendar-clock mr-1 text-success"></i><strong>{{ $nextHoliday->formatted_date }}</strong>
                        @if($nextHoliday->description)
                            &mdash; {{ $nextHoliday->description }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="mt-3 mt-md-0 text-md-right">
                <span class="badge badge-pill badge-success px-3.5 py-2 font-size-13 font-weight-bold shadow-sm">
                    @if($nextHoliday->is_today)
                        🏖️ Today is a Holiday!
                    @elseif($nextHoliday->is_tomorrow)
                        ⚡ Tomorrow (1 Day Away)
                    @else
                        In {{ $nextHoliday->days_away }} Days
                    @endif
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #4f46e5 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Total Holidays ({{ $year }})</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #10b981 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Remaining Holidays</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['remaining'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-success" style="background: #ecfdf5; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-clock-fast"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Long Weekends</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['long_weekends'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-warning" style="background: #fffbeb; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-beach"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #6b7280 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Completed Holidays</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['past'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-secondary" style="background: #f3f4f6; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-check-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Holiday Schedule Table --}}
    <div class="card border shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="mb-0 text-dark font-weight-bold font-size-15">
                    <i class="mdi mdi-format-list-bulleted mr-1 text-primary"></i> Annual Holiday List &mdash; {{ $year }}
                </h5>
                <span class="text-muted font-size-11">Chronological listing of all declared holidays</span>
            </div>
            <span class="badge badge-light border text-dark font-size-11 font-weight-bold px-2.5 py-1">
                {{ $holidays->count() }} Declared Days
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 font-size-13">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 px-4 text-muted font-weight-bold" style="width: 70px;">#</th>
                            <th class="border-0 text-muted font-weight-bold">Holiday Date</th>
                            <th class="border-0 text-muted font-weight-bold">Day</th>
                            <th class="border-0 text-muted font-weight-bold">Holiday Name</th>
                            <th class="border-0 text-muted font-weight-bold">Type</th>
                            <th class="border-0 text-muted font-weight-bold">Description</th>
                            <th class="border-0 text-muted font-weight-bold text-right px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $index => $h)
                        <tr class="{{ $h->is_today ? 'table-warning font-weight-semibold' : ($h->is_past ? 'text-muted' : '') }}" style="{{ $h->is_past ? 'opacity: 0.75;' : '' }}">
                            <td class="px-4 font-weight-bold">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-2.5 text-center px-2 py-1 rounded {{ $h->is_today ? 'bg-warning text-dark font-weight-bold' : ($h->is_past ? 'bg-light text-muted' : 'bg-primary text-white') }}" style="min-width: 48px; border-radius: 8px;">
                                        <div class="font-size-13 font-weight-bold line-height-1">{{ $h->carbon_date->format('d') }}</div>
                                        <div class="font-size-9 text-uppercase">{{ $h->carbon_date->format('M') }}</div>
                                    </div>
                                    <div>
                                        <span class="font-weight-bold {{ $h->is_past ? 'text-muted' : 'text-dark' }} font-size-13">{{ $h->formatted_date }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-weight-semibold {{ $h->is_long_weekend ? 'text-primary' : '' }}">
                                    {{ $h->day_name }}
                                </span>
                                @if($h->is_long_weekend)
                                <i class="mdi mdi-beach ml-0.5 text-warning" title="Long Weekend"></i>
                                @endif
                            </td>
                            <td>
                                <span class="font-weight-bold {{ $h->is_past ? 'text-muted' : 'text-dark' }}">{{ $h->name }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $h->type_badge }} px-2 py-0.5 font-size-11">{{ $h->type }}</span>
                            </td>
                            <td class="text-muted font-size-12">
                                {{ $h->description ?? 'Official Holiday' }}
                            </td>
                            <td class="text-right px-4">
                                @if($h->is_today)
                                    <span class="badge badge-warning px-2.5 py-1 font-size-11 font-weight-bold">🏖️ Today!</span>
                                @elseif($h->days_diff === 1)
                                    <span class="badge badge-soft-success px-2.5 py-1 font-size-11 font-weight-bold">Tomorrow</span>
                                @elseif($h->days_diff > 1)
                                    <span class="badge badge-soft-primary px-2.5 py-1 font-size-11">In {{ $h->days_diff }} days</span>
                                @else
                                    <span class="badge badge-light border text-muted font-size-10">Completed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="mdi mdi-calendar-blank-outline font-size-36 text-muted d-block mb-2"></i>
                                No holidays configured for year {{ $year }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
