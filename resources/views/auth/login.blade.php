@extends('layouts.authlayout')
@section('authcontent')
<div class="row">
    <div class="col-lg-12">
        <div class="text-center mb-5">
            <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/images/logo-light1.png')}}" height="80" alt="logo"></a>
        </div>
    </div>
</div>
<!-- end row -->
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <div class="p-2">
                    <h5 class="mb-4 text-center">Sign in to continue to {{ env('APP_NAME') }}.</h5>
                    <form method="POST" class="form-horizontal" action="{{ route('login') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-4">
                                    <label for="username">Username</label>
                                    <div class="col-md-12">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" autofocus>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="userpassword">Password</label>
                                    <div class="col-md-12">
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password"
                                        value="{{ old('password') }}"
                                        autocomplete="current-password">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>>
                                            <label class="custom-control-label" for="remember">Remember me</label>
                                        </div>
                                    </div>
                                    @if (Route::has('password.request'))
                                    <div class="col-md-6">
                                        <div class="text-md-right mt-3 mt-md-0">
                                            <a href="{{ route('password.request') }}" class="text-muted"><i class="mdi mdi-lock"></i> {{ __('Forgot Your Password?') }}</a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <button class="btn btn-success btn-block waves-effect waves-light" type="submit">{{ __('Login') }}</button>
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="javascript:void(0)" class="text-muted"><i class="mdi mdi-account-circle mr-1"></i> Create an account</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
