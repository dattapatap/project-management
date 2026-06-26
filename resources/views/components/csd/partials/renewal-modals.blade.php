<div id="mdlAdd" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmAdd">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Renewals</p>
                        <h5 class="modal-title csd-modal__title">Add Renewal</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="alert alert-light border small mb-3 py-2">
                        <strong>Process:</strong> Status is set automatically from the due date — <em>Upcoming</em> → <em>Due</em> (within 30 days) → <em>Lapsed</em> (30+ days overdue). Mark <em>Renewed</em> when the client renews.
                    </div>
                    <div class="form-group csd-field">
                        <label>Client <span class="text-danger">*</span></label>
                        <select name="client" id="renewalClientSelect" class="form-control" required>
                            <option value="">Select client…</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Type <span class="text-danger">*</span></label>
                                <select name="renewal_type" id="renewalTypeSelect" class="form-control" required>
                                    <option value="amc">AMC — Annual maintenance contract</option>
                                    <option value="domain">Domain — Domain name renewal</option>
                                    <option value="hosting">Hosting — Server / hosting plan</option>
                                    <option value="subscription">Subscription — SaaS or recurring service</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field" id="amcLinkField" style="display:none;">
                                <label>Link AMC Contract</label>
                                <select name="reference_id" id="renewalAmcSelect" class="form-control">
                                    <option value="">Select contract…</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group csd-field">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="renewalTitle" class="form-control" required placeholder="e.g. Annual AMC renewal">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" id="renewalDueDate" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field mb-0">
                                <label>Amount</label>
                                <input type="number" name="amount" id="renewalAmount" class="form-control" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="form-group csd-field mb-0 mt-3">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional context…"></textarea>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Renewal</button>
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
                        <p class="csd-modal__eyebrow">CSD · Renewals</p>
                        <h5 class="modal-title csd-modal__title">Update Renewal</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Client</small>
                            <strong id="editClientLabel">—</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Type</small>
                            <strong id="editTypeLabel">—</strong>
                        </div>
                    </div>
                    <div class="form-group csd-field">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="editTitle" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" id="editDueDate" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Amount</label>
                                <input type="number" name="amount" id="editAmount" class="form-control" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="form-group csd-field">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-control">
                            <option value="upcoming">Upcoming</option>
                            <option value="due">Due</option>
                            <option value="renewed">Renewed — client has paid / extended</option>
                            <option value="lapsed">Lapsed — not renewed</option>
                        </select>
                        <small class="text-muted">Choosing <em>Renewed</em> extends linked AMC contracts and updates domain records.</small>
                    </div>
                    <div class="form-group csd-field mb-0">
                        <label>Notes</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="3" placeholder="Follow-up notes…"></textarea>
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
