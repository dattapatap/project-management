@php
$celebrationService = app(\App\Services\Hrms\CelebrationService::class);
$todays = $celebrationService->getTodaysCelebrations();
$upcomingBirthdays = $celebrationService->getUpcomingBirthdays(30);
$upcomingAnniversaries = $celebrationService->getUpcomingAnniversaries(30);

$announcementService = app(\App\Services\Hrms\AnnouncementService::class);
$activeAnnouncements = Auth::user() ? $announcementService->getActiveAnnouncementsForUser(Auth::user()) : collect();
@endphp

{{-- Modern Bottom Dashboard Row: Birthday Card, Anniversary Card & Announcements Card --}}
<div class="row mb-4">
    {{-- 🎂 1. Birthday Card (col-xl-4 col-md-6) --}}
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow-sm h-100" style="border-radius: 16px; background: #ffffff; border: 1px solid var(--erp-border, #e8ecf1) !important;">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between" style="padding: 14px 20px; border-radius: 16px 16px 0 0; border-color: var(--erp-border, #e8ecf1) !important;">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center mr-2.5 text-danger font-size-16" style="background: #fff1f2; width: 32px; height: 32px;">
                        <i class="mdi mdi-cake-variant"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark font-weight-bold font-size-14">Birthday Celebrations</h6>
                        <span class="text-muted font-size-11">Birthdays & upcoming wishes</span>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <button type="button" class="btn btn-sm btn-soft-primary px-2.5 py-1 font-size-11 font-weight-bold" data-toggle="modal" data-target="#staffDirectoryModal" style="border-radius: 8px;">
                        <i class="mdi mdi-account-group-outline mr-1"></i> Staffs
                    </button>
                    @if($todays['birthdays']->count() > 0)
                    <span class="badge badge-danger font-size-10 font-weight-bold px-2 py-1" style="border-radius: 6px;">
                        {{ $todays['birthdays']->count() }} TODAY
                    </span>
                    @endif
                </div>
            </div>

            <div class="card-body d-flex flex-column justify-content-between" style="padding: 18px 20px;">
                <div>
                    {{-- If today has birthdays --}}
                    @if($todays['birthdays']->count() > 0)
                    <div class="d-flex flex-column mb-3" style="gap: 10px;">
                        @foreach($todays['birthdays'] as $emp)
                        @php
                        $cardUserAcc = $emp->userAccount;
                        $cardRawPhone = $cardUserAcc?->mobile ?: $emp->alt_number;
                        $cardCleanPhone = preg_replace('/[^0-9]/', '', (string)$cardRawPhone);
                        if (strlen($cardCleanPhone) === 10) {
                        $cardCleanPhone = '91' . $cardCleanPhone;
                        }
                        $cardWaMsg = urlencode("Happy Birthday {$emp->name}! 🎉🎂 Wishing you joy, health, and success ahead!");
                        $cardWaUrl = $cardCleanPhone
                        ? "https://api.whatsapp.com/send?phone={$cardCleanPhone}&text={$cardWaMsg}"
                        : "https://api.whatsapp.com/send?text={$cardWaMsg}";
                        $cardEmail = $cardUserAcc?->email ?: $emp->alt_email;
                        @endphp
                        <div class="rounded-lg d-flex align-items-center justify-content-between flex-wrap" style="padding: 12px 14px; background: linear-gradient(135deg, #fff1f2 0%, #fff7ed 100%); border: 1px solid #fecdd3; border-radius: 12px; gap: 8px;">
                            <div class="d-flex align-items-center" style="min-width: 0;">
                                <div class="position-relative flex-shrink-0" style="margin-right: 14px !important;">
                                    @if($emp->userAccount?->profile)
                                    <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle border border-danger shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                    @else
                                    <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle border border-danger shadow-sm" style="width: 42px; height: 42px;">
                                    @endif
                                    <span class="position-absolute" style="bottom: -2px; right: -2px; font-size: 14px;">🎂</span>
                                </div>
                                <div class="text-truncate">
                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                        <h6 class="mb-0 text-dark font-weight-bold font-size-13 text-truncate">{{ $emp->name }}</h6>
                                        <span class="badge badge-danger font-size-9 font-weight-bold px-1.5 py-0.5" style="border-radius: 4px;">Today!</span>
                                    </div>
                                    <span class="text-muted font-size-11 d-block text-truncate">{{ $emp->designation ?? 'Team Member' }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-shrink-0" style="gap: 6px;">
                                <a href="{{ $cardWaUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                    title="Wish {{ $emp->name }} on WhatsApp"
                                    data-toggle="tooltip" data-placement="top"
                                    style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); font-size: 16px; padding: 0; border: none; transition: transform 0.2s;"
                                    onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class="mdi mdi-whatsapp"></i>
                                </a>
                                @if($cardEmail)
                                <a href="mailto:{{ $cardEmail }}?subject=Happy%20Birthday%20{{ urlencode($emp->name) }}!%20🎂"
                                    class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                    title="Send Birthday Email to {{ $emp->name }}"
                                    data-toggle="tooltip" data-placement="top"
                                    style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); font-size: 16px; padding: 0; border: none; transition: transform 0.2s;"
                                    onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class="mdi mdi-email-outline"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Upcoming Birthdays list --}}
                    <div>
                        <span class="text-uppercase text-muted font-size-10 font-weight-bold letter-spacing-1 d-block mb-2">
                            Upcoming in Next 30 Days
                        </span>
                        <div class="d-flex flex-column" style="gap: 6px; max-height: 180px; overflow-y: auto;">
                            @forelse($upcomingBirthdays->take(4) as $emp)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: #f8fafc; border: 1px solid #eef2f6;">
                                <div class="d-flex align-items-center" style="min-width: 0;">
                                    <div class="mr-2 flex-shrink-0">
                                        @if($emp->userAccount?->profile)
                                        <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                        <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle" style="width: 30px; height: 30px;">
                                        @endif
                                    </div>
                                    <div class="text-truncate">
                                        <h6 class="mb-0 text-dark font-weight-medium font-size-12 text-truncate">{{ $emp->name }}</h6>
                                        <small class="text-muted font-size-10">{{ $emp->formatted_date ?? ($emp->next_date ? \Carbon\Carbon::parse($emp->next_date)->format('d M') : '') }} ({{ $emp->days_away ?? ($emp->days_until_birthday ?? 0) }}d left)</small>
                                    </div>
                                </div>
                                <div class="text-right pl-2 flex-shrink-0">
                                    <span class="badge badge-light border text-danger font-weight-bold font-size-10 px-2 py-0.5">
                                        {{ $emp->formatted_date ?? ($emp->next_date ? \Carbon\Carbon::parse($emp->next_date)->format('d M') : '') }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-3 text-muted font-size-11">
                                No other birthdays in the next 30 days.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between" style="border-color: var(--erp-border, #e8ecf1) !important;">
                    <small class="text-muted font-size-11 font-weight-medium">
                        🎂 {{ $todays['birthdays']->count() }} today &bull; {{ $upcomingBirthdays->count() }} upcoming
                    </small>
                    @if(Auth::user()?->isGlobalAdmin() || Auth::user()?->isBranchManager())
                    <a href="{{ route('hrms.celebrations.index') }}" class="font-size-11 font-weight-bold text-danger">
                        View Hub &rarr;
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 🎖️ 2. Work Anniversary Card (col-xl-4 col-md-6) --}}
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow-sm h-100" style="border-radius: 16px; background: #ffffff; border: 1px solid var(--erp-border, #e8ecf1) !important;">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between" style="padding: 14px 20px; border-radius: 16px 16px 0 0; border-color: var(--erp-border, #e8ecf1) !important;">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center mr-2.5 text-warning font-size-16" style="background: #fffbeb; width: 32px; height: 32px;">
                        <i class="mdi mdi-party-popper"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark font-weight-bold font-size-14">Work Anniversaries</h6>
                        <span class="text-muted font-size-11">Tenure & loyalty milestones</span>
                    </div>
                </div>
                <div>
                    @if($todays['anniversaries']->count() > 0)
                    <span class="badge badge-warning font-size-10 font-weight-bold px-2 py-1" style="border-radius: 6px;">
                        {{ $todays['anniversaries']->count() }} TODAY
                    </span>
                    @endif
                </div>
            </div>

            <div class="card-body d-flex flex-column justify-content-between" style="padding: 18px 20px;">
                <div>
                    {{-- If today has anniversaries --}}
                    @if($todays['anniversaries']->count() > 0)
                    <div class="d-flex flex-column mb-3" style="gap: 10px;">
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
                        <div class="rounded-lg d-flex align-items-center justify-content-between flex-wrap" style="padding: 12px 14px; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a; border-radius: 12px; gap: 8px;">
                            <div class="d-flex align-items-center" style="min-width: 0;">
                                <div class="position-relative flex-shrink-0" style="margin-right: 14px !important;">
                                    @if($emp->userAccount?->profile)
                                    <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle border border-warning shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                    @else
                                    <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle border border-warning shadow-sm" style="width: 42px; height: 42px;">
                                    @endif
                                    <span class="position-absolute" style="bottom: -2px; right: -2px; font-size: 14px;">🎉</span>
                                </div>
                                <div class="text-truncate">
                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                        <h6 class="mb-0 text-dark font-weight-bold font-size-13 text-truncate">{{ $emp->name }}</h6>
                                        <span class="badge badge-warning font-size-9 font-weight-bold px-1.5 py-0.5" style="border-radius: 4px;">{{ $emp->years_completed }} {{ $emp->years_completed > 1 ? 'Years' : 'Year' }}!</span>
                                    </div>
                                    <span class="text-muted font-size-11 d-block text-truncate">{{ $emp->designation ?? 'Team Member' }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-shrink-0" style="gap: 6px;">
                                <a href="{{ $annWaUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                    title="Congratulate {{ $emp->name }} on WhatsApp"
                                    data-toggle="tooltip" data-placement="top"
                                    style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); font-size: 16px; padding: 0; border: none; transition: transform 0.2s;"
                                    onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class="mdi mdi-whatsapp"></i>
                                </a>
                                @if($annEmail)
                                <a href="mailto:{{ $annEmail }}?subject=Happy%20Work%20Anniversary%20{{ urlencode($emp->name) }}!%20🎊"
                                    class="btn btn-sm d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                    title="Send Anniversary Email to {{ $emp->name }}"
                                    data-toggle="tooltip" data-placement="top"
                                    style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); font-size: 16px; padding: 0; border: none; transition: transform 0.2s;"
                                    onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class="mdi mdi-email-outline"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Upcoming Anniversaries list --}}
                    <div>
                        <span class="text-uppercase text-muted font-size-10 font-weight-bold letter-spacing-1 d-block mb-2">
                            Upcoming in Next 30 Days
                        </span>
                        <div class="d-flex flex-column" style="gap: 6px; max-height: 180px; overflow-y: auto;">
                            @forelse($upcomingAnniversaries->take(4) as $emp)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: #f8fafc; border: 1px solid #eef2f6;">
                                <div class="d-flex align-items-center" style="min-width: 0;">
                                    <div class="mr-2 flex-shrink-0">
                                        @if($emp->userAccount?->profile)
                                        <img src="{{ asset('storage/' . $emp->userAccount->profile) }}" alt="{{ $emp->name }}" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                        <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle" style="width: 30px; height: 30px;">
                                        @endif
                                    </div>
                                    <div class="text-truncate">
                                        <h6 class="mb-0 text-dark font-weight-medium font-size-12 text-truncate">{{ $emp->name }}</h6>
                                        <small class="text-muted font-size-10">{{ $emp->formatted_date ?? ($emp->next_date ? \Carbon\Carbon::parse($emp->next_date)->format('d M') : '') }} (Completing {{ $emp->completing_years ?? ($emp->years_completed ?? 0) }}y)</small>
                                    </div>
                                </div>
                                <div class="text-right pl-2 flex-shrink-0">
                                    <span class="badge badge-light border text-warning font-weight-bold font-size-10 px-2 py-0.5">
                                        {{ $emp->formatted_date ?? ($emp->next_date ? \Carbon\Carbon::parse($emp->next_date)->format('d M') : '') }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-3 text-muted font-size-11">
                                No other work anniversaries in the next 30 days.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between" style="border-color: var(--erp-border, #e8ecf1) !important;">
                    <small class="text-muted font-size-11 font-weight-medium">
                        🎖️ {{ $todays['anniversaries']->count() }} today &bull; {{ $upcomingAnniversaries->count() }} upcoming
                    </small>
                    @if(Auth::user()?->isGlobalAdmin() || Auth::user()?->isBranchManager())
                    <a href="{{ route('hrms.celebrations.index') }}" class="font-size-11 font-weight-bold text-warning">
                        View Hub &rarr;
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 📢 3. Announcements & Broadcasts Card (col-xl-4 col-md-12) --}}
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card shadow-sm h-100" style="border-radius: 16px; background: #ffffff; border: 1px solid var(--erp-border, #e8ecf1) !important;">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between" style="padding: 14px 20px; border-radius: 16px 16px 0 0; border-color: var(--erp-border, #e8ecf1) !important;">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center mr-2.5 text-primary font-size-16" style="background: #eef2ff; width: 32px; height: 32px;">
                        <i class="mdi mdi-bullhorn"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark font-weight-bold font-size-14">Announcements</h6>
                        <span class="text-muted font-size-11">Broadcasts & company alerts</span>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <button type="button" class="btn btn-sm btn-primary px-2.5 py-1 font-size-11 font-weight-bold shadow-sm" data-toggle="modal" data-target="#autoAnnouncementModal" style="border-radius: 8px;">
                        <i class="mdi mdi-bullhorn-outline mr-1"></i> Open Notice
                    </button>
                    @if($activeAnnouncements->count() > 0)
                    <span class="badge badge-soft-primary font-size-10 font-weight-bold px-2 py-1" style="border-radius: 6px;">
                        {{ $activeAnnouncements->count() }} ACTIVE
                    </span>
                    @endif
                </div>
            </div>

            <div class="card-body d-flex flex-column justify-content-between" style="padding: 18px 20px;">
                <div>
                    @if($activeAnnouncements->count() > 0)
                    <div class="d-flex flex-column" style="gap: 12px; max-height: 250px; overflow-y: auto;">
                        @foreach($activeAnnouncements->take(3) as $ann)
                        @php
                        $typeColor = match($ann->type) {
                            'urgent' => 'danger',
                            'event' => 'purple',
                            default => 'primary',
                        };
                        $typeIcon = match($ann->type) {
                            'urgent' => 'mdi-alert-circle',
                            'event' => 'mdi-calendar-star',
                            default => 'mdi-information-outline',
                        };
                        @endphp
                        <div class="rounded-lg border d-flex flex-column cursor-pointer"
                            data-toggle="modal" data-target="#autoAnnouncementModal"
                            style="padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0 !important; border-radius: 12px; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';"
                            onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge badge-soft-{{ $typeColor }} font-size-11 font-weight-bold px-2 py-0.5 text-uppercase" style="border-radius: 6px;">
                                    <i class="mdi {{ $typeIcon }} mr-1"></i> {{ $ann->type }}
                                </span>
                                <span class="badge {{ $ann->is_read ? 'badge-soft-success' : 'badge-soft-warning' }} font-size-11 font-weight-bold px-2 py-0.5" style="border-radius: 6px;">
                                    <i class="mdi {{ $ann->is_read ? 'mdi-check-all' : 'mdi-alert-circle-outline' }} mr-0.5"></i> {{ $ann->is_read ? 'Read' : 'New' }}
                                </span>
                            </div>
                            <h6 class="text-dark font-weight-bold font-size-14 mb-1" style="line-height: 1.3;">{{ $ann->title }}</h6>
                            <p class="text-muted font-size-12 mb-2" style="line-height: 1.5;">
                                {{ Str::limit($ann->message, 85) }}
                            </p>
                            <div class="d-flex align-items-center text-muted font-size-11">
                                <i class="mdi mdi-clock-outline mr-1 text-primary"></i> Until {{ $ann->end_date->format('d M, Y') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 text-muted font-size-12">
                        <i class="mdi mdi-bullhorn-outline font-size-26 text-muted d-block mb-1 opacity-50"></i>
                        <span class="font-weight-medium">No active broadcasts right now</span>
                        <small class="d-block text-muted mt-0.5 font-size-11">All company notices and urgent alerts will appear here.</small>
                    </div>
                    @endif
                </div>

                <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between" style="border-color: var(--erp-border, #e8ecf1) !important;">
                    <small class="text-muted font-size-11 font-weight-medium">
                        📢 {{ $activeAnnouncements->count() }} active broadcasts
                    </small>
                    @if(Auth::user()?->isGlobalAdmin() || Auth::user()?->isBranchManager())
                    <a href="{{ route('hrms.announcements.index') }}" class="font-size-11 font-weight-bold text-primary">
                        Manage &rarr;
                    </a>
                    @else
                    <a href="javascript:void(0);" data-toggle="modal" data-target="#autoAnnouncementModal" class="font-size-11 font-weight-bold text-primary">
                        View All &rarr;
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
