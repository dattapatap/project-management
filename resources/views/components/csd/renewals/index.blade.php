@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @php
        $statusFilter = $statusFilter ?? 'all';
        $statusTabs = [
            'all' => 'All',
            'due' => 'Due',
            'upcoming' => 'Upcoming',
            'renewed' => 'Renewed',
            'lapsed' => 'Lapsed',
        ];
    @endphp
    @include('layouts.partials.erp-page-header', [
        'title' => 'Renewal Management',
        'subtitle' => 'Track AMC, domain, hosting & subscription renewals through to completion.',
        'actions' => '<a href="' . url('/') . '" class="btn btn-outline-primary btn-sm mr-2"><i class="mdi mdi-arrow-left mr-1"></i> Back</a>'
            . '<button type="button" class="btn btn-outline-primary btn-sm mr-1" id="btnSyncRenewals"><i class="mdi mdi-sync"></i> Sync AMC & Domains</button>'
            . '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="mdi mdi-plus"></i> Add Renewal</button>',
    ])

    <ul class="nav nav-pills mb-3 erp-filter-pills">
        @foreach($statusTabs as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === $key ? 'active' : '' }}" href="{{ route('csd.renewals.index', ['status' => $key]) }}">{{ $label }}</a>
        </li>
        @endforeach
    </ul>

    <div class="card erp-table-card">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Client</th><th>Type</th><th>Title</th>
                        <th>Due Date</th><th>Amount</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@include('components.csd.partials.renewal-modals', ['clients' => $clients])
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/csd-common.js') }}"></script>
<script>
$(function(){
    var statusFilter = @json($statusFilter);

    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('csd.renewals.data') }}',
            data: function(d) { d.status = statusFilter; }
        },
        columns: [
            {data:'DT_RowIndex',orderable:false},
            {data:'client_name'},
            {data:'renewal_type'},
            {data:'title'},
            {data:'due_date'},
            {data:'amount'},
            {data:'status'},
            {data:'action',orderable:false}
        ]
    });

    function loadRenewalClients(preselect) {
        var $sel = $('#renewalClientSelect');
        $sel.prop('disabled', true).html('<option value="">Loading clients…</option>');
        $.get('{{ route('csd.clients.active') }}', function(res) {
            var opts = '<option value="">Select client…</option>';
            if (res.success && res.data && res.data.length) {
                res.data.forEach(function(c) {
                    opts += '<option value="' + c.id + '">' + c.name + '</option>';
                });
            } else {
                opts += '<option value="" disabled>No clients available</option>';
            }
            $sel.html(opts).prop('disabled', false);
            if (preselect) { $sel.val(String(preselect)).trigger('change'); }
        }).fail(function() {
            $sel.html('<option value="">Failed to load clients</option>').prop('disabled', false);
        });
    }

    function loadAmcOptions(clientId) {
        var $amc = $('#renewalAmcSelect');
        $amc.html('<option value="">Loading…</option>');
        if (!clientId) {
            $amc.html('<option value="">Select contract…</option>');
            return;
        }
        $.get('{{ route('csd.renewals.amc-options') }}', { client: clientId }, function(res) {
            var opts = '<option value="">None / manual</option>';
            if (res.success && res.data.length) {
                res.data.forEach(function(c) {
                    opts += '<option value="' + c.id + '" data-end="' + c.end_date + '" data-amount="' + c.amount + '">' + c.label + '</option>';
                });
            }
            $amc.html(opts);
        });
    }

    $('#renewalClientSelect').on('change', function() {
        if ($('#renewalTypeSelect').val() === 'amc') {
            loadAmcOptions($(this).val());
        }
    });

    $('#renewalTypeSelect').on('change', function() {
        var isAmc = $(this).val() === 'amc';
        $('#amcLinkField').toggle(isAmc);
        if (isAmc) {
            loadAmcOptions($('#renewalClientSelect').val());
        }
        var titles = {
            amc: 'AMC renewal',
            domain: 'Domain renewal',
            hosting: 'Hosting renewal',
            subscription: 'Subscription renewal'
        };
        if (!$('#renewalTitle').data('touched')) {
            $('#renewalTitle').val(titles[$(this).val()] || '');
        }
    });

    $('#renewalTitle').on('input', function() { $(this).data('touched', true); });

    $('#renewalAmcSelect').on('change', function() {
        var $opt = $(this).find(':selected');
        if ($opt.data('end')) {
            $('#renewalDueDate').val($opt.data('end'));
        }
        if ($opt.data('amount')) {
            $('#renewalAmount').val($opt.data('amount'));
        }
    });

    $('#mdlAdd').on('show.bs.modal', function() {
        $('#renewalTitle').removeData('touched');
        var preselect = new URLSearchParams(window.location.search).get('client');
        loadRenewalClients(preselect);
        $('#renewalTypeSelect').trigger('change');
    });

    $('#frmAdd').on('submit', function(e) {
        e.preventDefault();
        csdApi.post('{{ route('csd.renewals.store') }}', $(this).serialize(), function() {
            csdApi.closeModal('#mdlAdd', '#frmAdd');
            table.ajax.reload();
        });
    });

    $(document).on('click', '.editRenewal', function() {
        csdApi.get('/csd/renewals/' + $(this).data('id'), function(r) {
            if (!r.success) { if (r.message) alertify.error(r.message); return; }
            var d = r.data;
            $('#editId').val(d.id);
            $('#editClientLabel').text(d.client ? d.client.name : '—');
            $('#editTypeLabel').text((d.renewal_type || '').toUpperCase());
            $('#editTitle').val(d.title);
            $('#editDueDate').val(d.due_date ? String(d.due_date).split('T')[0] : '');
            $('#editAmount').val(d.amount || '');
            $('#editStatus').val(d.status);
            $('#editNotes').val(d.notes || '');
            $('#mdlEdit').modal('show');
        });
    });

    $('#frmEdit').on('submit', function(e) {
        e.preventDefault();
        csdApi.put('/csd/renewals/' + $('#editId').val(), $(this).serializeArray().reduce(function(o, i) {
            o[i.name] = i.value;
            return o;
        }, {}), function() {
            csdApi.closeModal('#mdlEdit', '#frmEdit');
            table.ajax.reload();
        });
    });

    $(document).on('click', '.markRenewed', function() {
        var id = $(this).data('id');
        csdApi.post('/csd/renewals/' + id + '/mark-renewed', {}, function() {
            table.ajax.reload();
        });
    });

    $(document).on('click', '.markLapsed', function() {
        var id = $(this).data('id');
        if (!confirm('Mark this renewal as lapsed (not renewed)?')) return;
        csdApi.post('/csd/renewals/' + id + '/mark-lapsed', {}, function() {
            table.ajax.reload();
        });
    });

    $('#btnSyncRenewals').on('click', function() {
        var $btn = $(this).prop('disabled', true);
        csdApi.post('{{ route('csd.renewals.sync') }}', {}, function() {
            table.ajax.reload();
            $btn.prop('disabled', false);
        });
        setTimeout(function() { $btn.prop('disabled', false); }, 5000);
    });

    var preselectClient = new URLSearchParams(window.location.search).get('client');
    if (preselectClient) { $('#mdlAdd').modal('show'); }
});
</script>
@endsection
