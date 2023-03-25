@extends('layouts.app')




@section('content')


    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-iteml-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Profile</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME') }}</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="offset-2 col-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <div class="mb-2 profile-image">
                                    @if($user->profile)
                                        <img class="rounded-circle profile-pic" src="{{ asset('storage/'.$user->profile ) }}" data-holder-rendered="true">
                                    @else
                                        <img class="rounded-circle profile-pic" src="{{ Avatar::create($user->name)->toBase64()  }}" data-holder-rendered="true" alt="{{ $user->name }}">
                                    @endif
                                </div>
                                <div class="p-image">
                                    <i class="mdi mdi-camera upload-button" for="image"></i>
                                    <input class="file-upload" type="file" name="image" id="image"
                                        accept="image/*" />
                                </div>
                                <h3 class="pt-2"> {{ $user->name }}</h3>
                            </div>
                        </div>

                        <hr style="border-top: 3px solid #3fbeaa;">

                        @if(!$user->hasRole('Admin'))
                        <div id="accordion" class="profile-accordian">
                            <div class="card mb-0">
                                <div class="card-header" id="headingOne">
                                    <h5 class="m-0 font-size-14">
                                        <a class=" acco-heading" data-toggle="collapse" data-parent="#accordion"
                                            href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            BASIC INFORMATION
                                        </a>
                                    </h5>
                                </div>

                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                                    <div class="card-body">
                                        <form method="POST" id="frm_basicInfo">
                                            @csrf
                                                <div class="row">
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4">  <h1 class="display-6 mb-0"> <i class="mdi mdi-account-outline"></i></h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2">Name</p>
                                                                <h6 class="mb-0 editinfolable">  {{ $user->name }} </h6>
                                                                <div class="editinfo">
                                                                    <input type="text" value="{{ $user->name }}" name="name" class="form-control">
                                                                    <span class="invalid-feedback" id="name_input-error"  role="alert"><strong></strong></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4"> <h1 class="display-6 mb-0"> <i class="mdi mdi-cellphone-iphone"></i></h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2">Mobile Number</p>
                                                                <h6 class="mb-0 editinfolable">   {{ $user->mobile }}</h6>
                                                                <div class="editinfo">
                                                                    <input type="number" value="{{ $user->mobile }}" name="number"  maxlength="10" minlength="10" class="editinfo form-control">
                                                                    <span class="invalid-feedback" id="number_input-error" role="alert"> <strong></strong> </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4"> <h1 class="display-6 mb-0"> <i class="mdi mdi-email-open-outline"></i> </h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2"> Email </p>
                                                                <h6 class="mb-0 editinfolable"> {{ $user->email }}</h6>
                                                                <div class="editinfo">
                                                                    <input type="email" value="{{ $user->email }}" name="mail" class="editinfo form-control " readonly>
                                                                    <span class="invalid-feedback" id="mail_input-error" role="alert">  <strong></strong> </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4"> <h1 class="display-6 mb-0"> <i class="mdi mdi-cake-variant"></i> </h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2"> Date of Birth </p>
                                                                <h6 class="mb-0 editinfolable"> {{ Carbon\Carbon::parse($user->emp->dob)->format('d M Y') }}</h6>
                                                                <div class="editinfo">
                                                                    <input type="date" value="{{ $user->emp->dob }}" name="dob" class="editinfo form-control">
                                                                    <span class="invalid-feedback" id="dob_input-error" role="alert">  <strong></strong> </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4"> <h1 class="display-6 mb-0"> <i class="mdi mdi-gender-male-female"></i> </h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2"> Gender </p>
                                                                <h6 class="mb-0 editinfolable"> {{ $user->emp->gender }}</h6>
                                                                <div class="editinfo">
                                                                    <select type="gender"  name="gender" id="gender" class="editinfo form-control">
                                                                        <option value="Male" @if($user->emp->gender == 'Male') selected @endif> Male</option>
                                                                        <option value="Female" @if($user->emp->gender == 'Female') selected @endif> Female</option>
                                                                    </select>
                                                                    <span class="invalid-feedback" id="gender_input-error" role="alert">  <strong></strong> </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4"> <h1 class="display-6 mb-0"> <i class="mdi mdi-calendar-clock"></i> </h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2"> Joining Date </p>
                                                                <h6 class="mb-0 editinfolable">  {{ Carbon\Carbon::parse($user->emp->joining_dt)->format('d M Y') }} </h6>
                                                                <div class="editinfo">
                                                                    <input type="text" value="{{ $user->emp->joining_dt }}" name="joiningdt" class="editinfo form-control" readonly>
                                                                    <span class="invalid-feedback" id="joiningdt_input-error" role="alert">  <strong></strong> </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4"> <h1 class="display-6 mb-0"> <i class="mdi mdi-code-not-equal"></i> </h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2"> Employee Code </p>
                                                                <h6 class="mb-0 editinfolable">  {{ $user->emp->mem_code }} </h6>
                                                                <div class="editinfo">
                                                                    <input type="text" value="{{ $user->emp->mem_code }}" name="mem_code" class="editinfo form-control" readonly>
                                                                    <span class="invalid-feedback" id="mem_code_input-error" role="alert">  <strong></strong> </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="d-flex">
                                                            <div class="ml-3 mr-4"> <h1 class="display-6 mb-0"> <i class="mdi mdi-target-account"></i> </h1></div>
                                                            <div class="flex-1">
                                                                <p class="mb-2"> Designation </p>
                                                                <h6 class="mb-0 editinfolable">  {{ $user->emp->designation }} </h6>
                                                                <div class="editinfo">
                                                                    <input type="text" value="{{ $user->emp->designation }}" name="designation" class="editinfo form-control">
                                                                    <span class="invalid-feedback" id="designation_input-error" role="alert">  <strong></strong> </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-12 mt-3 editinfo " style="margin-left: 78%;">
                                                        <button class="btn btn-outline-primary btn-cancle-forminfo">   Cancel</button> &nbsp;&nbsp;
                                                        <button type="submit" class="btn btn-primary"> Update</button>
                                                    </div>
                                                </div>

                                        </form>
                                        <span class="mdi mdi-pencil-plus-outline float-end btn_edit_info"  onclick="foo(event);"
                                            style="position: absolute;margin-right: 2%;top: 3%;right: 3%; display:block;cursor: pointer;color: #fff;;">
                                            edit
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-0">
                                <div class="card-header" id="headingTwo">
                                    <h5 class="m-0 font-size-14">
                                        <a class="collapsed acco-heading " data-toggle="collapse"  data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            SOCIAL INFORMATION
                                        </a>
                                    </h5>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"  data-parent="#accordion">
                                    <div class="card-body">
                                        <form method="POST" id="frm_socialinfo">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <div class="row">
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <div class="col-md-12 mb-4">
                                                    <div class="d-flex">
                                                        <div class="ml-3 mr-4">
                                                            <h1 class="display-6 mb-0">
                                                                <a href="{{ $user->emp->github }}" target="_blank"><i class="mdi mdi-facebook"></i> </a>
                                                            </h1>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="editinfo">
                                                                <input type="text" value="{{ $user->emp->fb }}" name="facebook" class="form-control">
                                                                <span class="invalid-feedback" id="facebook_input-error"  role="alert"><strong></strong></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-4">
                                                    <div class="d-flex">
                                                        <div class="ml-3 mr-4">
                                                            <h1 class="display-6 mb-0">
                                                                <a href="{{ $user->emp->insta }}" target="_blank" style="color:#f58301"><i class="mdi mdi-instagram"></i></a>
                                                                </h1>
                                                            </div>
                                                        <div class="flex-1">
                                                            <div class="editinfo">
                                                                <input type="text" value="{{ $user->emp->insta }}" name="insta" class="editinfo form-control">
                                                                <span class="invalid-feedback" id="number_input-error" role="alert"> <strong></strong> </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-4">
                                                    <div class="d-flex">
                                                        <div class="ml-3 mr-4">
                                                            <h1 class="display-6 mb-0">
                                                                <a href="{{ $user->emp->linkedin }}" target="_blank" style="color:#0077b5"> <i class="mdi mdi-linkedin"></i> </a>
                                                                </h1>
                                                            </div>
                                                        <div class="flex-1">
                                                            <div class="editinfo">
                                                                <input type="text" value="{{ $user->emp->linkedin }}" name="linkedin" class="editinfo form-control ">
                                                                <span class="invalid-feedback" id="linkedin_input-error" role="alert">  <strong></strong> </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-4">
                                                    <div class="d-flex">
                                                        <div class="ml-3 mr-4">
                                                            <h1 class="display-6 mb-0">
                                                                <a href="{{ $user->emp->github }}" target="_blank" style="color: #171515"> <i class="mdi mdi-github-box"></i> </a>
                                                            </h1>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="editinfo">
                                                                <input type="git" value="{{ $user->emp->github }}" name="git" class="editinfo form-control">
                                                                <span class="invalid-feedback" id="git_input-error" role="alert">  <strong></strong> </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 mt-3 editinfo " style="margin-left: 78%;">
                                                    <button class="btn btn-outline-primary btn-cancle-forminfo">   Cancel</button> &nbsp;&nbsp;
                                                    <button type="submit" class="btn btn-primary"> Update</button>
                                                </div>
                                            </div>
                                        </form>
                                        <span class="mdi mdi-pencil-plus-outline float-end btn_edit_info"
                                            style="position: absolute;margin-right: 2%;top: 3%;right: 3%; display:block;cursor: pointer;color: #fff;;">
                                            edit
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
    </div>
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {
            var readURL = function(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.readAsDataURL(input.files[0]);
                    var postData = new FormData();
                    postData.append('file', input.files[0]);
                    $.ajax({
                        async: true,
                        type: "post",
                        url: '{{ route('profileimg') }}',
                        contentType: false,
                        data: postData,
                        processData: false,
                        success: function(response) {
                            console.log(response);
                            if (response.status == 'true') {
                                alertify.success(response.message);
                                setTimeout(() => { window.location.reload();}, 1500);
                            } else {
                                alertify.success(response.message);
                                setTimeout(() => { window.location.reload();}, 1500);
                            }

                        },
                        error: function(response) {
                            if (response.responseJSON.status === 400) {
                                let errors = response.responseJSON.errors;
                                Object.keys(errors).forEach(function(key) {
                                    alertify.error(errors[key][0]);
                                });
                            }
                        }

                    });

                }
            }

            $(".file-upload").on('change', function() {
                readURL(this);
            });
            $(".upload-button").on('click', function() {
                $(".file-upload").click();
            });
        });

        $(document).ready(function(){
            $('.btn_edit_info').click(function(e) {
                e.preventDefault();
                console.log('Click');
                $(this).siblings("form").find('.editinfolable').css('display', 'none');
                $(this).siblings("form").find('.editinfo').css('display', 'block');
                $(this).css('display', 'none');
            });
            $('.btn-cancle-forminfo').click(function(e) {
                e.preventDefault();
                $(this).parent('div').siblings("div").find('.editinfolable').css('display', 'block');
                $(this).parent('div').siblings("div").find('.editinfo').css('display', 'none');
                $(this).parent('div').css('display', 'none');
                $(this).parent().parent().parent('form').siblings('span').css('display', 'block')
            })
        })


        $('#frm_basicInfo').submit(function(e) {
            e.preventDefault();
            $(".invalid-feedback").children("strong").text("");
            $("#frm_basicInfo input").removeClass("is-invalid");
            $("#frm_basicInfo textarea").removeClass("is-invalid");
            var formData = new FormData($(this)[0]);
            $.ajax({
                type: 'POST',
                url: '{{ route('profile.update.info') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.code == 200) {
                        alertify.success(response.message);
                        setTimeout(() => { window.location.reload();}, 2000);
                    }
                },
                error: function(response) {
                    console.log(response.responseText);
                    if (response.responseJSON.status === 400) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $("#" + key + "Input").addClass("is-invalid");
                            $("#" + key + "_input-error").children("strong").text(errors[key][
                                0
                            ]);
                        });
                    }
                }
            });

        });

        $('#frm_socialinfo').submit(function(e) {
            e.preventDefault();
            $(".invalid-feedback").children("strong").text("");
            $("#frm_socialinfo input").removeClass("is-invalid");
            $("#frm_socialinfo textarea").removeClass("is-invalid");
            var formData = new FormData($(this)[0]);
            $.ajax({
                type: 'POST',
                url: '{{ route('profile.update.socialinfo') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.code == 200) {
                        alertify.success(response.message);
                        setTimeout(() => { window.location.reload();}, 2000);
                    }
                },
            });
        });

    </script>
@endsection
