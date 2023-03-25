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
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Payments</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Payments</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="payments" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Company</th>
                                        <th>Project</th>
                                        <th>Package</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Created</th>
                                        <th>Action</th>
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

<div id="mdlPaymentHistory" class="modal fade bs-example-modal-center " role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mdldomain mt-0">Payment History</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-12 p-0">
                    <table id="tbl_payment_history" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-light">
                        <tr>
                            <th>Sl No</th>
                            <th>Date</th>
                            <th>Paid</th>
                            <th>Balance</th>
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

<div id="mdlAddPayment" class="modal fade bs-example-modal-center " role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Add Entry</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_add_payments" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <div class="row">
                        <input type="hidden"  name="project_type" id="project_type" >
                        <input type="hidden"  name="client" id="client" >
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
                                onKeyPress="return isNumberKey(event);"  required tabindex="1">
                                <span class="invalid-feedback" id="amount-input-error" role="alert" >  <strong></strong></span>
                            </div>
                        </div>

                        <div class="col-6">
                            <label> Payment Type </label>
                            <select class="form-control select2" name="payment_type" id="payment_type" tabindex="2">
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
                                        <input type="file" class="upload up" id="payment_cheque_receipt" name="payment_cheque_receipt" accept="image/*"  tabindex="3"  />
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
                                        <input type="file" class="upload up" id="payment_cash_receipt" name="payment_cash_receipt" accept="image/*"  tabindex="4"  />
                                        </span>
                                    </div>
                                </div>
                                <span class="invalid-feedback" id="payment_cash_receipt-input-error" role="alert"><strong></strong></span>
                            </div>
                        </div>
                        <div class="pay_type_online col-6" style="display: none;">
                            <label> Transaction Id </label>
                            <input type="text" name="transactionid" id="transactionid" class="form-control" placeholder="Transaction Id"
                            onKeyPress="return isNumberKey(event);" tabindex="5">
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
<script src="{{ asset('js/payments.js')}}"></script>
@endsection
