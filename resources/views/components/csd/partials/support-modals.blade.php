<div id="mdlAdd" class="modal fade csd-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <form id="frmAdd">
                @csrf
                <div class="modal-header csd-modal__header">
                    <div>
                        <p class="csd-modal__eyebrow">CSD · Support</p>
                        <h5 class="modal-title csd-modal__title">New Support Ticket</h5>
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
                        @if($canAssignToOthers ?? false)
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Assign To</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">Self</option>
                                    @foreach($executives as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-12">
                            <div class="form-group csd-field">
                                <label>Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" required placeholder="Brief summary of the issue">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required placeholder="Describe the issue in detail…"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="ticket">Ticket</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="escalation">Escalation</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group csd-field mb-0">
                                <label>Priority</label>
                                <select name="priority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Ticket</button>
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
                        <p class="csd-modal__eyebrow">CSD · Support</p>
                        <h5 class="modal-title csd-modal__title">Update Ticket</h5>
                    </div>
                    <button type="button" class="close csd-modal__close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body csd-modal__body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group csd-field">
                                <label>Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="editSubject" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group csd-field">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="editDescription" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group csd-field">
                                <label>Type</label>
                                <select name="type" id="editType" class="form-control">
                                    <option value="ticket">Ticket</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="escalation">Escalation</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group csd-field">
                                <label>Priority</label>
                                <select name="priority" id="editPriority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group csd-field">
                                <label>Status</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </div>
                        @if($canAssignToOthers ?? false)
                        <div class="col-12">
                            <div class="form-group csd-field mb-0">
                                <label>Assign To</label>
                                <select name="assigned_to" id="editAssignedTo" class="form-control">
                                    <option value="">Unassigned</option>
                                    @foreach($executives as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
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
