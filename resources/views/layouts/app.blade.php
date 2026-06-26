<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
    <link rel="stylesheet" type="text/css" href="{{ asset('css/erp-theme.css') }}">
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
                    <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="mdi mdi-backburger"></i>
                    </button>
                </div>

                <div class="d-flex">

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
        });
    </script>


    @yield('component')
    @yield('scripts')

</body>

</html>
