@php
    $isEdit = !empty($contract);
    $c = $contract;
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card erp-table-card">
            <div class="card-body">
                @if(!$isEdit)
                <div class="form-group csd-field">
                    <label>Client <span class="text-danger">*</span></label>
                    <select name="client" class="form-control" required>
                        <option value="">Select client…</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="alert alert-light border mb-3">
                    <strong>Client:</strong> {{ $c->client->name ?? '—' }}
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group csd-field">
                            <label>Contract Type <span class="text-danger">*</span></label>
                            <select name="contract_type" class="form-control" required>
                                <option value="amc" {{ old('contract_type', $c?->contract_type ?? 'amc') === 'amc' ? 'selected' : '' }}>AMC — Annual Maintenance</option>
                                <option value="support" {{ old('contract_type', $c?->contract_type ?? '') === 'support' ? 'selected' : '' }}>Support — Support Plan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group csd-field">
                            <label>Billing Cycle <span class="text-danger">*</span></label>
                            <select name="billing_cycle" id="billingCycle" class="form-control" required>
                                <option value="monthly" {{ old('billing_cycle', $c?->billing_cycle ?? '') === 'monthly' ? 'selected' : '' }}>Monthly — reminder 5 days before end</option>
                                <option value="yearly" {{ old('billing_cycle', $c?->billing_cycle ?? 'yearly') === 'yearly' ? 'selected' : '' }}>Yearly — reminder 30 days before end</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group csd-field">
                            <label>Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="contractStartDate" class="form-control" required
                                value="{{ old('start_date', $c?->start_date?->format('Y-m-d') ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group csd-field">
                            <label>End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="contractEndDate" class="form-control" required
                                value="{{ old('end_date', $c?->end_date?->format('Y-m-d') ?? '') }}">
                            <small class="text-muted" id="endDateHint">End date auto-calculates from billing cycle when start date changes.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group csd-field">
                            <label>Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" required step="0.01" min="0"
                                value="{{ old('amount', $c?->amount ?? '') }}" placeholder="0.00">
                            <small class="text-muted">Per <span id="amountPeriodLabel">year</span></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group csd-field">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                @foreach(['active' => 'Active', 'expired' => 'Expired', 'renewed' => 'Renewed', 'cancelled' => 'Cancelled'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $c?->status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group csd-field mb-0">
                    <label>Contract Document <span class="text-muted font-weight-normal">(optional)</span></label>
                    <input type="file" name="document" class="form-control-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small class="text-muted">PDF, Word, or image — max 10 MB</small>
                    @if($isEdit && $c->document_path)
                    <div class="mt-2 d-flex align-items-center flex-wrap gap-2">
                        <a href="{{ route('csd.amc.document', $c) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="mdi mdi-file-download-outline"></i> {{ $c->document_name ?? 'Download document' }}
                        </a>
                        <label class="mb-0 ml-2">
                            <input type="checkbox" name="remove_document" value="1"> Remove current document
                        </label>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="mdi mdi-information-outline text-primary"></i> How it works</h6>
                <ul class="small text-muted pl-3 mb-0">
                    <li class="mb-2"><strong>Monthly</strong> contracts renew every month. System reminds <strong>5 days</strong> before end date.</li>
                    <li class="mb-2"><strong>Yearly</strong> contracts renew every year. System reminds <strong>30 days</strong> before end date.</li>
                    <li class="mb-2">Expiring contracts appear on the CSD dashboard and in <strong>Renewal Management</strong> (use Sync).</li>
                    <li>Upload signed agreement or PO for your records (optional).</li>
                </ul>
            </div>
        </div>
    </div>
</div>
