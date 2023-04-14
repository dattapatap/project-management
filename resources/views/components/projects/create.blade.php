@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="pb-2 d-flex align-items-center justify-content-between">
                    <a href="{{ url('/projects') }}" class="btn-back" >
                        <i class="mdi mdi-keyboard-backspace fs-20"></i>
                    </a>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/projects') }}">Projects</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title"> New Project </h4>
                </div>
                <div class="card-body">
                    <form id="frm_create_new_project" class="custom-validation"  method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-4">
                                    <label> Client <span class="text_required">*</span></label>
                                    <select class="form-control select2" name="clientsid" id="clientsid" width="100%" >
                                        <option selected value> Select Department</option>
                                        @foreach ($clients as $item)
                                            <option value="{{ $item->id }}"> {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback" id="clientsid-input-error" role="alert"> <strong></strong></span>
                            </div>

                            <div class="col-4">
                                <label> Project Department( Category ) <span class="text_required">*</span></label>
                                @php
                                    $departments = DB::table('project_category')->where('deleted_at', null)->orderBy('id', 'asc')->get();
                                @endphp
                                <select class="form-control select2" name="department" id="department" width="100%"  >
                                    <option selected value> Select Category</option>
                                    @foreach ($departments as $item)
                                        <option value="{{ $item->id }}"> {{ $item->category }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback" id="department-input-error" role="alert"><strong></strong></span>


                            </div>
                            <div class="col-4">
                                <label> Sub Category </label>
                                <div class="form-group">
                                    <select class="form-control select2" name="category" id="category" style="width:100%"  tabindex="3">
                                        <option selected value> Select Sub Category</option>
                                    </select>
                                    <span class="invalid-feedback" id="category-input-error" role="alert"><strong></strong></span>
                                </div>
                            </div>
                            <div class="col-3">
                                <label> Package <span class="text_required">*</span></label>
                                <div class="form-group">
                                    <input type="number" class="form-control" name="package" id="package" placeholder="Package" tabindex="4"
                                    onKeyPress="return isNumberKey(event);">
                                    <span class="invalid-feedback" id="package-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-3">
                                <label> Estimate Start Date <span class="text_required">*</span></label>
                                <div class="form-group">
                                    <input type="date" class="form-control" name="start_date" id="start_date" placeholder="Start Date"
                                    max="<?= date('Y-m-d', strtotime(date('Y-m-d').' +10 days')); ?>" tabindex="5">
                                    <span class="invalid-feedback" id="start_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-3">
                                <label>Estimate End Date </label>
                                <div class="form-group">
                                    <input type="date" class="form-control" name="end_date" id="end_date" placeholder="End Date" tabindex="6"
                                    min="<?= date('Y-m-d'); ?>">
                                    <span class="invalid-feedback" id="end_date-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>
                            <div class="col-3">
                                <label> Reference Link</label>
                                <div class="form-group">
                                    <input type="url" class="form-control" name="referel_link" id="referel_link" placeholder="Referel Link" tabindex="7">
                                    <span class="invalid-feedback" id="referel_link-input-error" role="alert"> <strong></strong></span>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12 float-right">
                                <label> Project Description <span class="text_required">*</span></label>
                                <textarea class="form-control" name="description" id="description"></textarea>
                                <span class="invalid-feedback" id="description-input-error" role="alert"> <strong></strong></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mt-3 float-roght btns_div">
                                <div class="float-right">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                    > Create Project </button>
                                </div>
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
$(document).ready(function(){

    tinymce.init({
            selector: 'textarea#description',
            branding: false,
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table paste codesample"
            ],
            toolbar: "undo redo | fontselect styleselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | codesample action section button",
            font_formats:"Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino;",
            fontsize_formats: "8px 9px 10px 11px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px 38px 40px 42px 44px 46px 48px 50px 52px 54px",
            height: 300
    });

    $(document).on('change', '#department', function(){
        let dept_value = $(this).val();
        $('#category').empty().append('<option selected="selected" value="">Select Category</option>');
        $.ajax({
            type: 'GET',
            url: base_url + "/projectcategory/subcategories",
            data: {'projcategory' : dept_value },
            success: function(response) {
                if(response.status = true){
                    $("#category").select2({data :response.data });
                }else{

                }
            },
        });
    })

    $('#frm_create_new_project').submit(function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");

        $.ajax({
            type: 'POST',
            url: base_url +'/client/createprojecct',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".creatBtn").html('Creating...');
                $(".creatBtn").prop('disabled', true);
            },
            success: function(response) {
                console.log(response);
                if (response.status == true) {
                    $('#frm_create_new_project')[0].reset();
                    alertify.success(response.message);
                    window.location.href = "{{URL::to('/projects')}}"

                } else {
                    alertify.error(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Create Project');
                }
            },
            error: function(response) {
                $(".creatBtn").prop('disabled', false);
                $(".creatBtn").html('Create Project');
                if (response.responseJSON.status === 400) {
                    let errors = response.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        $("#" + key + "Input").addClass("is-invalid");
                        $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                    });
                }
            }

        });
    })


})

</script>


@endsection
