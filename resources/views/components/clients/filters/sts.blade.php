@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
<style>
    .table-mysts th {
        color: #495057;
        font-weight: 600;
        background-color: #eeeeee;
        text-align: center;
        border: 2px solid #3fbeaa !important;
        padding: 0.35rem;
    }
    .table-mysts td {
        color: #495057;
        font-weight: 600;
        background-color: #ffffff;
        text-align: center;
        border: 2px solid #3fbeaa !important;
        padding: 0.5rem;
    }
    .table-mysts td a {
        text-decoration: underline !important;
        color: #000;
        cursor: pointer;
    }
</style>

@endsection

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Search STS</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Search STS</li>
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
                    <h4 class="header-title"> Search STS </h4>
                </div>
                <div class="card-body">
                    <form class="" id="frm-search-sts" action="{{ route('report.searchsts')}}" >
                        <div class="row">

                            @if($user->hasRole('Sales-Executive'))
                                <input type="hidden" name="employee" id="employee" value="{{ $user->id}}">
                            @else
                                @php
                                    $teams  =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
                                    $fltUsers =  App\Models\TeamMembers::with('users.roles')
                                                            ->whereHas('users.roles', function($query){
                                                                $query->where('name', 'Sales-Executive');
                                                            })
                                                            ->whereIn('team', $teams)->where('status', true)->get();

                                @endphp

                                <div class="col-3">
                                    <div class="form-group">
                                        <label> User </label>
                                        <select class="form-control select2" name="employee" id="employee" style="width:100%" required>
                                            <option value="All"> All </option>
                                            @if($user->hasRole('Team-Leader'))
                                                 <option value="{{ $user->id }}" @if( $search && $search['employee'] == $user->id ) selected  @endif> Self </option>
                                            @endif
                                            @foreach ($fltUsers as $item)
                                                <option value="{{ $item->users->id }}" @if( $search && $search['employee'] == $item->users->id ) selected  @endif>
                                                      {{ $item->users->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif


                            <div class="col-3">
                                <div class="form-group">
                                    <label>STS</label>
                                    <select class="form-control select2" name="category" id="category" style="width:100%" required>
                                        <option value="All" @if($search && $search['category'] == 'All')  selected @endif>All</option>
                                        <option value="Fresh" @if($search && $search['category'] == 'Fresh')  selected @endif>Fresh</option>
                                        <option value="Followup" @if($search &&  $search['category'] == 'Followup')  selected @endif>Followup</option>
                                        <option value="Meeting Fixed" @if($search &&  $search['category'] == 'Meeting Fixed')  selected @endif>Meeting Fixed</option>
                                        <option value="Not Interested" @if($search &&  $search['category'] == 'Not Interested')  selected @endif>Not Interested</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label>STS Date  </label>
                                    <input type="text" name="from_date" id="from_date" value="@if($search) {{ $search['from_date'] }} @endif"
                                        class="form-control" placeholder="Date" required>
                                </div>
                            </div>
                            <input type="hidden" name="searchCategory" value="STS">

                        </div>
                        <div class="row">
                            <div class="col-12 text-left">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btnsts">Search STS</button>
                                    @if(!$clients->isEmpty())
                                    &nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary btn-sts-export">
                                        <i class="mdi mdi-download"></i>
                                        Export STS
                                    </button>
                                    @endif
                                    &nbsp;&nbsp;
                                    <button type="button" class="btn btn-primary btn-my-sts-count">My STS</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr style="border-top: 1px solid #3fbeaa;">

                    @if(!$clients->isEmpty())
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" style="width: 5%"  >Sl</th>
                                        @if($user->hasRole(["Admin","Team-Leader"]))
                                        <th scope="col"  style="width: 20%"  >Name</th>
                                        @else
                                        <th scope="col"  style="width: 25%"  >Name</th>
                                        @endif
                                        @if($user->hasRole(["Admin","Team-Leader"]))
                                        <th scope="col" > Referral</th>
                                        @endif
                                        <th scope="col" > Status</th>
                                        <th scope="col" class="text-center" style="width: 15%"  > History Dt.</th>
                                        @if($user->hasRole(["Admin","Team-Leader"]))
                                        <th scope="col" style="width: 20%"> Remarks</th>
                                        @else
                                        <th scope="col" style="width: 30%"> Remarks</th>
                                        @endif
                                        <th scope="col" style="width: 12%" class="text-center"> TBRO </th>
                                        <th scope="col" style="width: 5%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($clients as $key=>$items)
                                    <tr>
                                        <td> {{ ($clients->currentpage()-1) * $clients->perpage() + $key + 1 }} </td>
                                        <td> {{ $items->name }} </td>
                                        @if($user->hasRole(["Admin","Team-Leader"]))
                                        <td>{{ $items->referral->name }}   </td>
                                        @endif

                                        <td class="text-success">{{ $items->status }}   </td>
                                        <td class="text-center"> {{ Carbon\Carbon::parse($items->history->created_at)->format('d M Y').' '. Carbon\Carbon::parse($items->history->time)->format('h:i A')  }}</td>
                                        <td > {{ $items->history->remarks }}</td>
                                        <td class="text-center" > @if($items->history->tbro) {{ Carbon\Carbon::parse($items->history->tbro)->format('d M Y')  }} @else --- @endif</td>

                                        <td class="text-center">
                                            <a type="button" class="btn btn-outline-success btn-sm" target="_blank" href="{{ url('clients/'.base64_encode($items->id).'/'.'sts' ) }}"
                                                data-toggle="tooltip" data-placement="bottom" title="Update STS">
                                                <i class="mdi mdi-plus-outline"></i>
                                            </a>
                                        </td>

                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            No Clients exist.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3 float-right">
                            {{ $clients->links("pagination::bootstrap-4") }}
                        </div>
                    @else
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <span class=" text-danger"> NO STS FOUND</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
</div>

<div id="mySTS" class="modal fade bs-example-modal-xl" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #3fbeaa;">
                <h5 class="modal-title mt-0" id="myExtraLargeModalLabel">MY STS</h5>
                <button type="button" class="close mdlClose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="col-12 sts_report">
                    <div class="table-responsive">
                        <table class="table table-centered mb-0 table-mysts">
                            <thead>
                                <tr>
                                    <th scope="col" rowspan="2" >Emp Id</th>
                                    <th scope="col" rowspan="2"  > Name </th>
                                    <th scope="col" rowspan="2" >STS</th>
                                    <th scope="col" rowspan="2"  >UnTouch</th>
                                    <th scope="col" rowspan="2"  >Touch</th>
                                    <th scope="col" colspan="4" >MET</th>
                                    <th scope="col" colspan="4" >NOT MET</th>
                                </tr>
                                <tr>
                                    <th scope="col" > MET </th>
                                    <th scope="col" > Matured</th>
                                    <th scope="col" > TBRO </th>
                                    <th scope="col" > Remi </th>
                                    <th scope="col" > NOT MET </th>
                                    <th scope="col" > TBRO</th>
                                    <th scope="col" > Remi </th>
                                    <th scope="col" > Meet Fxd. </th>

                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="content-filtermysts col-12 mt-5 d-none" >
                    <table id="datatable" class="table table-bordered dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Company</th>
                            {{-- <th>Category</th> --}}
                            <th>Contact Info</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>TBRO/Meet Fxd. Dt</th>
                            <th>STS Update</th>
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



@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('assets/js/moment.min.js') }}" ></script>
<script type="text/javascript" src="{{ asset('assets/js/datepicket.min.js') }}"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>

