@extends('layouts.app')

@section('styles')
<style>
    /* Premium Page Typography & Theme Variables */
    .client-form-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    /* Back & Title Header styling */
    .title-card-glass {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(245,247,255,0.95) 100%);
        border-left: 5px solid #7F00FF;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }
    .btn-back-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid rgba(220, 220, 235, 0.8);
        color: #495057;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }
    .btn-back-circle:hover {
        background: #7F00FF;
        color: #ffffff;
        border-color: #7F00FF;
        transform: translateX(-3px);
        box-shadow: 0 4px 12px rgba(127, 0, 255, 0.25);
    }

    /* Form Container Card */
    .form-card-premium {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(230, 230, 240, 0.8);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    /* Form Section Headers */
    .form-section-header {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #7F00FF;
        margin-bottom: 20px;
        padding-bottom: 8px;
        border-bottom: 2px solid rgba(127, 0, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Premium Form Inputs */
    .label-premium {
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        color: #343a40;
        font-size: 13px;
        margin-bottom: 6px;
    }
    .input-premium {
        border-radius: 10px !important;
        border: 1px solid rgba(210, 210, 225, 0.7);
        padding: 10px 14px;
        height: 44px;
        font-family: 'Outfit', sans-serif;
        font-size: 13.5px;
        color: #333333;
        transition: all 0.25s ease;
        background-color: #ffffff;
    }
    .input-premium:focus {
        border-color: #7F00FF !important;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.15) !important;
        outline: none;
    }
    .textarea-premium {
        border-radius: 10px !important;
        border: 1px solid rgba(210, 210, 225, 0.7);
        padding: 12px 14px;
        font-family: 'Outfit', sans-serif;
        font-size: 13.5px;
        color: #333333;
        transition: all 0.25s ease;
        background-color: #ffffff;
    }
    .textarea-premium:focus {
        border-color: #7F00FF !important;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.15) !important;
        outline: none;
    }
    .text_required {
        color: #ff4d4f;
    }

    /* Datepicker styling integration overrides */
    .bootstrap-datetimepicker-widget table td.active, 
    .bootstrap-datetimepicker-widget table td.active:hover {
        background-color: #7F00FF !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid client-form-wrapper">
    
    <!-- 🗓 Title and Navigation Header Card -->
    <div class="card mb-4 title-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center" style="gap: 15px;">
                <a href="{{ url('client/Fresh') }}" class="btn-back-circle" data-toggle="tooltip" data-placement="top" title="Go Back">
                    <i class="mdi mdi-keyboard-backspace font-size-18"></i>
                </a>
                <div>
                    <h3 class="text-premium-dark font-size-18 mb-1">🏢 Register New Company</h3>
                    <p class="text-muted font-size-12 mb-0">Record and route a fresh business opportunity into your pipeline.</p>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/client/Fresh') }}" class="text-primary">Companies</a></li>
                    <li class="breadcrumb-item active text-muted">New</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="offset-md-1 col-md-10 col-12">
            <div class="card form-card-premium">
                <div class="card-body p-5">
                    
                    <form class="custom-validation" action="{{ route('clients.store') }}" method="POST" novalidate>
                        @csrf
                        
                        <!-- SECTION 1: COMPANY DETAILS -->
                        <div class="form-section-header">
                            <i class="bx bx-buildings font-size-16"></i> Company Profile Details
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Company Name <span class="text_required">*</span> </label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control input-premium @error('name') parsley-error @enderror" placeholder="Enter official company name" tabindex="1" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Category / Niche / Industry <span class="text_required">*</span> </label>
                                    <input type="text" name="category" value="{{ old('category') }}" class="form-control input-premium @error('category') parsley-error @enderror" placeholder="e.g. Interior Design, IT Consulting, Retail" tabindex="2" required>
                                    @error('category')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: CONTACT INFORMATION -->
                        <div class="form-section-header">
                            <i class="bx bx-user-voice font-size-16"></i> Key Point of Contact
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Contact Person Name </label>
                                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-control input-premium @error('contact_person') parsley-error @enderror" placeholder="Contact Person Name" tabindex="3">
                                    @error('contact_person')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Designation </label>
                                    <input type="text" name="designation" value="{{ old('designation') }}" class="form-control input-premium @error('designation') parsley-error @enderror" placeholder="e.g. Managing Director, HR" tabindex="4">
                                    @error('designation')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Email Address <span class="text-muted font-size-11">(Nullable)</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control input-premium @error('email') parsley-error @enderror" placeholder="e.g. contact@company.com" tabindex="5">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Mobile Number <span class="text_required">*</span></label>
                                    <input type="text" name="mobile" value="{{ old('mobile') }}" class="form-control input-premium @error('mobile') parsley-error @enderror" placeholder="10 digit mobile" minlength="10" maxlength="10" onKeyPress="return isNumberKey(event);" tabindex="6" required>
                                    @error('mobile')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: LOCATION & WEB LINK -->
                        <div class="form-section-header">
                            <i class="bx bx-map font-size-16"></i> Geographic & Web Presence
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> City <span class="text_required">*</span></label>
                                    <input type="text" name="city" value="{{ old('city') }}" class="form-control input-premium @error('city') parsley-error @enderror" placeholder="City" tabindex="7" required>
                                    @error('city')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Website URL </label>
                                    <input type="url" name="website_link" value="{{ old('website_link') }}" class="form-control input-premium @error('website_link') parsley-error @enderror" placeholder="https://company.com" tabindex="8">
                                    @error('website_link')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Official Address <span class="text_required">*</span> </label>
                                    <input type="text" name="address" value="{{ old('address') }}" class="form-control input-premium @error('address') parsley-error @enderror" placeholder="Full headquarter address" tabindex="9" required>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="referral" id="referral" value="{{ $user->id }}">

                        <!-- SECTION 4: INITIAL SCHEDULES / PIPELINE STATUS -->
                        <div class="form-section-header">
                            <i class="bx bx-time-five font-size-16"></i> Initial Call Touchpoint & pipeline Routing
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium">TBRO Touchpoint Type</label>
                                    <select class="form-control input-premium select2 @error('type') parsley-error @enderror" name="type" id="type" tabindex="10" required>
                                        <option value="Call" @if(old('type') == 'Call') selected @endif> Call </option>
                                        <option value="Direct visit" @if(old('type') == 'Direct visit') selected @endif> Direct visit </option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium">Schedule Time</label>
                                    <input type="text" name="time" id="time" value="{{ old('time') }}" class="form-control input-premium @error('time') parsley-error @enderror" placeholder="HH:MM A" tabindex="11" autocomplete="off" required>
                                    @error('time')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium">Schedule Date (TBRO)</label>
                                    <input type="text" name="tbro_date" id="tbro_date" class="form-control input-premium @error('tbro_date') parsley-error @enderror" value="{{ old('tbro_date') }}" autocomplete="off" placeholder="DD-MM-YYYY" tabindex="12" required>
                                    @error('tbro_date')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                @php
                                    $stsStatus = App\Models\ParentStatus::where('category', 'STS')->get();
                                @endphp
                                <div class="form-group mb-0">
                                    <label class="label-premium">STS Routing Status</label>
                                    <select class="form-control input-premium select2 @error('status') parsley-error @enderror" name="status" tabindex="13" required>
                                        <option value="" selected>Select STS status</option>
                                        @foreach ($stsStatus as $item)
                                            <option value="{{$item->name}}" @if(old('status') == $item->name) selected @endif> {{$item->name}} </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: INITIAL TOUCHPOINT REMARKS -->
                        <div class="row mb-5">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="label-premium">Touchpoint Conversation Notes / Remarks <span class="text_required">*</span></label>
                                    <textarea name="remarks" class="form-control textarea-premium @error('remarks') parsley-error @enderror" placeholder="Type specific conversation logs, client reactions, and next action items..." rows="4" required>{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center font-weight-bold float-right" style="border-radius: 12px; height: 46px; padding: 0 30px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); border: none; box-shadow: 0 4px 15px rgba(127,0,255,0.25); font-size: 14px;">
                                    <i class="bx bx-check-double font-size-18 mr-1"></i> Register Lead & Create Record
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function(e){
    $('#time').datetimepicker({
        format: 'hh:mm A',
        useCurrent: false,
        defaultDate: new Date(),
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

    $('#tbro_date').datetimepicker({
        format:'DD-MM-YYYY',
        maxDate: moment().add(60, 'days'),
        minDate: moment(),
        useCurrent: false,
    });
});
</script>
@endsection
