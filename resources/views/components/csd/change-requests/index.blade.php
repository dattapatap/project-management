@extends('layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid erp-page erp-page--csd">
    @include('layouts.partials.erp-page-header', [
        'title' => 'Change Requests',
        'actions' => '<a href="' . url('/') . '" class="btn btn-outline-primary btn-sm mr-2"><i class="mdi mdi-arrow-left mr-1"></i> Back</a><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mdlAdd"><i class="mdi mdi-plus"></i> New Request</button>',
    ])
    <div class="card erp-table-card">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Client</th><th>Title</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div id="mdlAdd" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmAdd">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Change Requests</p>
                        <h5 class="modal-title csd-modal__title">New Change Request</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="form-group csd-field">
                        <label>Client <span class="text-danger">*</span></label>
                        <select name="client" class="form-control" required>
                            <option value="">Select client…</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group csd-field">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="Brief title for the change">
                    </div>
                    <div class="form-group csd-field mb-0">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Describe the requested change…"></textarea>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="mdlEdit" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmEdit">
                @csrf
                <input type="hidden" id="editId">
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Change Requests</p>
                        <h5 class="modal-title csd-modal__title">Update Change Request</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="form-group csd-field">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="editTitle" class="form-control" required>
                    </div>
                    <div class="form-group csd-field">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="editDescription" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group csd-field mb-0">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-control">
                            <option value="submitted">Submitted</option>
                            <option value="estimating">Estimating</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="transferred_to_od">Transferred to OD</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/csd-common.js') }}"></script>
<script>
$(function(){
    var table = $('#dataTable').DataTable({processing:true,serverSide:true,ajax:'{{ route('csd.change-requests.data') }}',
        columns:[{data:'DT_RowIndex',orderable:false},{data:'client_name'},{data:'title'},{data:'status'},{data:'action',orderable:false}]});
    $('#frmAdd').on('submit',function(e){e.preventDefault();csdApi.post('{{ route('csd.change-requests.store') }}',$(this).serialize(),function(){csdApi.closeModal('#mdlAdd','#frmAdd');table.ajax.reload();});});
    $(document).on('click','.editChangeRequest',function(){
        csdApi.get('/csd/change-requests/'+$(this).data('id'),function(r){
            if(!r.success)return; var d=r.data;
            $('#editId').val(d.id); $('#editTitle').val(d.title); $('#editDescription').val(d.description);
            $('#editStatus').val(d.status); $('#mdlEdit').modal('show');
        });
    });
    $('#frmEdit').on('submit',function(e){e.preventDefault();csdApi.put('/csd/change-requests/'+$('#editId').val(),$(this).serializeArray().reduce((o,i)=>{o[i.name]=i.value;return o;},{}),function(){csdApi.closeModal('#mdlEdit','#frmEdit');table.ajax.reload();});});
    $(document).on('click','.transferToOd',function(){var id=$(this).data('id');csdApi.post('/csd/change-requests/'+id+'/transfer-to-od',{},function(){table.ajax.reload();});});
});
</script>
@endsection
