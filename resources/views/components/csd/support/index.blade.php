@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Support Management',
        'actions' => '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="mdi mdi-plus"></i> New Ticket</button>',
    ])
    <div class="card erp-table-card">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Ticket</th><th>Client</th><th>Subject</th>
                        <th>Priority</th><th>Status</th><th>Assigned</th><th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@include('components.csd.partials.support-modals', [
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
    var table = $('#dataTable').DataTable({processing:true,serverSide:true,ajax:'{{ route('csd.support.data') }}',
        columns:[{data:'DT_RowIndex',orderable:false},{data:'ticket_no'},{data:'client_name'},{data:'subject'},{data:'priority'},{data:'status'},{data:'assignee_name'},{data:'action',orderable:false}]});
    $('#frmAdd').on('submit',function(e){e.preventDefault();csdApi.post('{{ route('csd.support.store') }}',$(this).serialize(),function(){csdApi.closeModal('#mdlAdd','#frmAdd');table.ajax.reload();});});
    $(document).on('click','.editTicket',function(){
        csdApi.get('/csd/support/'+$(this).data('id'),function(r){
            if(!r.success)return; var d=r.data;
            $('#editId').val(d.id); $('#editSubject').val(d.subject); $('#editDescription').val(d.description);
            $('#editType').val(d.type);             $('#editPriority').val(d.priority); $('#editStatus').val(d.status);
            if ($('#editAssignedTo').length) { $('#editAssignedTo').val(d.assigned_to||''); }
            $('#mdlEdit').modal('show');
        });
    });
    $('#frmEdit').on('submit',function(e){e.preventDefault();csdApi.put('/csd/support/'+$('#editId').val(),$(this).serializeArray().reduce((o,i)=>{o[i.name]=i.value;return o;},{}),function(){csdApi.closeModal('#mdlEdit','#frmEdit');table.ajax.reload();});});
});
</script>
@endsection
