@extends('layouts.authlayout')

@section('authcontent')
<div class="auth-card">

    {{-- Company Logo --}}
    <div class="auth-card-logo">
        <img src="{{ asset('assets/images/logo-light2.png') }}" alt="{{ env('APP_NAME') }}">
    </div>



    <h1 class="auth-card-title">Welcome back</h1>
    <p class="auth-card-subtitle">Sign in to your {{ env('APP_NAME') }} workspace</p>

    <form method="POST" action="{{ route('login') }}" autocomplete="off">
        @csrf

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
                    value="{{ old('email') }}"
                    placeholder="you@company.com"
                    autofocus
                    autocomplete="email">
            </div>
            @error('email')
            <div class="auth-error-msg">
                <i class='bx bx-error-circle'></i> {{ $message }}
            </div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="auth-field">
            <label class="auth-label" for="password">Password</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class='bx bx-lock-alt'></i></span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="auth-input{{ $errors->has('password') ? ' is-invalid' : '' }}"
                    placeholder="••••••••"
                    autocomplete="current-password">
                <button type="button" class="auth-eye-btn" id="togglePwd" tabindex="-1" title="Show/Hide password">
                    <i class='bx bx-hide' id="eyeIcon"></i>
                </button>
            </div>
            @error('password')
            <div class="auth-error-msg">
                <i class='bx bx-error-circle'></i> {{ $message }}
            </div>
            @enderror
        </div>

        {{-- Remember Me & Forgot Password --}}
        <div class="auth-row-check">
            <label class="auth-check-label" for="remember">
                <input
                    class="auth-checkbox"
                    type="checkbox"
                    name="remember"
                    id="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>

            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="auth-forgot-link">Forgot password?</a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit" class="auth-submit-btn">
            <i class='bx bx-log-in-circle'></i> Sign In
        </button>
    </form>

</div>
@endsection

@section('auth_scripts')
<script>
    document.getElementById('togglePwd').addEventListener('click', function() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bx bx-show';
        } else {
            input.type = 'password';
            icon.className = 'bx bx-hide';
        }
    });
</script>
@endsection
