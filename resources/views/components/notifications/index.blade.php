@extends('layouts.app')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif !important;
    }

    /* ─── Page Shell ─────────────────────────────── */
    .notif-page-wrap {
        padding: 24px 0 40px;
        min-height: 100vh;
    }

    /* ─── Inbox Panel ─────────────────────────────── */
    .inbox-panel {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        border: 1px solid #eef0f7;
    }

    /* ─── Inbox Top Bar ───────────────────────────── */
    .inbox-topbar {
        padding: 22px 28px;
        border-bottom: 1px solid #f0f2f8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }

    .inbox-topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .inbox-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35);
    }

    .inbox-icon-wrap i {
        color: #fff;
        font-size: 20px;
    }

    .inbox-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1a1d2e;
        margin: 0;
    }

    .inbox-subtitle {
        font-size: 0.78rem;
        color: #9aa0b4;
        margin: 0;
    }

    /* ─── Mark All Btn ────────────────────────────── */
    .btn-markall {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 18px;
        font-size: 0.83rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(102, 126, 234, 0.3);
    }

    .btn-markall:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.45);
    }

    .btn-markall i {
        font-size: 16px;
    }

    /* ─── Notification Item ───────────────────────── */
    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 28px;
        border-bottom: 1px solid #f5f6fc;
        text-decoration: none !important;
        transition: background 0.18s ease, transform 0.18s ease;
        position: relative;
        cursor: pointer;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item.is-unread {
        background: #fafbff;
    }

    .notif-item.is-read {
        background: #fff;
    }

    .notif-item:hover {
        background: #f4f5fd;
    }

    /* Left accent line for unread */
    .notif-item.is-unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #667eea, #764ba2);
        border-radius: 0 3px 3px 0;
    }

    /* Avatar */
    .notif-avatar {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.09);
        border: 2px solid #eef0f7;
    }

    .notif-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Body */
    .notif-body {
        flex: 1;
        min-width: 0;
    }

    .notif-row-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 4px;
    }

    .notif-headline {
        font-weight: 600;
        font-size: 0.92rem;
        color: #1a1d2e;
        line-height: 1.4;
        margin: 0;
    }

    .notif-item.is-read .notif-headline {
        font-weight: 500;
        color: #474d6d;
    }

    .notif-meta {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notif-time {
        font-size: 0.76rem;
        color: #b0b7cc;
        white-space: nowrap;
        font-weight: 500;
    }

    .unread-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        flex-shrink: 0;
        box-shadow: 0 0 6px rgba(102, 126, 234, 0.5);
    }

    .notif-desc {
        font-size: 0.85rem;
        color: #7b82a0;
        line-height: 1.5;
        margin: 0 0 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Category badge */
    .notif-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .badge-task {
        background: #e8f0fe;
        color: #4a6cf7;
    }

    .badge-project {
        background: #e6f4ea;
        color: #2e7d32;
    }

    .badge-nudge {
        background: #fff3e0;
        color: #e65100;
    }

    .badge-default {
        background: #f3e5f5;
        color: #6a1b9a;
    }

    /* ─── Empty State ─────────────────────────────── */
    .empty-notif {
        padding: 70px 30px;
        text-align: center;
    }

    .empty-notif-icon {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f0f2ff 0%, #e8eafc 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 22px;
    }

    .empty-notif-icon i {
        font-size: 38px;
        color: #667eea;
    }

    .empty-notif h5 {
        font-weight: 700;
        color: #1a1d2e;
        font-size: 1.05rem;
        margin-bottom: 8px;
    }

    .empty-notif p {
        color: #9aa0b4;
        font-size: 0.87rem;
        margin: 0;
    }

    /* ─── Pagination ──────────────────────────────── */
    .notif-pagination {
        padding: 18px 28px;
        border-top: 1px solid #f0f2f8;
        background: #fafbff;
        display: flex;
        justify-content: center;
    }

    .notif-pagination .pagination {
        margin: 0;
        gap: 4px;
    }

    .notif-pagination .page-link {
        border: none;
        border-radius: 10px !important;
        padding: 6px 14px;
        font-size: 0.84rem;
        font-weight: 600;
        color: #667eea;
        background: #eef0ff;
        transition: all 0.2s ease;
    }

    .notif-pagination .page-link:hover {
        background: #667eea;
        color: #fff;
    }

    .notif-pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
    }

    .notif-pagination .page-item.disabled .page-link {
        background: #f5f6fc;
        color: #c5c9de;
    }

    /* ─── Stats bar ───────────────────────────────── */
    .notif-stats-bar {
        display: flex;
        align-items: center;
        gap: 24px;
        padding: 12px 28px;
        background: #f9faff;
        border-bottom: 1px solid #f0f2f8;
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #7b82a0;
    }

    .stat-pill span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        border-radius: 8px;
        font-size: 0.75rem;
        padding: 0 6px;
    }

    .pill-total span {
        background: #eef0ff;
        color: #667eea;
    }

    .pill-unread span {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid notif-page-wrap">
    {{-- Breadcrumb --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18" style="font-weight:700; color:#1a1d2e;">Notifications</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME') }}</a></li>
                    <li class="breadcrumb-item active">Notifications</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-md-12">

            <div class="inbox-panel">

                {{-- ── Top Bar ── --}}
                <div class="inbox-topbar">
                    <div class="inbox-topbar-left">
                        <div class="inbox-icon-wrap">
                            <i class="bx bx-bell"></i>
                        </div>
                        <div>
                            <p class="inbox-title">Notifications</p>
                            <p class="inbox-subtitle">Stay on top of everything happening in your workspace</p>
                        </div>
                    </div>

                    @if($notification->where('read_at', null)->count() > 0)
                    <div class="marksall">
                        <a href="#" class="btn-markall mark-all">
                            <i class="bx bx-check-double"></i> Mark all as read
                        </a>
                    </div>
                    @endif
                </div>

                {{-- ── Stats Bar ── --}}
                <div class="notif-stats-bar">
                    <div class="stat-pill pill-total">
                        <i class="bx bx-list-ul"></i>
                        <span>{{ $notification->total() }}</span>
                        Total
                    </div>
                    <div class="stat-pill pill-unread">
                        <i class="bx bx-radio-circle-marked"></i>
                        <span>{{ $notification->where('read_at', null)->count() }}</span>
                        Unread
                    </div>
                </div>

                {{-- ── Notification Items ── --}}
                @forelse($notification as $items)
                @php
                $category = strtolower($items->data['category'] ?? '');
                $badgeClass = 'badge-default';
                $badgeIcon = 'bx bx-bell';
                if (str_contains($category, 'task')) { $badgeClass = 'badge-task'; $badgeIcon = 'bx bx-task'; }
                elseif (str_contains($category, 'project')) { $badgeClass = 'badge-project'; $badgeIcon = 'bx bx-briefcase'; }
                elseif (str_contains($category, 'nudge')) { $badgeClass = 'badge-nudge'; $badgeIcon = 'bx bx-bell-ring'; }
                @endphp

                <a href="{{ $items->data['link'] }}"
                    class="notif-item {{ $items->unread() ? 'is-unread mark-as-read' : 'is-read' }}"
                    data-id="{{ $items->id }}">

                    {{-- Avatar --}}
                    <div class="notif-avatar">
                        <img src="{{ Avatar::create($items->data['category'])->toBase64() }}" alt="">
                    </div>

                    {{-- Body --}}
                    <div class="notif-body">
                        <div class="notif-row-top">
                            <h6 class="notif-headline">{!! htmlspecialchars_decode($items->data['header']) !!}</h6>
                            <div class="notif-meta">
                                <span class="notif-time">
                                    <i class="bx bx-time-five" style="font-size:12px; vertical-align:middle;"></i>
                                    {{ Carbon\Carbon::parse($items->created_at)->diffForHumans() }}
                                </span>
                                @if($items->unread())
                                <div class="unread-dot"></div>
                                @endif
                            </div>
                        </div>

                        <p class="notif-desc">{!! htmlspecialchars_decode($items->data['data']) !!}</p>

                        <span class="notif-badge {{ $badgeClass }}">
                            <i class="{{ $badgeIcon }}" style="font-size:11px;"></i>
                            {{ ucfirst($items->data['category']) }}
                        </span>
                    </div>

                </a>
                @empty
                <div class="empty-notif">
                    <div class="empty-notif-icon">
                        <i class="bx bx-bell-off"></i>
                    </div>
                    <h5>All caught up!</h5>
                    <p>You have no notifications at the moment. Check back later.</p>
                </div>
                @endforelse

                {{-- ── Pagination ── --}}
                @if($notification->hasPages())
                <div class="notif-pagination">
                    {{ $notification->links('pagination::bootstrap-4') }}
                </div>
                @endif

            </div>{{-- /inbox-panel --}}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        // Mark single notification as read on click
        $('.mark-as-read').on('click', function() {
            const id = $(this).data('id');
            const row = $(this);
            $.post("{{ route('mark-as-read-notification') }}", {
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                function() {
                    row.removeClass('is-unread mark-as-read').addClass('is-read');
                    row.find('.unread-dot').remove();
                    row.css('background', '#fff');
                });
        });

        // Mark ALL as read
        $('.mark-all').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            btn.html('<i class="bx bx-loader bx-spin"></i> Processing…');
            $.post("{{ route('mark-all-as-read-notification') }}", {
                    id: null,
                    _token: '{{ csrf_token() }}'
                },
                function() {
                    $('.notif-item.is-unread').removeClass('is-unread mark-as-read').addClass('is-read');
                    $('.unread-dot').remove();
                    $('.marksall').fadeOut(300);
                }).fail(function() {
                btn.html('<i class="bx bx-check-double"></i> Mark all as read');
            });
        });

    });
</script>
@endsection
