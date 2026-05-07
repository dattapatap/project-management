@extends('layouts.authlayout')

@section('authcontent')
<div class="auth-card">

    {{-- Company Logo --}}
    <div class="auth-card-logo">
        <img src="{{ asset('assets/images/logo-light2.png') }}" alt="{{ env('APP_NAME') }}">
    </div>


    <h1 class="auth-card-title">Set new password</h1>
    <p class="auth-card-subtitle">Choose a strong password for your account</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email --}}
        <div class="auth-field">
            <label class="auth-label" for="email">Email Address</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class='bx bx-envelope'></i></span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="auth-input{{ $errors->has('email') ? ' is-invalid' : '' }}"
                    value="{{ $email ?? old('email') }}"
                    required
                    autofocus
                    autocomplete="email">
            </div>
            @error('email')
            <div class="auth-error-msg">
                <i class='bx bx-error-circle'></i> {{ $message }}
            </div>
            @enderror
        </div>

        {{-- New Password --}}
        <div class="auth-field">
            <label class="auth-label" for="password">New Password</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class='bx bx-lock-alt'></i></span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="auth-input{{ $errors->has('password') ? ' is-invalid' : '' }}"
                    placeholder="Min. 8 characters"
                    required
                    autocomplete="new-password">
                <button type="button" class="auth-eye-btn" id="togglePwd1" tabindex="-1" title="Show/Hide">
                    <i class='bx bx-hide' id="eyeIcon1"></i>
                </button>
            </div>
            @error('password')
            <div class="auth-error-msg">
                <i class='bx bx-error-circle'></i> {{ $message }}
            </div>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="auth-field" style="margin-bottom: 1.75rem;">
            <label class="auth-label" for="password-confirm">Confirm New Password</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class='bx bx-lock-alt'></i></span>
                <input
                    id="password-confirm"
                    type="password"
                    name="password_confirmation"
                    class="auth-input"
                    placeholder="Re-enter password"
                    required
                    autocomplete="new-password">
                <button type="button" class="auth-eye-btn" id="togglePwd2" tabindex="-1" title="Show/Hide">
                    <i class='bx bx-hide' id="eyeIcon2"></i>
                </button>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="auth-submit-btn">
            <i class='bx bx-check-shield'></i> Reset Password
        </button>
    </form>

    <div class="auth-back-wrap">
        <a href="{{ route('login') }}" class="auth-back-link">
            <i class='bx bx-arrow-back'></i> Back to Sign In
        </a>
    </div>

</div>
@endsection

@section('auth_scripts')
<script>
    function makeToggle(btnId, iconId, inputId) {
        document.getElementById(btnId).addEventListener('click', function() {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bx bx-show';
            } else {
                input.type = 'password';
                icon.className = 'bx bx-hide';
            }
        });
    }
    makeToggle('togglePwd1', 'eyeIcon1', 'password');
    makeToggle('togglePwd2', 'eyeIcon2', 'password-confirm');
</script>
@endsection
