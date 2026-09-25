{{-- Work Location Shift Start Modal --}}
<div class="modal fade wms-shift-modal" id="modal-start-shift-location" tabindex="-1" role="dialog" aria-labelledby="modalStartShiftTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title font-weight-bold text-white mb-0" id="modalStartShiftTitle">
                        <i class="mdi mdi-clock-start mr-1.5 text-success"></i> Start Daily Work Shift
                    </h5>
                    <small class="text-white-50 font-size-12">Select where you are working from today before clocking in</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div>
                        <span class="text-muted font-size-11 text-uppercase font-weight-bold letter-spacing-1">Today's Date</span>
                        <div class="font-weight-bold text-dark font-size-14">
                            <i class="mdi mdi-calendar-today text-primary mr-1"></i> {{ now()->format('l, d F Y') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-muted font-size-11 text-uppercase font-weight-bold letter-spacing-1">Current Time</span>
                        <div class="font-weight-bold text-dark font-size-14" id="modal-shift-live-time">
                            <i class="mdi mdi-clock-outline text-info mr-1"></i> {{ now()->format('h:i A') }}
                        </div>
                    </div>
                </div>

                <label class="font-size-12 font-weight-bold text-dark mb-2">
                    Work Location Category <span class="text-danger">*</span>
                </label>

                <input type="hidden" id="shift-selected-location" value="Office">

                <div class="wms-work-location-grid mb-3">
                    {{-- Option 1: Office --}}
                    <div class="wms-work-location-card active" data-location="Office">
                        <div class="wms-work-location-icon icon-office">
                            <i class="mdi mdi-office-building"></i>
                        </div>
                        <div class="wms-work-location-title">Office</div>
                        <div class="wms-work-location-desc">Working from company office branch or premises</div>
                        <div class="wms-work-location-radio"></div>
                    </div>

                    {{-- Option 2: Work from Home --}}
                    <div class="wms-work-location-card card-wfh" data-location="Work from Home">
                        <div class="wms-work-location-icon icon-wfh">
                            <i class="mdi mdi-home-variant"></i>
                        </div>
                        <div class="wms-work-location-title">Work from Home</div>
                        <div class="wms-work-location-desc">Working remotely from home desk</div>
                        <div class="wms-work-location-radio"></div>
                    </div>

                    {{-- Option 3: Client Place --}}
                    <div class="wms-work-location-card card-client" data-location="Client Place">
                        <div class="wms-work-location-icon icon-client">
                            <i class="mdi mdi-briefcase-check"></i>
                        </div>
                        <div class="wms-work-location-title">Client Place</div>
                        <div class="wms-work-location-desc">Visiting or on-site work at client location</div>
                        <div class="wms-work-location-radio"></div>
                    </div>
                </div>

                {{-- Location Details --}}
                <div class="form-group mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <label for="shift-work-location-notes" class="font-size-12 font-weight-bold text-dark mb-0">
                            Location Details
                        </label>
                        <button type="button" id="btn-auto-detect-location" class="btn btn-sm btn-outline-primary py-0 px-2 font-size-11 font-weight-medium rounded-pill shadow-none" title="Refresh or auto-detect current location">
                            <i class="mdi mdi-crosshairs-gps mr-1"></i> <span id="auto-detect-btn-label">Auto Detect</span>
                        </button>
                    </div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light text-primary border-right-0" id="location-input-icon-addon">
                                <i class="mdi mdi-map-marker-radius font-size-16" id="shift-location-icon"></i>
                            </span>
                        </div>
                        <input type="text" id="shift-work-location-notes" class="form-control font-size-13 border-left-0" 
                            placeholder="e.g. #270/2/21, Sir M V Road, Near Bhanu Nursing Home, Bommanahalli, Bengaluru - 560068" maxlength="500">
                    </div>
                    <small class="font-size-11 mt-1 d-block text-muted" id="shift-location-feedback">
                        <i class="mdi mdi-information-outline mr-0.5"></i> Full address is auto-detected via GPS, and you can edit or add details anytime.
                    </small>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-3 font-weight-medium" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" id="btn-submit-start-shift" class="btn btn-success px-4 font-weight-bold shadow-sm" disabled title="Please wait for location detection or enter address manually">
                    <i class="mdi mdi-play mr-1"></i> Start Shift Now
                </button>
            </div>
        </div>
    </div>
</div>
