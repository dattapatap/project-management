@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endsection


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
                        <a class="nav-link active" href="javascript:void(0);" role="tab">
                            <span class="d-none d-md-inline-block">Payment</span>
                        </a>
                    </li>
                    @endif


                </ul>




                <!-- Tab panes -->
                <div class="tab-content p-3">
                    <div class="tab-pane active" role="tabpanel">

                        <div class="heading_tab_content d-flex">
                            <h4 class="lbl-heading-pane fs-15"> Payments </h4>
                            <div class="adddocs">
                                <a class="text-info add-pay-btn fs-14" style="cursor: pointer;">
                                    <i class="mdi mdi-plus-circle-outline"></i> Add Payment
                                </a>
                            </div>
                            <div>
                                <span id="pane-timer"> <?= date('M d Y h:m:s')?></span>
                                <i class="mdi mdi-calendar-month"></i>
                            </div>
                        </div>
                        <hr>

                        <div class="col-12 p-0">
                            <table id="tbl_payments" class="table table-bordered dt-responsive nowrap"
                                style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Project Name</th>
                                    <th>Package</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th> Reference </th>
                                    <th>Added By</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>


                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- end row -->
</div>

@include('components.clients.history.visitingcard')

<div id="mdlAddPayment" class="modal fade bs-example-modal-center " role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Add New Payment</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_add_payments" class="custom-validation"  method="POST" novalidate>

                    @csrf
                    <input type="hidden" value="{{ $client->id}}" name="client" id="client">
                    <div class="row">
                        @php
                            $projects = DB::table('department_projects')->select('id', 'project_name')
                                                ->where('client', $client->id)->get();
                        @endphp
                        <div class="col-12">
                            <div class="form-group">
                                <select class="form-control select2" name="project_type" id="project_type" required >
                                    <option value="" selected> Select Project</option>

                                    @foreach ($projects as $item)
                                        <option value="{{ $item->id }}"> {{ $item->project_name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback" id="dock_type-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="">Balance</label>
                                <input type="text" id="balance" name='balance' class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for=""> Amount </label>
                                <input type="text" id="amount" name='amount' class="form-control"
                                onKeyPress="return isNumberKey(event);"  required>
                                <span class="invalid-feedback" id="amount-input-error" role="alert" >  <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-6">
                            <label> Payment Type </label>
                            <select class="form-control select2" name="payment_type" id="payment_type" tabindex="7">
                                <option selected value=''> Select Type</option>
                                <option value="Cheque"> Cheque </option>
                                <option value="Online"> Online </option>
                                <option value="Cash"> Cash </option>
                            </select>
                            <span class="invalid-feedback" id="payment_type-input-error" role="alert"><strong></strong></span>
                        </div>

                        <div class="pay_type_cheque col-6" style="display: none;">
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
                        <div class="pay_type_cash col-6" style="display: none;">
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
                        <div class="pay_type_online col-6" style="display: none;">
                            <label> Transaction Id </label>
                            <input type="text" name="transactionid" id="transactionid" class="form-control" placeholder="Transaction Id"
                            onKeyPress="return isNumberKey(event);" tabindex="11">
                            <span class="invalid-feedback" id="transactionid-input-error" role="alert"><strong></strong></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 float-roght btns_div">
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary waves-effect waves-light me-1 btn-submit creatBtn"
                                > Add Payment </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>



@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>

<script>
    $(document).ready(function(){
        $(document).on('change','.up', function(){
            $(this).closest('.form-group').find('.form-control').attr("value",$(this).get(0).files[0].name);
        });

        $("#tbl_payments").DataTable({
            processing: true,
            serverSide: true,
            bDestroy: true,
            ajax :{
                    type: 'GET',
                    data:{'client': {{ $client->id }} },
                    url: "{{ route('client.payments') }}",
                    error:function(err){ console.log(err);}
            },
            columns: [
                {data: 'DT_RowIndex', name: 'Sl No', orderable: false, searchable: false},
                {data: 'project', name: 'name', orderable: false, searchable: false},
                {data: 'packages.package', name: 'packages.package', orderable: false, searchable: true},
                {data: 'amount', name: 'amount', orderable: false, searchable: true},
                {data: 'remains', name: 'remains', orderable: false, searchable: true},
                {data: 'paid_date', name: 'paid_date', orderable: false, searchable: true},
                {data: 'payment_type', name: 'payment_type', orderable: false, searchable: true},
                {data: 'referance', name: 'referance', orderable: false, searchable: false},
                {data: 'addedBy.name', name: 'addedBy.name', orderable: false, searchable: true},
            ]
        });

        $('.add-pay-btn').click(function(){
            $('#mdlAddPayment').modal('show');
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
            }else if(transactiontype == 'Online'){
                $('.pay_type_cash').css('display', 'none');
                $('.pay_type_cheque').css('display', 'none');
                $('.pay_type_online').css('display', 'block');
            }else{
                $('.pay_type_cash').css('display', 'none');
                $('.pay_type_cheque').css('display', 'none');
                $('.pay_type_online').css('display', 'none');
            }
        })


        $('#project_type').change(function(e){
            let project = $(this).val();
            $('#balance').val('')
            if(project != ''){
                $('#balance').val('')
                $.ajax({
                    type: 'GET',
                    url: "{{ route('client.getPendingPayments.byProject') }}",
                    data: {'project': project},
                    success: function(response) {
                        $('#balance').val(response.balance)
                    }
                });
            }
        })

        $('#frm_add_payments').on('submit', function(e){
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({
                type: 'POST',
                url: '{{ route('client.addPayment') }}',
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
                        $('#frm_add_payments')[0].reset();
                        alertify.success(response.message);
                        setTimeout(() => { window.location.reload();}, 1500);
                    } else {
                        alertify.error(response.message);
                        $(".creatBtn").prop('disabled', false);
                        $(".creatBtn").html('Add Payment');
                    }
                },
                error: function(response) {
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Add Payment');
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


    });
</script>



@endsection
