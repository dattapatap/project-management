@extends('layouts.app')

@section('content')


    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-iteml-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Password</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME') }}</a></li>
                            <li class="breadcrumb-item active">Change Password</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="offset-3 col-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="header-title"> Change Password </h4>
                    </div>

                    <div class="card-body">
                        <form class="custom-validation" action="{{ route('updatePassword') }}" method="POST">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="user_id" value="{{ $user->id }}" class="form-control">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Old Password <span class="text_required">*</span> </label>
                                        <input type="text" name="old_password" value="{{ old('old_password')}}" class="form-control @error('old_password') parsley-error  @enderror" placeholder="Old Password">
                                        @error('old_password')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="col-12">
                                    <div class="form-group">
                                        <label> New Password <span class="text_required">*</span></label>
                                        <input type="password" name="new_password" value="{{ old('new_password') }}" class="form-control @error('new_password') parsley-error  @enderror"
                                        placeholder="New password">
                                        @error('new_password')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong> </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label> Confirm Password<span class="text_required">*</span></label>
                                        <input type="password" name="confirm_password" value="{{ old('confirm_password') }}"  class="form-control @error('confirm_password') parsley-error  @enderror"
                                        placeholder="Confirm password">
                                        @error('confirm_password')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row float-roght">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right">
                                        Change Password
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
    </div>
@endsection
