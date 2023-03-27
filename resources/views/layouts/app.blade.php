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
    <link href="{{ asset('assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"
        rel="stylesheet" type="text/css">


    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    @vite(['resources/js/app.js'])
    @yield('styles')

</head>

<body data-sidebar="dark">
    <!-- Begin page -->
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
                            @if ($user->hasRole('Admin'))
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="mdi mdi-settings font-size-16 align-middle mr-1"></i>
                                    Change Password
                                </a>
                            @endif


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
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">Menu</li>

                        @if ($user->hasRole(['Project-Manager']) && $user->departments->department == 2)
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="mdi mdi-plus-outline"></i>
                                    <span>Quick Add</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li>
                                        <a class="waves-effect" href="javascript: void(0);">
                                            <i class="mdi mdi-checkbox-marked-circle-outline"></i><span>Task</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="waves-effect" href="javascript: void(0);">
                                            <i class="mdi mdi-folder-outline"></i><span>Project</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="waves-effect" href="javascript: void(0);">
                                            <i class="mdi mdi-timer"></i><span>Start Timer</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="waves-effect" href="javascript: void(0);">
                                            <i class="mdi mdi mdi-clock-outline"></i><span>Log Time</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="waves-effect" href="javascript: void(0);">
                                            <i class="mdi mdi-message-text-outline"></i><span>Message</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="waves-effect" href="javascript: void(0);">
                                            <i class="mdi mdi-calendar-outline"></i><span>Events</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <li>
                            <a class="waves-effect" href="{{url('/home')}}">
                                <i class="mdi mdi-view-dashboard"></i><span>Home</span>
                            </a>
                        </li>
                        @if ($user->hasRole(['Project-Manager']) && $user->departments->department == 2)
                            <li>
                                <a class="waves-effect" href="{{ url('/projects/all') }}">
                                    <i class="mdi mdi mdi-folder-multiple"></i><span>Projects</span>
                                </a>
                            </li>
                        @endif




                        @if ($user->hasRole('Admin'))
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="mdi mdi-account-multiple-outline"></i>
                                    <span>Users</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ url('/departments') }}">Departments</a></li>
                                    <li><a href="{{ url('/users') }}">Users</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="mdi mdi-account-group-outline"></i>
                                    <span>Company</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ url('client/Fresh') }}">Companies</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="waves-effect" href="{{ url('/domains') }}">
                                    <i class="mdi mdi-domain-plus"></i><span>Domains</span>
                                </a>
                            </li>
                            <li>
                                <a class="waves-effect" href="{{ url('/payments') }}">
                                    <i class="mdi mdi-wallet-outline"></i><span>Payments</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="mdi mdi-note-text"></i>
                                    <span>Reports</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ url('mysts/searchsts') }}">STS</a></li>
                                    <li><a href="{{ url('reports/dsr/searchdsr') }}">DSR</a></li>
                                    <li><a href="{{ url('reports/dsr/salesreports') }}">Sales Report</a></li>
                                </ul>
                            </li>
                        @endif

                        @if ($user->hasRole(['Sales-Executive', 'Team-Leader']) && $user->departments->department == 1)
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="mdi mdi-cloud-print-outline"></i>
                                    <span>My CRM </span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ url('mysts/searchsts') }}">Search STS</a></li>
                                    <li><a href="{{ route('profile') }}">Profile</a></li>
                                    <li><a href="{{ route('changepassword') }}">Change Password</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="mdi mdi-account-group-outline"></i>
                                    <span>Company</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ url('clients/create') }}">New Company</a></li>
                                    @if ($user->hasRole(['Sales-Executive']))
                                        <li><a href="{{ url('client/Fresh') }}">My Companies</a></li>
                                    @else
                                        <li><a href="{{ url('client/Fresh') }}">Companies</a></li>
                                    @endif
                                </ul>
                            </li>

                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="mdi mdi-settings-outline"></i>
                                    <span>DSR</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ url('reports/dsr/searchdsr') }}">DSR</a></li>
                                    <li><a href="{{ url('reports/dsr/salesreports') }}">Sales Report</a></li>
                                </ul>
                            </li>

                        @endif



                        @if (!$user->hasRole('Admin'))
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effec">
                                    <i class="mdi mdi-page-layout-sidebar-right"></i>
                                    <span>Others</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="https://digitalnock.ngriffinpay.com" target="_blank">My
                                            Attendance</a></li>
                                </ul>
                            </li>
                        @endif

                    </ul>

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
    <script src="{{ asset('js/index.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

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
    @yield('scripts')

</body>

</html>
