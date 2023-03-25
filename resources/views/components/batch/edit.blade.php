@extends('layouts.app')

@section('content')

<style>
     select[readonly].select2-hidden-accessible + .select2-container {
        pointer-events: none;
        touch-action: none;
    }

    select[readonly].select2-hidden-accessible + .select2-container .select2-selection {
        background: #eee;
        box-shadow: none;
    }

    select[readonly].select2-hidden-accessible + .select2-container .select2-selection__arrow, select[readonly].select2-hidden-accessible + .select2-container .select2-selection__clear {
        display: none;
    }
</style>

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Batch</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/batches') }}">Batch</a></li>
                        <li class="breadcrumb-item active">Edit Batch</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title"> SSCC Code - {{ $batch->sscc_code }} </h4>
                </div>
                <div class="card-body">
                    <form class="custom-validation" id="frm_batch" method="POST">
                        <div class="row">
                            <input type="hidden" name="batch_id" value="{{ $batch->id}}" class="form-control">
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Brand Name  <span class="text_required">*</span></label>
                                    <select class="form-control select2" name="batch_name" id="batch_name" aria-placeholder="Brand" readonly>
                                        <option>Select</option>
                                        @foreach ($brands as $item)
                                            <option value="{{ $item->id }}" @if($batch->brand_id ==  $item->id) selected @endif  >{{ $item->brand_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback" id="batch_name-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Product <span class="text_required">*</span></label>
                                    <select class="form-control select2" name="product" id="product" aria-placeholder="Product"readonly >
                                        <option value="{{ $batch->product_id }}" selected> {{ $batch->product->product_name }} </option>
                                    </select>
                                    <span class="invalid-feedback" id="product-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>GTIN Number <span class="text_required">*</span></label>
                                    <input type="number" name="gtin_number" id="gtin_number" class="form-control"
                                    value="{{ $batch->gtin_no }}" placeholder="GTIN Number" readonly style="    background-color: #eee;">
                                    <span class="invalid-feedback" id="gtin_number-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Product Description</label>
                                    <input type="text" name="product_description" id="product_description" class="form-control"
                                    value="{{ $batch->description }}" placeholder="Product Description">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Batch/Lot No.</label>
                                    <div class="input-group mt-3 mt-sm-0 mr-sm-3">
                                        <input type="text" name="batch_no_detail" class="form-control"  placeholder="Batch Number"
                                        value="{{$batch->batch_no_detail}}">
                                        <div class="input-group-prepend">
                                            <select class="form-control" name="batch_type" id="batch_type" aria-placeholder="Type" style="border-radius: 0;">
                                                <option value="BATCH" @if($batch->batch_type == "BATCH") selected @endif>Batch No</option>
                                                <option value="LOT" @if($batch->batch_type == "LOT") selected @endif>Lot No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <span class="invalid-feedback" id="batch_no_detail-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Batch Sl. Number</label>
                                    <input type="text" class="form-control" value="{{$batch->batch_no}}" readonly style="background-color: #eee;">
                                </div>
                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label>Batch/Lot Size <span class="text_required">*</span></label>
                                    <input type="text" name="batch_size" class="form-control"  placeholder="Batch Size"
                                    onkeyup="this.value = this.value.toUpperCase();" value="{{ $batch->batch_size }}">
                                    <span class="invalid-feedback" id="batch_size-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label> Gross Weight </label>
                                    <input type="text" name="gross_weight" class="form-control"  placeholder="Grass Weight" value="{{ $batch->gross_weight }}">
                                </div>
                            </div>
                            <div class="col-1">
                                <div class="form-group">
                                    <label> Tare Weight</label>
                                    <input type="text" name="tare_weight" class="form-control"  placeholder="Tare Weight" value="{{ $batch->tare_weight }}">

                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label> Net Weight</label>
                                   <input type="text" name="net_weight" class="form-control"  placeholder="Net Weight" value="{{ $batch->net_weight }}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">

                            <div class="col-2">
                                <div class="form-group">
                                    <label>Total (BAG/DRUM/BOX)<span class="text_required">*</span></label>
                                    <div class="input-group mt-3 mt-sm-0 mr-sm-3">
                                        <input type="text" name="total" class="form-control"  placeholder="Total"
                                        onKeyPress="return isNumberKey(event);" onpaste="return false;" value="{{ $batch->tot_drums }}">
                                        <div class="input-group-prepend">
                                            <select class="form-control" name="total_type" id="total_type" aria-placeholder="Type" style="border-radius: 0;">
                                                <option value="DRUM" @if($batch->tot_type == "DRUM") selected @endif>DRUM</option>
                                                <option value="BAG" @if($batch->tot_type ==  "BAG") selected @endif >BAG</option>
                                                <option value="BOX" @if($batch->tot_type ==  "BOX") selected @endif >BOX</option>
                                            </select>
                                        </div>
                                    </div>
                                    <span class="invalid-feedback" id="total-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>

                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label> Drum Number <span class="text_required">*</span></label>
                                    <input type="text" name="drum_number" class="form-control"  placeholder="Drum Number" value="{{  $batch->drum_no }}"
                                    onKeyPress="return isNumberKey(event);" onpaste="return false;">
                                    <span class="invalid-feedback" id="drum_number-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label>Item Code</label>
                                    <input type="text" name="item_code" class="form-control"  placeholder="Item Code"
                                    value="{{ $batch->item_code }}">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Manufacture Date</label>
                                    <input type="text" name="manufature_date" id="manufature_date" class="form-control"  placeholder="Manufature Date"
                                    value="{{ $batch->manf_date }}">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Expiry/Retest Date</label>
                                    <input type="text" name="expiry_date" id="expiry_date" class="form-control"  placeholder="Expiry Date"
                                    value="{{ $batch->exp_date }}">
                                </div>
                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label>LIC. No.</label>
                                    <input type="text" name="man_lic_number" class="form-control"  placeholder="Man. lic No."
                                    value="{{ $batch->manf_lic_no }}">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label>Special Storage Conditions</label>
                                    <input type="text"  name="storage_condition" class="form-control"  placeholder="Special Storage Condition" value="{{ $batch->storage_condition  }}">
                                    <span class="invalid-feedback" id="storage_condition-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <input type="text"  name="remarks" class="form-control"  placeholder="Remarks" value="{{ $batch->remarks  }}">
                                    <span class="invalid-feedback" id="remarks-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                        </div>

                        <hr>
                        <div class="row float-roght">
                            <div class="col-12">
                                <button type="submit" class="creatBtn btn btn-primary waves-effect waves-light mr-1 float-right">
                                    UPDATE
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
        $('.select2').select2();

        $('#batch_name').change(function(){
            let brand_value = $(this).val();
            $('#product').empty().append('<option selected="selected" value="">Select Product</option>');
            $('#gtin_number').val('');
            $('#product_description').val('');
            $.ajax({
                type: 'GET',
                url: "{{ route('products.allproductsbybrandid') }}",
                data: {'brandid' : brand_value },
                success: function(response) {
                    if(response.status = true){
                        $("#product").select2({data :response.data });
                    }
                },
            });



        })

        $('#product').change(function(){
            let product_value = $(this).val();
            $('#gtin_number').val('');
            $('#product_description').val('');
            $.ajax({
                type: 'GET',
                url: "{{ route('products.getProductByid') }}",
                data: {'productid' : product_value },
                success: function(response) {
                    $("#gtin_number").val(response.data.gtin_no);
                    $("#product_description").val(response.data.product_desc);
                },
            });
        })


        $('#frm_batch').submit(function(e){

            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({
                type: 'POST',
                url: '{{ route('batches.updateBatch') }}',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".creatBtn").html('Updating..');
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    if (response.status == true) {
                        $('#frm_batch')[0].reset();
                        alertify.success(response.message);
                        setTimeout(() => {
                            window.location.href = "{{ route('batches.index')}}";
                        }, 1000);

                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('UPDATE');
                    }

                },
                error: function(response) {
                    console.log(response);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('UPDATE');
                    if (response.responseJSON.status === 400) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            $("#" + key + "Input").addClass("is-invalid");
                            $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                        });
                    }
                }
            });
        });



    })

    function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }


</script>
@endsection
