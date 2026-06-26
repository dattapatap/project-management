<div id="mdlAddAssignment" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmCsdAssignment">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Client Assignment</p>
                        <h5 class="modal-title csd-modal__title">Assign Client</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Client <span class="text-danger">*</span></label>
                                <select name="client" class="form-control" required id="csdClientSelect">
                                    <option value="">Loading clients…</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Assign To</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">Unassigned</option>
                                    @foreach($executives as $exec)
                                    <option value="{{ $exec->id }}">{{ $exec->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Health Status <span class="text-danger">*</span></label>
                                <select name="health_status" class="form-control" required>
                                    <option value="healthy">Healthy</option>
                                    <option value="at_risk">At Risk</option>
                                    <option value="churning">Churning</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Satisfaction (1–10)</label>
                                <input type="number" name="satisfaction_score" class="form-control" min="1" max="10" placeholder="e.g. 8">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field mb-0">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Handoff context, expectations, risks…"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-check mr-1"></i> Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>
