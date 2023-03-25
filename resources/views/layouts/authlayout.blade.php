<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
        <meta charset="utf-8">
        <!-- Meta Tags -->
        <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

        <!-- Favicon and Touch Icons -->
        <link href="{{ asset('assets/images/Icon.png') }}" rel="shortcut icon" type="image/png">
        <link href="{{ asset('assets/images/Icon.png') }}" rel="apple-touch-icon">
        <link href="{{ asset('assets/images/Icon.png') }}" rel="apple-touch-icon" sizes="72x72">
        <link href="{{ asset('assets/images/Icon.png') }}" rel="apple-touch-icon" sizes="114x114">
        <link href="{{ asset('assets/images/Icon.png') }}" rel="apple-touch-icon" sizes="144x144">

        <title>ERP : {{ config('app.name', 'Digitalnock It Solutions') }}</title>

        <meta property="og:type" content="ERP Application">
        <meta property="og:image" content="{{ asset('assets/images/Icon.png') }}" property="og:image" />

        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/alertifyjs/build/css/alertify.min.css') }}" rel="stylesheet" type="text/css">
        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>

    <body class="bg-primary bg-pattern" style="background-color: #06223c !important;">
        <div class="account-pages my-5">
            <div class="container">
                @yield('authcontent')
            </div>
        </div>
        <!-- end Account pages -->

        <!-- JAVASCRIPT -->
        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('assets/libs/alertifyjs/build/alertify.min.js') }}"></script>

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


    </body>

</html>
