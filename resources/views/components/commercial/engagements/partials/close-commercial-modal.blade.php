<div class="modal fade csd-modal" id="mdlCloseCommercial" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content csd-modal__content">
            <div class="modal-header csd-modal__header">
                <div>
                    <p class="csd-modal__eyebrow">Commercial</p>
                    <h5 class="modal-title csd-modal__title">Close Commercial — <span id="closeEngagementTitle"></span></h5>
                </div>
                <button type="button" class="close csd-modal__close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <form id="frmCloseCommercial" enctype="multipart/form-data">
                <input type="hidden" id="closeEngagementId" name="engagement_id">
                <div class="modal-body csd-modal__body">
                    <p class="text-muted small mb-3">Client stays <strong>Matured</strong>. A new OD project and package will be created for this upsell order.</p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Category</label>
                            <select class="form-control" name="category" id="engagementCategory" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Sub-Category</label>
                            <select class="form-control" name="sub_category" id="engagementSubCategory" required>
                                <option value="">Select Sub-Category</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Package (₹)</label>
                            <input type="number" class="form-control" name="package" min="100" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Advance (₹)</label>
                            <input type="number" class="form-control" name="advance" min="100" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Payment Type</label>
                            <select class="form-control" name="payment_type" id="engPaymentType" required>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group d-none" id="engOnlineWrap">
                            <label>Transaction ID</label>
                            <input type="text" class="form-control" name="transactionid">
                        </div>
                        <div class="col-md-6 form-group d-none" id="engChequeWrap">
                            <label>Cheque Receipt</label>
                            <input type="file" class="form-control" name="payment_cheque_receipt" accept="image/*,application/pdf">
                        </div>
                        <div class="col-md-6 form-group" id="engCashWrap">
                            <label>Cash Receipt</label>
                            <input type="file" class="form-control" name="payment_cash_receipt" accept="image/*,application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer csd-modal__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Project &amp; Close Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
