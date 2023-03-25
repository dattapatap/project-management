@extends('layouts.app')
@section('content')
<style>
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
                  <li class="breadcrumb-item active"> Batch Details</li>
               </ol>
            </div>
         </div>
      </div>
   </div>
   <!-- end page title -->
   <div class="row">
      <div class="col-lg-12">
            <div id="print-area" class="printpreview">
                <table class="batchdiv" width="100%" align="center">
                    <tbody>
                        <tr class="masthead">
                            <td colspan="100" align="center">BATCH DETAILS</td>
                        </tr>

                        <tr class="odd">
                            <td><strong>Brand Name</strong></td>
                            <td>{{  $batch->brand->brand_name }}</td>
                            <td><strong>Product Name</strong></td>
                            <td> {{  $batch->product->product_name }} </td>
                        </tr>

                        <tr class="odd">
                            <td><strong>GTIN Number</strong></td>
                            <td> {{  $batch->product->gtin_no  }}  </td>
                            <td><strong>Company Name</strong></td>
                            <td>{{  $batch->brand->company_name }} </td>
                        </tr>

                        <tr class="even">
                            <td><strong>Product Description</strong></td>
                            <td colspan="3">{{ $batch->description }}</td>
                        </tr>
                        <tr class="even">
                            <td><strong>{{ $batch->batch_type }} Number</strong></td>
                            <td> {{ $batch->batch_no_detail }}</td>
                            <td><strong> Batch Sl Number</strong></td>
                            <td> {{ $batch->batch_no }}</td>

                        </tr>
                        <tr class="even">
                            <td><strong>{{ $batch->batch_type }} Size</strong></td>
                            <td> {{ $batch->batch_size }}</td>
                            <td><strong>SSCC Code</strong></td>
                            <td colspan="3">{{ $batch->sscc_code }}</td>
                        </tr>
                        <tr class="even">
                            <td><strong>Gross Weight</strong></td>
                            <td> {{ $batch->gross_weight }} </td>
                            <td><strong>Tare Weight</strong></td>
                            <td>{{ $batch->tare_weight }}</td>
                        </tr>
                        <tr class="even">
                            <td><strong>Net Weight</strong></td>
                            <td colspan="3"> {{ $batch->net_weight }} </td>
                        </tr>

                        <tr>
                            <td colspan="4"><strong class="mainheading">Quantities</strong></td>
                        </tr>
                        <tr class="even">
                            <td><strong>Drum Number</strong></td>
                            <td > {{ $batch->drum_no }} </td>
                            <td><strong>Total {{ $batch->tot_typs }}</strong></td>
                            <td > {{ $batch->tot_drums }}</td>
                        </tr>
                        <tr class="even">
                            <td><strong>Manufature Date</strong></td>
                            <td> {{ $batch->manf_date }}</td>
                            <td><strong>Expity/Retest Date</strong></td>
                            <td> {{ $batch->exp_date }}</td>
                        </tr>
                        <tr class="even">
                            <td><strong>LIC Number</strong></td>
                            <td > {{ $batch->manf_lic_no }}</td>
                            <td><strong>Item Code</strong></td>
                            <td>{{ $batch->item_code }}</td>
                        </tr>
                        <tr class="even">

                            <td><strong>Storage Condition</strong></td>
                            <td >{{ $batch->storage_condition }}</td>
                            <td ><strong>Remarks</strong></td>
                            <td >{{ $batch->remarks }}</td>
                        </tr>


                    </tbody>
                </table>
            </div>
      </div>
   </div>
   <!-- end row -->
</div>
@endsection
@section('scripts')
<script>
   $(document).ready(function(){

   })

   function isNumberKey(evt){
       var charCode = (evt.which) ? evt.which : evt.keyCode;
       if (charCode > 31 && (charCode < 48 || charCode > 57))
           return false;
       return true;
   }


</script>
@endsection
