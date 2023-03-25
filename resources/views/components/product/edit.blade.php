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
                        <li class="breadcrumb-item active">Edit</li>
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
                    <h4 class="header-title"> Edit Product </h4>
                </div>
                <div class="card-body">
                    <form class="custom-validation" action="{{ route('products.update', $product->id ) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Brand Name  <span class="text_required">*</span></label>
                                    <select class="form-control @error('brand_name') parsley-error  @enderror" name="brand_name" aria-placeholder="Brand" >
                                        <option>Select</option>
                                        @foreach ($brands as $item)
                                            <option {{ old('brand_name', $product->brand_id) == $item->id ? "selected" : "" }}
                                                value="{{ $item->id }}">{{ $item->brand_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label> Product Name  <span class="text_required">*</span></label>
                                    <input type="text" name="product_name" class="form-control @error('product_name') parsley-error  @enderror"  placeholder="Product Name"
                                    value="{{ old('product_name', $product->product_name ) }}">
                                    @error('product_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>GTIN Number  <span class="text_required">*</span></label>
                                    <input type="number" name="gtin_no" class="form-control @error('gtin_no') parsley-error  @enderror"  placeholder="GTIN Number"
                                    value="{{ old('gtin_no' , $product->gtin_no) }}">
                                    @error('gtin_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Product Description  <span class="text_required">*</span></label>
                                    <textarea  name="product_desc" class="form-control @error('product_desc') parsley-error  @enderror"
                                    placeholder="Product Name, Net Content">{{ old('product_desc' , $product->product_desc) }}</textarea>
                                    @error('product_desc')
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

</script>
@endsection


