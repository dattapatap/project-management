@php
    $autoCelebrationService = app(\App\Services\Hrms\CelebrationService::class);
    $todaysBirthdaysList = $autoCelebrationService->getTodaysCelebrations()['birthdays'] ?? collect();
    $todayDateKey = date('Y-m-d');
@endphp

@if($todaysBirthdaysList->count() > 0)
<!-- 🎂 Auto Birthday Celebration Modal Popup -->
<div class="modal fade" id="autoTodayBirthdayModal" tabindex="-1" role="dialog" aria-labelledby="autoTodayBirthdayModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 560px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff;">
            {{-- Festive Modal Header Banner --}}
            <div class="modal-header border-0 text-center flex-column justify-content-center py-4 px-4 position-relative" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); color: #ffffff;">
                <button type="button" class="close text-white position-absolute btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="top: 16px; right: 18px; opacity: 0.85; font-size: 26px; text-shadow: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="display-4 mb-2 animate-bounce" style="font-size: 48px; line-height: 1;">🎂 🎈 🥳</div>
                <h4 class="modal-title text-white font-weight-bold font-size-22 mb-1" id="autoTodayBirthdayModalLabel">
                    Happy Birthday!
                </h4>
                <p class="text-white-50 font-size-13 mb-0 font-weight-medium">
                    Let's celebrate our team members celebrating their special day today!
                </p>
            </div>

            {{-- Modal Body: Birthday Stars List --}}
            <div class="modal-body p-4" style="background: #fdfdfd; max-height: 440px; overflow-y: auto;">
                <div class="d-flex flex-column" style="gap: 14px;">
                    @foreach($todaysBirthdaysList as $bdayEmp)
                    @php
                        $userAcc = $bdayEmp->userAccount;
                        $deptName = $userAcc?->departments?->dept?->name ?? 'Team Member';
                        $branchName = $userAcc?->branch?->branch?->name ?? 'Head Office';
                        $empEmail = $userAcc?->email ?? $bdayEmp->alt_email;
                        $rawPhone = $userAcc?->mobile ?: $bdayEmp->alt_number;
                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
                        if (strlen($cleanPhone) === 10) {
                            $cleanPhone = '91' . $cleanPhone;
                        }
                        $whatsappMsg = urlencode("Happy Birthday {$bdayEmp->name}! 🎉🎂 Wishing you joy, good health, and immense success ahead!");
                        $whatsappUrl = $cleanPhone 
                            ? "https://api.whatsapp.com/send?phone={$cleanPhone}&text={$whatsappMsg}"
                            : "https://api.whatsapp.com/send?text={$whatsappMsg}";

                        $emailSubject = urlencode("Happy Birthday {$bdayEmp->name}! 🎉🎂");
                        $emailBody = urlencode("Dear {$bdayEmp->name},\n\nWishing you a fantastic Happy Birthday! 🎉🎂\n\nMay this year bring you great happiness, fulfillment, and tremendous success in all your endeavors.\n\nWarm regards,\n" . (Auth::user()?->name ?? 'Your Team at Digitalnock'));
                        $emailUrl = $empEmail ? "mailto:{$empEmail}?subject={$emailSubject}&body={$emailBody}" : null;
                    @endphp
                    <div class="p-3 border d-flex align-items-center justify-content-between flex-wrap" style="background: linear-gradient(135deg, #fff5f5 0%, #fff8f5 100%); border-color: #ffd6d6 !important; border-radius: 16px; gap: 12px;">
                        <div class="d-flex align-items-center" style="min-width: 0; flex: 1 1 240px;">
                            {{-- Avatar with generous right margin --}}
                            <div class="position-relative flex-shrink-0" style="margin-right: 18px;">
                                @if($userAcc?->profile)
                                    <img src="{{ asset('storage/' . $userAcc->profile) }}" alt="{{ $bdayEmp->name }}" class="rounded-circle border border-danger shadow-sm" style="width: 54px; height: 54px; object-fit: cover;">
                                @else
                                    <img src="{{ Avatar::create($bdayEmp->name)->toBase64() }}" alt="{{ $bdayEmp->name }}" class="rounded-circle border border-danger shadow-sm" style="width: 54px; height: 54px;">
                                @endif
                                <span class="position-absolute" style="bottom: -4px; right: -4px; font-size: 18px; line-height: 1;">🎉</span>
                            </div>
                            <div style="min-width: 0;">
                                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                    <h5 class="mb-0 text-dark font-weight-bold font-size-15 text-truncate">{{ $bdayEmp->name }}</h5>
                                    <span class="badge badge-danger font-size-10 font-weight-bold px-2 py-0.5" style="border-radius: 6px;">Birthday Today!</span>
                                </div>
                                <span class="text-muted font-size-12 d-block mt-1 text-truncate">
                                    {{ $bdayEmp->designation ?? 'Team Member' }} &bull; <strong class="text-dark">{{ $deptName }}</strong> ({{ $branchName }})
                                </span>
                            </div>
                        </div>

                        {{-- Modern Icon Action Buttons (WhatsApp & Email) --}}
                        <div class="d-flex align-items-center flex-shrink-0" style="gap: 10px;">
                            {{-- WhatsApp Button --}}
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" 
                               class="btn d-inline-flex align-items-center justify-content-center text-white shadow-sm" 
                               title="Wish {{ $bdayEmp->name }} on WhatsApp" 
                               data-toggle="tooltip" data-placement="top"
                               style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); font-size: 22px; padding: 0; border: none; transition: transform 0.2s, box-shadow 0.2s;"
                               onmouseover="this.style.transform='scale(1.12)'; this.style.boxShadow='0 4px 12px rgba(37,211,102,0.4)';" 
                               onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                <i class="mdi mdi-whatsapp"></i>
                            </a>

                            {{-- Email Button --}}
                            @if($emailUrl)
                            <a href="{{ $emailUrl }}" 
                               class="btn d-inline-flex align-items-center justify-content-center text-white shadow-sm" 
                               title="Send Birthday Email to {{ $bdayEmp->name }}" 
                               data-toggle="tooltip" data-placement="top"
                               style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); font-size: 20px; padding: 0; border: none; transition: transform 0.2s, box-shadow 0.2s;"
                               onmouseover="this.style.transform='scale(1.12)'; this.style.boxShadow='0 4px 12px rgba(255,65,108,0.4)';" 
                               onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                <i class="mdi mdi-email-outline"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer px-4 py-3 bg-light border-top justify-content-between">
                <span class="text-muted font-size-12 font-weight-medium">
                    🎂 {{ $todaysBirthdaysList->count() }} {{ $todaysBirthdaysList->count() > 1 ? 'colleagues celebrating today' : 'colleague celebrating today' }}
                </span>
                <button type="button" class="btn btn-sm btn-secondary px-4 py-1.5 font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var todayKey = 'wms_bday_popup_{{ $todayDateKey }}';
    if (window.jQuery) {
        $('[data-toggle="tooltip"]').tooltip({ boundary: 'window' });
    }
    // Show automatically once per session on birthday date
    if (!sessionStorage.getItem(todayKey)) {
        setTimeout(function() {
            if (window.jQuery && $('#autoTodayBirthdayModal').length) {
                $('#autoTodayBirthdayModal').modal('show');
            }
        }, 800);
        sessionStorage.setItem(todayKey, 'shown');
    }
});
</script>
@endif
