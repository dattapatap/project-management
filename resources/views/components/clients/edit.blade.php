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
                        <li class="breadcrumb-item"><a href="{{ url('/client/Fresh') }}">Clients</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="offset-1 col-lg-10 ">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title"> Edit Client </h4>
                </div>
                <div class="card-body">
                    <form class="custom-validation" action="{{ route('clients.update', $client->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Name <span class="text_required">*</span> </label>
                                    <input type="text" name="name" value="{{ old('name', $client->name ) }}" class="form-control @error('name') parsley-error  @enderror" placeholder="Client Name">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Category <span class="text_required">*</span> </label>
                                    <input type="text" name="category" value="{{ old('category',  $client->category ) }}" class="form-control @error('category') parsley-error  @enderror"
                                    placeholder="Category (ex. Interior, IT, Design )">
                                    @error('category')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label> Contact Person</label>
                                    <input type="text" name="contact_person" value="{{ old('contact_person',  $client->cont_person ) }}" class="form-control @error('contact_person') parsley-error  @enderror"
                                    placeholder="Contact Person">
                                    @error('contact_person')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Designation</label>
                                    <input type="text" name="designation" value="{{ old('designation',  $client->designation ) }}" class="form-control @error('designation') parsley-error  @enderror" placeholder="Designation">
                                    @error('designation')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label> Email Id<span class="text_required">*</span></label>
                                    <input type="email" name="email" value="{{ old('email',  $client->email ) }}" class="form-control @error('email') parsley-error  @enderror" placeholder="Email Id">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label> Alternate Email Id<span class="text_required">*</span></label>
                                    <input type="email" name="alternate_email" value="{{ old('alternate_email',  $client->alt_email) }}" class="form-control @error('alternate_email') parsley-error  @enderror" placeholder="Email Id">
                                    @error('alternate_email')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label> Mob. Number <span class="text_required">*</span></label>
                                    <input type="text" name="mobile" value="{{ old('mobile',  $client->mobile) }}"  class="form-control @error('mobile') parsley-error  @enderror"
                                    placeholder="Mob. Number" minlength="10" minlength="10" onKeyPress="return isNumberKey(event);">
                                    @error('mobile')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Alternate Mob. Number</label>
                                    <input type="text" name="alternate_mobile" value="{{ old('alternate_mobile',  $client->alt_mobile) }}"  class="form-control @error('alternate_mobile') parsley-error  @enderror"
                                    placeholder="Alternate Mob. Number" minlength="10" minlength="10" onKeyPress="return isNumberKey(event);">
                                    @error('alternate_mobile')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label> Telephone Number</label>
                                    <input type="text" name="telephone" value="{{ old('telephone',  $client->telephone ) }}"  class="form-control @error('telephone') parsley-error  @enderror"
                                    placeholder="Talephone Number" onKeyPress="return isNumberMinusKey(event);">
                                    @error('telephone')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Alternate Talephone Number</label>
                                    <input type="text" name="alternate_telephone" value="{{ old('alternate_telephone',  $client->alt_telephone ) }}"  class="form-control @error('alternate_telephone') parsley-error  @enderror"
                                    placeholder="Alternate Telephone Number" onKeyPress="return isNumberMinusKey(event);">
                                    @error('alternate_telephone')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label> City <span class="text_required">*</span></label>
                                    <input type="text" name="city" value="{{ old('city',  $client->city ) }}"  class="form-control @error('city') parsley-error  @enderror"
                                     placeholder="City">
                                    @error('city')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Website Link</label>
                                    <input type="text" name="website_link" value="{{ old('website_link',  $client->website_link ) }}"  class="form-control @error('website_link') parsley-error  @enderror"
                                     placeholder="Website Link">
                                    @error('website_link')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Referral User <span class="text_required">*</span> </label>
                                    <input class="form-control" name="referral" id="referral" value="{{  $client->referral->name }}" readonly>
                                    @error('referral')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="form-group">
                                    <label> Address 1 <span class="text_required">*</span> </label>
                                    <textarea type="text" name="address1"  class="form-control @error('address1') parsley-error  @enderror"
                                     placeholder="Address 1" rows="3">{{ old('address1', $client->address) }}</textarea>
                                    @error('address1')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Address 2</label>
                                    <textarea type="text" name="address2"  class="form-control @error('address2') parsley-error  @enderror"
                                     placeholder="Address 2" rows="3" >{{ old('address2', $client->alt_address)}}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert"> <strong>{{ $message }}</strong> </span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label style="font-weight: 600;">Created :</label>
                                    <span> {{ Carbon\Carbon::parse($client->created_at)->format('d-M-Y') }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label style="font-weight: 600;">Updated :</label>
                                    <span> {{ Carbon\Carbon::parse($client->updated_at)->format('d-M-Y') }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label style="font-weight: 600;">Created By :</label>
                                    @php
                                        $created = DB::table('users')->where('id', $client->created_by)->first();
                                    @endphp
                                    <span> {{ $created->name }}  </span>
                                </div>
                            </div>
                        </div>

                        <div class="row float-roght">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right">
                                    Update
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
            console.log(charCode);
            if(charCode == 45){
                return true;
            }
            return false;
        }
        return true;
    }

</script>

@endsection
