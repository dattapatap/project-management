@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Collection Management',
        'actions' => '<a href="' . url('/') . '" class="btn btn-outline-primary btn-sm mr-2"><i class="mdi mdi-arrow-left mr-1"></i> Back</a><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="mdi mdi-plus"></i> Add Follow-up</button>',
    ])
    <div class="card erp-table-card">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Client</th><th>Amount Due</th><th>Due Date</th>
                        <th>Follow-up</th><th>Status</th><th>Assigned</th><th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@include('components.csd.partials.collection-modals', [
    'clients' => $clients,
    'executives' => $executives,
    'canAssignToOthers' => $canAssignToOthers ?? false,
])
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/csd-common.js') }}"></script>
<script>
$(function(){
    var table = $('#dataTable').DataTable({processing:true,serverSide:true,ajax:'{{ route('csd.collections.data') }}',
        columns:[{data:'DT_RowIndex',orderable:false},{data:'client_name'},{data:'amount_due'},{data:'due_date'},{data:'followup_date'},{data:'status'},{data:'assignee_name'},{data:'action',orderable:false}]});
    $('#frmAdd').on('submit', function (e) {
        e.preventDefault();
        csdApi.post('{{ route('csd.collections.store') }}', $(this).serialize(), function () {
            csdApi.closeModal('#mdlAdd', '#frmAdd');
            table.ajax.reload();
        });
    });
    $(document).on('click','.editCollection',function(){
        csdApi.get('/csd/collections/'+$(this).data('id'),function(r){
            if(!r.success)return; var d=r.data;
            $('#editId').val(d.id); $('#editAmount').val(d.amount_due); $('#editDueDate').val(d.due_date?d.due_date.split('T')[0]:'');
            $('#editFollowup').val(d.followup_date?d.followup_date.split('T')[0]:''); $('#editCommitmentDate').val(d.commitment_date?d.commitment_date.split('T')[0]:'');
            $('#editCommitmentAmount').val(d.commitment_amount||''); $('#editStatus').val(d.status);             $('#editRemarks').val(d.remarks||'');
            if ($('#editAssignedTo').length) { $('#editAssignedTo').val(d.assigned_to||''); }
            $('#mdlEdit').modal('show');
        });
    });
    $('#frmEdit').on('submit',function(e){e.preventDefault();csdApi.put('/csd/collections/'+$('#editId').val(),$(this).serializeArray().reduce((o,i)=>{o[i.name]=i.value;return o;},{}),function(){csdApi.closeModal('#mdlEdit','#frmEdit');table.ajax.reload();});});
});
</script>
@endsection
