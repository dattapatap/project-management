@extends('layouts.app')

@section('styles')
<style>
    /* Styling System Custom Tokens */
    .form-wrapper-premium {
        font-family: 'Outfit', 'Inter', -apple-system, sans-serif;
        padding-bottom: 3rem;
    }

    .btn-back-premium {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #4b5563;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
    }

    .btn-back-premium:hover {
        background: #f8fafc;
        color: #4f46e5;
        border-color: #cbd5e1;
        transform: translateX(-4px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);
    }

    .btn-back-premium i {
        font-size: 18px;
    }

    .form-card-premium {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .form-card-header {
        background: linear-gradient(135deg, #1e1e38 0%, #0f172a 100%);
        padding: 24px 30px;
        color: #ffffff;
        border-bottom: none;
    }

    .form-card-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: -0.3px;
        color: #ffffff;
    }

    .form-card-subtitle {
        font-size: 13px;
        color: #94a3b8;
        margin-bottom: 0;
    }

    .form-section-divider {
        position: relative;
        padding-bottom: 8px;
        margin-bottom: 24px;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
    }

    .form-section-title i {
        font-size: 18px;
        margin-right: 8px;
        color: #6366f1;
    }

    .form-group-premium {
        margin-bottom: 20px;
    }

    .form-group-premium label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .form-group-premium label i {
        font-size: 15px;
        margin-right: 6px;
        color: #94a3b8;
    }

    .text_required {
        color: #ef4444;
        margin-left: 3px;
    }

    .input-premium {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 10px 14px !important;
        height: auto !important;
        font-size: 14px !important;
        color: #0f172a !important;
        background-color: #ffffff !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.01) !important;
    }

    .input-premium:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12), inset 0 1px 2px rgba(0, 0, 0, 0) !important;
        background-color: #ffffff !important;
        outline: none !important;
    }

    .input-premium::placeholder {
        color: #94a3b8 !important;
    }

    /* Custom Standard Select */
    .select-premium {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 10px 14px !important;
        height: 43px !important;
        font-size: 14px !important;
        color: #0f172a !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background-color: #ffffff !important;
    }

    .select-premium:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
    }

    /* Select2 Overrides to match theme */
    .select2-container--default .select2-selection--single {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        height: 43px !important;
        padding: 6px 12px !important;
        transition: all 0.25s ease !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
        outline: none !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: #0f172a !important;
        font-size: 14px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 41px !important;
    }

    /* Validations states */
    .parsley-error,
    .is-invalid {
        border-color: #ef4444 !important;
        background-color: #fffafb !important;
    }

    .parsley-error:focus,
    .is-invalid:focus {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12) !important;
    }

    .invalid-feedback {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: #ef4444;
        margin-top: 6px;
    }

    /* Premium Button Actions */
    .btn-submit-premium {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #ffffff !important;
        font-weight: 600;
        padding: 12px 32px;
        border-radius: 12px;
        border: none;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        cursor: pointer;
    }

    .btn-submit-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(79, 70, 229, 0.45);
        filter: brightness(1.05);
    }

    .btn-submit-premium i {
        font-size: 18px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid form-wrapper-premium">
    <!-- Back Action Header -->
    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <a href="{{ url('/users') }}" class="btn-back-premium mr-3" data-toggle="tooltip" title="Back to User List">
                    <i class="mdi mdi-keyboard-backspace"></i>
                </a>
                <h4 class="mb-0 font-size-18 font-weight-bold text-dark">Create User Account</h4>
            </div>

            <div class="page-title-right d-none d-sm-block">
                <ol class="breadcrumb m-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-muted">{{ env('APP_NAME') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/users') }}" class="text-muted">Users</a></li>
                    <li class="breadcrumb-item active font-weight-semibold text-primary">Create</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Card Form -->
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card form-card-premium">
                <div class="form-card-header">
                    <h4 class="form-card-title">New Workspace Member</h4>
                    <p class="form-card-subtitle">Setup personal profile details, system operational access, and roles for the new employee.</p>
                </div>

                <div class="card-body p-4">
                    <form class="custom-validation" action="{{ route('users.store') }}" method="POST" novalidate>
                        @csrf
                        <div class="row">
                            <!-- Left Column: Personal Credentials -->
                            <div class="col-lg-6 pr-lg-4 border-right border-light">
                                <div class="form-section-divider">
                                    <h5 class="form-section-title">
                                        <i class="mdi mdi-account-card-details-outline"></i>Identity & Profile Info
                                    </h5>
                                </div>

                                <!-- Field: Name -->
                                <div class="form-group form-group-premium">
                                    <label for="name">
                                        <i class="mdi mdi-account"></i>Full Name <span class="text_required">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name', '') }}"
                                        class="form-control input-premium @error('name') parsley-error @enderror"
                                        placeholder="Enter full name" required>
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <!-- Field: Email -->
                                <div class="form-group form-group-premium">
                                    <label for="email">
                                        <i class="mdi mdi-email"></i>Email Address <span class="text_required">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                                        class="form-control input-premium @error('email') parsley-error @enderror"
                                        placeholder="username@domain.com" required>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <!-- Field: Phone -->
                                <div class="form-group form-group-premium">
                                    <label for="mobile">
                                        <i class="mdi mdi-phone"></i>Contact Number <span class="text_required">*</span>
                                    </label>
                                    <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}"
                                        class="form-control input-premium @error('mobile') parsley-error @enderror"
                                        placeholder="10-digit mobile number" minlength="10" maxlength="12"
                                        onKeyPress="return isNumberKey(event);" required>
                                    @error('mobile')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <!-- Row: Gender and DOB -->
                                <div class="row">
                                    <!-- Field: Gender -->
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-premium">
                                            <label for="gender">
                                                <i class="mdi mdi-gender-male-female"></i>Gender <span class="text_required">*</span>
                                            </label>
                                            <select class="form-control select-premium" name="gender" id="gender" required>
                                                <option selected value="">Select Gender</option>
                                                <option value="Male" @if(old('gender')=='Male' ) selected @endif>Male</option>
                                                <option value="Female" @if(old('gender')=='Female' ) selected @endif>Female</option>
                                            </select>
                                            @error('gender')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Field: DOB -->
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-premium">
                                            <label for="dob">
                                                <i class="mdi mdi-calendar-heart"></i>Birth Date <span class="text_required">*</span>
                                            </label>
                                            <input type="date" name="dob" id="dob" value="{{ old('dob') }}"
                                                class="form-control input-premium @error('dob') parsley-error @enderror"
                                                max="<?= date('Y-m-d', strtotime(date('Y-m-d') . ' -18 year')); ?>" required>
                                            @error('dob')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Row: Passwords -->
                                <div class="row">
                                    <!-- Field: Password -->
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-premium">
                                            <label for="password">
                                                <i class="mdi mdi-lock"></i>Password <span class="text_required">*</span>
                                            </label>
                                            <input type="password" name="password" id="password" value="{{ old('password', '') }}"
                                                class="form-control input-premium @error('password') parsley-error @enderror"
                                                placeholder="••••••••" required>
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Field: Confirm Password -->
                                    <div class="col-sm-6">
                                        <div class="form-group form-group-premium">
                                            <label for="password_confirmation">
                                                <i class="mdi mdi-lock-check"></i>Confirm Password <span class="text_required">*</span>
                                            </label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" value="{{ old('password', '') }}"
                                                class="form-control input-premium @error('password_confirmation') parsley-error @enderror"
                                                placeholder="••••••••" required>
                                            @error('password_confirmation')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Professional Settings -->
                            <div class="col-lg-6 pl-lg-4 mt-4 mt-lg-0">
                                <div class="form-section-divider">
                                    <h5 class="form-section-title">
                                        <i class="mdi mdi-briefcase-outline"></i>Work Profile & Access
                                    </h5>
                                </div>

                                <!-- Field: Member Code -->
                                <div class="form-group form-group-premium">
                                    <label for="code">
                                        <i class="mdi mdi-card-bulleted-outline"></i>Member Code (Employee ID) <span class="text_required">*</span>
                                    </label>
                                    <input type="text" name="code" id="code" value="{{ old('code') }}"
                                        class="form-control input-premium @error('code') parsley-error @enderror"
                                        placeholder="e.g., DN-2026-05" onkeyup="this.value = this.value.toUpperCase();" required>
                                    @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <!-- Field: Designation -->
                                <div class="form-group form-group-premium">
                                    <label for="designation">
                                        <i class="mdi mdi-badge-account-outline"></i>Designation / Job Title <span class="text_required">*</span>
                                    </label>
                                    <input type="text" name="designation" id="designation" value="{{ old('designation') }}"
                                        class="form-control input-premium @error('designation') parsley-error @enderror"
                                        placeholder="e.g., Senior Systems Developer" required>
                                    @error('designation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <!-- Field: Role -->
                                <div class="form-group form-group-premium">
                                    <label for="role">
                                        <i class="mdi mdi-shield-account"></i>System Authorization Role <span class="text_required">*</span>
                                    </label>
                                    <select class="form-control select2" name="role" id="role" required>
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $item)
                                        <option value="{{ $item->id }}" data-name="{{ $item->name }}" @if(old('role')==$item->id) selected @endif>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <!-- Field: Department -->
                                <div class="form-group form-group-premium">
                                    <label for="department">
                                        <i class="mdi mdi-office-building"></i>Assigned Department <span class="text_required">*</span>
                                    </label>
                                    <select class="form-control select2" name="department" id="department" required>
                                        <option value="">Select Department</option>
                                        @foreach ($derpartments as $item)
                                        <option value="{{ $item->id }}" @if(old('department')==$item->id) selected @endif>
                                            {{ $item->name }} ({{ $item->branch->code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('department')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <!-- Field: Joining Date -->
                                <div class="form-group form-group-premium">
                                    <label for="joining_date">
                                        <i class="mdi mdi-calendar-text"></i>Joining Date <span class="text_required">*</span>
                                    </label>
                                    <input type="date" name="joining_date" id="joining_date" value="{{ old('joining_date') }}"
                                        class="form-control input-premium @error('joining_date') parsley-error @enderror"
                                        max="<?= date('Y-m-d'); ?>" required>
                                    @error('joining_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="row mt-4 pt-3 border-top border-light">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn-submit-premium">
                                    <i class="mdi mdi-account-plus-outline mr-2"></i>Create Member Account
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }

    $(document).ready(function() {
        // Init tooltip
        $('[data-toggle="tooltip"]').tooltip();

        // Ensure Select2 fits form styles perfectly
        $('.select2').select2({
            width: '100%'
        });

        function toggleDepartmentRequired() {
            var selectedRoleName = $('#role option:selected').attr('data-name');
            if (selectedRoleName === 'Branch-Manager') {
                $('#department').removeAttr('required');
                $('#department').closest('.form-group').find('.text_required').hide();
            } else {
                $('#department').attr('required', 'required');
                $('#department').closest('.form-group').find('.text_required').show();
            }
        }

        $('#role').on('change', function() {
            toggleDepartmentRequired();
        });

        // Trigger on load
        toggleDepartmentRequired();
    });
</script>
@endsection