@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    {{-- Page Header --}}
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #fdfbfb 100%);">
        <div class="card-body py-3.5 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 14px; width: 48px; height: 48px; font-size: 24px;">
                    <i class="mdi mdi-cake-variant-outline"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-18">Celebrations</h4>
                    <span class="text-muted font-size-12">Celebrate birthdays, work anniversaries</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #ef4444 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Today's Birthdays</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['today_birthdays'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-danger" style="background: #fef2f2; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-cake-variant"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Today's Anniversaries</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['today_anniversaries'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-warning" style="background: #fffbeb; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-party-popper"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #3b82f6 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Upcoming Birthdays (Next 2 Months)</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['upcoming_birthdays_count'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-primary" style="background: #eff6ff; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-calendar-heart"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border shadow-sm h-100" style="border-radius: 14px; border-left: 4px solid #10b981 !important;">
                <div class="card-body py-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Upcoming Anniversaries (Next 2 Months)</span>
                        <h3 class="mb-0 font-weight-bold text-dark mt-1 font-size-22">{{ $stats['upcoming_anniv_count'] }}</h3>
                    </div>
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-success" style="background: #ecfdf5; width: 44px; height: 44px; font-size: 22px;">
                        <i class="mdi mdi-medal-outline"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Spotlight (If any celebration today) --}}
    @if($todays['total_count'] > 0)
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #fdf2f8 0%, #fff7ed 50%, #eff6ff 100%); border-color: #fbcfe8 !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
                <div class="d-flex align-items-center">
                    <span class="badge badge-danger px-3 py-1 font-size-12 font-weight-bold mr-2" style="border-radius: 6px;">
                        <i class="mdi mdi-fire mr-1"></i> TODAY'S CELEBRATION
                    </span>
                    <h5 class="mb-0 text-dark font-weight-bold font-size-16">Let's congratulate our team members today! 🎉</h5>
                </div>
                <span class="text-muted font-size-12 font-weight-semibold mt-1 mt-md-0">{{ \Carbon\Carbon::today()->format('l, d F Y') }}</span>
            </div>

            <div class="row">
                {{-- Today Birthdays --}}
                @foreach($todays['birthdays'] as $emp)
                @php
                $bdayUserAcc = $emp->userAccount;
                $bdayRawPhone = $bdayUserAcc?->mobile ?: $emp->alt_number;
                $bdayCleanPhone = preg_replace('/[^0-9]/', '', (string)$bdayRawPhone);
                if (strlen($bdayCleanPhone) === 10) {
                $bdayCleanPhone = '91' . $bdayCleanPhone;
                }
                $bdayWaMsg = urlencode("Happy Birthday {$emp->name}! 🎉🎂 Wishing you joy, health, and success ahead!");
                $bdayWaUrl = $bdayCleanPhone
                ? "https://api.whatsapp.com/send?phone={$bdayCleanPhone}&text={$bdayWaMsg}"
                : "https://api.whatsapp.com/send?text={$bdayWaMsg}";
                $bdayEmail = $bdayUserAcc?->email ?: $emp->alt_email;
                @endphp
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                        <div class="card-body p-3.5 d-flex align-items-center">
                            <div class="position-relative mr-3 flex-shrink-0">
                                @if($emp->userAccount?->profile)
                                <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle border border-danger shadow-sm" style="width: 54px; height: 54px; object-fit: cover;">
                                @else
                                <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle border border-danger shadow-sm" style="width: 54px; height: 54px;">
                                @endif
                                <span class="position-absolute" style="bottom: -2px; right: -2px; font-size: 18px;" title="Birthday Boy/Girl">🎂</span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 text-dark font-weight-bold font-size-14 text-truncate">{{ $emp->name }}</h6>
                                    <span class="badge badge-soft-danger font-size-10 font-weight-bold">Birthday Today!</span>
                                </div>
                                <p class="text-muted font-size-11 mb-1.5 text-truncate">{{ $emp->designation ?? 'Team Member' }}</p>
                                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                    <span class="badge badge-light border text-muted font-size-10 text-truncate" style="max-width: 130px;">
                                        <i class="mdi mdi-office-building mr-0.5"></i>{{ $emp->userAccount?->departments?->dept?->name ?? 'General' }}
                                    </span>
                                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 6px;">
                                        <a href="{{ $bdayWaUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                            title="Wish {{ $emp->name }} on WhatsApp"
                                            data-toggle="tooltip" data-placement="top"
                                            style="width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); font-size: 16px; padding: 0; border: none; transition: transform 0.2s;"
                                            onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                            <i class="mdi mdi-whatsapp"></i>
                                        </a>
                                        @if($bdayEmail)
                                        <a href="mailto:{{ $bdayEmail }}?subject=Happy%20Birthday%20{{ urlencode($emp->name) }}!%20🎉"
                                            class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                            title="Send Birthday Email to {{ $emp->name }}"
                                            data-toggle="tooltip" data-placement="top"
                                            style="width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); font-size: 15px; padding: 0; border: none; transition: transform 0.2s;"
                                            onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                            <i class="mdi mdi-email-outline"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Today Anniversaries --}}
                @foreach($todays['anniversaries'] as $emp)
                @php
                $annUserAcc = $emp->userAccount;
                $annRawPhone = $annUserAcc?->mobile ?: $emp->alt_number;
                $annCleanPhone = preg_replace('/[^0-9]/', '', (string)$annRawPhone);
                if (strlen($annCleanPhone) === 10) {
                $annCleanPhone = '91' . $annCleanPhone;
                }
                $annWaMsg = urlencode("Happy Work Anniversary {$emp->name}! 🎊 Thank you for your dedication and contributions to Digitalnock!");
                $annWaUrl = $annCleanPhone
                ? "https://api.whatsapp.com/send?phone={$annCleanPhone}&text={$annWaMsg}"
                : "https://api.whatsapp.com/send?text={$annWaMsg}";
                $annEmail = $annUserAcc?->email ?: $emp->alt_email;
                @endphp
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: #ffffff;">
                        <div class="card-body p-3.5 d-flex align-items-center">
                            <div class="position-relative mr-3 flex-shrink-0">
                                @if($emp->userAccount?->profile)
                                <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle border border-warning shadow-sm" style="width: 54px; height: 54px; object-fit: cover;">
                                @else
                                <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle border border-warning shadow-sm" style="width: 54px; height: 54px;">
                                @endif
                                <span class="position-absolute" style="bottom: -2px; right: -2px; font-size: 18px;" title="Work Anniversary">🎉</span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 text-dark font-weight-bold font-size-14 text-truncate">{{ $emp->name }}</h6>
                                    <span class="badge badge-soft-warning font-size-10 font-weight-bold">{{ $emp->years_completed }} {{ $emp->years_completed > 1 ? 'Years' : 'Year' }}!</span>
                                </div>
                                <p class="text-muted font-size-11 mb-1.5 text-truncate">{{ $emp->designation ?? 'Team Member' }}</p>
                                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                                    <span class="badge badge-light border text-muted font-size-10 text-truncate" style="max-width: 130px;">
                                        <i class="mdi mdi-medal-outline mr-0.5"></i>{{ $emp->milestone }}
                                    </span>
                                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 6px;">
                                        <a href="{{ $annWaUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                            title="Congratulate {{ $emp->name }} on WhatsApp"
                                            data-toggle="tooltip" data-placement="top"
                                            style="width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); font-size: 16px; padding: 0; border: none; transition: transform 0.2s;"
                                            onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                            <i class="mdi mdi-whatsapp"></i>
                                        </a>
                                        @if($annEmail)
                                        <a href="mailto:{{ $annEmail }}?subject=Happy%20Work%20Anniversary%20{{ urlencode($emp->name) }}!%20🎊"
                                            class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                            title="Send Anniversary Email to {{ $emp->name }}"
                                            data-toggle="tooltip" data-placement="top"
                                            style="width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); font-size: 15px; padding: 0; border: none; transition: transform 0.2s;"
                                            onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                            <i class="mdi mdi-email-outline"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Main 2-Column Section: Upcoming Birthdays & Upcoming Anniversaries --}}
    <div class="row mb-4">
        {{-- Column 1: Upcoming Birthdays --}}
        <div class="col-lg-6 mb-4">
            <div class="card border shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="mr-2 text-danger font-size-18"><i class="mdi mdi-cake-variant-outline"></i></div>
                        <div>
                            <h5 class="mb-0 text-dark font-weight-bold font-size-15">Upcoming Birthdays</h5>
                            <span class="text-muted font-size-11">Next 2 months schedule</span>
                        </div>
                    </div>
                    <span class="badge badge-soft-danger px-2.5 py-1 font-size-11 font-weight-bold">{{ $upcomingBirthdays->count() }} Upcoming</span>
                </div>
                <div class="card-body p-3" style="max-height: 480px; overflow-y: auto;">
                    @forelse($upcomingBirthdays as $emp)
                    <div class="card mb-2.5 border shadow-none" style="border-radius: 12px; background: #fafafa; transition: all 0.2s ease;">
                        <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                @if($emp->userAccount?->profile)
                                <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px;">
                                @endif
                                <div class="text-truncate">
                                    <h6 class="mb-0 text-dark font-weight-bold font-size-13 text-truncate">{{ $emp->name }}</h6>
                                    <span class="text-muted font-size-11">{{ $emp->designation ?? 'Employee' }} &bull; {{ $emp->userAccount?->departments?->dept?->name ?? 'WMS' }}</span>
                                </div>
                            </div>
                            <div class="text-right pl-2 flex-shrink-0">
                                <div class="font-weight-bold text-dark font-size-12">{{ $emp->formatted_date }}</div>
                                <span class="badge {{ $emp->days_away <= 7 ? 'badge-soft-danger' : 'badge-soft-secondary' }} font-size-10 font-weight-semibold">
                                    {{ $emp->days_away === 1 ? 'Tomorrow' : 'In ' . $emp->days_away . ' days' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="mdi mdi-cake-variant-outline text-muted" style="font-size: 38px;"></i>
                        <p class="text-muted font-size-13 mt-2 mb-0">No upcoming birthdays in the next 2 months.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Column 2: Upcoming Work Anniversaries --}}
        <div class="col-lg-6 mb-4">
            <div class="card border shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="mr-2 text-warning font-size-18"><i class="mdi mdi-party-popper"></i></div>
                        <div>
                            <h5 class="mb-0 text-dark font-weight-bold font-size-15">Upcoming Work Anniversaries</h5>
                            <span class="text-muted font-size-11">Next 2 months schedule & tenure recognition</span>
                        </div>
                    </div>
                    <span class="badge badge-soft-warning px-2.5 py-1 font-size-11 font-weight-bold">{{ $upcomingAnniversaries->count() }} Upcoming</span>
                </div>
                <div class="card-body p-3" style="max-height: 480px; overflow-y: auto;">
                    @forelse($upcomingAnniversaries as $emp)
                    <div class="card mb-2.5 border shadow-none" style="border-radius: 12px; background: #fafafa; transition: all 0.2s ease;">
                        <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                @if($emp->userAccount?->profile)
                                <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle mr-3 border shadow-sm" style="width: 42px; height: 42px;">
                                @endif
                                <div class="text-truncate">
                                    <h6 class="mb-0 text-dark font-weight-bold font-size-13 text-truncate">{{ $emp->name }}</h6>
                                    <span class="text-muted font-size-11">{{ $emp->designation ?? 'Employee' }} &bull; Completing <strong class="text-dark">{{ $emp->completing_years }} {{ $emp->completing_years > 1 ? 'Years' : 'Year' }}</strong></span>
                                </div>
                            </div>
                            <div class="text-right pl-2 flex-shrink-0">
                                <div class="font-weight-bold text-dark font-size-12">{{ $emp->formatted_date }}</div>
                                <span class="badge {{ $emp->days_away <= 7 ? 'badge-soft-warning' : 'badge-soft-secondary' }} font-size-10 font-weight-semibold">
                                    {{ $emp->days_away === 1 ? 'Tomorrow' : 'In ' . $emp->days_away . ' days' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="mdi mdi-party-popper text-muted" style="font-size: 38px;"></i>
                        <p class="text-muted font-size-13 mt-2 mb-0">No upcoming work anniversaries in the next 2 months.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && jQuery.fn.tooltip) {
            jQuery('[data-toggle="tooltip"]').tooltip();
        }
    });
</script>
@endsection
