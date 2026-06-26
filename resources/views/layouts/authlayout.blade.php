<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">

    <link href="{{ asset('assets/images/Icon.png') }}" rel="shortcut icon" type="image/png">
    <link href="{{ asset('assets/images/Icon.png') }}" rel="apple-touch-icon">

    <title>{{ config('app.name', 'ERP') }} — Workspace</title>

    {{-- Bootstrap --}}
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Boxicons CDN (required for bx-* icon classes) --}}
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    {{-- Auth Design System --}}
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    <link href="{{ asset('css/toastr.css') }}" rel="stylesheet">
</head>

<body>
    <div class="auth-wrapper">

        {{-- ── Left Branded Panel ── --}}
        <div class="auth-brand">
            <div class="auth-brand-top">
                <div class="auth-brand-logo">
                    <img src="{{ asset('assets/images/logo-light1.png') }}" alt="{{ config('app.name') }} Logo">
                </div>

                <h2 class="auth-brand-headline">
                    Your Workspace.<br>
                    <span>Unified & Powerful.</span>
                </h2>
                <p class="auth-brand-sub">
                    One platform to manage projects, teams, and operations — from onboarding to delivery.
                </p>

                <ul class="auth-features">
                    <li>
                        <span class="check-icon">&#10003;</span>
                        Real-time task &amp; project management
                    </li>
                    <li>
                        <span class="check-icon">&#10003;</span>
                        Department-level access control
                    </li>
                    <li>
                        <span class="check-icon">&#10003;</span>
                        Team performance intelligence
                    </li>
                    <li>
                        <span class="check-icon">&#10003;</span>
                        Sales pipeline &amp; CRM tracking
                    </li>
                </ul>
            </div>

            <div class="auth-brand-bottom">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>

        {{-- ── Right Form Panel ── --}}
        <div class="auth-form-panel">
            @yield('authcontent')
        </div>

    </div>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/alertifyjs/build/alertify.min.js') }}"></script>

    <!-- Modern Global Glassmorphic Toast Script -->
    <script>
        function showModernToast(type, message) {
            let container = document.getElementById('modern-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'modern-toast-container';
                document.body.appendChild(container);
            }

            // Determine boxicon class
            let iconClass = 'bx bx-info-circle';
            if (type === 'success') iconClass = 'bx bx-check-circle';
            if (type === 'error') iconClass = 'bx bx-error-circle';
            if (type === 'warning') iconClass = 'bx bx-error';

            // Create toast card
            let toast = document.createElement('div');
            toast.className = `modern-toast ${type}`;
            toast.innerHTML = `
            <div class="modern-toast-icon">
                <i class="${iconClass}" style="font-size: 1.25rem;"></i>
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

    @yield('auth_scripts')
</body>

</html>
