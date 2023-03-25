@extends('layouts.app')

@section('content')

<style>
</style>

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="pb-2 d-flex align-items-center justify-content-between">
                <a href="{{ url('client/Fresh')  }}" class="btn-back" >
                    <i class="mdi mdi-keyboard-backspace fs-20"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">

        <div class="card card-top-border cw-100">
            <div class="card-body">
                <!-- Header company details -->
                @include('components.clients.history.header')

                <ul class="nav nav-tabs nav-dept mt-3" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('clients/'.base64_encode($client->id).'/'.'contacts' ) }}" >
                            <span class="d-none d-md-inline-block">Contacts</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" @if($user->hasRole([1,2,3,4,5])) href="{{ url('clients/'.base64_encode($client->id).'/'.'sts' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">STS</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"
                            role="tab">
                            <span class="d-none d-md-inline-block">DSR</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            @if($user->hasRole([1,3,4,6])) href="{{ url('clients/'.base64_encode($client->id).'/'.'development' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">Development</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            @if($user->hasRole([1,3,4,7])) href="{{ url('clients/'.base64_encode($client->id).'/'.'designing' ) }}" @endif  role="tab">
                            <span class="d-none d-md-inline-block">Designing</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            @if($user->hasRole([1,3,4,8])) href="{{ url('clients/'.base64_encode($client->id).'/'.'seo' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">Digital Marketing</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('clients/'.base64_encode($client->id).'/'.'history' ) }}" role="tab">
                            <span class="d-none d-md-inline-block">History</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('clients/'.base64_encode($client->id).'/'.'docs' ) }}" role="tab">
                            <span class="d-none d-md-inline-block">Documents</span>
                        </a>
                    </li>

                    @if($client->is_active)
                    <li class="nav-item">
                        <a class="nav-link"  @if($user->hasRole([1,2,3,4,5])) href="{{ url('clients/'.base64_encode($client->id).'/'.'payment' ) }}" @endif role="tab">
                            <span class="d-none d-md-inline-block">Payment</span>
                        </a>
                    </li>
                    @endif


                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-3">
                    <div class="tab-pane active" role="tabpanel">

                        <div class="heading_tab_content d-flex">
                            <h4 class="lbl-heading-pane fs-15"> DSR Update</h4>
                            <span>
                                @php
                                    $lstupdate = DB::table('client_histories')->where('client', $client->id)
                                    ->where('category', 'DSR')->orderBy('id','desc')->first();
                                @endphp
                                <span> Last Updated :
                                    @if($lstupdate) {{ Carbon\Carbon::parse($lstupdate->created_at)->format('d M Y h:i') }}  @endif
                                </span>
                            </span>
                            <span>
                                <span id="pane-timer"> <?= date('M d Y h:m:s')?></span>
                                <i class="mdi mdi-calendar-month"></i>
                            </span>
                        </div>

                        <hr>
                        <div class="dsr_content_form">
                            @if($client && $client->status=="Matured")
                                <form class="frm_matured_dsr_update">
                                    <input type="hidden" value="{{ $client->id}}" name="client_id" id="client_id">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea class="form-control" name="dsr_remarks" id="dsr_remarks" rows="3" placeholder="Remarks" tabindex="1"
                                                autocomplete="off"></textarea>
                                                <span class="invalid-feedback" id="dsr_remarks-input-error" role="alert"><strong></strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div style="width:70%">
                                            <div class="row ml-0 mr-0 w-100">
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label>TBRO Type : </label>
                                                        <input type="text" value="Direct Visit" class="form-control" name="tbro_type" id="tbro_type"
                                                        autocomplete="off" readonly>
                                                        <span class="invalid-feedback" id="tbro_type-input-error" role="alert"><strong></strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label>Time : </label>
                                                        <input type="text" name="tbro_time" id="tbro_time" class="form-control"
                                                        placeholder="HH:MM A"  tabindex="3" autocomplete="off" >
                                                        <span class="invalid-feedback" id="tbro_time-input-error" role="alert"><strong></strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div  style="width:30%">
                                            <div class="col-12 text-right">
                                                <span class="fs-14 fw-600">
                                                        Sales Executive :
                                                        <span class="fw-500"> {{ $client->referral->name }} ( {{  $client->referral->mobile }})</span>
                                                </span>
                                            </div>
                                            <div class="col-12 mt-2 text-right">
                                                <span class="fs-14 fw-600">
                                                    Tele/CC Executive :
                                                    <span class="fw-500"> {{ $client->telereferral->name }} ( {{  $client->telereferral->mobile }})</span>
                                                </span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row float-roght">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                                UPDATE DSR
                                            </button>
                                        </div>
                                    </div>

                                </form>
                            @else
                                <form class="frm_dsr_update">
                                    <input type="hidden" value="{{ $client->id}}" name="client_id" id="client_id">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea class="form-control" name="dsr_remarks" id="dsr_remarks" rows="3" placeholder="Remarks" tabindex="1"></textarea>
                                                <span class="invalid-feedback" id="dsr_remarks-input-error" role="alert"><strong></strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div style="width:70%">
                                            <div class="row ml-0 mr-0 w-100">
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label>TBRO Type : </label>
                                                        <input type="text" value="Direct Visit" class="form-control" name="tbro_type" id="tbro_type" readonly autocomplete="off">
                                                        <span class="invalid-feedback" id="tbro_type-input-error" role="alert"><strong></strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label>Time : </label>
                                                        <input type="text" name="tbro_time" id="tbro_time" class="form-control"
                                                        placeholder="HH:MM A"  tabindex="3" autocomplete="off">
                                                        <span class="invalid-feedback" id="tbro_time-input-error" role="alert"><strong></strong></span>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label>TBRO :</label>
                                                        <input type="text" name="tbro_date" id="tbro_date" class="form-control"
                                                        placeholder="MM/DD/YYYY"  tabindex="4" autocomplete="off">
                                                        <span class="invalid-feedback" id="tbro_date-input-error" role="alert"><strong></strong></span>
                                                    </div>

                                                </div>
                                                <div class="col-3">
                                                    @php
                                                            $stsStatus = App\Models\ParentStatus::where('category', 'DSR')
                                                                                ->whereIn('name' ,['Not Interested', 'Matured', 'Hot Prespective', 'Warm Prespective'])->get();
                                                    @endphp
                                                    <label>STS Status :</label>
                                                    <select class="form-control" id="dsr_status" name="dsr_status" width="100%"  tabindex="5">
                                                        <option selected value="">Select DSR status</option>
                                                        @foreach ($stsStatus as $item)
                                                            <option value="{{$item->name}}"> {{$item->name}} </option>
                                                        @endforeach
                                                    </select>
                                                    <span class="invalid-feedback" id="dsr_status-input-error" role="alert"><strong></strong></span>
                                                </div>

                                            </div>
                                        </div>

                                        <div  style="width:30%">
                                            <div class="col-12 text-right">
                                                <span class="fs-14 fw-600">
                                                        Sales Executive :
                                                        <span class="fw-500"> {{ $client->referral->name }} ( {{  $client->referral->mobile }})</span>
                                                </span>
                                            </div>
                                            <div class="col-12 mt-2 text-right">
                                                <span class="fs-14 fw-600">
                                                    Tele/CC Executive :
                                                    <span class="fw-500"> {{ $client->telereferral->name }} ( {{  $client->telereferral->mobile }})</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row payment_field ml-0 mr-0 w-100" style="display: none;">
                                            <div class="col-3">
                                                <label> Proforma </label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" readonly>
                                                        <div class="input-group-btn">
                                                            <span class="fileUpload btn btn-primary">
                                                            <span class="upl" id="upload">Upload</span>
                                                            <input type="file" class="upload up" id="proforma" name="proforma" accept="image/*, .pdf"  tabindex="6" />
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="invalid-feedback" id="proforma-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>

                                            <div class="col-2">
                                                <label> Payment Type </label>
                                                <select class="form-control" name="payment_type" id="payment_type" width="100%"  tabindex="7">
                                                    <option selected value> Select Type</option>
                                                    <option value="Cheque"> Cheque </option>
                                                    <option value="Online"> Online </option>
                                                    <option value="Cash"> Cash </option>
                                                </select>
                                                <span class="invalid-feedback" id="payment_type-input-error" role="alert"><strong></strong></span>
                                            </div>
                                            <div class="col-2">
                                                <label> Package </label>
                                                <input type="text" name="package" id="package" class="form-control"  placeholder="Package Amount"
                                                onKeyPress="return isNumberKey(event);" tabindex="8" autocomplete="off">
                                                <span class="invalid-feedback" id="package-input-error" role="alert"><strong></strong></span>
                                            </div>
                                            <div class="col-2">
                                                <label> Advance </label>
                                                <input type="text" name="advance" id="advance" class="form-control"  placeholder="Advance Amount"
                                                onKeyPress="return isNumberKey(event);" tabindex="8" autocomplete="off">
                                                <span class="invalid-feedback" id="advance-input-error" role="alert"><strong></strong></span>
                                            </div>

                                            <div class="pay_type_cheque col-3" style="display: none;">
                                                <label> Cheque </label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" readonly>
                                                        <div class="input-group-btn">
                                                            <span class="fileUpload btn btn-primary">
                                                            <span class="upl" id="upload">Upload</span>
                                                            <input type="file" class="upload up" id="payment_cheque_receipt" name="payment_cheque_receipt" accept="image/*"  tabindex="9"  />
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="invalid-feedback" id="payment_cheque_receipt-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>
                                            <div class="pay_type_cash col-3" style="display: none;">
                                                <label> Challan </label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" readonly>
                                                        <div class="input-group-btn">
                                                            <span class="fileUpload btn btn-primary">
                                                            <span class="upl" id="upload">Upload</span>
                                                            <input type="file" class="upload up" id="payment_cash_receipt" name="payment_cash_receipt" accept="image/*"  tabindex="10"  />
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="invalid-feedback" id="payment_cash_receipt-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>
                                            <div class="pay_type_online col-3" style="display: none;">
                                                <label> Transaction Id </label>
                                                <input type="text" name="transactionid" id="transactionid" class="form-control" placeholder="Transaction Id"
                                                onKeyPress="return isNumberKey(event);" tabindex="11" autocomplete="off">
                                                <span class="invalid-feedback" id="transactionid-input-error" role="alert"><strong></strong></span>
                                            </div>

                                        </div>



                                        <div class="row payment_field ml-0 mr-0 w-100" style="display: none;">
                                            <div class="col-3">
                                                <label> Category </label>
                                                <div class="form-group">
                                                    @php
                                                        $departments = DB::table('project_category')->where('deleted_at', null)->orderBy('id', 'asc')->get();
                                                    @endphp
                                                    <select class="form-control" name="category" id="category" width="100%"  tabindex="12">
                                                        <option selected value> Select Category</option>
                                                        @foreach ($departments as $item)
                                                            <option value="{{ $item->id }}"> {{ $item->category }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="invalid-feedback" id="category-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <label> Sub-Category </label>
                                                <div class="form-group">
                                                    <select class="form-control select2" name="sub_category" id="sub_category" style="width:100%"  tabindex="13">
                                                        <option selected value> Select Sub-Category</option>
                                                    </select>
                                                    <span class="invalid-feedback" id="sub_category-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row float-roght">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                                UPDATE DSR
                                            </button>
                                        </div>
                                    </div>

                                </form>
                            @endif
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- end row -->
</div>


@include('components.clients.history.visitingcard')

@endsection

@section('scripts')
<script>


    $(document).ready(function(){
        $(document).on('change','.up', function(){
            $(this).closest('.form-group').find('.form-control').attr("value",$(this).get(0).files[0].name);
        });

        $('#payment_type').change(function(){
            let transactiontype = $(this).val();
            if(transactiontype == 'Cheque'){
                $('.pay_type_online').css('display', 'none');
                $('.pay_type_cash').css('display', 'none');
                $('.pay_type_cheque').css('display', 'block');
            }else if(transactiontype == 'Cash'){
                $('.pay_type_online').css('display', 'none');
                $('.pay_type_cheque').css('display', 'none');
                $('.pay_type_cash').css('display', 'block');
            }else{
                $('.pay_type_cash').css('display', 'none');
                $('.pay_type_cheque').css('display', 'none');
                $('.pay_type_online').css('display', 'block');

            }
        })

        $('#category').change(function(){
            let dept_value = $(this).val();
            $('#sub_category').empty().append('<option selected="selected" value="">Select Sub-Category</option>');
            $.ajax({
                type: 'GET',
                url: base_url +"/projectcategory/subcategories",
                data: {'projcategory' : dept_value },
                success: function(response) {
                    if(response.status = true){
                        $("#sub_category").select2({data :response.data });
                    }else{

                    }
                },
            });
        })


        $('#tbro_time').datetimepicker({
            format: 'hh:mm A',
            useCurrent:true,
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

        $('#tbro_date').datetimepicker({
            format:'DD-MM-YYYY',
            maxDate: moment().add(90, 'days'),
            minDate: moment(),
            useCurrent:false,
        });


        $('#dsr_status').on('change',function(e){
            if($(this).val() === 'Matured'){
                $('.payment_field').css('display', 'flex');
            }else{
                $('#payment_type').val("").trigger('change');
                $('.payment_field').css('display', 'none');
            }
        });

        $('.frm_matured_dsr_update').submit(function(e){
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({

                type: 'POST',
                url: '{{ route('client.updateDsr') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".creatBtn").html('Submiting...');
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    if (response.status == true) {
                        if($('#dsr_status').val() == 'Matured'){
                            setTimeout(() => { window.location.reload();}, 1500);
                        }
                        $('.frm_matured_dsr_update')[0].reset();
                        alertify.success(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('UPDATE DSR');
                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('UPDATE DSR');
                    }
                },
                error: function(response) {
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('UPDATE DSR');
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


        $('.frm_dsr_update').submit(function(e){
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({

                type: 'POST',
                url: '{{ route('client.createDsr') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".creatBtn").html('Submiting...');
                    $(".creatBtn").prop('disabled', true);
                },
                success: function(response) {
                    if (response.status == true) {
                        if($('#dsr_status').val() == 'Matured'){
                            setTimeout(() => { window.location.reload();}, 1500);
                        }
                        $('.frm_dsr_update')[0].reset();
                        alertify.success(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('UPDATE DSR');
                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('UPDATE DSR');
                    }
                },
                error: function(response) {
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('UPDATE DSR');
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
</script>
@endsection
