{{-- Add Collection Follow-up --}}
<div id="mdlAdd" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmAdd">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Collections</p>
                        <h5 class="modal-title csd-modal__title">Add Collection Follow-up</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Client <span class="text-danger">*</span></label>
                                <select name="client" class="form-control" required>
                                    <option value="">Select client…</option>
                                    @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Amount Due <span class="text-danger">*</span></label>
                                <input type="number" name="amount_due" class="form-control" required step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Follow-up Date</label>
                                <input type="date" name="followup_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Commitment Date</label>
                                <input type="date" name="commitment_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Commitment Amount</label>
                                <input type="number" name="commitment_amount" class="form-control" step="0.01">
                            </div>
                        </div>
                        @if($canAssignToOthers ?? false)
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Assigned To</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">Self</option>
                                    @foreach($executives as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field mb-0">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Payment context, client response…"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-check mr-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Collection --}}
<div id="mdlEdit" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmEdit">
                @csrf
                <input type="hidden" id="editId">
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Collections</p>
                        <h5 class="modal-title csd-modal__title">Update Collection</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Amount Due <span class="text-danger">*</span></label>
                                <input type="number" name="amount_due" id="editAmount" class="form-control" required step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Status</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Due Date</label>
                                <input type="date" name="due_date" id="editDueDate" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Follow-up Date</label>
                                <input type="date" name="followup_date" id="editFollowup" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Commitment Date</label>
                                <input type="date" name="commitment_date" id="editCommitmentDate" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Commitment Amount</label>
                                <input type="number" name="commitment_amount" id="editCommitmentAmount" class="form-control" step="0.01">
                            </div>
                        </div>
                        @if($canAssignToOthers ?? false)
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Assigned To</label>
                                <select name="assigned_to" id="editAssignedTo" class="form-control">
                                    <option value="">Unassigned</option>
                                    @foreach($executives as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-12">
                            <div class="form-group csd-field mb-0">
                                <label>Remarks</label>
                                <textarea name="remarks" id="editRemarks" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save-outline mr-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
