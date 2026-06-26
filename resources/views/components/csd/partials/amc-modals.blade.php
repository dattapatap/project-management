<div id="mdlAdd" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmAdd">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Contracts</p>
                        <h5 class="modal-title csd-modal__title">Add AMC Contract</h5>
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Contract Type</label>
                                <select name="contract_type" class="form-control">
                                    <option value="amc">AMC</option>
                                    <option value="support">Support</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group csd-field mb-0">
                        <label>Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" required step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Contract</button>
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
                <input type="hidden" id="editId">
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Contracts</p>
                        <h5 class="modal-title csd-modal__title">Update AMC Contract</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="form-group csd-field">
                        <label>Contract Type</label>
                        <select name="contract_type" id="editType" class="form-control">
                            <option value="amc">AMC</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="editStart" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="editEnd" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="editAmount" class="form-control" required step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field mb-0">
                                <label>Status</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
                                    <option value="renewed">Renewed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
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
