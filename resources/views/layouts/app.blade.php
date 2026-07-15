<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@php
$showGlobalTimer = false;
$hasSubmittedClosingToday = false;
if (Auth::check()) {
$user = Auth::user();
$user->loadMissing('departments');
if (!$user->hasRole('Admin')) {
$userPerformance = new \App\Services\UserPerformanceService();
$deptType = $userPerformance->departmentType($user);
if (in_array($deptType, ['od', 'csd'])) {
$showGlobalTimer = true;
$hasSubmittedClosingToday = \App\Models\DayClosing::where('user_id', $user->id)
->where('closing_date', now()->format('Y-m-d'))
->exists();
}
}
}
@endphp

<head>
    <meta charset="utf-8">
    <!-- Meta Tags -->
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />

    <!-- Favicon and Touch Icons -->
    <link href="{{ asset('assets/images/icon.png') }}" rel="shortcut icon" type="image/png">
    <link href="{{ asset('assets/images/icon.png') }}" rel="apple-touch-icon">
    <link href="{{ asset('assets/images/icon.png') }}" rel="apple-touch-icon" sizes="72x72">
    <link href="{{ asset('assets/images/icon.png') }}" rel="apple-touch-icon" sizes="114x114">
    <link href="{{ asset('assets/images/icon.png') }}" rel="apple-touch-icon" sizes="144x144">

    <title>ERP : {{ config('app.name', 'Digitalnock It Solutions') }}</title>

    <meta property="og:type" content="ERP Application">
    <meta property="og:image" content="{{ asset('assets/images/icon.png') }}" property="og:image" />


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app_url" content="{{ url('/') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="shortcut icon" href="{{ asset('assets/images/icon.png') }}">

    <link href="{{ asset('assets/libs/slick-slider/slick/slick.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/slick-slider/slick/slick-theme.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/jqvmap/jqvmap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css">
    <!-- alertifyjs Css -->

    <link href="{{ asset('assets/libs/alertifyjs/build/css/alertify.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/magnific-popup/magnific-popup.css') }}" rel="stylesheet" type="text/css">
    <!-- alertifyjs default themes  Css -->
    <link href="{{ asset('assets/libs/alertifyjs/build/css/themes/default.min.css') }}" rel="stylesheet"
        type="text/css">
    <link href="{{ asset('assets/libs/select2/select2.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/reports.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/erp-theme.css') }}?v={{ filemtime(public_path('css/erp-theme.css')) }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/erp-components.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sales-dashboard.css') }}">

    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">


    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    @vite(['resources/js/app.js', 'resources/sass/app.scss'])

    @yield('styles')

</head>

@php
$bodyDept = optional($user->departments ?? null)->department ?? ($user->hasBranchWideAccess() ? 'admin' : null);
$bodyModule = request()->is('csd*') ? 'csd-module' : (request()->is('client*') || request()->is('clients*') || request()->is('mysts*') || request()->is('reports/dsr*') ? 'nsd-module' : (request()->is('projects*') ? 'od-module' : ''));
@endphp

<body data-sidebar="dark" data-dept="{{ $bodyDept }}" class="{{ $bodyModule }}">
    <!-- Begin page -->
    <div id="cover-spin"></div>

    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">

                <div class="d-flex">
                    <div class="navbar-brand-box">
                        <a href="{{ url('/') }}" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/icon.png') }}" alt="" height="30">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo-light1.png') }}" alt="" height="40">
                            </span>
                        </a>
                    </div>
                </div>

                <div class="d-flex">

                    {{-- Start Global Timer Widget --}}
                    @if($showGlobalTimer)
                    <style>
                        @keyframes rec-blink {
                            0% {
                                opacity: 1;
                            }

                            50% {
                                opacity: 0.3;
                            }

                            100% {
                                opacity: 1;
                            }
                        }

                        .timer-rec-dot.active {
                            background-color: #ef4444 !important;
                            animation: rec-blink 1.5s ease-in-out infinite;
                        }

                        .global-timer-glass {
                            background: rgba(255, 255, 255, 0.9);
                            border: 1px solid rgba(85, 110, 230, 0.2);
                            border-radius: 30px;
                            transition: all 0.3s ease;
                        }

                        .global-timer-glass:hover {
                            border-color: rgba(85, 110, 230, 0.5);
                            box-shadow: 0 4px 12px rgba(85, 110, 230, 0.08);
                        }
                    </style>
                    <div class="d-inline-flex align-items-center mr-3" style="align-self: center;">
                        <div id="global-timer-widget" class="global-timer-glass d-flex align-items-center px-3 py-1.5 shadow-sm" style="height: 36px; gap: 8px; color: #495057; font-weight: 600; font-size: 12.5px;">
                            <span class="timer-rec-dot" style="width: 8px; height: 8px; border-radius: 50%; background-color: #cbd5e1; display: inline-block;"></span>
                            <span id="global-timer-status" class="text-muted text-uppercase" style="font-size: 9px; font-weight: 800; letter-spacing: 0.5px; max-width: 150px; overflow: hidden; text-truncate: ellipsis; white-space: nowrap;">Shift Off</span>
                            <span id="global-timer-display" class="font-weight-bold text-dark" style="font-family: monospace; font-size: 13.5px; letter-spacing: 0.5px;">00:00:00</span>
                            <button type="button" class="btn btn-sm btn-success rounded-circle p-0 d-flex align-items-center justify-content-center" id="btn-global-shift" title="Start Shift" style="width: 22px; height: 22px; min-width: 22px; border: none; @if($hasSubmittedClosingToday) display: none !important; @endif">
                                <i class="mdi mdi-play font-size-12 text-white"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning rounded-circle p-0 d-flex align-items-center justify-content-center ml-2" id="btn-global-break" title="Take Break" style="width: 22px; height: 22px; min-width: 22px; border: none; display: none; @if($hasSubmittedClosingToday) display: none !important; @endif">
                                <i class="mdi mdi-coffee font-size-12 text-white"></i>
                            </button>
                        </div>
                    </div>
                    @endif
                    {{-- End Global Timer Widget --}}

                    {{-- Start Add Task Shortcut Button --}}
                    @auth
                    @if($user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager']) || ($user->hasRole('Team-Leader') && optional($user->departments)->department == 2))
                    <div class="d-inline-flex align-items-center mr-3" style="align-self: center;">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold btn_header_add_task" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none;">
                            <i class="mdi mdi-plus-circle font-size-14 text-white"></i> Add Task
                        </button>
                    </div>
                    @endif
                    @endauth
                    {{-- End Add Task Shortcut Button --}}


                    {{-- Start Notifications --}}
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect"
                            id="page-header-notifications-dropdown" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="mdi mdi-bell-outline"></i>
                            @if ($unreadNotf > 0)
                            <span class="badge badge-danger badge-pill">
                                @if ($unreadNotf >= 99)
                                {{ '+99' }}
                                @else
                                {{ $unreadNotf }}
                                @endif
                            </span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                            aria-labelledby="page-header-notifications-dropdown">
                            <div class="p-3 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 font-weight-medium text-uppercase"> Notifications </h6>
                                    </div>
                                    <div class="col-auto">
                                        @if ($unreadNotf > 0)
                                        <span class="badge badge-pill badge-danger">New {{ $unreadNotf }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div data-simplebar="" style="max-height: 230px;" class="dy-notif">
                                @forelse ($notifications as $items)
                                <div
                                    class="notifications @if ($items->unread()) unread @else  read @endif">
                                    <a href="{{ $items->data['link'] }}"
                                        class="text-reset notification-item  @if ($items->unread()) mark-as-read @endif"
                                        data-id="{{ $items->id }}">
                                        <div class="media">
                                            <img src="{{ Avatar::create($items->data['category'])->toBase64() }}"
                                                class="mr-3 rounded-circle avatar-xs" alt="user-pic">
                                            <div class="media-body">
                                                <h6 class="mt-0 mb-1" style="color: #090909;">
                                                    {!! htmlspecialchars_decode($items->data['header']) !!}</h6>
                                                <div class="font-size-12 text-muted">
                                                    <p class="mb-1">{!! htmlspecialchars_decode($items->data['data']) !!}</p>
                                                    <p class="mb-0"><i class="mdi mdi-clock-outline"></i>
                                                        {{ Carbon\Carbon::parse($items->created_at)->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @empty
                                <a href="#" class="text-reset notification-item ">
                                    <div class="d-flex" style="justify-content: center;">
                                        <p class="mb-0 text-danger text-center"> No new notifications </p>
                                    </div>
                                </a>
                                @endforelse
                            </div>
                            @if ($notifications->count() > 0)
                            <div class="p-2 border-top text-center">
                                <a class="btn btn-sm btn-link font-size-14 text-center"
                                    href="{{ route('notifications') }}">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> View More..
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    {{-- End Notifications --}}

                    {{-- Start User Dropdoun --}}
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @if ($user->profile)
                            <img class="rounded-circle header-profile-user"
                                src="{{ asset('storage/' . $user->profile) }}" alt="Header Avatar">
                            @else
                            <img class="rounded-circle header-profile-user"
                                src="{{ Avatar::create($user->name)->toBase64() }}" alt="{{ $user->name }}">
                            @endif
                            <span class="d-none d-sm-inline-block ml-1">{{ $user->name }}</span>
                            <i class="mdi mdi-chevron-down d-none d-sm-inline-block"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right">
                            <!-- item-->
                            <a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="mdi mdi-face-profile font-size-16 align-middle mr-1"></i>
                                Profile
                            </a>
                            <a class="dropdown-item" href="{{ route('changepassword') }}">
                                <i class="mdi mdi-settings font-size-16 align-middle mr-1"></i>
                                Change Password
                            </a>


                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                <i class="mdi mdi-logout font-size-16 align-middle mr-1"></i> Logout
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                    {{-- End User Dropdown --}}

                </div>

            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">
            <div data-simplebar="" class="h-100">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    @include('layouts.partials.sidebar')
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->



        <!-- ============================================================== -->
        <!-- Start main-content -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                {{-- Start Global Timer Sticky Alert Banners --}}
                @if($showGlobalTimer)
                <div id="global-timer-banners" class="d-none" style="margin-bottom: 20px;">
                    {{-- Shift Not Started Banner --}}
                    <div id="banner-shift-not-started" class="alert alert-soft-danger p-3 shadow-sm d-none" style="border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.3); background-color: rgba(239, 68, 68, 0.05);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="mdi mdi-alert-circle font-size-20"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 font-weight-bold text-danger">Shift Attendance Not Started!</h6>
                                    <p class="mb-0 text-muted font-size-12">You have not clocked in for today. Please start your global timer shift to track tasks and work progress.</p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm py-1.5" onclick="$('#btn-global-shift').click()" style="white-space: nowrap;">
                                <i class="mdi mdi-play mr-1"></i> Start Shift
                            </button>
                        </div>
                    </div>

                    {{-- Break Time / Paused Banner --}}
                    <div id="banner-shift-paused" class="alert alert-soft-warning p-3 shadow-sm d-none" style="border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.3); background-color: rgba(245, 158, 11, 0.05);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="mdi mdi-coffee font-size-20"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 font-weight-bold text-warning">You are currently on Break!</h6>
                                    <p class="mb-0 text-muted font-size-12">Your workday attendance and task logging are paused. Don't forget to resume when you return to work.</p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm text-white rounded-pill px-4 shadow-sm py-1.5" onclick="$('#btn-global-break').click()" style="white-space: nowrap;">
                                <i class="mdi mdi-play mr-1"></i> Resume Shift
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                {{-- End Global Timer Sticky Alert Banners --}}

                @yield('content')
            </div>

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->


    <div class="rightbar-overlay"></div>


    <script src="{{ asset('assets/libs/alertifyjs/build/alertify.min.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Modern Global Glassmorphic Toast Script -->
    <script>
        function showModernToast(type, message) {
            let container = document.getElementById('modern-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'modern-toast-container';
                document.body.appendChild(container);
            }

            // Determine modern line icon
            let iconClass = 'mdi mdi-information-outline';
            if (type === 'success') iconClass = 'mdi mdi-checkbox-marked-circle-outline';
            if (type === 'error') iconClass = 'mdi mdi-alert-circle-outline';
            if (type === 'warning') iconClass = 'mdi mdi-alert-outline';

            // Create toast card
            let toast = document.createElement('div');
            toast.className = `modern-toast ${type}`;
            toast.innerHTML = `
                <div class="modern-toast-icon">
                    <i class="${iconClass} font-size-18"></i>
                </div>
                <div class="modern-toast-body">
                    <div class="modern-toast-title">${type}</div>
                    <div class="modern-toast-message">${message}</div>
                </div>
                <div class="modern-toast-progress">
                    <div class="modern-toast-progress-bar"></div>
                </div>
            `;

            container.appendChild(toast);

            // Animate entry
            setTimeout(() => {
                toast.classList.add('show');
            }, 50);

            // Animate progress shrink
            let pBar = toast.querySelector('.modern-toast-progress-bar');
            setTimeout(() => {
                pBar.style.width = '0%';
            }, 100);

            // Auto dismiss
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.remove();
                }, 350);
            }, 4000);
        }

        // Hook alertify global object to show modern premium toasts instantly
        (function() {
            if (window.alertify) {
                const nativeAlertify = {
                    ...window.alertify
                };
                window.alertify = {
                    ...nativeAlertify,
                    success: function(msg) {
                        showModernToast('success', msg);
                    },
                    error: function(msg) {
                        showModernToast('error', msg);
                    },
                    warning: function(msg) {
                        showModernToast('warning', msg);
                    },
                    message: function(msg) {
                        showModernToast('info', msg);
                    }
                };
            }
            // Also register as window.toastr for standard calls
            window.toastr = {
                success: function(msg) {
                    showModernToast('success', msg);
                },
                error: function(msg) {
                    showModernToast('error', msg);
                },
                warning: function(msg) {
                    showModernToast('warning', msg);
                },
                info: function(msg) {
                    showModernToast('info', msg);
                }
            };
        })();
    </script>

    <script type="module">
        window.Echo.private('post_like.{{ $user->id }}')
            .notification((notification) => {
                let notif = notification.notifications;
                swal(notif.header)
                    .then((value) => {
                        window.location.href = notif.link;
                    });
            })
    </script>
    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/slick-slider/slick/slick.min.js') }}"></script>
    <!-- Jq vector map -->
    <script src="{{ asset('assets/libs/magnific-popup/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <script src="{{ asset('assets/libs/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/libs/tinymce/js/tinymce.min.js') }}"></script>


    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>

    <script src="{{ asset('js/index.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('js/sidebar.js') }}"></script>

    @if (\Session::has('error'))
    <script>
        alertify.error("{!! \Session::get('error') !!}");
    </script>
    @endif
    @if (\Session::has('success'))
    <script>
        alertify.success("{!! \Session::get('success') !!}");
    </script>
    @endif


    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script>
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57))
                return false;
            return true;
        }
        $(document).ready(function() {
            $('.select2').select2();
            $('.btnmdlclose').click(function() {
                $('.modal').modal('hide');
            })

            setInterval(() => {
                date = new Date;
                year = date.getFullYear();
                month = date.getMonth();
                months = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
                    'Nov', 'Dec');
                d = date.getDate();
                h = date.getHours();
                if (h < 10) {
                    h = "0" + h;
                }
                m = date.getMinutes();
                if (m < 10) {
                    m = "0" + m;
                }
                s = date.getSeconds();
                if (s < 10) {
                    s = "0" + s;
                }
                result = months[month] + ' ' + d + ' ' + year + ' ' + h + ':' + m + ':' + s;
                $('#pane-timer').text(result);
            }, 1000);

            // --- Global Shift Timer Javascript Integration ---
            @if($showGlobalTimer)
                (function() {
                    let timerInterval = null;
                    let activeStartMs = null;
                    let baseAccumulatedSeconds = 0;
                    let shiftRunning = false;
                    let shiftHasTodayEntry = false;
                    let shiftIsPaused = false;

                    function formatSeconds(totalSeconds) {
                        let hrs = Math.floor(totalSeconds / 3600);
                        let mins = Math.floor((totalSeconds % 3600) / 60);
                        let secs = totalSeconds % 60;
                        return [
                            hrs.toString().padStart(2, '0'),
                            mins.toString().padStart(2, '0'),
                            secs.toString().padStart(2, '0')
                        ].join(':');
                    }

                    function updateWidgetStatus() {
                        $.ajax({
                            url: "{{ route('global-timer.status') }}",
                            method: 'GET',
                            dataType: 'json',
                            success: function(res) {
                                if (!res.success) return;

                                baseAccumulatedSeconds = Math.round(parseFloat(res.accumulated_hours || 0) * 3600);
                                shiftRunning = res.is_running;
                                shiftHasTodayEntry = res.has_today_entry;
                                shiftIsPaused = res.is_paused;

                                // Clean interval
                                if (timerInterval) clearInterval(timerInterval);

                                if (res.has_submitted_closing) {
                                    $('.timer-rec-dot').removeClass('active').css('background-color', '#cbd5e1');
                                    $('#global-timer-status').text('Day Completed').addClass('text-muted').removeClass('text-success text-primary');
                                    $('#global-timer-display').text(formatSeconds(baseAccumulatedSeconds));
                                    $('#btn-global-shift').hide();
                                    $('#btn-global-break').hide();

                                    $('#global-timer-banners').addClass('d-none');
                                    $('#banner-shift-not-started').addClass('d-none');
                                    $('#banner-shift-paused').addClass('d-none');
                                    return;
                                }

                                if (res.is_running) {
                                    // Hide alert banners
                                    $('#global-timer-banners').addClass('d-none');
                                    $('#banner-shift-not-started').addClass('d-none');
                                    $('#banner-shift-paused').addClass('d-none');

                                    $('.timer-rec-dot').addClass('active');
                                    if (res.active_task_title) {
                                        $('#global-timer-status').text('Working: ' + res.active_task_title).addClass('text-primary').removeClass('text-muted');
                                    } else {
                                        $('#global-timer-status').text('Shift Running').addClass('text-success').removeClass('text-muted');
                                    }

                                    // Parse started time
                                    let datePart = res.log_date; // Y-m-d
                                    let timePart = res.starttime; // H:i:s
                                    // Parse safely across browsers
                                    let startDateTimeStr = datePart + 'T' + timePart;
                                    let startedDateObj = new Date(startDateTimeStr);

                                    activeStartMs = startedDateObj.getTime();

                                    // Update buttons display
                                    $('#btn-global-shift').show().removeClass('btn-success').addClass('btn-danger').attr('title', 'End Shift').html('<i class="mdi mdi-stop font-size-12 text-white"></i>');
                                    $('#btn-global-break').show().removeClass('btn-success').addClass('btn-warning').attr('title', 'Take Break').html('<i class="mdi mdi-coffee font-size-12 text-white"></i>');

                                    timerInterval = setInterval(function() {
                                        let nowMs = new Date().getTime();
                                        let elapsedSeconds = Math.max(0, Math.floor((nowMs - activeStartMs) / 1000));

                                        // 9:00 PM cap logic on client-side too
                                        let capDateTimeStr = datePart + 'T21:00:00';
                                        let capMs = new Date(capDateTimeStr).getTime();
                                        if (nowMs > capMs) {
                                            elapsedSeconds = Math.max(0, Math.floor((capMs - activeStartMs) / 1000));
                                        }

                                        let totalSeconds = baseAccumulatedSeconds + elapsedSeconds;
                                        $('#global-timer-display').text(formatSeconds(totalSeconds));
                                    }, 1000);
                                } else {
                                    $('.timer-rec-dot').removeClass('active');
                                    $('#global-timer-status').text(baseAccumulatedSeconds > 0 ? 'Shift Paused' : 'Shift Off').addClass('text-muted').removeClass('text-success text-primary');

                                    $('#global-timer-display').text(formatSeconds(baseAccumulatedSeconds));

                                    // Handle buttons display based on daily shift status
                                    if (!res.has_today_entry) {
                                        $('#btn-global-shift').show().removeClass('btn-danger').addClass('btn-success').attr('title', 'Start Shift').html('<i class="mdi mdi-play font-size-12 text-white"></i>');
                                        $('#btn-global-break').hide();
                                    } else if (res.is_paused) {
                                        $('#btn-global-shift').show().removeClass('btn-success').addClass('btn-danger').attr('title', 'End Shift').html('<i class="mdi mdi-stop font-size-12 text-white"></i>');
                                        $('#btn-global-break').show().removeClass('btn-warning').addClass('btn-success').attr('title', 'Resume Work').html('<i class="mdi mdi-play font-size-12 text-white"></i>');
                                    } else {
                                        // Shift is completely stopped for today
                                        $('#btn-global-shift').hide();
                                        $('#btn-global-break').hide();
                                    }

                                    // Handle banners visibility logic based on daily shift entry
                                    if (!res.has_today_entry) {
                                        // Shift not clocked-in today
                                        $('#global-timer-banners').removeClass('d-none');
                                        $('#banner-shift-not-started').removeClass('d-none');
                                        $('#banner-shift-paused').addClass('d-none');
                                    } else if (res.is_paused) {
                                        // Shift is started today, but currently paused/on-break
                                        $('#global-timer-banners').removeClass('d-none');
                                        $('#banner-shift-not-started').addClass('d-none');
                                        $('#banner-shift-paused').removeClass('d-none');
                                    } else {
                                        // Shift is completed/stopped for today
                                        $('#global-timer-banners').addClass('d-none');
                                        $('#banner-shift-not-started').addClass('d-none');
                                        $('#banner-shift-paused').addClass('d-none');
                                    }
                                }
                            }
                        });
                    }

                    // Initial fetch
                    updateWidgetStatus();

                    // Shift Control click (Start / End Shift)
                    $('#btn-global-shift').click(function() {
                        let btn = $(this);
                        if (!shiftHasTodayEntry) {
                            // Start Shift
                            btn.prop('disabled', true);
                            $.ajax({
                                url: "{{ route('global-timer.start') }}",
                                method: 'POST',
                                success: function(res) {
                                    if (res.success) {
                                        alertify.success(res.message);
                                        setTimeout(() => {
                                            location.reload();
                                        }, 600);
                                    } else {
                                        alertify.error(res.message);
                                        btn.prop('disabled', false);
                                    }
                                },
                                error: function() {
                                    btn.prop('disabled', false);
                                }
                            });
                        } else {
                            // End Shift Confirmation Popup
                            swal({
                                    title: "Close Today's Shift?",
                                    text: "Are you sure you want to end your workday attendance shift? This will also pause any active task timer.",
                                    icon: "warning",
                                    buttons: {
                                        cancel: {
                                            text: "Cancel",
                                            value: null,
                                            visible: true,
                                            className: "btn btn-secondary shadow-sm",
                                            closeModal: true,
                                        },
                                        confirm: {
                                            text: "Close Shift",
                                            value: true,
                                            visible: true,
                                            className: "btn btn-danger shadow-sm",
                                            closeModal: true
                                        }
                                    },
                                    dangerMode: true,
                                })
                                .then((willStop) => {
                                    if (willStop) {
                                        btn.prop('disabled', true);
                                        $.ajax({
                                            url: "{{ route('global-timer.stop') }}",
                                            method: 'POST',
                                            success: function(res) {
                                                if (res.success) {
                                                    alertify.success(res.message);
                                                    setTimeout(() => {
                                                        location.reload();
                                                    }, 600);
                                                } else {
                                                    alertify.error(res.message);
                                                    btn.prop('disabled', false);
                                                }
                                            },
                                            error: function() {
                                                btn.prop('disabled', false);
                                            }
                                        });
                                    }
                                });
                        }
                    });

                    // Break Control click (Take Break / Resume)
                    $('#btn-global-break').click(function() {
                        let btn = $(this);
                        btn.prop('disabled', true);
                        let targetUrl = shiftIsPaused ? "{{ route('global-timer.start') }}" : "{{ route('global-timer.pause') }}";
                        $.ajax({
                            url: targetUrl,
                            method: 'POST',
                            success: function(res) {
                                if (res.success) {
                                    alertify.success(res.message);
                                    setTimeout(() => {
                                        location.reload();
                                    }, 600);
                                } else {
                                    alertify.error(res.message);
                                    btn.prop('disabled', false);
                                }
                            },
                            error: function() {
                                btn.prop('disabled', false);
                            }
                        });
                    });
                })();
            @endif
        });
    </script>


    @yield('component')
    @yield('scripts')


    @auth
    @if($user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager']) || ($user->hasRole('Team-Leader') && optional($user->departments)->department == 2))
    @include('components.projects.components.projecttask')
    @endif
    @endauth

</body>

</html>
