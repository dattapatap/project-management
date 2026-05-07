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

    /* Metadata Box */
    .metadata-box {
        background: #f8f9fc;
        border: 1px solid rgba(215, 215, 230, 0.7);
        border-radius: 12px;
        padding: 18px;
    }
    .metadata-title {
        font-size: 11px;
        text-transform: uppercase;
        color: #888da8;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .metadata-value {
        font-size: 13.5px;
        font-weight: 600;
        color: #343a40;
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
                    <h3 class="text-premium-dark font-size-18 mb-1">📝 Edit Company Record</h3>
                    <p class="text-muted font-size-12 mb-0">Modify and update pipeline profiles and communication metadata for this company.</p>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/client/Fresh') }}" class="text-primary">Companies</a></li>
                    <li class="breadcrumb-item active text-muted">Edit</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="offset-md-1 col-md-10 col-12">
            <div class="card form-card-premium">
                <div class="card-body p-5">
                    
                    <form class="custom-validation" action="{{ route('clients.update', $client->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- SECTION 1: COMPANY DETAILS -->
                        <div class="form-section-header">
                            <i class="bx bx-buildings font-size-16"></i> Company Profile Details
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Company Name <span class="text_required">*</span> </label>
                                    <input type="text" name="name" value="{{ old('name', $client->name) }}" class="form-control input-premium @error('name') parsley-error @enderror" placeholder="Company Name" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Category / Niche / Industry <span class="text_required">*</span> </label>
                                    <input type="text" name="category" value="{{ old('category', $client->category) }}" class="form-control input-premium @error('category') parsley-error @enderror" placeholder="e.g. Interior Design, IT Consulting" required>
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
                                    <input type="text" name="contact_person" value="{{ old('contact_person', $client->cont_person) }}" class="form-control input-premium @error('contact_person') parsley-error @enderror" placeholder="Contact Person Name">
                                    @error('contact_person')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Designation </label>
                                    <input type="text" name="designation" value="{{ old('designation', $client->designation) }}" class="form-control input-premium @error('designation') parsley-error @enderror" placeholder="Designation">
                                    @error('designation')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Primary Email Address <span class="text-muted font-size-11">(Nullable)</span></label>
                                    <input type="email" name="email" value="{{ old('email', $client->email) }}" class="form-control input-premium @error('email') parsley-error @enderror" placeholder="Email Id">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Alternate Email Address <span class="text-muted font-size-11">(Nullable)</span></label>
                                    <input type="email" name="alternate_email" value="{{ old('alternate_email', $client->alt_email) }}" class="form-control input-premium @error('alternate_email') parsley-error @enderror" placeholder="Alternate Email Id">
                                    @error('alternate_email')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: COMMUNICATIONS CHANNELS (TELEPHONE / MOBILE) -->
                        <div class="form-section-header">
                            <i class="bx bx-phone-call font-size-16"></i> Active Communications Channels
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Primary Mobile Number <span class="text_required">*</span></label>
                                    <input type="text" name="mobile" value="{{ old('mobile', $client->mobile) }}" class="form-control input-premium @error('mobile') parsley-error @enderror" placeholder="Mob. Number" minlength="10" maxlength="10" onKeyPress="return isNumberKey(event);" required>
                                    @error('mobile')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Alternate Mobile Number </label>
                                    <input type="text" name="alternate_mobile" value="{{ old('alternate_mobile', $client->alt_mobile) }}" class="form-control input-premium @error('alternate_mobile') parsley-error @enderror" placeholder="Alt Mob. Number" minlength="10" maxlength="10" onKeyPress="return isNumberKey(event);">
                                    @error('alternate_mobile')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Telephone Number </label>
                                    <input type="text" name="telephone" value="{{ old('telephone', $client->telephone) }}" class="form-control input-premium @error('telephone') parsley-error @enderror" placeholder="Telephone Number" onKeyPress="return isNumberMinusKey(event);">
                                    @error('telephone')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Alternate Telephone </label>
                                    <input type="text" name="alternate_telephone" value="{{ old('alternate_telephone', $client->alt_telephone) }}" class="form-control input-premium @error('alternate_telephone') parsley-error @enderror" placeholder="Alt Telephone" onKeyPress="return isNumberMinusKey(event);">
                                    @error('alternate_telephone')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: LOCATION & PRESENCE -->
                        <div class="form-section-header">
                            <i class="bx bx-map font-size-16"></i> Location & Web Presence
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> City <span class="text_required">*</span></label>
                                    <input type="text" name="city" value="{{ old('city', $client->city) }}" class="form-control input-premium @error('city') parsley-error @enderror" placeholder="City" required>
                                    @error('city')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Website URL </label>
                                    <input type="text" name="website_link" value="{{ old('website_link', $client->website_link) }}" class="form-control input-premium @error('website_link') parsley-error @enderror" placeholder="Website Link">
                                    @error('website_link')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Referral Executive (Readonly) <span class="text_required">*</span> </label>
                                    <input class="form-control input-premium" name="referral" id="referral" value="{{ $client->referral->name }}" readonly style="background-color: #f1f2f7;">
                                    @error('referral')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Official Address 1 <span class="text_required">*</span> </label>
                                    <textarea name="address1" class="form-control textarea-premium @error('address1') parsley-error @enderror" placeholder="Address Line 1" rows="3" required>{{ old('address1', $client->address) }}</textarea>
                                    @error('address1')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="label-premium"> Alternate Address 2 </label>
                                    <textarea name="address2" class="form-control textarea-premium @error('address2') parsley-error @enderror" placeholder="Address Line 2 (Optional)" rows="3">{{ old('address2', $client->alt_address) }}</textarea>
                                    @error('address2')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: METADATA & SYSTEM LOGS -->
                        <div class="form-section-header">
                            <i class="bx bx-shield-quarter font-size-16"></i> Audit Trail & Record Metadata
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-4 col-12 mb-3">
                                <div class="metadata-box">
                                    <div class="metadata-title">Created Date</div>
                                    <div class="metadata-value">{{ Carbon\Carbon::parse($client->created_at)->format('d-M-Y h:i A') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="metadata-box">
                                    <div class="metadata-title">Last Updated</div>
                                    <div class="metadata-value">{{ Carbon\Carbon::parse($client->updated_at)->format('d-M-Y h:i A') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="metadata-box">
                                    <div class="metadata-title">Registered By</div>
                                    @php
                                        $created = DB::table('users')->where('id', $client->created_by)->first();
                                    @endphp
                                    <div class="metadata-value">{{ $created ? $created->name : '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center font-weight-bold float-right" style="border-radius: 12px; height: 46px; padding: 0 30px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none; box-shadow: 0 4px 15px rgba(30, 60, 114, 0.25); font-size: 14px;">
                                    <i class="bx bx-save font-size-18 mr-1"></i> Update Company Profile
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
function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57 )){
        return false;
    }
    return true;
}

function isNumberMinusKey(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57 )){
        if(charCode == 45){
            return true;
        }
        return false;
    }
    return true;
}
</script>
@endsection
