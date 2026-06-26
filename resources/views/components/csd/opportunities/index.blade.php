@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Upselling & Opportunities',
        'subtitle' => 'Mark Won creates a tracked commercial order — after Won, edit the order in Commercial Orders (opportunity is locked).',
        'actions' => '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="mdi mdi-plus"></i> New Opportunity</button>',
    ])
    <div class="card erp-table-card">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Client</th><th>Title</th><th>Type</th>
                        <th>Value</th><th>Status</th><th>Assigned</th><th>Order</th><th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@include('components.csd.partials.opportunity-modals', [
    'clients' => $clients,
    'executives' => $executives ?? collect(),
    'canAssignToOthers' => $canAssignToOthers ?? false,
])
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/csd-common.js') }}"></script>
<script>
$(function(){
    var table = $('#dataTable').DataTable({processing:true,serverSide:true,ajax:'{{ route('csd.opportunities.data') }}',
        columns:[{data:'DT_RowIndex',orderable:false},{data:'client_name'},{data:'title'},{data:'type'},{data:'estimated_value'},{data:'status'},{data:'assignee_name'},{data:'engagement_no'},{data:'action',orderable:false}]});
    $('#frmAdd').on('submit', function (e) {
        e.preventDefault();
        csdApi.post('{{ route('csd.opportunities.store') }}', $(this).serialize(), function () {
            csdApi.closeModal('#mdlAdd', '#frmAdd');
            table.ajax.reload();
        });
    });
    $(document).on('click','.editOpportunity',function(){
        csdApi.get('/csd/opportunities/'+$(this).data('id'),function(r){
            if(!r.success)return; var d=r.data;
            $('#editOpportunityId').val(d.id);
            $('#editTitle').val(d.title);
            $('#editDescription').val(d.description);
            $('#editType').val(d.type);
            $('#editEstimatedValue').val(d.estimated_value);
            $('#editStatus').val(d.status);
            $('#editFollowupDate').val(d.followup_date ? d.followup_date.substring(0,10) : '');
            if ($('#editAssignedTo').length) { $('#editAssignedTo').val(d.assigned_to || ''); }
            $('#mdlEdit').modal('show');
        });
    });
    $('#frmEdit').on('submit',function(e){e.preventDefault();var id=$('#editOpportunityId').val();
        csdApi.put('/csd/opportunities/'+id,$(this).serializeArray().reduce((o,i)=>{o[i.name]=i.value;return o;},{}),function(){csdApi.closeModal('#mdlEdit','#frmEdit');table.ajax.reload();});});
});
</script>
@endsection
