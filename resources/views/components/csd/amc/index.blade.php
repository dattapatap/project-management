@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @php
        $billingFilter = $billingFilter ?? 'all';
        $cycleTabs = ['all' => 'All', 'monthly' => 'Monthly', 'yearly' => 'Yearly'];
    @endphp

    @include('layouts.partials.erp-page-header', [
        'title' => 'AMC / Support Contracts',
        'subtitle' => 'Monthly (5-day reminder) and yearly (30-day reminder) agreements with document storage.',
        'actions' => '<a href="' . route('csd.amc.create') . '" class="btn btn-primary btn-sm"><i class="mdi mdi-plus"></i> Add Contract</a>',
    ])

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <ul class="nav nav-pills mb-3">
        @foreach($cycleTabs as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $billingFilter === $key ? 'active' : '' }}" href="{{ route('csd.amc.index', ['cycle' => $key]) }}">{{ $label }}</a>
        </li>
        @endforeach
    </ul>

    <div class="card erp-table-card">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Cycle</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Amount</th>
                        <th>Doc</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
$(function () {
    var cycleFilter = @json($billingFilter);
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('csd.amc.data') }}',
            data: function (d) { d.cycle = cycleFilter; }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false },
            { data: 'client_name' },
            { data: 'contract_type' },
            { data: 'billing_cycle' },
            { data: 'start_date' },
            { data: 'end_date' },
            { data: 'amount' },
            { data: 'document', orderable: false, searchable: false },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endsection
