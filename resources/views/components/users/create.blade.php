@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center justify-content-between">
                    <a href="{{ url()->previous()  }}" class="btn-back" >
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/users') }}">Member</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="offset-2 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title"> New Member </h4>
                </div>
                <div class="card-body">
                    <form class="custom-validation" action="{{ route('users.store') }}" method="POST" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Name <span class="text_required">*</span> </label>
                                    <input type="text" name="name" value="{{ old('name', "") }}" class="form-control @error('name') parsley-error  @enderror" placeholder="Name">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Email Id<span class="text_required">*</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') parsley-error  @enderror" placeholder="Email Id">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Mob. Number<span class="text_required">*</span></label>
                                    <input type="number" name="mobile" value="{{ old('mobile') }}"  class="form-control @error('mobile') parsley-error  @enderror"
                                    placeholder="Mob. Number" minlength="10" onKeyPress="return isNumberKey(event);">
                                    @error('mobile')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Gender <span class="text_required">*</span></label>
                                    <select class="form-control" name="gender" id="gender" aria-placeholder="Gender" >
                                        <option selected value="">Select</option>
                                        <option value="Male" @if( old('gender') == 'Male' )  selected  @endif>Male</option>
                                        <option value="Female" @if( old('gender') == 'Female')  selected  @endif>Female</option>
                                    </select>
                                    @error('gender')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> DOB <span class="text_required">*</span></label>
                                    <input type="date" name="dob" value="{{ old('dob') }}" class="form-control @error('dob') parsley-error  @enderror"
                                    max="<?= date('Y-m-d', strtotime(date('Y-m-d').' -18 year')); ?>"  placeholder="Birth Date">
                                    @error('dob')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Joining Date <span class="text_required">*</span></label>
                                    <input type="date" name="joining_date" value="{{ old('joining_date') }}"  class="form-control @error('joining_date') parsley-error  @enderror"
                                    max="<?= date('Y-m-d'); ?>"
                                    placeholder="Joining Date">
                                    @error('joining_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-4">
                                <div class="form-group">
                                    <label> Department <span class="text_required">*</span></label>
                                    <select class="form-control select2" name="department" id="department" aria-placeholder="Department" >
                                        <option value="">Select</option>
                                        @foreach ($derpartments as $item)
                                            <option value="{{ $item->id }}" @if( old('department') ==  $item->id )  selected  @endif>
                                                {{ $item->name }} ( {{ $item->branch->code }} )
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-4">
                                <div class="form-group">
                                    <label>Role<span class="text_required">*</span></label>
                                    <select class="form-control select2" name="role" id="role" aria-placeholder="Role" >
                                        <option value="">Select</option>
                                        @foreach ($roles as $item)
                                            <option value="{{ $item->id }}" @if( old('role') ==  $item->id )  selected  @endif>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label>Designation <span class="text_required">*</span></label>
                                    <input type="text" name="designation" value="{{ old('designation') }}" class="form-control @error('designation') parsley-error  @enderror" placeholder="Designation">
                                    @error('designation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Member Code(Emp. Code) <span class="text_required">*</span></label>
                                    <input type="text" name="code" value="{{ old('code') }}"  class="form-control @error('code') parsley-error  @enderror"
                                     placeholder="Code" onkeyup="this.value = this.value.toUpperCase();">
                                    @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label> Password <span class="text_required">*</span></label>
                                    <input type="password" name="password" value="{{ old('password', "") }}" class="form-control @error('password') parsley-error  @enderror" placeholder="password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Confirm Password <span class="text_required">*</span></label>
                                    <input type="password" name="password_confirmation" value="{{ old('password', "") }}"  class="form-control @error('password_confirmation') parsley-error  @enderror" placeholder="Confirm password">
                                    @error('password_confirmation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <div class="row float-roght">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right">
                                    Create
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
