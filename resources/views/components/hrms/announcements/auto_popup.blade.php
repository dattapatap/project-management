@php
    $currentUser = Auth::user();
    $allActiveAnnouncements = collect();
    $unreadCount = 0;
    if ($currentUser) {
        $announcementService = app(\App\Services\Hrms\AnnouncementService::class);
        $allActiveAnnouncements = $announcementService->getActiveAnnouncementsForUser($currentUser);
        $unreadCount = $allActiveAnnouncements->where('is_read', false)->count();
    }
@endphp

{{-- 📢 Persistent Right-Center Floating Announcement Trigger --}}
<div id="floatingAnnouncementTrigger" 
     class="floating-announcement-pill shadow-lg" 
     data-toggle="modal" 
     data-target="#autoAnnouncementModal" 
     title="Company Announcements & Broadcasts"
     data-placement="left"
     style="position: fixed; right: 0; top: 50%; transform: translateY(-50%); z-index: 1045; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: #ffffff; padding: 10px 14px 10px 12px; border-radius: 16px 0 0 16px; cursor: pointer; display: flex !important; align-items: center; justify-content: center; gap: 8px; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: -2px 4px 18px rgba(79, 70, 229, 0.45) !important;"
     onmouseover="this.style.transform='translateY(-50%) translateX(-4px)'; this.style.boxShadow='-4px 6px 22px rgba(79, 70, 229, 0.6)';"
     onmouseout="this.style.transform='translateY(-50%) translateX(0)'; this.style.boxShadow='-2px 4px 18px rgba(79, 70, 229, 0.45)';">
    <div class="position-relative d-flex align-items-center">
        <i class="mdi mdi-bullhorn font-size-20"></i>
        <span id="floatingAnnBadge" class="badge badge-danger rounded-circle position-absolute {{ $unreadCount > 0 ? '' : 'd-none' }}" 
              style="top: -8px; right: -12px; font-size: 10px; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff; font-weight: bold;">
            {{ $unreadCount }}
        </span>
    </div>
    <span class="font-size-12 font-weight-bold ml-1.5 d-none d-md-inline" style="letter-spacing: 0.3px;">Notice</span>
</div>

