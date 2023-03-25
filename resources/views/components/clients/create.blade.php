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
                        <li class="breadcrumb-item"><a href="{{ url('/client/Fresh') }}">Company</a></li>
                        <li class="breadcrumb-item active">Company</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="offset-1 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title"> New Company </h4>
                </div>
                <div class="card-body">
                    <form class="custom-validation" action="{{ route('clients.store') }}" method="POST" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Company Name <span class="text_required">*</span> </label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') parsley-error  @enderror"
                                    placeholder="Company Name" tabindex="1">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Category <span class="text_required">*</span> </label>
                                    <input type="text" name="category" value="{{ old('category') }}" class="form-control @error('category') parsley-error  @enderror"
                                    placeholder="Category (ex. Interior, IT, Design )" tabindex="2">
                                    @error('category')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label> Contact Person</label>
                                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-control @error('contact_person') parsley-error  @enderror"
                                    placeholder="Contact Person" tabindex="3">
                                    @error('contact_person')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Designation</label>
                                    <input type="text" name="designation" value="{{ old('designation') }}" class="form-control @error('designation') parsley-error  @enderror"
                                     placeholder="Designation" tabindex="4">
                                    @error('designation')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label> Email Id<span class="text_required">*</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') parsley-error  @enderror"
                                    placeholder="Email Id" tabindex="5">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label> Mob. Number<span class="text_required">*</span></label>
                                    <input type="text" name="mobile" value="{{ old('mobile') }}"  class="form-control @error('mobile') parsley-error  @enderror"
                                    placeholder="Mob. Number" minlength="10" minlength="10" onKeyPress="return isNumberKey(event);" tabindex="6">
                                    @error('mobile')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label> City <span class="text_required">*</span></label>
                                    <input type="text" name="city" value="{{ old('city') }}"  class="form-control @error('city') parsley-error  @enderror"
                                     placeholder="City" tabindex="7">
                                    @error('city')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label> Website Link</label>
                                    <input type="text" name="website_link" value="{{ old('website_link') }}"  class="form-control @error('website_link') parsley-error  @enderror"
                                    placeholder="Website Link" tabindex="8">
                                    @error('website_link')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="referral" id="referral" value="{{ $user->id }}">

                            <div class="col-6">
                                <div class="form-group">
                                    <label> Address <span class="text_required">*</span> </label>
                                    <input type="text" name="address" value="{{ old('address')}}" class="form-control @error('address') parsley-error  @enderror"
                                        placeholder="Address"  tabindex="9">
                                    @error('address')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row ml-0 mr-0 w-100">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>TBRO Type : </label>
                                        <select class="form-control @error('type') parsley-error  @enderror" name="type" id="type" width="100%"  tabindex="10">
                                            <option value="Call" selected @if( old('type') == 'Call') selected @endif> Call </option>
                                            <option value="Direct visit" @if( old('type') == 'Direct visit') selected @endif> Direct visit </option>
                                        </select>
                                        @error('type')
                                            <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Time : </label>
                                        <input type="text" name="time" id="time" value="{{ old('time')}}" class="form-control @error('time') parsley-error  @enderror"
                                            placeholder="HH:MM A" tabindex="11" autocomplete="off">
                                        @error('time')
                                            <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>TBRO :</label>
                                        <input type="text" name="date" id="date" class="form-control @error('date') parsley-error  @enderror" value="{{ old('date')}}"
                                         autocomplete="off"  placeholder="MM/DD/YYYY"  tabindex="12">
                                        @error('date')
                                            <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                        @enderror
                                    </div>

                                </div>
                                <div class="col-3">
                                    @php
                                            $stsStatus = App\Models\ParentStatus::where('category', 'STS')->where('name', '!=', 'Fresh')->get();
                                    @endphp
                                    <label>STS Status :</label>
                                    <select class="form-control" name="status" name="status" width="100%"  tabindex="13" @error('status') parsley-error  @enderror>
                                        <option selected value="">Select STS status</option>
                                        @foreach ($stsStatus as $item)
                                            <option value="{{$item->name}}" @if( old('status') == $item->name ) selected @endif> {{$item->name}} </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-12">
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea type="text" name="remarks"  class="form-control @error('remarks') parsley-error  @enderror"
                                     placeholder="remarks">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row float-roght">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right">
                                    Submit
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

@section('scripts')
<script>

$(document).ready(function(e){
        $('#time').datetimepicker({
            format: 'hh:mm A',
            useCurrent:false,
            defaultDate:new Date(),
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-check',
                clear: 'fa fa-trash',
                close: 'fa fa-times'
            }
        });

        $('#date').datetimepicker({
            format:'DD-MM-YYYY',
            maxDate: moment().add(60, 'days'),
            minDate: moment(),
            useCurrent:false,
        })
})



</script>

@endsection
