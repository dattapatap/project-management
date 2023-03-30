<div id="mdlEditProject" class="modal fade bs-example-modal-center" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Edit Project</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_project_edit" class="custom-validation"  method="POST" novalidate>
                    @csrf
                    <input type="hidden" value="" name="client" id="client">
                    @php
                        $teams = DB::table('teams')->where('department', 2)->orderBy('name', 'asc')->get();
                    @endphp
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Select Team</label>
                               <select class="form-control select2" width="100%" name="team" id="team">
                                    <option value="">Choose Team</option>
                                    @foreach ($teams as $item)
                                        <option value="{{ $item->id }}"> {{ $item->name }} </option>
                                    @endforeach
                               </select>
                                <span class="invalid-feedback" id="team-input-error" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row float-roght">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1 float-right creatBtn">
                                Assign
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
