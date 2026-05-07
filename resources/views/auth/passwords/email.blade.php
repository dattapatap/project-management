@extends('layouts.authlayout')

@section('authcontent')
<div class="auth-card">

    {{-- Company Logo --}}
    <div class="auth-card-logo">
        <img src="{{ asset('assets/images/logo-light2.png') }}" alt="{{ env('APP_NAME') }}">
    </div>


    <h1 class="auth-card-title">Forgot password?</h1>
    <p class="auth-card-subtitle">Enter your email and we'll send you a secure reset link</p>

    {{-- Success status --}}
    @if (session('status'))
    <div class="auth-success-alert">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('status') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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

        {{-- Submit --}}
        <button type="submit" class="auth-submit-btn" style="margin-top: 0.5rem;">
            <i class='bx bx-send'></i> Send Reset Link
        </button>
    </form>

    <div class="auth-back-wrap">
        <a href="{{ route('login') }}" class="auth-back-link">
            <i class='bx bx-arrow-back'></i> Back to Sign In
        </a>
    </div>

</div>
@endsection
