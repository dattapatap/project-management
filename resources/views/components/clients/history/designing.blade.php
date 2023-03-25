@extends('layouts.app')

@section('content')


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
                        <a class="nav-link" @if($user->hasRole([1,2,3,4,5])) href="{{ url('clients/'.base64_encode($client->id).'/'.'dsr' ) }}" @endif
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
                        <a class="nav-link active" href="javascript:void(0);" role="tab">
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
                                <span id="pane-timer"> <?= date('M d Y h:m:s')?></span>
                                <i class="mdi mdi-calendar-month"></i>
                            </span>
                        </div>

                        <hr>

                        <div class="dsr_content_form">
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
                                                    <select class="form-control" name="tbro_type" id="tbro_type" width="100%"  tabindex="2">
                                                        <option selected value> Select</option>
                                                        <option value="Call" > Call </option>
                                                        <option value="Direct visit" selected> Direct visit </option>
                                                    </select>
                                                    <span class="invalid-feedback" id="tbro_type-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label>Time : </label>
                                                    <input type="text" name="tbro_time" id="tbro_time" class="form-control"
                                                    placeholder="HH:MM A"  tabindex="3" >
                                                    <span class="invalid-feedback" id="tbro_time-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label>TBRO :</label>
                                                    <input type="text" name="tbro_date" id="tbro_date" class="form-control"
                                                    placeholder="MM/DD/YYYY"  tabindex="4">
                                                    <span class="invalid-feedback" id="tbro_date-input-error" role="alert"><strong></strong></span>
                                                </div>

                                            </div>
                                            <div class="col-3">
                                                @php
                                                        $stsStatus = App\Models\ParentStatus::where('category', 'DSR')->get();
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

                                        <div class="row payment_field ml-0 mr-0 w-100" style="display: none;">

                                            <div class="col-3">
                                                <label> Proforma </label>
                                                <div class="custom-file">
                                                    <input type="file" name="proforma" id="proforma" class="custom-file-input" accept="image/*, .pdf">
                                                    <label class="custom-file-label" for="validationCustomFile">Choose file</label>
                                                    <span class="invalid-feedback" id="proforma-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>

                                            <div class="col-3">
                                                <label> Payment Type </label>
                                                <select class="form-control" name="payment_type" id="payment_type" width="100%"  tabindex="4">
                                                    <option selected value> Select Type</option>
                                                    <option value="Cheque"> Cheque </option>
                                                    <option value="Online"> Online </option>
                                                </select>
                                                <span class="invalid-feedback" id="payment_type-input-error" role="alert"><strong></strong></span>
                                            </div>
                                            <div class="col-4">
                                                <label> Cheque/Receipt </label>
                                                    <input type="file" name="payment_receipt" id="payment_receipt" class="custom-file-input" accept="image/*, .pdf">
                                                    <span class="invalid-feedback" id="payment_receipt-input-error" role="alert"><strong></strong></span>
                                                </div>
                                            </div>4

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
                                                <span class="fw-500"> {{ $client->referral->name }} ( {{  $client->referral->mobile }})</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row float-roght">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                            Create DSR
                                        </button>
                                    </div>
                                </div>

                            </form>



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
                $('.payment_field').css('display', 'none');
            }

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
                        $('.frm_dsr_update')[0].reset();
                        alertify.success(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('Create DSR');
                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('Create DSR');
                    }
                },
                error: function(response) {

                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Create DSR');
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
