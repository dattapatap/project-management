@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Reports</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Reports</li>
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
                    <h4 class="header-title"> Reports </h4>
                </div>
                <div class="card-body">
                    <form id="frmDownloadReport"  class="custom-validation" method="POST" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label> Report Type<span class="text_required">*</span> </label>
                                    <select class="form-control" name="reporttype" id="reporttype" placeholder="Report Type"
                                     required>
                                        <option value=""> Select Report Type</option>
                                        <option value="product"> Product </option>
                                        <option value="batch"> Batches </option>
                                    </select>
                                    <span class="invalid-feedback" id="reporttype-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>

                            </div>


                            <div class="col-12 reportCategory" style="display: none;">
                                <div class="form-group">
                                    <label>Report Category <span class="text_required">*</span></label>
                                    <select class="form-control" name="report_category" id="report_category" placeholder="Report Category"
                                     required>
                                        <option value=""> Select Report Category</option>
                                        <option value="batchwise"> Batch Wise </option>
                                        <option value="datewise"> Date Wise </option>
                                    </select>
                                    <span class="invalid-feedback" id="report_category-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 batchwise" style="display: none;">
                                <div class="form-group">
                                    <label>Batches <span class="text_required">*</span></label>
                                    <select class="form-control select2" name="batches" id="batches" placeholder="Batches" style="width: 100%">
                                       <option value=""> Select Batch</option>
                                       @foreach($batches as $items)
                                            <option value="{{ $items }}"> {{ $items }} </option>
                                       @endforeach
                                   </select>
                                   <span class="invalid-feedback" id="batches-input-error" role="alert">
                                        <strong></strong>
                                   </span>
                                </div>
                            </div>

                            <div class="col-12 datewise" style="display: none;">
                                <div class="form-group">
                                    <label> Select Date <span class="text_required">*</span></label>
                                    <input type="text" name="date" id="date" class="form-control" placeholder="Date">
                                    <span class="invalid-feedback" id="date-input-error" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 btndownload">
                                    Export
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
<script type="text/javascript" src="{{ asset('assets/js/moment.min.js') }}" ></script>
<script type="text/javascript" src="{{ asset('assets/js/datepicket.min.js') }}"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
    $(function() {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#date').val(start.format('D/M/YYYY') + ' - ' + end.format('D/M/YYYY'));
            }

            $('#date').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                },
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            }, cb);

    });



    $(document).ready(function(){
        $('.select2').select2();

        $('.btndownload').prop('disabled', true);
        $('#reporttype').change(function(){
            $('#batchwise').hide();
            $('#datewise').hide();

            if($(this).val() == 'product'){
                $('.batchwise').hide(); $('#batches').val('');
                $('.datewise').hide(); $('#date').val('');
                $('.reportCategory').hide(); $('#report_category').val('');
                $('.btndownload').prop('disabled', false);
            }else if($(this).val() == 'batch'){
                $('.batchwise').hide(); $('#batches').val('');
                $('.datewise').hide(); $('#date').val('');
                $('.reportCategory').show();  $('#report_category').val('');
                $('.btndownload').prop('disabled', true);

            }else{
                $('.batchwise').hide(); $('#batches').val('');
                $('.datewise').hide(); $('#date').val('');
                $('.reportCategory').hide();  $('#report_category').val('');
                $('.btndownload').prop('disabled', true);
            }
        });

        $('#report_category').change(function(){
            if($(this).val() == 'batchwise'){
                $('.batchwise').show(); $('#batches').val('');
                $('.datewise').hide();  $('#date').val('');
                $('.btndownload').prop('disabled', false);
            }else if($(this).val() == 'datewise'){
                $('.batchwise').hide(); $('#date').val('');
                $('.datewise').show(); $('#date').val('');
                $('.btndownload').prop('disabled', false);
            }else{
                $('.batchwise').hide(); $('#date').val('');
                $('.datewise').hide(); $('#date').val('');
                $('.btndownload').prop('disabled', true);
            }
        })

        $('#frmDownloadReport').submit(function(e){
            e.preventDefault();
            var formData = new FormData($(this)[0]);
            $(".invalid-feedback").children("strong").text("");

            $.ajax({
                type: 'POST',
                url: '{{ route('batches.exportall') }}',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                xhrFields: {
                    responseType: 'blob',
                },
                beforeSend: function() {
                    $(".btndownload").html('Exporting..');
                    $(".btndownload").prop('disabled', true);
                },
                success: function(result, status, xhr) {
                    $(".btndownload").prop('disabled', false);
                    $(".btndownload").html('Export');
                    console.log(result);
                    var disposition = xhr.getResponseHeader('content-disposition');
                    var matches = /"([^"]*)"/.exec(disposition);
                    if($('#reporttype').val() == 'product'){
                        var filename = (matches != null && matches[1] ? matches[1] : 'Product_list.xlsx');
                    }else{
                        var filename = (matches != null && matches[1] ? matches[1] : 'Batch_list.xlsx');
                    }

                    // The actual download
                    var blob = new Blob([result], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(response) {
                    console.log(response);
                    $(".btndownload").prop('disabled', false);
                    $(".btndownload").html('Export');
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
