@extends('layouts.app')

@section('content')


<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Products</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/products') }}">Product</a></li>
                        <li class="breadcrumb-item active">Upload</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="offset-3 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title"> Upload Product </h4>
                </div>
                <div class="card-body">
                    <form id="productsUpload" class="custom-validation" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="alert alert-danger errors" style="display:none;">
                            <ul>
                            </ul>
                            <span><span>
                        </div>
                        <div class="alert alert-success success" style="display:none;">
                        </div>


                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Brand Name  <span class="text_required">*</span></label>
                                    <select class="form-control @error('brand_name') parsley-error  @enderror" name="brand_name" aria-placeholder="Brand" >
                                        <option>Select</option>
                                        @foreach ($brands as $item)
                                            <option {{ old('brand_name') == $item->id ? "selected" : "" }}  value="{{ $item->id }}">{{ $item->brand_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback" id="brand_name-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Product File (.xlsx)  <span class="text_required">*</span></label>
                                    <span> For File Format
                                        <a href="{{ asset('docs/import.xlsx')}}" target="_blank"> Click </a>
                                    </span>
                                    <br>
                                    <input type="file"  name="product_file" class="form-control @error('product_file') parsley-error  @enderror"
                                    placeholder="Product file" />
                                    <span class="invalid-feedback" id="product_file-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row float-roght">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right uploadbtn">
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
    $(document).ready(function(){
        $("#activation_date").datepicker({
            todayHighlight: true,
            autoclose: true,
            format: 'dd-mm-yyyy',
            endDate: new Date()
        })
        $("#deactivate_date").datepicker({
            todayHighlight: true,
            autoclose: true,
            format: 'dd-mm-yyyy',
            startDate : new Date(),
        })
    })


    $('#productsUpload').submit(function(e) {
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");
        $("#productsUpload input").removeClass("is-invalid");
        $('.errors ul').empty();
        $('.errors span').empty();
        $('.success').empty();
        $('.errors').hide();
        $('.success').hide();

        $.ajax({
            type: 'POST',
            url: "{{ route('products.uploadFile') }}",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".uploadbtn").html('Uploading..');
                $(".uploadbtn").prop('disabled', true);
            },
            success: function(data) {
                if (data.code == 200) {
                    $('#productsUpload')[0].reset();
                    alertify.success(data.message);
                } else if (data.code === 201) {
                    $('.errors').append('<span>' + data.message + '</span>');
                    $('.errors').show();
                    $(".uploadbtn").prop('disabled', false); // enable button
                    $(".uploadbtn").html('Upload');

                } else if (data.code === 202) {

                    $(".uploadbtn").prop('disabled', false); // enable button
                    $(".uploadbtn").html('Upload');

                    var errors = data.error;
                    var lng = Object.keys(errors).length;
                    if (lng > 0) {
                        var list = '';
                        for (var key in errors) {
                            var inError = errors[key];
                            for (var txt in inError) {
                                console.log(inError[txt]);
                                list += '<li> Row ' + inError[txt]['row'] + ' - ' + inError[txt]['error'] + '</li>';
                            }
                        }
                        $('.errors ul').append(list);
                        $('.errors span').append(lng + ' - rows are not added, Please try again with correction of data');
                        $('.errors').show();
                    }

                    if (data.totRows > 0) {
                        $('.success').append('<span>' + data.totRows + ' Rows Uploaded </span>');
                        $('.success').show();
                    }

                } else if (data.code === 203) {
                    $(".uploadbtn").prop('disabled', false);
                    $(".uploadbtn").html('Save');
                    var errors = data.error;
                    var lng = Object.keys(errors).length;
                    if (lng > 0) {
                        var list = '';
                        for (var key in errors) {
                            var inError = errors[key];
                            for (var txt in inError) {
                                console.log(inError[txt]);
                                list += '<li> Row ' + inError[txt]['row'] + ' - ' + inError[txt]['error'] + '</li>';
                            }
                        }
                        $('.errors ul').append(list);
                        $('.errors span').append(lng + ' - rows are not added, Please try again with correction of data');
                        $('.errors').show();
                    }
                }
            },

            error: (response) => {
                console.log(response);
                $(".uploadbtn").prop('disabled', false);
                $(".uploadbtn").html('Upload');
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



</script>
@endsection