<script>

    $(function() {

            var start = moment();
            var end = moment();
            function cb(start, end) {
                $('#from_date').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            }
            $('#from_date').daterangepicker({
                startDate: start,
                endDate: end,
                maxDate:end,
                ranges: {
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
                },
                autoUpdateInput: false,
                locale: { cancelLabel: 'Clear' }
            }, cb);
    });

    $(document).ready(function(){
        $("#mySTS").modal({show: false, backdrop: 'static'});
        $('.mdlClose').click(function(){
            $("#mySTS").modal('hide');
        });
        $('.btn-my-sts-count').click(function(){
                $('.table-mysts tbody').empty();
                $.ajax({
                    type: 'GET',
                    url: "{{ route('report.get-count-my-sts') }}",
                    beforeSend:function($e){
                        $('.btn-my-sts-count').prop('disabled', true);
                    },
                    success: function(response) {
                        $('.btn-my-sts-count').prop('disabled', false);
                        if(response.status){
                            let user = response.user;
                            let sts = response.data;

                            let txtTr = '<tr>'+
                                            '<td >'+ user.code +'</td>'+
                                            '<td >'+ user.name +'</td>'+
                                            '<td ><a href="javascript:void(0);" class="mysts_client" category="sts" usercode="'+ user.id +'" >'+ sts.sts +'</a></td>'+
                                            '<td > <a class="mysts_client" category="untouch" usercode="'+ user.id +'" >'+ sts.unTouch +'</a></td>'+
                                            '<td > <a class="mysts_client" category="touch" usercode="'+ user.id +'" >'+ sts.touch +'</a></td>'+
                                            '<td > <a class="mysts_client" category="dsrMet" usercode="'+ user.id +'" >'+ sts.dsrMet +'</a></td>'+
                                            '<td > <a class="mysts_client" category="dsrMatured" usercode="'+ user.id +'" >'+ sts.dsrMatured +'</a></td>'+
                                            '<td > <a class="mysts_client" category="dsrTbro" usercode="'+ user.id +'" >'+ sts.dsrTbro +'</a></td>'+
                                            '<td > <a class="mysts_client" category="dsrReminder" usercode="'+ user.id +'" >'+ sts.dsrReminder +'</a></td>'+

                                            '<td > <a class="mysts_client" category="stsNotMet" usercode="'+ user.id +'" >'+ sts.stsNotMet +'</a></td>'+
                                            '<td > <a class="mysts_client" category="stsTbro" usercode="'+ user.id +'" >'+ sts.stsTBRO +'</a></td>'+
                                            '<td > <a class="mysts_client" category="stsReminder" usercode="'+ user.id +'" >'+ sts.stsReminder +'</a></td>'+
                                            '<td > <a class="mysts_client" category="stsMeetFixed" usercode="'+ user.id +'" >'+ sts.stsMeetingFixed +'</a></td>'+
                                        '</tr>';

                            $('.table-mysts tbody').append(txtTr);
                            $('#mySTS').modal('show');
                        }else{
                            $('.table-mysts tbody').append('<tr><td colspan="13"> NO STS RECORDS EXIST!</td></tr>');
                            $('#mySTS').modal('show');
                        }
                    },
                });
        });

        $(document).on('click', '.mysts_client', function(){
            $('.content-filtermysts').addClass('d-none');
            let category = $(this).attr('category');
            let usercode = $(this).attr('usercode');
            $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                bDestroy: true,
                pageLength : 5,
                lengthMenu: [5, 10, 20, 50],
                ajax :{
                        type: 'GET',
                        data:{ 'category' : category, 'usercode': usercode},
                        url: "{{ route('report.get-count-by-category') }}",
                        error:function(err){ console.log(err);}
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'Sl No', orderable: false, searchable: false},
                    {data: 'name', name: 'name', orderable: false, searchable: true},
                    // {data: 'category', name: 'category', orderable: false, searchable: true},
                    {data: 'contactinfo', name: 'cont_person', orderable: false, searchable: true},
                    {data: 'mobile', name: 'mobile', orderable: false, searchable: true},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'tbro', name: 'history.tbro', orderable: false, searchable: true},
                    {data: 'action',  name: 'action', orderable: false, searchable: false },
                ]

            });

            $('.content-filtermysts').removeClass('d-none');


        })

    });


    $(document).ready(function(){
        $('.btn-sts-export').click(function(e){
            e.preventDefault();
            $.ajax({
                type: 'GET',
                url : base_url + '/exportsts',
                data: $('#frm-search-sts').serialize(),
                cache: false,
                contentType: false,
                processData: false,
                xhrFields: {
                    responseType: 'blob',
                },
                beforeSend: function() {
                    $(".btn-sts-export").html('Exporting..');
                    $(".btn-sts-export").prop('disabled', true);
                },
                success: function(result, status, xhr) {
                    $(".btn-sts-export").prop('disabled', false);
                    $(".btn-sts-export").html('Export STS');
                    console.log(result);
                    let disposition = xhr.getResponseHeader('content-disposition');
                    let matches = /"([^"]*)"/.exec(disposition);
                    let filename = (matches != null && matches[1] ? matches[1] : 'sts_list.xlsx');

                    // The actual download
                    var blob = new Blob([result], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(response) {
                    $(".btn-sts-export").prop('disabled', false);
                    $(".btn-sts-export").html('Export STS');
                }
            });
        });
    })


</script>

@endsection
