@extends('layouts.app')

@section('styles')
<style>
    /* Premium Page Styling */
    .bulkupload-wrapper {
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    /* Breathtaking Glassmorphic Title Card */
    .title-card-glass {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 255, 0.95) 100%);
        border-left: 5px solid #7F00FF;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .text-premium-dark {
        color: #343a40;
        font-weight: 700;
    }

    /* Form Container Card */
    .form-card-premium {
        background: #ffffff;
        border: 1px solid rgba(230, 230, 245, 0.8);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    /* Back circular navigation icon */
    .btn-back-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid rgba(220, 220, 235, 0.8);
        color: #495057;
        transition: all 0.25s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    }

    .btn-back-circle:hover {
        background: #7F00FF;
        color: #ffffff;
        border-color: #7F00FF;
        transform: translateX(-3px);
        box-shadow: 0 4px 12px rgba(127, 0, 255, 0.25);
    }

    /* Elegant Drag & Drop Upload Zone */
    .upload-drag-zone {
        border: 2px dashed rgba(127, 0, 255, 0.35);
        background: rgba(127, 0, 255, 0.015);
        border-radius: 16px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }

    .upload-drag-zone:hover {
        border-color: #7F00FF;
        background: rgba(127, 0, 255, 0.04);
        transform: translateY(-2px);
    }

    .upload-drag-zone i {
        font-size: 48px;
        color: #7F00FF;
        margin-bottom: 12px;
        transition: transform 0.2s ease;
    }

    .upload-drag-zone:hover i {
        transform: scale(1.1);
    }

    .upload-drag-zone input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    /* Template download banner card */
    .template-banner {
        background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        color: #ffffff !important;
        border-radius: 14px;
        padding: 20px 24px;
        box-shadow: 0 8px 25px rgba(30, 60, 114, 0.15);
    }

    .template-banner a {
        background: #ffffff;
        color: #1e3c72 !important;
        font-weight: 700;
        border-radius: 10px;
        padding: 8px 18px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        text-decoration: none !important;
    }

    .template-banner a:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(255, 255, 255, 0.2);
    }

    /* Styled select menus and text fields */
    .select-premium {
        height: 48px !important;
        border-radius: 12px !important;
        border: 1px solid rgba(210, 210, 225, 0.7) !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        color: #343a40;
    }

    .select-premium:focus {
        border-color: #7F00FF !important;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.15) !important;
    }

    /* Action triggers */
    .btn-upload-submit {
        background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 30px !important;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 15px rgba(127, 0, 255, 0.25);
        transition: all 0.2s ease;
    }

    .btn-upload-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(127, 0, 255, 0.35);
    }

    /* Guidelines listing */
    .instructions-card {
        background-color: #fcfdfe;
        border: 1px solid rgba(220, 220, 235, 0.8);
        border-radius: 16px;
        padding: 24px;
    }

    .instructions-table th {
        font-weight: 700 !important;
        color: #343a40 !important;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-top: none !important;
    }

    .instructions-table td {
        font-size: 13px !important;
        vertical-align: middle !important;
    }

    /* Session Errors Panel */
    .bulk-errors-box {
        background: rgba(255, 77, 79, 0.04);
        border: 1px solid rgba(255, 77, 79, 0.15);
        border-radius: 14px;
        padding: 20px;
        margin-top: 25px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid bulkupload-wrapper">

    <!-- 🗓 Title and Navigation Header Card -->
    <div class="card mb-4 title-card-glass">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center" style="gap: 15px;">
                <a href="{{ url('client/Fresh') }}" class="btn-back-circle" data-toggle="tooltip" data-placement="top" title="Go Back">
                    <i class="mdi mdi-keyboard-backspace font-size-18"></i>
                </a>
                <div>
                    <h3 class="text-premium-dark font-size-18 mb-1">📂 Companies Bulk Upload</h3>
                    <p class="text-muted font-size-12 mb-0">Import multiple companies instantly from a single CSV spreadsheet template.</p>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <ol class="breadcrumb m-0 bg-transparent p-0 font-size-12">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-primary"><i class="bx bx-home-alt"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/client/Fresh') }}" class="text-primary">Companies</a></li>
                    <li class="breadcrumb-item active text-muted">Bulk Upload</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Alert Messaging -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; font-weight: 500;">
        <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; font-weight: 500;">
        <i class="mdi mdi-alert-circle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; font-weight: 500;">
        <i class="mdi mdi-alert-circle mr-1"></i> Please resolve validation errors listed below.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Log for skipped/failed rows -->
    @if(session('bulk_errors'))
    <div class="bulk-errors-box mb-4">
        <h5 class="text-danger font-size-14 mb-2"><i class="mdi mdi-alert-octagon mr-1"></i> Import Action Log: Skipping Details</h5>
        <ul class="mb-0 text-muted font-size-12" style="max-height: 200px; overflow-y: auto; padding-left: 18px;">
            @foreach(session('bulk_errors') as $error)
            <li class="mb-1 text-danger">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        <!-- Left: Upload Action Frame -->
        <div class="col-lg-7">
            <div class="card form-card-premium">
                <div class="card-body p-4 p-md-5">
                    <h4 class="text-premium-dark font-size-16 mb-4">📤 Upload Your Spreadsheet</h4>

                    <form class="custom-validation" action="{{ route('clients.bulkupload.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Executive Allocation -->
                        @if($requiresAssign)
                        <div class="form-group mb-4">
                            <label class="font-weight-600 mb-2">Assign Uploaded Leads To:</label>
                            <select name="referral" id="referral" class="form-control select-premium" required>
                                <option value="" selected>Select Sales Executive</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('referral') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->getRoleNames()->first() ?? 'Sales' }})
                                </option>
                                @endforeach
                            </select>
                            <span class="text-muted font-size-11 mt-1 d-block">All correctly parsed companies in this file will automatically route to the selected team member's active list.</span>
                            @error('referral')
                            <span class="text-danger font-size-12 mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif

                        <!-- Drag & Drop Zone -->
                        <div class="form-group mb-5">
                            <label class="font-weight-600 mb-2">Select Upload Spreadsheet (CSV format):</label>
                            <div class="upload-drag-zone" id="dropzone">
                                <i class="mdi mdi-cloud-upload-outline"></i>
                                <h5 class="font-size-14 mb-1">Drag and drop your file here</h5>
                                <p class="text-muted font-size-12 mb-3">or click to browse from local computer</p>
                                <span id="file-name" class="badge badge-soft-primary px-3 py-2 font-size-12 d-none"></span>
                                <input type="file" name="file" id="file" accept=".csv" required onchange="displaySelectedFile(this)">
                            </div>
                            @error('file')
                            <span class="text-danger font-size-12 mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Action Controls -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <a href="{{ url('client/Fresh') }}" class="btn btn-light" style="border-radius: 10px; padding: 10px 20px;">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-upload-submit">
                                <i class="mdi mdi-check-all mr-1"></i> Start Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Sample & Instructions Sidebar -->
        <div class="col-lg-5">
            <!-- Downloader Widget -->
            <div class="card template-banner mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
                    <div>
                        <h4 class="font-size-15 font-weight-700 text-white mb-1">📥 Standard Template Format</h4>
                        <p class="text-white-50 font-size-12 mb-0">Download our pre-structured template containing example columns to avoid header mismatch errors.</p>
                    </div>
                    <a href="{{ route('clients.bulkupload.sample') }}">
                        <i class="mdi mdi-file-excel"></i> Download Template
                    </a>
                </div>
            </div>

            <!-- Guidelines Frame -->
            <div class="card instructions-card">
                <h4 class="text-premium-dark font-size-15 mb-3"><i class="mdi mdi-information-outline text-primary mr-1"></i> Excel/CSV Column Blueprint</h4>
                <p class="text-muted font-size-12 mb-4">Your uploaded spreadsheet must strictly feature these matching columns in order. Any missing mandatory cells will cause that row to be skipped.</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped instructions-table mb-0">
                        <thead>
                            <tr>
                                <th>Column Header</th>
                                <th class="text-center">Required</th>
                                <th>Format / Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Company Name</strong></td>
                                <td class="text-center"><span class="badge badge-soft-danger px-2">Yes</span></td>
                                <td class="text-muted font-size-12">Unique business title.</td>
                            </tr>
                            <tr>
                                <td><strong>Contact Person</strong></td>
                                <td class="text-center"><span class="badge badge-soft-danger px-2">Yes</span></td>
                                <td class="text-muted font-size-12">Primary contact full name.</td>
                            </tr>
                            <tr>
                                <td><strong>Designation</strong></td>
                                <td class="text-center"><span class="badge badge-soft-danger px-2">Yes</span></td>
                                <td class="text-muted font-size-12">e.g. Director, Owner, Partner.</td>
                            </tr>
                            <tr>
                                <td><strong>Email ID</strong></td>
                                <td class="text-center"><span class="badge badge-soft-secondary px-2">No</span></td>
                                <td class="text-muted font-size-12">Must be a valid email format.</td>
                            </tr>
                            <tr>
                                <td><strong>Mobile</strong></td>
                                <td class="text-center"><span class="badge badge-soft-danger px-2">Yes</span></td>
                                <td class="text-muted font-size-12">10 digits, starting with 6, 7, 8, or 9.</td>
                            </tr>
                            <tr>
                                <td><strong>City</strong></td>
                                <td class="text-center"><span class="badge badge-soft-danger px-2">Yes</span></td>
                                <td class="text-muted font-size-12">Location / registered city.</td>
                            </tr>
                            <tr>
                                <td><strong>Website Link</strong></td>
                                <td class="text-center"><span class="badge badge-soft-secondary px-2">No</span></td>
                                <td class="text-muted font-size-12">URL (http/https).</td>
                            </tr>
                            <tr>
                                <td><strong>Address</strong></td>
                                <td class="text-center"><span class="badge badge-soft-danger px-2">Yes</span></td>
                                <td class="text-muted font-size-12">Full office location address.</td>
                            </tr>
                            <tr>
                                <td><strong>Remarks</strong></td>
                                <td class="text-center"><span class="badge badge-soft-danger px-2">Yes</span></td>
                                <td class="text-muted font-size-12">Initial contact / pipeline comments.</td>
                            </tr>
                            <tr>
                                <td><strong>TBRO Touchpoint Type</strong></td>
                                <td class="text-center"><span class="badge badge-soft-secondary px-2">No</span></td>
                                <td class="text-muted font-size-12">e.g. Call, WhatsApp, Direct visit, etc.</td>
                            </tr>
                            <tr>
                                <td><strong>Schedule Time</strong></td>
                                <td class="text-center"><span class="badge badge-soft-secondary px-2">No</span></td>
                                <td class="text-muted font-size-12">Format: "hh:mm AM/PM" (e.g., 03:30 PM).</td>
                            </tr>
                            <tr>
                                <td><strong>Schedule Date (TBRO)</strong></td>
                                <td class="text-center"><span class="badge badge-soft-secondary px-2">No</span></td>
                                <td class="text-muted font-size-12">Format: DD-MM-YYYY (e.g., 15-05-2026).</td>
                            </tr>
                            <tr>
                                <td><strong>STS Routing Status</strong></td>
                                <td class="text-center"><span class="badge badge-soft-secondary px-2">No</span></td>
                                <td class="text-muted font-size-12">Status of lead (Defaults to "Fresh").</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // File upload label updater script
    function displaySelectedFile(input) {
        const fileBox = document.getElementById('file-name');
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            fileBox.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            fileBox.classList.remove('d-none');
        } else {
            fileBox.classList.add('d-none');
        }
    }

    // Drag-and-drop feedback integrations
    const dropzone = document.getElementById('dropzone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.style.borderColor = '#7F00FF';
            dropzone.style.background = 'rgba(127, 0, 255, 0.08)';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.style.borderColor = 'rgba(127, 0, 255, 0.35)';
            dropzone.style.background = 'rgba(127, 0, 255, 0.015)';
        }, false);
    });
</script>
@endsection
