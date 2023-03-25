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
                <h4 class="mb-0 font-size-18">Sales Reports</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ env('APP_NAME')}}</a></li>
                        <li class="breadcrumb-item active">Sales Reports</li>
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
                    <div class="col-12 sts_report">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Company</th>
                                        <th>Category</th>
                                        <th>Contact Info</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th> Date </th>
                                        @if($user->hasRole(['Admin', 'Team-Leader']))
                                        <th> Sal/Exc </th>
                                        @endif
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

@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>

<script>

    $(document).ready(function(){

        $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            bDestroy: true,
            ajax :{
                    type: 'GET',
                    url: "{{ route('report.salesreports') }}",
                    error:function(err){ console.log(err);}
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false},
                {data: 'name', name: 'name', orderable: false, searchable: true},
                {data: 'category', name: 'category', orderable: false, searchable: true},
                {data: 'contactinfo', name: 'cont_person', orderable: false, searchable: true},
                {data: 'mobile', name: 'mobile', orderable: false, searchable: true},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'active_from', name: 'active_from', orderable: false, searchable: true},
                @if($user->hasRole(['Admin', 'Team-Leader']))
                {data: 'telereferral', name: 'telereferral.name', orderable: false, searchable: true},
                @endif
                {data: 'action',  name: 'action', orderable: false, searchable: false },
            ]
        });

    });

</script>

@endsection
