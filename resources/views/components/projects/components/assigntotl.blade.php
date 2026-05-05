<!-- Assign to Team Leader Modal -->
<div class="modal fade" id="assignToTLModal" tabindex="-1" role="dialog" aria-labelledby="assignToTLModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignToTLModalLabel">Assign Project to Team Leader</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="frm_assign_to_tl">
                @csrf
                <input type="hidden" name="projectid" id="assign_tl_project_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="assigned_to_tl">Select Team Leader</label>
                        <select class="form-control select2" name="assigned_to" id="assigned_to_tl" style="width: 100%;" required>
                            <option value="">Select Team Leader</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn_submit_assign_tl">Assign Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
