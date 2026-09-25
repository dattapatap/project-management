@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page">
    {{-- Page Header & Breadcrumbs --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between py-2">
                <div>
                    <h4 class="mb-1 font-size-18 font-weight-bold text-dark">My Profile</h4>
                    <span class="text-muted font-size-12">Manage your personal details, photo, and social networks</span>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ config('app.name', 'WMS') }}</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @php
        $emp = $user->emp;
        $dobFormatted = $emp?->dob ? \Carbon\Carbon::parse($emp->dob)->format('d M, Y') : 'Not set';
        $ageYears = $emp?->dob ? \Carbon\Carbon::parse($emp->dob)->age : null;
        $joiningFormatted = $emp?->joining_dt ? \Carbon\Carbon::parse($emp->joining_dt)->format('d M, Y') : 'Not set';
        $tenureYears = $emp?->joining_dt ? (int) \Carbon\Carbon::parse($emp->joining_dt)->diffInYears(now()) : 0;
        $maxDobDate = \Carbon\Carbon::today()->subYears(18)->format('Y-m-d');
        $primaryRole = $user->roles->first()?->name ?? 'Staff';
        $userDept = $user->departments?->dept?->name ?? ($user->departments instanceof \Illuminate\Support\Collection ? $user->departments->first()?->dept?->name : null) ?? 'General';
        $userBranch = $user->userBranch?->branch?->name ?? $user->userBranch?->branchRel?->name ?? $user->branch?->branch?->name ?? $user->branch?->branchRel?->name ?? 'Main Branch';
    @endphp

    <div class="row">
        {{-- ============================================================ --}}
        {{-- LEFT COLUMN: Profile Hero Summary Card                       --}}
        {{-- ============================================================ --}}
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card wms-profile-hero-card">
                {{-- Banner --}}
                <div class="wms-profile-hero-banner"></div>

                {{-- Avatar & Camera Upload Overlay --}}
                <div class="wms-profile-avatar-wrap">
                    @if($user->profile)
                        <img id="profileAvatarImg" class="wms-profile-avatar" src="{{ asset('storage/' . $user->profile) }}" alt="{{ $user->name }}">
                    @else
                        <img id="profileAvatarImg" class="wms-profile-avatar" src="{{ Avatar::create($user->name)->toBase64() }}" alt="{{ $user->name }}">
                    @endif

                    <div id="avatarUploadSpinner" class="wms-profile-avatar-spinner d-none">
                        <div class="spinner-border spinner-border-sm text-white" role="status"></div>
                    </div>

                    <button type="button" class="wms-profile-avatar-btn" id="btnTriggerPhotoUpload" title="Upload new profile picture" data-toggle="tooltip">
                        <i class="mdi mdi-camera"></i>
                    </button>
                    <input type="file" id="profileImageFileInput" accept="image/png,image/jpeg,image/jpg,image/webp" style="display: none;">
                </div>

                {{-- User Primary Info --}}
                <div class="card-body pt-0 text-center px-4 pb-4">
                    <h5 class="font-weight-bold text-dark font-size-17 mb-1">{{ $user->name }}</h5>
                    <p class="text-muted font-size-13 mb-2">{{ $emp?->designation ?? $user->designation ?? 'Team Member' }}</p>

                    <div class="d-flex align-items-center justify-content-center flex-wrap mb-3" style="gap: 6px;">
                        <span class="badge badge-soft-primary px-2.5 py-1 font-size-11 font-weight-bold" style="border-radius: 6px;">
                            <i class="mdi mdi-shield-account mr-1"></i> {{ $primaryRole }}
                        </span>
                        @php $stBadge = $user->status_badge; @endphp
                        <span class="badge {{ $stBadge['class'] ?? 'badge-soft-success' }} px-2.5 py-1 font-size-11 font-weight-bold" style="border: 1px solid {{ $stBadge['border'] ?? '#10b981' }}; color: {{ $stBadge['color'] ?? '#065f46' }}; background: {{ $stBadge['bg'] ?? '#ecfdf5' }}; border-radius: 6px;">
                            <i class="mdi {{ $stBadge['icon'] ?? 'mdi-check-circle' }} mr-1"></i> {{ $stBadge['label'] ?? ($user->status ?: 'Active') }}
                        </span>
                    </div>

                    {{-- Quick Metadata Strip --}}
                    <div class="border-top border-bottom py-3 text-left">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted font-size-12"><i class="mdi mdi-card-account-details-outline mr-1.5 text-primary"></i> Employee Code</span>
                            <span class="font-weight-bold text-dark font-size-12 font-monospace">{{ $emp?->mem_code ?? $user->code ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted font-size-12"><i class="mdi mdi-domain mr-1.5 text-info"></i> Department</span>
                            <span class="font-weight-medium text-dark font-size-12">{{ $userDept }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted font-size-12"><i class="mdi mdi-map-marker-radius-outline mr-1.5 text-success"></i> Branch</span>
                            <span class="font-weight-medium text-dark font-size-12">{{ $userBranch }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted font-size-12"><i class="mdi mdi-calendar-check-outline mr-1.5 text-warning"></i> Joined</span>
                            <span class="font-weight-medium text-dark font-size-12">
                                {{ $joiningFormatted }}
                                @if($tenureYears > 0)
                                    <span class="badge badge-soft-warning font-size-10 ml-1">({{ $tenureYears }}y)</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Social Quick Icons on Hero --}}
                    <div class="mt-3">
                        <span class="text-muted font-size-11 font-weight-bold text-uppercase d-block mb-2" style="letter-spacing: 0.5px;">Connected Networks</span>
                        <div class="d-flex align-items-center justify-content-center" style="gap: 8px;">
                            @if($emp?->linkedin)
                                <a href="{{ Str::startsWith($emp->linkedin, 'http') ? $emp->linkedin : 'https://' . $emp->linkedin }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light border rounded-circle text-primary" title="LinkedIn" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="mdi mdi-linkedin font-size-16" style="color: #0A66C2;"></i>
                                </a>
                            @endif
                            @if($emp?->github)
                                <a href="{{ Str::startsWith($emp->github, 'http') ? $emp->github : 'https://github.com/' . ltrim($emp->github, '@') }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light border rounded-circle text-dark" title="GitHub" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="mdi mdi-github font-size-16"></i>
                                </a>
                            @endif
                            @if($emp?->insta)
                                <a href="{{ Str::startsWith($emp->insta, 'http') ? $emp->insta : 'https://instagram.com/' . ltrim($emp->insta, '@') }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light border rounded-circle text-danger" title="Instagram" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="mdi mdi-instagram font-size-16" style="color: #E1306C;"></i>
                                </a>
                            @endif
                            @if($emp?->fb)
                                <a href="{{ Str::startsWith($emp->fb, 'http') ? $emp->fb : 'https://facebook.com/' . $emp->fb }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light border rounded-circle text-primary" title="Facebook" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="mdi mdi-facebook font-size-16" style="color: #1877F2;"></i>
                                </a>
                            @endif
                            @if($emp?->twitter)
                                <a href="{{ Str::startsWith($emp->twitter, 'http') ? $emp->twitter : 'https://twitter.com/' . ltrim($emp->twitter, '@') }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light border rounded-circle text-info" title="Twitter / X" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="mdi mdi-twitter font-size-16" style="color: #1DA1F2;"></i>
                                </a>
                            @endif
                            @if($emp?->youtube)
                                <a href="{{ Str::startsWith($emp->youtube, 'http') ? $emp->youtube : 'https://youtube.com/' . $emp->youtube }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light border rounded-circle text-danger" title="YouTube" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="mdi mdi-youtube font-size-16" style="color: #FF0000;"></i>
                                </a>
                            @endif

                            @if(!$emp?->linkedin && !$emp?->github && !$emp?->insta && !$emp?->fb && !$emp?->twitter && !$emp?->youtube)
                                <span class="text-muted font-size-12">No social networks added</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- RIGHT COLUMN: Navigation Tabs (Basic, Social, Security)     --}}
        {{-- ============================================================ --}}
        <div class="col-xl-8 col-lg-7 col-md-12 mb-4">
            <div class="card shadow-sm" style="border-radius: 18px; border: 1px solid var(--erp-border, #e8ecf1); background: #ffffff;">
                {{-- Tabs Navigation --}}
                <div class="card-header bg-white border-bottom p-0" style="border-radius: 18px 18px 0 0;">
                    <ul class="nav nav-tabs wms-profile-nav-tabs px-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-basic-link" data-toggle="tab" href="#pane-basic" role="tab" aria-selected="true">
                                <i class="mdi mdi-account-circle-outline mr-1.5 font-size-16"></i> Personal Information
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-social-link" data-toggle="tab" href="#pane-social" role="tab" aria-selected="false">
                                <i class="mdi mdi-share-variant-outline mr-1.5 font-size-16"></i> Social Profiles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-security-link" data-toggle="tab" href="#pane-security" role="tab" aria-selected="false">
                                <i class="mdi mdi-shield-key-outline mr-1.5 font-size-16"></i> Security & Password
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content">
                        {{-- ============================================================ --}}
                        {{-- TAB 1: BASIC INFORMATION                                     --}}
                        {{-- ============================================================ --}}
                        <div class="tab-pane fade show active" id="pane-basic" role="tabpanel">
                            {{-- View Mode: Grid of details --}}
                            <div id="viewModeBasic">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="font-weight-bold text-dark font-size-15 mb-0">Personal & Work Profile</h6>
                                        <small class="text-muted">Primary identity and contact information</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary px-3 font-weight-medium" id="btnEnterEditBasic" style="border-radius: 8px;">
                                        <i class="mdi mdi-pencil-outline mr-1"></i> Edit Details
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">FULL NAME</span>
                                            <span class="text-dark font-weight-bold font-size-14">{{ $user->name }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">MOBILE NUMBER</span>
                                            <span class="text-dark font-weight-bold font-size-14">{{ $user->mobile ?: 'Not configured' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">EMAIL ADDRESS</span>
                                            <span class="text-dark font-weight-bold font-size-14">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">DATE OF BIRTH</span>
                                            <span class="text-dark font-weight-bold font-size-14">
                                                {{ $dobFormatted }}
                                                @if($ageYears)
                                                    <span class="badge badge-light border text-muted ml-1 font-size-11 font-weight-medium">({{ $ageYears }} years old)</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">GENDER</span>
                                            <span class="text-dark font-weight-bold font-size-14">{{ $emp?->gender ?? 'Not set' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">DESIGNATION</span>
                                            <span class="text-dark font-weight-bold font-size-14">{{ $emp?->designation ?? $user->designation ?? 'Team Member' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">EMPLOYEE CODE</span>
                                            <span class="text-dark font-weight-bold font-size-14 font-monospace">{{ $emp?->mem_code ?? $user->code ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-profile-info-row">
                                            <span class="text-muted font-size-11 d-block font-weight-medium">JOINING DATE</span>
                                            <span class="text-dark font-weight-bold font-size-14">{{ $joiningFormatted }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Edit Mode: Form --}}
                            <div id="editModeBasic" class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="font-weight-bold text-dark font-size-15 mb-0">Edit Personal Details</h6>
                                        <small class="text-muted">Update your profile information</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border px-3 font-weight-medium" id="btnCancelEditBasic" style="border-radius: 8px;">
                                        Cancel
                                    </button>
                                </div>

                                <form id="frm_basicInfo" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required style="border-radius: 8px;">
                                                <span class="invalid-feedback d-block" id="name_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">Mobile Number <span class="text-danger">*</span></label>
                                                <input type="text" name="number" class="form-control" value="{{ $user->mobile }}" maxlength="10" required style="border-radius: 8px;" placeholder="10-digit mobile number">
                                                <span class="invalid-feedback d-block" id="number_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">Email Address <span class="text-muted">(Locked)</span></label>
                                                <input type="email" name="mail" class="form-control bg-light" value="{{ $user->email }}" readonly style="border-radius: 8px;">
                                                <span class="invalid-feedback d-block" id="mail_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">
                                                    Date of Birth <span class="text-danger">*</span>
                                                    <small class="text-primary font-weight-normal ml-1">(Must be 18+ years)</small>
                                                </label>
                                                <input type="date" name="dob" id="dobInput" class="form-control" 
                                                       value="{{ $emp?->dob ? \Carbon\Carbon::parse($emp->dob)->format('Y-m-d') : '' }}" 
                                                       max="{{ $maxDobDate }}" 
                                                       required style="border-radius: 8px;">
                                                <span class="invalid-feedback d-block" id="dob_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">Gender <span class="text-danger">*</span></label>
                                                <select name="gender" class="form-control" required style="border-radius: 8px;">
                                                    <option value="Male" {{ ($emp?->gender == 'Male') ? 'selected' : '' }}>Male</option>
                                                    <option value="Female" {{ ($emp?->gender == 'Female') ? 'selected' : '' }}>Female</option>
                                                    <option value="Other" {{ ($emp?->gender == 'Other') ? 'selected' : '' }}>Other</option>
                                                </select>
                                                <span class="invalid-feedback d-block" id="gender_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">Designation <span class="text-danger">*</span></label>
                                                <input type="text" name="designation" class="form-control" value="{{ $emp?->designation ?? $user->designation }}" required style="border-radius: 8px;">
                                                <span class="invalid-feedback d-block" id="designation_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">Employee Code <span class="text-muted">(Locked)</span></label>
                                                <input type="text" name="mem_code" class="form-control bg-light" value="{{ $emp?->mem_code ?? $user->code }}" readonly style="border-radius: 8px;">
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">Joining Date <span class="text-muted">(Locked)</span></label>
                                                <input type="text" name="joiningdt" class="form-control bg-light" value="{{ $joiningFormatted }}" readonly style="border-radius: 8px;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end mt-3 pt-3 border-top" style="gap: 10px;">
                                        <button type="button" class="btn btn-light border px-4 font-weight-medium" id="btnCancelEditBasic2" style="border-radius: 8px;">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmitBasic" style="border-radius: 8px;">
                                            <span id="btnSubmitBasicText">Save Changes</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- TAB 2: SOCIAL PROFILES                                       --}}
                        {{-- ============================================================ --}}
                        <div class="tab-pane fade" id="pane-social" role="tabpanel">
                            {{-- View Mode: Grid of Social Badges --}}
                            <div id="viewModeSocial">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="font-weight-bold text-dark font-size-15 mb-0">Social & Professional Links</h6>
                                        <small class="text-muted">Connect your online profiles (all optional)</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary px-3 font-weight-medium" id="btnEnterEditSocial" style="border-radius: 8px;">
                                        <i class="mdi mdi-pencil-outline mr-1"></i> Edit Social Links
                                    </button>
                                </div>

                                <div class="row">
                                    {{-- 1. LinkedIn --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-social-badge-pill">
                                            <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white mr-3 flex-shrink-0" style="background: #0A66C2; width: 36px; height: 36px; font-size: 18px;">
                                                <i class="mdi mdi-linkedin"></i>
                                            </div>
                                            <div class="text-truncate flex-1">
                                                <span class="text-muted font-size-11 d-block font-weight-medium">LINKEDIN</span>
                                                @if($emp?->linkedin)
                                                    <a href="{{ Str::startsWith($emp->linkedin, 'http') ? $emp->linkedin : 'https://' . $emp->linkedin }}" target="_blank" rel="noopener noreferrer" class="text-primary font-weight-bold font-size-13 text-truncate d-block">
                                                        {{ $emp->linkedin }} <i class="mdi mdi-open-in-new font-size-11 ml-0.5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted font-size-12 fst-italic">Not configured</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. GitHub --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-social-badge-pill">
                                            <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white mr-3 flex-shrink-0" style="background: #24292F; width: 36px; height: 36px; font-size: 18px;">
                                                <i class="mdi mdi-github"></i>
                                            </div>
                                            <div class="text-truncate flex-1">
                                                <span class="text-muted font-size-11 d-block font-weight-medium">GITHUB</span>
                                                @if($emp?->github)
                                                    <a href="{{ Str::startsWith($emp->github, 'http') ? $emp->github : 'https://github.com/' . ltrim($emp->github, '@') }}" target="_blank" rel="noopener noreferrer" class="text-dark font-weight-bold font-size-13 text-truncate d-block">
                                                        {{ $emp->github }} <i class="mdi mdi-open-in-new font-size-11 ml-0.5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted font-size-12 fst-italic">Not configured</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 3. Instagram --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-social-badge-pill">
                                            <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white mr-3 flex-shrink-0" style="background: linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045); width: 36px; height: 36px; font-size: 18px;">
                                                <i class="mdi mdi-instagram"></i>
                                            </div>
                                            <div class="text-truncate flex-1">
                                                <span class="text-muted font-size-11 d-block font-weight-medium">INSTAGRAM</span>
                                                @if($emp?->insta)
                                                    <a href="{{ Str::startsWith($emp->insta, 'http') ? $emp->insta : 'https://instagram.com/' . ltrim($emp->insta, '@') }}" target="_blank" rel="noopener noreferrer" class="text-danger font-weight-bold font-size-13 text-truncate d-block">
                                                        {{ $emp->insta }} <i class="mdi mdi-open-in-new font-size-11 ml-0.5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted font-size-12 fst-italic">Not configured</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 4. Facebook --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-social-badge-pill">
                                            <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white mr-3 flex-shrink-0" style="background: #1877F2; width: 36px; height: 36px; font-size: 18px;">
                                                <i class="mdi mdi-facebook"></i>
                                            </div>
                                            <div class="text-truncate flex-1">
                                                <span class="text-muted font-size-11 d-block font-weight-medium">FACEBOOK</span>
                                                @if($emp?->fb)
                                                    <a href="{{ Str::startsWith($emp->fb, 'http') ? $emp->fb : 'https://facebook.com/' . $emp->fb }}" target="_blank" rel="noopener noreferrer" class="text-primary font-weight-bold font-size-13 text-truncate d-block">
                                                        {{ $emp->fb }} <i class="mdi mdi-open-in-new font-size-11 ml-0.5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted font-size-12 fst-italic">Not configured</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 5. Twitter / X --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-social-badge-pill">
                                            <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white mr-3 flex-shrink-0" style="background: #1DA1F2; width: 36px; height: 36px; font-size: 18px;">
                                                <i class="mdi mdi-twitter"></i>
                                            </div>
                                            <div class="text-truncate flex-1">
                                                <span class="text-muted font-size-11 d-block font-weight-medium">TWITTER / X</span>
                                                @if($emp?->twitter)
                                                    <a href="{{ Str::startsWith($emp->twitter, 'http') ? $emp->twitter : 'https://twitter.com/' . ltrim($emp->twitter, '@') }}" target="_blank" rel="noopener noreferrer" class="text-info font-weight-bold font-size-13 text-truncate d-block">
                                                        {{ $emp->twitter }} <i class="mdi mdi-open-in-new font-size-11 ml-0.5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted font-size-12 fst-italic">Not configured</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 6. YouTube --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="wms-social-badge-pill">
                                            <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white mr-3 flex-shrink-0" style="background: #FF0000; width: 36px; height: 36px; font-size: 18px;">
                                                <i class="mdi mdi-youtube"></i>
                                            </div>
                                            <div class="text-truncate flex-1">
                                                <span class="text-muted font-size-11 d-block font-weight-medium">YOUTUBE</span>
                                                @if($emp?->youtube)
                                                    <a href="{{ Str::startsWith($emp->youtube, 'http') ? $emp->youtube : 'https://youtube.com/' . $emp->youtube }}" target="_blank" rel="noopener noreferrer" class="text-danger font-weight-bold font-size-13 text-truncate d-block">
                                                        {{ $emp->youtube }} <i class="mdi mdi-open-in-new font-size-11 ml-0.5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted font-size-12 fst-italic">Not configured</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Edit Mode: Form --}}
                            <div id="editModeSocial" class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="font-weight-bold text-dark font-size-15 mb-0">Update Social Links</h6>
                                        <small class="text-muted">Enter your usernames or profile links (all optional)</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border px-3 font-weight-medium" id="btnCancelEditSocial" style="border-radius: 8px;">
                                        Cancel
                                    </button>
                                </div>

                                <form id="frm_socialinfo" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                                    <div class="row">
                                        {{-- LinkedIn --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">
                                                    <i class="mdi mdi-linkedin mr-1" style="color: #0A66C2;"></i> LinkedIn Profile
                                                </label>
                                                <input type="text" name="linkedin" class="form-control" value="{{ $emp?->linkedin }}" placeholder="e.g. linkedin.com/in/username" style="border-radius: 8px;">
                                            </div>
                                        </div>

                                        {{-- GitHub --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">
                                                    <i class="mdi mdi-github mr-1 text-dark"></i> GitHub Profile / Handle
                                                </label>
                                                <input type="text" name="github" class="form-control" value="{{ $emp?->github }}" placeholder="e.g. github.com/username" style="border-radius: 8px;">
                                            </div>
                                        </div>

                                        {{-- Instagram --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">
                                                    <i class="mdi mdi-instagram mr-1" style="color: #E1306C;"></i> Instagram Handle
                                                </label>
                                                <input type="text" name="insta" class="form-control" value="{{ $emp?->insta }}" placeholder="e.g. @username or full URL" style="border-radius: 8px;">
                                            </div>
                                        </div>

                                        {{-- Facebook --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">
                                                    <i class="mdi mdi-facebook mr-1" style="color: #1877F2;"></i> Facebook Profile
                                                </label>
                                                <input type="text" name="facebook" class="form-control" value="{{ $emp?->fb }}" placeholder="e.g. facebook.com/username" style="border-radius: 8px;">
                                            </div>
                                        </div>

                                        {{-- Twitter / X --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">
                                                    <i class="mdi mdi-twitter mr-1" style="color: #1DA1F2;"></i> Twitter / X
                                                </label>
                                                <input type="text" name="twitter" class="form-control" value="{{ $emp?->twitter }}" placeholder="e.g. @username or x.com/username" style="border-radius: 8px;">
                                            </div>
                                        </div>

                                        {{-- YouTube --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-medium font-size-13 text-dark">
                                                    <i class="mdi mdi-youtube mr-1" style="color: #FF0000;"></i> YouTube Channel
                                                </label>
                                                <input type="text" name="youtube" class="form-control" value="{{ $emp?->youtube }}" placeholder="e.g. youtube.com/@channel" style="border-radius: 8px;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end mt-3 pt-3 border-top" style="gap: 10px;">
                                        <button type="button" class="btn btn-light border px-4 font-weight-medium" id="btnCancelEditSocial2" style="border-radius: 8px;">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmitSocial" style="border-radius: 8px;">
                                            <span id="btnSubmitSocialText">Save Social Links</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- TAB 3: SECURITY & PASSWORD                                   --}}
                        {{-- ============================================================ --}}
                        <div class="tab-pane fade" id="pane-security" role="tabpanel">
                            <div class="mb-3 pb-2 border-bottom">
                                <h6 class="font-weight-bold text-dark font-size-15 mb-0">Password & Authentication</h6>
                                <small class="text-muted">Keep your account secure with a strong password</small>
                            </div>

                            <form action="{{ route('updatePassword') }}" method="POST" class="custom-validation" id="frm_changePassword">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-medium font-size-13 text-dark">Current / Old Password <span class="text-danger">*</span></label>
                                            <input type="password" name="old_password" class="form-control @error('old_password') is-invalid @enderror" required placeholder="Enter current password" style="border-radius: 8px;">
                                            @error('old_password')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-medium font-size-13 text-dark">New Password <span class="text-danger">*</span></label>
                                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required minlength="8" placeholder="Minimum 8 characters" style="border-radius: 8px;">
                                            @error('new_password')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-medium font-size-13 text-dark">Confirm New Password <span class="text-danger">*</span></label>
                                            <input type="password" name="confirm_password" class="form-control @error('confirm_password') is-invalid @enderror" required minlength="8" placeholder="Re-type new password" style="border-radius: 8px;">
                                            @error('confirm_password')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">
                                        <i class="mdi mdi-key-variant mr-1"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: Frontend Interactive Avatar Cropper (Zero Dependencies) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="wmsAvatarCropModal" tabindex="-1" role="dialog" aria-labelledby="wmsAvatarCropModalTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content wms-crop-modal-content">
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs mr-2">
                        <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-16">
                            <i class="mdi mdi-crop"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 text-dark" id="wmsAvatarCropModalTitle">Crop & Adjust Profile Picture</h6>
                        <small class="text-muted font-size-11">Drag to position, use the slider to zoom, and crop to minimum KB</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="btnCancelCropX">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row align-items-center">
                    {{-- Left: Interactive Canvas Viewport --}}
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="wms-crop-workspace">
                            <div class="wms-crop-canvas-box" id="cropCanvasBox">
                                <canvas id="avatarCropCanvas" width="320" height="320"></canvas>
                            </div>
                            <div class="wms-crop-controls-bar">
                                <button type="button" class="wms-crop-btn-tool" id="btnCropZoomOut" title="Zoom Out">
                                    <i class="mdi mdi-magnify-minus-outline"></i>
                                </button>
                                <input type="range" class="wms-crop-slider" id="cropZoomSlider" min="100" max="350" value="100" step="1">
                                <button type="button" class="wms-crop-btn-tool" id="btnCropZoomIn" title="Zoom In">
                                    <i class="mdi mdi-magnify-plus-outline"></i>
                                </button>
                                <button type="button" class="wms-crop-btn-tool ml-2" id="btnCropRotate" title="Rotate 90°">
                                    <i class="mdi mdi-rotate-right"></i>
                                </button>
                                <button type="button" class="wms-crop-btn-tool" id="btnCropReset" title="Reset View">
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Live Avatar Previews --}}
                    <div class="col-md-5">
                        <div class="wms-crop-preview-card">
                            <p class="font-size-12 font-weight-bold text-muted text-uppercase mb-3">Live Avatar Previews</p>
                            
                            {{-- Large Circular Preview --}}
                            <div class="wms-crop-preview-circle-lg">
                                <canvas id="previewCanvasLg" width="90" height="90"></canvas>
                            </div>
                            <small class="text-muted font-size-11 mb-3">Profile Hero Display (90px)</small>

                            {{-- Small Circular Preview --}}
                            <div class="wms-crop-preview-circle-sm mb-1">
                                <canvas id="previewCanvasSm" width="42" height="42"></canvas>
                            </div>
                            <small class="text-muted font-size-11">Navbar Header Display (42px)</small>

                            <div class="wms-crop-size-chip">
                                <i class="mdi mdi-flash mr-1"></i> Output Payload: <span id="cropSizeEstimate">~20-35 KB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-2.5">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" id="btnCancelCrop">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm px-4 font-weight-bold" id="btnApplyCropAndSave">
                    <span id="btnApplyCropText"><i class="mdi mdi-check mr-1"></i> Crop & Save Avatar</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // --------------------------------------------------------
    // 1. Frontend Avatar Cropper & Optimized KB Upload
    // --------------------------------------------------------
    var rawCropperImg = new Image();
    var cropCanvas = document.getElementById('avatarCropCanvas');
    var cropCtx = cropCanvas ? cropCanvas.getContext('2d') : null;
    var previewLg = document.getElementById('previewCanvasLg');
    var previewCtxLg = previewLg ? previewLg.getContext('2d') : null;
    var previewSm = document.getElementById('previewCanvasSm');
    var previewCtxSm = previewSm ? previewSm.getContext('2d') : null;

    var CROP_RADIUS = 135; // circular cutout radius (diameter 270px)
    var CANVAS_CENTER = 160;

    var baseScale = 1.0;
    var zoomFactor = 1.0;
    var panX = 0;
    var panY = 0;
    var rotationDeg = 0;
    var isDragging = false;
    var dragStartX = 0;
    var dragStartY = 0;

    $('#btnTriggerPhotoUpload').on('click', function(e) {
        e.preventDefault();
        $('#profileImageFileInput').trigger('click');
    });

    $('#profileImageFileInput').on('change', function() {
        var fileInput = this;
        if (!fileInput.files || !fileInput.files[0]) return;

        var file = fileInput.files[0];

        // 1. Client-Side Image Size Restriction (Max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alertify.error('File size exceeds 10MB limit. Please choose a smaller image.');
            $(this).val('');
            return;
        }

        var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if ($.inArray(file.type.toLowerCase(), validTypes) === -1) {
            alertify.error('Please upload a valid image file (PNG, JPEG, JPG, WebP).');
            $(this).val('');
            return;
        }

        // Read file into image for frontend cropping
        var reader = new FileReader();
        reader.onload = function(e) {
            rawCropperImg = new Image();
            rawCropperImg.onload = function() {
                // Initialize cropper coordinates
                var minDim = Math.min(rawCropperImg.width, rawCropperImg.height);
                baseScale = (CROP_RADIUS * 2) / minDim;
                zoomFactor = 1.0;
                panX = 0;
                panY = 0;
                rotationDeg = 0;
                $('#cropZoomSlider').val(100);

                $('#wmsAvatarCropModal').modal('show');
                setTimeout(function() {
                    renderCropper();
                }, 150);
            };
            rawCropperImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    function renderCropper() {
        if (!cropCtx || !rawCropperImg.src) return;

        // Clear canvas
        cropCtx.clearRect(0, 0, 320, 320);

        // 1. Draw Image with user transformation
        cropCtx.save();
        cropCtx.translate(CANVAS_CENTER + panX, CANVAS_CENTER + panY);
        cropCtx.rotate((rotationDeg * Math.PI) / 180);
        var curScale = baseScale * zoomFactor;
        var drawW = rawCropperImg.width * curScale;
        var drawH = rawCropperImg.height * curScale;
        cropCtx.drawImage(rawCropperImg, -drawW / 2, -drawH / 2, drawW, drawH);
        cropCtx.restore();

        // 2. Draw Stencil Overlay (dark vignette with clear circular viewport)
        cropCtx.save();
        cropCtx.fillStyle = 'rgba(15, 23, 42, 0.72)';
        cropCtx.beginPath();
        cropCtx.rect(0, 0, 320, 320);
        cropCtx.arc(CANVAS_CENTER, CANVAS_CENTER, CROP_RADIUS, 0, Math.PI * 2, true);
        cropCtx.fill();

        // Circular ring border
        cropCtx.beginPath();
        cropCtx.arc(CANVAS_CENTER, CANVAS_CENTER, CROP_RADIUS, 0, Math.PI * 2, false);
        cropCtx.lineWidth = 2.5;
        cropCtx.strokeStyle = 'rgba(255, 255, 255, 0.9)';
        cropCtx.stroke();
        cropCtx.restore();

        // 3. Render Circular Previews
        renderPreview();
    }

    function renderPreview() {
        if (!rawCropperImg.src) return;

        // Render to offscreen 256x256 circular canvas
        var offCanvas = document.createElement('canvas');
        offCanvas.width = 256;
        offCanvas.height = 256;
        var offCtx = offCanvas.getContext('2d');

        offCtx.save();
        offCtx.beginPath();
        offCtx.arc(128, 128, 128, 0, Math.PI * 2);
        offCtx.clip();

        // Scale ratio from 270px stencil diameter to 256px
        var scaleRatio = 256 / (CROP_RADIUS * 2);
        offCtx.translate(128 + panX * scaleRatio, 128 + panY * scaleRatio);
        offCtx.rotate((rotationDeg * Math.PI) / 180);
        var curScale = baseScale * zoomFactor * scaleRatio;
        var drawW = rawCropperImg.width * curScale;
        var drawH = rawCropperImg.height * curScale;
        offCtx.drawImage(rawCropperImg, -drawW / 2, -drawH / 2, drawW, drawH);
        offCtx.restore();

        // Draw to Large 90px Preview
        if (previewCtxLg) {
            previewCtxLg.clearRect(0, 0, 90, 90);
            previewCtxLg.drawImage(offCanvas, 0, 0, 90, 90);
        }

        // Draw to Small 42px Preview
        if (previewCtxSm) {
            previewCtxSm.clearRect(0, 0, 42, 42);
            previewCtxSm.drawImage(offCanvas, 0, 0, 42, 42);
        }
    }

    // Drag to Pan inside Crop Workspace
    var $canvasBox = $('#cropCanvasBox');

    $canvasBox.on('mousedown', function(e) {
        isDragging = true;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        e.preventDefault();
    });

    $(document).on('mousemove', function(e) {
        if (!isDragging) return;
        var dx = e.clientX - dragStartX;
        var dy = e.clientY - dragStartY;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        panX += dx;
        panY += dy;
        renderCropper();
    });

    $(document).on('mouseup', function() {
        isDragging = false;
    });

    // Touch support for mobile/tablets
    $canvasBox.on('touchstart', function(e) {
        if (e.originalEvent.touches && e.originalEvent.touches[0]) {
            isDragging = true;
            dragStartX = e.originalEvent.touches[0].clientX;
            dragStartY = e.originalEvent.touches[0].clientY;
        }
    });

    $canvasBox.on('touchmove', function(e) {
        if (!isDragging || !e.originalEvent.touches || !e.originalEvent.touches[0]) return;
        var touch = e.originalEvent.touches[0];
        var dx = touch.clientX - dragStartX;
        var dy = touch.clientY - dragStartY;
        dragStartX = touch.clientX;
        dragStartY = touch.clientY;
        panX += dx;
        panY += dy;
        renderCropper();
        e.preventDefault();
    });

    $canvasBox.on('touchend', function() {
        isDragging = false;
    });

    // Mouse Wheel Zoom
    $canvasBox.on('wheel', function(e) {
        e.preventDefault();
        var delta = e.originalEvent.deltaY < 0 ? 5 : -5;
        var currentVal = parseInt($('#cropZoomSlider').val(), 10) || 100;
        var newVal = Math.max(100, Math.min(350, currentVal + delta));
        $('#cropZoomSlider').val(newVal).trigger('input');
    });

    // Slider Zoom
    $('#cropZoomSlider').on('input change', function() {
        zoomFactor = (parseInt($(this).val(), 10) || 100) / 100;
        renderCropper();
    });

    // Zoom Buttons
    $('#btnCropZoomIn').on('click', function() {
        var currentVal = parseInt($('#cropZoomSlider').val(), 10) || 100;
        var newVal = Math.min(350, currentVal + 15);
        $('#cropZoomSlider').val(newVal).trigger('input');
    });

    $('#btnCropZoomOut').on('click', function() {
        var currentVal = parseInt($('#cropZoomSlider').val(), 10) || 100;
        var newVal = Math.max(100, currentVal - 15);
        $('#cropZoomSlider').val(newVal).trigger('input');
    });

    // 90° Rotate
    $('#btnCropRotate').on('click', function() {
        rotationDeg = (rotationDeg + 90) % 360;
        renderCropper();
    });

    // Reset Crop Controls
    $('#btnCropReset').on('click', function() {
        zoomFactor = 1.0;
        panX = 0;
        panY = 0;
        rotationDeg = 0;
        $('#cropZoomSlider').val(100);
        renderCropper();
    });

    // Reset input on modal cancel
    $('#btnCancelCrop, #btnCancelCropX').on('click', function() {
        $('#profileImageFileInput').val('');
    });

    // --------------------------------------------------------
    // Apply Crop, Compress to Minimal KB, and Upload via AJAX
    // --------------------------------------------------------
    $('#btnApplyCropAndSave').on('click', function() {
        var $btn = $(this);
        var $btnText = $('#btnApplyCropText');
        var originalBtnHtml = $btnText.html();

        $btn.prop('disabled', true);
        $btnText.html('<span class="spinner-border spinner-border-sm mr-1"></span> Uploading...');

        // Render final 256x256 cropped square image
        var finalCanvas = document.createElement('canvas');
        finalCanvas.width = 256;
        finalCanvas.height = 256;
        var finalCtx = finalCanvas.getContext('2d');

        var scaleRatio = 256 / (CROP_RADIUS * 2);
        finalCtx.translate(128 + panX * scaleRatio, 128 + panY * scaleRatio);
        finalCtx.rotate((rotationDeg * Math.PI) / 180);
        var curScale = baseScale * zoomFactor * scaleRatio;
        var drawW = rawCropperImg.width * curScale;
        var drawH = rawCropperImg.height * curScale;
        finalCtx.drawImage(rawCropperImg, -drawW / 2, -drawH / 2, drawW, drawH);

        // Convert to highly optimized JPEG Blob (Quality 0.85 = ~20KB-35KB)
        finalCanvas.toBlob(function(blob) {
            if (!blob) {
                $btn.prop('disabled', false);
                $btnText.html(originalBtnHtml);
                alertify.error('Failed to process cropped image.');
                return;
            }

            var formData = new FormData();
            formData.append('file', blob, 'avatar.jpg');
            formData.append('cropped_image', finalCanvas.toDataURL('image/jpeg', 0.85));
            formData.append('_token', '{{ csrf_token() }}');

            // Show hero avatar spinner during network request
            $('#avatarUploadSpinner').removeClass('d-none');
            $('#btnTriggerPhotoUpload').prop('disabled', true);

            $.ajax({
                url: "{{ route('profileimg') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res) {
                    $btn.prop('disabled', false);
                    $btnText.html(originalBtnHtml);
                    $('#avatarUploadSpinner').addClass('d-none');
                    $('#btnTriggerPhotoUpload').prop('disabled', false);

                    if (res.status === true || res.code === 200) {
                        alertify.success(res.message || 'Profile picture updated successfully!');
                        if (res.image_url) {
                            $('#profileAvatarImg').attr('src', res.image_url);
                            $('.header-profile-user').attr('src', res.image_url);
                            $('#headerUserAvatar').attr('src', res.image_url);
                            $('.nav-user-img').attr('src', res.image_url);
                        }
                        $('#wmsAvatarCropModal').modal('hide');
                        $('#profileImageFileInput').val('');
                    } else {
                        alertify.error(res.message || 'Failed to update profile picture.');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    $btnText.html(originalBtnHtml);
                    $('#avatarUploadSpinner').addClass('d-none');
                    $('#btnTriggerPhotoUpload').prop('disabled', false);

                    var errMsg = 'Error uploading photo. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                            errMsg = xhr.responseJSON.errors[firstKey][0];
                        }
                    }
                    alertify.error(errMsg);
                }
            });
        }, 'image/jpeg', 0.85);
    });

    // --------------------------------------------------------
    // 2. Toggle Edit Mode for Basic Details
    // --------------------------------------------------------
    $('#btnEnterEditBasic').on('click', function() {
        $('#viewModeBasic').addClass('d-none');
        $('#editModeBasic').removeClass('d-none');
    });

    $('#btnCancelEditBasic, #btnCancelEditBasic2').on('click', function() {
        $('#editModeBasic').addClass('d-none');
        $('#viewModeBasic').removeClass('d-none');
        $('.invalid-feedback').text('');
        $('input, select').removeClass('is-invalid');
    });

    // --------------------------------------------------------
    // 3. Submit Basic Details via AJAX
    // --------------------------------------------------------
    $('#frm_basicInfo').on('submit', function(e) {
        e.preventDefault();
        $('.invalid-feedback').text('');
        $('input, select').removeClass('is-invalid');

        var $btn = $('#btnSubmitBasic');
        var originalBtnHtml = $('#btnSubmitBasicText').text();
        $('#btnSubmitBasicText').html('<span class="spinner-border spinner-border-sm mr-1"></span> Saving...');
        $btn.prop('disabled', true);

        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('profile.update.info') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                $btn.prop('disabled', false);
                $('#btnSubmitBasicText').text(originalBtnHtml);

                if (res.code === 200 || res.status === true) {
                    alertify.success(res.message || 'Profile updated successfully!');
                    setTimeout(function() {
                        window.location.reload();
                    }, 800);
                } else {
                    alertify.error(res.message || 'Failed to update profile.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                $('#btnSubmitBasicText').text(originalBtnHtml);

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, msgs) {
                        var $input = $('[name="' + field + '"]');
                        $input.addClass('is-invalid');
                        $('#' + field + '_error').text(msgs[0]);
                    });
                    if (xhr.responseJSON.message) {
                        alertify.error(xhr.responseJSON.message);
                    }
                } else {
                    alertify.error('An unexpected error occurred while saving.');
                }
            }
        });
    });

    // --------------------------------------------------------
    // 4. Toggle Edit Mode for Social Links
    // --------------------------------------------------------
    $('#btnEnterEditSocial').on('click', function() {
        $('#viewModeSocial').addClass('d-none');
        $('#editModeSocial').removeClass('d-none');
    });

    $('#btnCancelEditSocial, #btnCancelEditSocial2').on('click', function() {
        $('#editModeSocial').addClass('d-none');
        $('#viewModeSocial').removeClass('d-none');
    });

    // --------------------------------------------------------
    // 5. Submit Social Links via AJAX
    // --------------------------------------------------------
    $('#frm_socialinfo').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitSocial');
        var originalBtnHtml = $('#btnSubmitSocialText').text();
        $('#btnSubmitSocialText').html('<span class="spinner-border spinner-border-sm mr-1"></span> Saving...');
        $btn.prop('disabled', true);

        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('profile.update.socialinfo') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                $btn.prop('disabled', false);
                $('#btnSubmitSocialText').text(originalBtnHtml);

                if (res.code === 200 || res.status === true) {
                    alertify.success(res.message || 'Social links updated successfully!');
                    setTimeout(function() {
                        window.location.reload();
                    }, 800);
                } else {
                    alertify.error(res.message || 'Failed to update social links.');
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                $('#btnSubmitSocialText').text(originalBtnHtml);
                alertify.error('An unexpected error occurred while saving social links.');
            }
        });
    });
});
</script>
@endsection
