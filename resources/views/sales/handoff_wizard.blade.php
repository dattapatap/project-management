@extends('layouts.app')

@section('content')
<div class="container erp-page pb-5">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between my-4">
                <h4 class="mb-0 text-white font-weight-bold">
                    <i class="mdi mdi-arrow-decision-outline mr-2 text-success"></i>Sales-to-Operations Handoff Wizard
                </h4>
                <div class="page-title-right">
                    <a href="{{ route('sales.pipeline') }}" class="btn btn-outline-light btn-sm">
                        <i class="mdi mdi-arrow-left mr-1"></i>Back to Pipeline
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Steps Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark-card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between wizard-steps">
                        <div class="step-indicator active" id="step-ind-1">
                            <span class="step-num">1</span>
                            <span class="step-text text-white">Project Scope</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-ind-2">
                            <span class="step-num">2</span>
                            <span class="step-text text-white">Select Services</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-ind-3">
                            <span class="step-num">3</span>
                            <span class="step-text text-white">Financials</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-ind-4">
                            <span class="step-num">4</span>
                            <span class="step-text text-white">Review & Handoff</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Form -->
    <form id="handoffWizardForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="client_id" value="{{ $client->id }}">

        <!-- Step 1: Project Metadata -->
        <div class="wizard-step-content active" id="step-content-1">
            <div class="card bg-dark-card border-0">
                <div class="card-body">
                    <h5 class="text-white font-weight-bold mb-4">Step 1: Project Details</h5>
                    
                    <div class="form-group">
                        <label class="text-white-50">Client Name</label>
                        <input type="text" class="form-control bg-dark border-secondary text-white" value="{{ $client->name }}" disabled>
                    </div>

                    <div class="form-group">
                        <label class="text-white">Project Name <span class="text-danger">*</span></label>
                        <input type="text" name="project_name" class="form-control bg-dark-input text-white border-secondary" required placeholder="e.g. {{ $client->name }} - E-commerce Portal">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-white">Department Category <span class="text-danger">*</span></label>
                                <select name="category" id="wizard-category" class="form-control bg-dark-input text-white border-secondary" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-white">Sub Category <span class="text-danger">*</span></label>
                                <select name="sub_category" id="wizard-subcategory" class="form-control bg-dark-input text-white border-secondary" required>
                                    <option value="">-- Select Sub Category --</option>
                                    @foreach($subcategories as $sub)
                                        <option value="{{ $sub->id }}" data-category="{{ $sub->category }}">{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-white">Estimated Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control bg-dark-input text-white border-secondary" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-white">Estimated End Date/Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control bg-dark-input text-white border-secondary" required value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-white">Scope & Requirements Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control bg-dark-input text-white border-secondary" rows="4" required placeholder="Outline project deliverables, customer expectations, and general constraints..."></textarea>
                    </div>

                    <div class="text-right mt-4">
                        <button type="button" class="btn btn-primary next-step" data-next="2">Next Step <i class="mdi mdi-arrow-right ml-1"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Select Services -->
        <div class="wizard-step-content" id="step-content-2">
            <div class="card bg-dark-card border-0">
                <div class="card-body">
                    <h5 class="text-white font-weight-bold mb-4">Step 2: Choose Services from Catalog</h5>
                    <p class="text-white-50 font-size-13 mb-4">
                        <i class="mdi mdi-information-outline mr-1"></i>
                        Select the catalog items matching the package. You will be prompted to set a custom price for each item.
                    </p>

                    <div class="row">
                        <div class="col-md-7">
                            <h6 class="text-white font-weight-semibold mb-3">Available Catalog Offerings</h6>
                            <div class="list-group bg-dark border-secondary rounded overflow-y-auto" style="max-height: 350px;">
                                @foreach($catalogItems as $item)
                                    <div class="list-group-item bg-transparent text-white border-secondary-dark d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-0 text-white font-weight-bold">{{ $item->name }}</h6>
                                            <span class="badge badge-pill badge-soft-light font-size-11">{{ $item->category }} · {{ ucfirst($item->billing_cycle) }}</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary select-service-btn" data-id="{{ $item->id }}" data-name="{{ $item->name }}">
                                            Select Service
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="text-white font-weight-semibold mb-3">Selected Services & Dynamic Pricing</h6>
                            <div class="selected-services-container p-3 rounded" style="background: rgba(0, 0, 0, 0.2); min-height: 250px;">
                                <div id="no-services-placeholder" class="text-center py-5 text-muted-light">
                                    <i class="mdi mdi-cart-outline font-size-36 d-block"></i>
                                    <span>No services selected yet.</span>
                                </div>
                                <ul class="list-unstyled mb-0" id="selected-services-list">
                                    <!-- Selected services injected here -->
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary prev-step" data-prev="1"><i class="mdi mdi-arrow-left mr-1"></i> Previous</button>
                        <button type="button" class="btn btn-primary next-step" id="step2-next-btn" data-next="3" disabled>Next Step <i class="mdi mdi-arrow-right ml-1"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Financials & Receipts -->
        <div class="wizard-step-content" id="step-content-3">
            <div class="card bg-dark-card border-0">
                <div class="card-body">
                    <h5 class="text-white font-weight-bold mb-4">Step 3: Financial Agreement & Advance Receipt</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-white">Package Total Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="package" id="package-total" class="form-control bg-dark-input text-white border-secondary" required placeholder="e.g. 150000" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-white">Advance Payment Paid (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="advance" id="package-advance" class="form-control bg-dark-input text-white border-secondary" required placeholder="e.g. 50000" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-white">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_type" id="payment_type" class="form-control bg-dark-input text-white border-secondary" required>
                            <option value="Online">Online / Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>

                    <!-- Dynamic Payment Sub-Fields -->
                    <div id="payment-online-fields" class="payment-fields">
                        <div class="form-group animate-entrance">
                            <label class="text-white">Transaction Reference / UTR ID <span class="text-danger">*</span></label>
                            <input type="text" name="transactionid" id="transactionid" class="form-control bg-dark-input text-white border-secondary" placeholder="e.g. UTR1234567890">
                        </div>
                    </div>

                    <div id="payment-cheque-fields" class="payment-fields d-none">
                        <div class="form-group animate-entrance">
                            <label class="text-white">Cheque Scan Copy / Receipt File <span class="text-danger">*</span></label>
                            <input type="file" name="payment_cheque_receipt" id="payment_cheque_receipt" class="form-control-file text-white">
                            <small class="text-white-50">Upload jpeg, png, or pdf. Max 2MB.</small>
                        </div>
                    </div>

                    <div id="payment-cash-fields" class="payment-fields d-none">
                        <div class="form-group animate-entrance">
                            <label class="text-white">Cash Receipt Document <span class="text-danger">*</span></label>
                            <input type="file" name="payment_cash_receipt" id="payment_cash_receipt" class="form-control-file text-white">
                            <small class="text-white-50">Upload scan copy of signed cash receipt (jpeg, png, pdf).</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary prev-step" data-prev="2"><i class="mdi mdi-arrow-left mr-1"></i> Previous</button>
                        <button type="button" class="btn btn-primary next-step" data-next="4">Next Step <i class="mdi mdi-arrow-right ml-1"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Scope Attachment, Review & Handoff -->
        <div class="wizard-step-content" id="step-content-4">
            <div class="card bg-dark-card border-0">
                <div class="card-body">
                    <h5 class="text-white font-weight-bold mb-4">Step 4: Finalize Scope Document & Submit</h5>

                    <div class="form-group mb-4">
                        <label class="text-white">Project Scope Document / Proforma Invoice (Optional)</label>
                        <input type="file" name="proforma" class="form-control-file text-white">
                        <small class="text-white-50">Upload proposal scope or client-signed invoice (jpeg, png, pdf).</small>
                    </div>

                    <hr class="border-secondary-dark my-4">

                    <!-- Review Pane -->
                    <div class="review-pane p-4 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                        <h6 class="text-white font-weight-bold mb-3"><i class="mdi mdi-eye-outline mr-1 text-primary"></i> Handoff Summary Review</h6>
                        <div class="row font-size-14 text-white-80">
                            <div class="col-md-6 mb-2">
                                <span class="text-muted-light d-block small">PROJECT NAME</span>
                                <strong id="rev-project-name" class="text-white">-</strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="text-muted-light d-block small">ESTIMATED TIMELINE</span>
                                <strong id="rev-dates" class="text-white">-</strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="text-muted-light d-block small">SERVICE ITEMS BUDGET</span>
                                <strong id="rev-package" class="text-success">-</strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="text-muted-light d-block small">ADVANCE RECEIVED</span>
                                <strong id="rev-advance" class="text-success">-</strong>
                            </div>
                            <div class="col-md-12">
                                <span class="text-muted-light d-block small mb-1">PROVISIONED SERVICES</span>
                                <ul id="rev-services" class="pl-3 mb-0 small text-white-50">
                                    <!-- List of services -->
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-secondary prev-step" data-prev="3"><i class="mdi mdi-arrow-left mr-1"></i> Previous</button>
                        <button type="submit" class="btn btn-success px-4" id="submit-wizard-btn">
                            <i class="mdi mdi-check-all mr-1"></i> Complete Handoff & Launch Project
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Price Input Popup Modal -->
<div class="modal fade" id="priceModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h6 class="modal-title font-weight-bold text-primary"><i class="mdi mdi-cash-usd mr-1"></i>Set Service Price</h6>
            </div>
            <div class="modal-body">
                <p id="price-modal-title" class="font-size-13 text-muted-light"></p>
                <div class="form-group mb-0">
                    <label>Custom Price for Client (₹) <span class="text-danger">*</span></label>
                    <input type="number" id="popup-service-price" class="form-control bg-dark-input text-white border-secondary" placeholder="e.g. 45000" min="1">
                </div>
            </div>
            <div class="modal-footer border-secondary p-2">
                <button type="button" class="btn btn-sm btn-secondary" id="cancel-price-btn">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="confirm-price-btn">Confirm & Add</button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-dark-card {
        background-color: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .bg-dark-input {
        background-color: rgba(0, 0, 0, 0.25) !important;
    }
    .text-muted-light {
        color: #cbd5e1 !important;
    }
    .text-white-80 {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    .border-secondary-dark {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    .wizard-steps {
        position: relative;
    }
    .step-indicator {
        text-align: center;
        opacity: 0.5;
        flex: 1;
        transition: all 0.3s;
    }
    .step-indicator.active {
        opacity: 1;
    }
    .step-num {
        display: inline-flex;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-bottom: 5px;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
    .step-indicator.active .step-num {
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .step-line {
        height: 2px;
        background: rgba(255, 255, 255, 0.1);
        flex-grow: 1;
        align-self: center;
        margin-bottom: 25px;
    }
    .wizard-step-content {
        display: none;
    }
    .wizard-step-content.active {
        display: block;
        animation: fadeIn 0.4s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let selectedServices = [];
        let activeSelectingServiceId = null;
        let activeSelectingServiceName = '';

        // Dynamic Subcategory Filtering based on category dept
        $('#wizard-category').change(function() {
            const catId = $(this).val();
            if (!catId) {
                $('#wizard-subcategory option').show();
                return;
            }
            $('#wizard-subcategory option').each(function() {
                const subCat = $(this);
                if (subCat.data('category') == catId || subCat.val() === '') {
                    subCat.show();
                } else {
                    subCat.hide();
                }
            });
            $('#wizard-subcategory').val('');
        });

        // Step Navigation (Next / Prev)
        $('.next-step').click(function() {
            const nextStep = $(this).data('next');
            
            // Basic validation for Step 1
            if (nextStep === 2) {
                const pName = $('input[name="project_name"]').val();
                const cat = $('#wizard-category').val();
                const sub = $('#wizard-subcategory').val();
                const desc = $('textarea[name="description"]').val();
                if (!pName || !cat || !sub || !desc) {
                    alertify.error("Please fill in all required project scope fields.");
                    return;
                }
            }

            $('.wizard-step-content').removeClass('active');
            $('#step-content-' + nextStep).addClass('active');

            $('.step-indicator').removeClass('active');
            for (let i = 1; i <= nextStep; i++) {
                $('#step-ind-' + i).addClass('active');
            }

            if (nextStep === 4) {
                buildReviewSummary();
            }
        });

        $('.prev-step').click(function() {
            const prevStep = $(this).data('prev');
            $('.wizard-step-content').removeClass('active');
            $('#step-content-' + prevStep).addClass('active');

            $('.step-indicator').removeClass('active');
            for (let i = 1; i <= prevStep; i++) {
                $('#step-ind-' + i).addClass('active');
            }
        });

        // Payment Type dynamic fields toggle
        $('#payment_type').change(function() {
            const pType = $(this).val();
            $('.payment-fields').addClass('d-none');
            $('#transactionid, #payment_cheque_receipt, #payment_cash_receipt').removeAttr('required');

            if (pType === 'Online') {
                $('#payment-online-fields').removeClass('d-none');
                $('#transactionid').attr('required', 'required');
            } else if (pType === 'Cheque') {
                $('#payment-cheque-fields').removeClass('d-none');
                $('#payment_cheque_receipt').attr('required', 'required');
            } else if (pType === 'Cash') {
                $('#payment-cash-fields').removeClass('d-none');
                $('#payment_cash_receipt').attr('required', 'required');
            }
        });

        // Catalog Service Selection Click
        $('.select-service-btn').click(function() {
            activeSelectingServiceId = $(this).data('id');
            activeSelectingServiceName = $(this).data('name');

            // Set up modal popup to ask the price
            $('#price-modal-title').html(`Configuring price for: <strong>${activeSelectingServiceName}</strong>`);
            $('#popup-service-price').val('');
            $('#priceModal').modal('show');
        });

        $('#cancel-price-btn').click(function() {
            $('#priceModal').modal('hide');
        });

        // Confirm price and Add
        $('#confirm-price-btn').click(function() {
            const price = parseFloat($('#popup-service-price').val());
            if (!price || price <= 0) {
                alertify.error("Please enter a valid positive price.");
                return;
            }

            // Check if already selected, update it
            const existingIndex = selectedServices.findIndex(s => s.id === activeSelectingServiceId);
            if (existingIndex > -1) {
                selectedServices[existingIndex].price = price;
            } else {
                selectedServices.push({
                    id: activeSelectingServiceId,
                    name: activeSelectingServiceName,
                    price: price
                });
            }

            $('#priceModal').modal('hide');
            renderSelectedServices();
            recomputeFinancialTotals();
            alertify.success(`Added ${activeSelectingServiceName} at ₹${price.toLocaleString()}`);
        });

        function renderSelectedServices() {
            const container = $('#selected-services-list');
            container.empty();

            if (selectedServices.length === 0) {
                $('#no-services-placeholder').show();
                $('#step2-next-btn').attr('disabled', 'disabled');
                return;
            }

            $('#no-services-placeholder').hide();
            $('#step2-next-btn').removeAttr('disabled');

            selectedServices.forEach((s, idx) => {
                const li = `
                    <li class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary-dark text-white">
                        <div>
                            <span class="font-weight-bold d-block font-size-13">${s.name}</span>
                            <span class="text-success font-weight-semibold">₹${s.price.toLocaleString()}</span>
                            <input type="hidden" name="services[${idx}][id]" value="${s.id}">
                            <input type="hidden" name="services[${idx}][price]" value="${s.price}">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-service-btn" data-index="${idx}">
                            <i class="mdi mdi-delete-outline"></i>
                        </button>
                    </li>
                `;
                container.append(li);
            });

            // Bind delete button
            $('.remove-service-btn').click(function() {
                const idx = $(this).data('index');
                selectedServices.splice(idx, 1);
                renderSelectedServices();
                recomputeFinancialTotals();
            });
        }

        function recomputeFinancialTotals() {
            const sum = selectedServices.reduce((acc, curr) => acc + curr.price, 0);
            $('#package-total').val(sum);
            // Default advance to 0 or leave blank
            $('#package-advance').val(0);
        }

        // Summary generation for Step 4
        function buildReviewSummary() {
            $('#rev-project-name').text($('input[name="project_name"]').val());
            
            const start = $('input[name="start_date"]').val();
            const end = $('input[name="end_date"]').val();
            $('#rev-dates').text(`${start} to ${end}`);

            const total = parseFloat($('#package-total').val()) || 0;
            const advance = parseFloat($('#package-advance').val()) || 0;
            $('#rev-package').text(`₹${total.toLocaleString()}`);
            $('#rev-advance').text(`₹${advance.toLocaleString()}`);

            const revList = $('#rev-services');
            revList.empty();
            selectedServices.forEach(s => {
                revList.append(`<li>${s.name} - ₹${s.price.toLocaleString()}</li>`);
            });
        }

        // Form Submit
        $('#handoffWizardForm').on('submit', function(e) {
            e.preventDefault();
            
            const total = parseFloat($('#package-total').val()) || 0;
            const advance = parseFloat($('#package-advance').val()) || 0;
            if (advance > total) {
                alertify.error("Advance amount cannot exceed package total amount.");
                return;
            }

            const formData = new FormData(this);

            // Disable submit button during load
            $('#submit-wizard-btn').attr('disabled', 'disabled').html(`<i class="mdi mdi-spin mdi-loading mr-1"></i> Processing Handoff...`);

            $.ajax({
                url: "{{ route('sales.handoff.process') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alertify.success(response.message);
                        setTimeout(() => {
                            window.location.href = "{{ url('/') }}/projects/" + btoa(response.project_id) + "/history";
                        }, 1200);
                    } else {
                        alertify.error(response.message || "Handoff failed.");
                        $('#submit-wizard-btn').removeAttr('disabled').html(`<i class="mdi mdi-check-all mr-1"></i> Complete Handoff & Launch Project`);
                    }
                },
                error: function(xhr) {
                    alertify.error(xhr.responseJSON?.message || "An error occurred during handoff submission.");
                    $('#submit-wizard-btn').removeAttr('disabled').html(`<i class="mdi mdi-check-all mr-1"></i> Complete Handoff & Launch Project`);
                }
            });
        });
    });
</script>
@endsection
