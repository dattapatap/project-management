<div id="mdlAdd" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmAdd">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Growth</p>
                        <h5 class="modal-title csd-modal__title">New Opportunity</h5>
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
                        <input type="text" name="title" class="form-control" required placeholder="Opportunity title">
                    </div>
                    <div class="form-group csd-field">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional details…"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="upsell">Upsell</option>
                                    <option value="cross_sell">Cross Sell</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="identified">Identified</option>
                                    <option value="proposed">Proposed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Estimated Value</label>
                                <input type="number" name="estimated_value" class="form-control" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Follow-up Date</label>
                                <input type="date" name="followup_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    @if($canAssignToOthers ?? false)
                    <div class="form-group csd-field mb-0">
                        <label>Assign To</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">Self</option>
                            @foreach($executives as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Opportunity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="mdlEdit" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmEdit">
                @csrf
                <input type="hidden" id="editOpportunityId">
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Growth</p>
                        <h5 class="modal-title csd-modal__title">Update Opportunity</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="form-group csd-field">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="editTitle" class="form-control" required>
                    </div>
                    <div class="form-group csd-field">
                        <label>Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Type</label>
                                <select name="type" id="editType" class="form-control">
                                    <option value="upsell">Upsell</option>
                                    <option value="cross_sell">Cross Sell</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Status</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="identified">Identified</option>
                                    <option value="proposed">Proposed</option>
                                    <option value="won">Won</option>
                                    <option value="lost">Lost</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Estimated Value</label>
                                <input type="number" name="estimated_value" id="editEstimatedValue" class="form-control" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Follow-up Date</label>
                                <input type="date" name="followup_date" id="editFollowupDate" class="form-control">
                            </div>
                        </div>
                    </div>
                    @if($canAssignToOthers ?? false)
                    <div class="form-group csd-field mb-0">
                        <label>Assign To</label>
                        <select name="assigned_to" id="editAssignedTo" class="form-control">
                            <option value="">Unassigned</option>
                            @foreach($executives as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