<!-- 📢 Announcements Modal Popup -->
<div class="modal fade" id="autoAnnouncementModal" tabindex="-1" role="dialog" aria-labelledby="autoAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 620px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; overflow: hidden; background: #ffffff;">
            @if($allActiveAnnouncements->count() > 0)
                @php
                    $firstAnn = $allActiveAnnouncements->first();
                    $headerGradient = match($firstAnn->type) {
                        'urgent' => 'linear-gradient(135deg, #dc2626 0%, #ef4444 100%)',
                        'event'  => 'linear-gradient(135deg, #7c3aed 0%, #6366f1 100%)',
                        default  => 'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)',
                    };
                    $headerIcon = match($firstAnn->type) {
                        'urgent' => 'mdi-alert-decagram',
                        'event'  => 'mdi-calendar-star',
                        default  => 'mdi-bullhorn-outline',
                    };
                @endphp

                {{-- Modal Header --}}
                <div id="announcementModalHeader" class="modal-header border-0 text-white p-4 position-relative" style="background: {{ $headerGradient }};">
                    <button type="button" class="close text-white position-absolute btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="top: 16px; right: 18px; opacity: 0.85; font-size: 24px; text-shadow: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center bg-white mr-3 flex-shrink-0" style="width: 48px; height: 48px; font-size: 24px;">
                            <i id="announcementHeaderIcon" class="mdi {{ $headerIcon }} text-primary"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span id="announcementTypeBadge" class="badge badge-light text-dark font-size-11 font-weight-bold text-uppercase px-2 py-0.5" style="border-radius: 6px;">
                                    {{ $firstAnn->type }}
                                </span>
                                <span class="text-white-50 font-size-12">
                                    <span id="announcementIndex">1</span> of {{ $allActiveAnnouncements->count() }}
                                </span>
                            </div>
                            <h4 class="modal-title text-white font-weight-bold font-size-18 mt-1 mb-0" id="autoAnnouncementModalLabel">
                                Company Announcement
                            </h4>
                        </div>
                    </div>
                </div>

                {{-- Modal Body: Announcements Carousel / Single --}}
                <div class="modal-body p-4" style="background: #ffffff; min-height: 220px;">
                    @foreach($allActiveAnnouncements as $index => $ann)
                    <div class="announcement-slide {{ $index === 0 ? '' : 'd-none' }}" 
                         id="ann-slide-{{ $ann->id }}" 
                         data-id="{{ $ann->id }}" 
                         data-index="{{ $index + 1 }}"
                         data-is-read="{{ $ann->is_read ? '1' : '0' }}"
                         data-type="{{ $ann->type }}"
                         data-bg="{{ match($ann->type) { 'urgent' => 'linear-gradient(135deg, #dc2626 0%, #ef4444 100%)', 'event' => 'linear-gradient(135deg, #7c3aed 0%, #6366f1 100%)', default => 'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)' } }}"
                         data-icon="{{ match($ann->type) { 'urgent' => 'mdi-alert-decagram', 'event' => 'mdi-calendar-star', default => 'mdi-bullhorn-outline' } }}">
                        
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap" style="gap: 8px;">
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <span class="ann-read-indicator badge {{ $ann->is_read ? 'badge-soft-success' : 'badge-soft-warning' }} font-size-11 font-weight-bold px-2 py-0.5" style="border-radius: 6px;">
                                    <i class="mdi {{ $ann->is_read ? 'mdi-check-all' : 'mdi-alert-circle-outline' }} mr-1"></i>
                                    {{ $ann->is_read ? 'Read' : 'New Notice' }}
                                </span>
                                <span class="text-muted font-size-12">
                                    &bull; Active until {{ $ann->end_date->format('d M, Y') }}
                                </span>
                            </div>
                            <span class="text-muted font-size-12">
                                <i class="mdi mdi-account-circle-outline mr-1"></i> By {{ $ann->creator?->name ?? 'Admin' }}
                            </span>
                        </div>

                        <h5 class="text-dark font-weight-bold font-size-16 mb-2">{{ $ann->title }}</h5>
                        
                        <div class="text-secondary font-size-13 mb-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; line-height: 1.6; white-space: pre-line;">
                            {{ $ann->message }}
                        </div>

                        @if($ann->target_dept || $ann->target_branch)
                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                            <span class="text-muted font-size-11 font-weight-semibold">Target Audience:</span>
                            @if($ann->target_dept)
                                <span class="badge badge-light border text-muted px-2 py-0.5 font-size-10">
                                    {{ $ann->department?->name ?? 'Dept #' . $ann->target_dept }}
                                </span>
                            @endif
                            @if($ann->target_branch)
                                <span class="badge badge-light border text-muted px-2 py-0.5 font-size-10">
                                    {{ $ann->branch?->name ?? 'Branch #' . $ann->target_branch }}
                                </span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer px-4 py-3 bg-light border-top d-flex align-items-center justify-content-between">
                    <div>
                        @if($allActiveAnnouncements->count() > 1)
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2.5 py-1 mr-1" id="btnPrevAnn" style="border-radius: 8px;" disabled>
                            <i class="mdi mdi-chevron-left"></i> Prev
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2.5 py-1" id="btnNextAnn" style="border-radius: 8px;">
                            Next <i class="mdi mdi-chevron-right"></i>
                        </button>
                        @endif
                    </div>

                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="button" class="btn btn-sm btn-light border px-3 py-1.5 font-weight-medium" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px;">
                            Close
                        </button>

                        <button type="button" class="btn btn-sm btn-primary px-3.5 py-1.5 font-weight-bold shadow-sm" id="btnMarkAsReadAnn" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px;">
                            <i class="mdi mdi-check mr-1"></i> <span id="markReadText">Ok, I've Read This</span>
                        </button>
                    </div>
                </div>
            @else
                {{-- Empty State when no announcements are active --}}
                <div class="modal-header border-0 text-white p-4 position-relative" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                    <button type="button" class="close text-white position-absolute btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="top: 16px; right: 18px; opacity: 0.85; font-size: 24px; text-shadow: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center bg-white mr-3 flex-shrink-0" style="width: 48px; height: 48px; font-size: 24px;">
                            <i class="mdi mdi-bullhorn-outline text-primary"></i>
                        </div>
                        <div>
                            <h4 class="modal-title text-white font-weight-bold font-size-18 mb-0">
                                Company Announcements
                            </h4>
                            <span class="text-white-50 font-size-12">Broadcasts & official notices</span>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-5 text-center" style="background: #ffffff;">
                    <div class="avatar-lg rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 text-primary" style="background: #eef2ff; width: 68px; height: 68px; font-size: 32px;">
                        <i class="mdi mdi-checkbox-marked-circle-outline"></i>
                    </div>
                    <h5 class="text-dark font-weight-bold font-size-16 mb-1">You're All Caught Up!</h5>
                    <p class="text-muted font-size-13 mb-0" style="max-width: 420px; margin: 0 auto; line-height: 1.6;">
                        There are no active company broadcasts or urgent alerts at this moment. New announcements will appear here automatically.
                    </p>
                </div>
                <div class="modal-footer px-4 py-3 bg-light border-top justify-content-end">
                    <button type="button" class="btn btn-sm btn-secondary px-4 py-1.5 font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 10px;">
                        Close
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var slides = document.querySelectorAll('.announcement-slide');
    var totalCount = slides.length;
    var unreadTotal = {{ $unreadCount }};
    var sessionKey = 'wms_announcement_dismissed_session';

    if (slides.length) {
        var currentIndex = 0;

        function updateView(idx) {
            slides.forEach(function(slide, i) {
                if (i === idx) {
                    slide.classList.remove('d-none');
                    var type = slide.getAttribute('data-type');
                    var bg = slide.getAttribute('data-bg');
                    var icon = slide.getAttribute('data-icon');
                    var isRead = slide.getAttribute('data-is-read') === '1';

                    var header = document.getElementById('announcementModalHeader');
                    var badge = document.getElementById('announcementTypeBadge');
                    var headerIconEl = document.getElementById('announcementHeaderIcon');
                    var indexEl = document.getElementById('announcementIndex');
                    var btnMarkRead = document.getElementById('btnMarkAsReadAnn');
                    var markReadText = document.getElementById('markReadText');

                    if (header) header.style.background = bg;
                    if (badge) badge.innerText = type;
                    if (headerIconEl) headerIconEl.className = 'mdi ' + icon + ' text-primary';
                    if (indexEl) indexEl.innerText = (idx + 1);

                    if (btnMarkRead && markReadText) {
                        if (isRead) {
                            btnMarkRead.className = 'btn btn-sm btn-soft-success px-3.5 py-1.5 font-weight-bold';
                            btnMarkRead.disabled = false;
                            markReadText.innerText = 'Ok, Got It';
                        } else {
                            btnMarkRead.className = 'btn btn-sm btn-primary px-3.5 py-1.5 font-weight-bold shadow-sm';
                            btnMarkRead.disabled = false;
                            markReadText.innerText = "Ok, I've Read This";
                        }
                    }
                } else {
                    slide.classList.add('d-none');
                }
            });

            var btnPrev = document.getElementById('btnPrevAnn');
            var btnNext = document.getElementById('btnNextAnn');
            if (btnPrev) btnPrev.disabled = (idx === 0);
            if (btnNext) btnNext.disabled = (idx === totalCount - 1);
        }

        var btnPrev = document.getElementById('btnPrevAnn');
        var btnNext = document.getElementById('btnNextAnn');

        if (btnPrev) {
            btnPrev.addEventListener('click', function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateView(currentIndex);
                }
            });
        }

        if (btnNext) {
            btnNext.addEventListener('click', function() {
                if (currentIndex < totalCount - 1) {
                    currentIndex++;
                    updateView(currentIndex);
                }
            });
        }

        // Mark as Read click action: immediately dismiss modal and persist read status in background
        var btnMarkRead = document.getElementById('btnMarkAsReadAnn');
        if (btnMarkRead) {
            btnMarkRead.addEventListener('click', function() {
                var activeSlide = slides[currentIndex];
                if (!activeSlide) {
                    if (window.jQuery) {
                        $('#autoAnnouncementModal').modal('hide');
                    }
                    return;
                }

                var annId = activeSlide.getAttribute('data-id');
                var isRead = activeSlide.getAttribute('data-is-read') === '1';

                // Save session dismissal immediately
                sessionStorage.setItem(sessionKey, 'dismissed');

                // If unread, mark in DOM and trigger background request
                if (!isRead) {
                    activeSlide.setAttribute('data-is-read', '1');
                    var indicator = activeSlide.querySelector('.ann-read-indicator');
                    if (indicator) {
                        indicator.className = 'ann-read-indicator badge badge-soft-success font-size-11 font-weight-bold px-2 py-0.5';
                        indicator.innerHTML = '<i class="mdi mdi-check-all mr-1"></i> Read';
                    }

                    unreadTotal = Math.max(0, unreadTotal - 1);
                    var floatingBadge = document.getElementById('floatingAnnBadge');
                    if (floatingBadge) {
                        if (unreadTotal > 0) {
                            floatingBadge.innerText = unreadTotal;
                            floatingBadge.classList.remove('d-none');
                        } else {
                            floatingBadge.classList.add('d-none');
                        }
                    }

                    // Background AJAX call
                    var readUrl = "{{ route('hrms.announcements.read', ['id' => ':id']) }}".replace(':id', annId);
                    if (window.jQuery) {
                        $.ajax({
                            url: readUrl,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json'
                        });
                    } else {
                        fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ _token: '{{ csrf_token() }}' })
                        }).catch(function(err) { console.warn(err); });
                    }
                }

                // Close modal explicitly
                if (window.jQuery && $('#autoAnnouncementModal').length) {
                    $('#autoAnnouncementModal').modal('hide');
                } else {
                    var m = document.getElementById('autoAnnouncementModal');
                    if (m) {
                        m.classList.remove('show');
                        m.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        var backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(function(b) { b.remove(); });
                    }
                }
            });
        }

        updateView(0);

        // Auto popup on page load if unread
        if (unreadTotal > 0 && !sessionStorage.getItem(sessionKey)) {
            setTimeout(function() {
                if (window.jQuery && $('#autoAnnouncementModal').length) {
                    if ($('#autoTodayBirthdayModal').hasClass('show')) {
                        $('#autoTodayBirthdayModal').on('hidden.bs.modal', function() {
                            $('#autoAnnouncementModal').modal('show');
                        });
                    } else {
                        $('#autoAnnouncementModal').modal('show');
                    }

                    $('#autoAnnouncementModal').on('hidden.bs.modal', function() {
                        sessionStorage.setItem(sessionKey, 'dismissed');
                    });
                }
            }, 1200);
        }
    }
});
</script>
