@extends('layouts.app')

@section('styles')
<style>
    .proj-cat-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .custom-pill-tabs .nav-link {
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-weight: 600;
        font-size: 13.5px;
        padding: 8px 20px;
        border-radius: 30px;
        margin-right: 8px;
        background: #ffffff;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .custom-pill-tabs .nav-link:hover {
        color: #1e293b;
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .custom-pill-tabs .nav-link.active {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .badge-tab {
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.25);
        color: inherit;
        font-weight: 700;
    }

    .custom-pill-tabs .nav-link:not(.active) .badge-tab {
        background: #f1f5f9;
        color: #64748b;
    }

    .filter-card {
        border-radius: 14px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        background: #ffffff;
    }

    .cat-table thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 13px 16px;
    }

    .cat-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .cat-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .status-switch {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 20px;
    }

    .status-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .status-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 20px;
    }

    .status-slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }

    input:checked + .status-slider {
        background-color: #10b981;
    }

    input:checked + .status-slider:before {
        transform: translateX(18px);
    }
</style>
@endsection

@section('content')
<div class="container-fluid proj-cat-wrapper pb-5">

    {{-- Breadcrumb & Title --}}
    <div class="row align-items-center mb-4 mt-2">
        <div class="col-md-6">
            <h4 class="mb-1 text-dark font-weight-bold d-flex align-items-center">
                <i class="mdi mdi-shape-plus-outline text-primary mr-2 font-size-22"></i>
                Project Categories & Sub-Categories
            </h4>
            <p class="text-muted font-size-13 mb-0">
                Manage project classifications, associate sub-categories to parent categories, and configure departmental mappings.
            </p>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            <div class="d-inline-flex" style="gap: 8px;">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm font-weight-semibold" data-toggle="modal" data-target="#addCategoryModal">
                    <i class="mdi mdi-folder-plus-outline mr-1"></i> Add Parent Category
                </button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm font-weight-semibold" data-toggle="modal" data-target="#addSubCategoryModal">
                    <i class="mdi mdi-plus-circle mr-1"></i> Add Sub-Category
                </button>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show font-size-13 py-2.5 px-3 rounded-lg shadow-sm" role="alert">
            <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close py-2.5" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show font-size-13 py-2.5 px-3 rounded-lg shadow-sm" role="alert">
            <i class="mdi mdi-alert-circle mr-1"></i> <strong>Validation Error:</strong>
            <ul class="mb-0 mt-1 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close py-2.5" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="row mb-3 align-items-center">
        <div class="col-md-6 mb-2 mb-md-0">
            <ul class="nav nav-pills custom-pill-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'sub_categories' ? 'active' : '' }}" href="{{ route('administration.project-categories.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'sub_categories'])) }}">
                        <i class="mdi mdi-shape-outline font-size-15"></i> Sub-Categories
                        <span class="badge-tab">{{ $stats['total_sub_categories'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'categories' ? 'active' : '' }}" href="{{ route('administration.project-categories.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'categories'])) }}">
                        <i class="mdi mdi-folder-outline font-size-15"></i> Parent Categories
                        <span class="badge-tab">{{ $stats['total_categories'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('administration.project-categories.index') }}" id="catFilterForm">
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <div class="row align-items-end">
                    {{-- Search Input --}}
                    <div class="col-xl-3 col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Search</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-magnify"></i></span>
                            </div>
                            <input type="text" class="form-control form-control-sm border-left-0" name="search" placeholder="Search by name or category..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    {{-- Parent Category Filter (For Sub-Categories Tab) --}}
                    @if($activeTab === 'sub_categories')
                    <div class="col-xl-3 col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Parent Category</label>
                        <select name="category_id" class="form-control form-control-sm select2">
                            <option value="">All Categories</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->category }} ({{ optional($cat->department)->name ?? 'General' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Department Filter --}}
                    <div class="{{ $activeTab === 'sub_categories' ? 'col-xl-2' : 'col-xl-4' }} col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Department</label>
                        <select name="dept_id" class="form-control form-control-sm select2">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ (string)($filters['dept_id'] ?? '') === (string)$d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-xl-2 col-md-6 mb-2 mb-xl-0">
                        <label class="font-size-12 font-weight-bold text-muted mb-1 text-uppercase">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="1" {{ ($filters['status'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($filters['status'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="{{ $activeTab === 'sub_categories' ? 'col-xl-2' : 'col-xl-3' }} col-md-6 text-right">
                        <div class="d-flex" style="gap: 4px;">
                            <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Filter Results">
                                <i class="mdi mdi-filter font-size-13 mr-1"></i> Apply Filter
                            </button>
                            <a href="{{ route('administration.project-categories.index', ['tab' => $activeTab]) }}" class="btn btn-sm btn-light border" title="Reset Filters">
                                <i class="mdi mdi-refresh font-size-13"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Content Tables --}}
    @if($activeTab === 'sub_categories')
        {{-- ======================================================== --}}
        {{-- 1. SUB-CATEGORIES TABLE VIEW                             --}}
        {{-- ======================================================== --}}
        <div class="card border shadow-sm" style="border-radius: 14px; overflow: hidden;">
            <div class="card-body p-0">
                @if($subCategories->isEmpty())
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-3" style="background: rgba(241, 245, 249, 0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="mdi mdi-shape-outline font-size-24 text-muted"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">No Sub-Categories Found</h5>
                        <p class="text-muted font-size-13 mb-3">No project sub-categories match your search or filter criteria.</p>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-toggle="modal" data-target="#addSubCategoryModal">
                            <i class="mdi mdi-plus mr-1"></i> Create First Sub-Category
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table cat-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="padding-left: 20px; width: 6%;">#</th>
                                    <th style="width: 26%;">Sub-Category Name</th>
                                    <th style="width: 24%;">Parent Category</th>
                                    <th style="width: 14%;">Department</th>
                                    <th style="width: 10%;">Projects</th>
                                    <th style="width: 10%;">Status</th>
                                    <th class="text-right" style="padding-right: 20px; width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subCategories as $index => $sub)
                                <tr>
                                    {{-- Index --}}
                                    <td class="text-muted font-size-12" style="padding-left: 20px;">
                                        {{ $subCategories->firstItem() + $index }}
                                    </td>

                                    {{-- Sub Category Name --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs mr-2" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                                                <i class="mdi mdi-shape-outline"></i>
                                            </div>
                                            <div>
                                                <span class="font-weight-bold text-dark d-block font-size-13">{{ $sub->name }}</span>
                                                <small class="text-muted font-size-11">Created {{ $sub->created_at ? $sub->created_at->format('d M Y') : '-' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Parent Category --}}
                                    <td>
                                        @if($sub->projectCategory)
                                            <span class="badge badge-soft-primary px-2.5 py-1.5 font-size-12 font-weight-semibold" style="border-radius: 6px;">
                                                <i class="mdi mdi-folder-outline mr-1"></i> {{ $sub->projectCategory->category }}
                                            </span>
                                        @else
                                            <span class="text-muted font-size-12"><em>None / Unassigned</em></span>
                                        @endif
                                    </td>

                                    {{-- Department --}}
                                    <td>
                                        @php
                                            $deptName = optional($sub->projectCategory?->department)->name ?? 'General';
                                            $deptClass = 'badge-soft-secondary';
                                            if (stripos($deptName, 'Sales') !== false || stripos($deptName, 'NSD') !== false) {
                                                $deptClass = 'badge-soft-purple';
                                            } elseif (stripos($deptName, 'Operation') !== false || stripos($deptName, 'OD') !== false) {
                                                $deptClass = 'badge-soft-info';
                                            } elseif (stripos($deptName, 'Customer') !== false || stripos($deptName, 'CSD') !== false) {
                                                $deptClass = 'badge-soft-success';
                                            }
                                        @endphp
                                        <span class="badge {{ $deptClass }} font-size-11 px-2 py-1 font-weight-semibold">
                                            {{ $deptName }}
                                        </span>
                                    </td>

                                    {{-- Linked Projects --}}
                                    <td>
                                        <span class="badge badge-light border font-size-11 px-2 py-1 font-weight-bold">
                                            <i class="mdi mdi-briefcase mr-0.5 text-muted"></i> {{ $sub->projects_count }}
                                        </span>
                                    </td>

                                    {{-- Status Toggle --}}
                                    <td>
                                        <label class="status-switch mb-0" title="Toggle active/inactive status">
                                            <input type="checkbox" class="toggle-status-btn" data-type="sub_category" data-id="{{ $sub->id }}" {{ $sub->status ? 'checked' : '' }}>
                                            <span class="status-slider"></span>
                                        </label>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-right" style="padding-right: 20px;">
                                        <div class="d-inline-flex" style="gap: 4px;">
                                            <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5 py-1 border shadow-sm edit-sub-btn" 
                                                data-id="{{ $sub->id }}"
                                                data-name="{{ $sub->name }}"
                                                data-proj_id="{{ $sub->proj_id }}"
                                                data-status="{{ $sub->status ? 1 : 0 }}"
                                                title="Edit Sub-Category">
                                                <i class="mdi mdi-pencil font-size-13 text-primary"></i>
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5 py-1 border shadow-sm delete-sub-btn" 
                                                data-id="{{ $sub->id }}"
                                                data-name="{{ $sub->name }}"
                                                title="Delete Sub-Category">
                                                <i class="mdi mdi-trash-can-outline font-size-13 text-danger"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Footer --}}
                    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap" style="background: #fafbfc;">
                        <div class="d-flex align-items-center flex-wrap mb-2 mb-sm-0" style="gap: 12px;">
                            <span class="text-muted font-size-12">
                                Showing <strong>{{ $subCategories->firstItem() ?? 0 }}</strong> to <strong>{{ $subCategories->lastItem() ?? 0 }}</strong> of <strong>{{ $subCategories->total() }}</strong> sub-categories
                            </span>
                            <div class="d-inline-flex align-items-center" style="gap: 6px;">
                                <label class="text-muted font-size-11 mb-0">Per page:</label>
                                <select class="form-control form-control-sm" style="width: 75px; height: 30px; font-size: 12px; padding: 2px 6px;" onchange="window.location.href='{{ route('administration.project-categories.index', array_merge(request()->except(['page', 'per_page']), ['tab' => 'sub_categories'])) }}&per_page=' + this.value">
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        @if($subCategories->hasPages())
                        <div>
                            {{ $subCategories->links('pagination::bootstrap-4') }}
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    @else
        {{-- ======================================================== --}}
        {{-- 2. PARENT CATEGORIES TABLE VIEW                          --}}
        {{-- ======================================================== --}}
        <div class="card border shadow-sm" style="border-radius: 14px; overflow: hidden;">
            <div class="card-body p-0">
                @if($categories->isEmpty())
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-3" style="background: rgba(241, 245, 249, 0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="mdi mdi-folder-outline font-size-24 text-muted"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">No Parent Categories Found</h5>
                        <p class="text-muted font-size-13 mb-3">No parent project categories match your search or filter criteria.</p>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-toggle="modal" data-target="#addCategoryModal">
                            <i class="mdi mdi-plus mr-1"></i> Create First Parent Category
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table cat-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="padding-left: 20px; width: 6%;">#</th>
                                    <th style="width: 30%;">Category Name</th>
                                    <th style="width: 20%;">Department</th>
                                    <th style="width: 14%;">Sub-Categories</th>
                                    <th style="width: 10%;">Projects</th>
                                    <th style="width: 10%;">Status</th>
                                    <th class="text-right" style="padding-right: 20px; width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $index => $cat)
                                <tr>
                                    {{-- Index --}}
                                    <td class="text-muted font-size-12" style="padding-left: 20px;">
                                        {{ $index + 1 }}
                                    </td>

                                    {{-- Category Name --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs mr-2" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                                                <i class="mdi mdi-folder-outline"></i>
                                            </div>
                                            <div>
                                                <span class="font-weight-bold text-dark d-block font-size-13">{{ $cat->category }}</span>
                                                <small class="text-muted font-size-11">Created {{ $cat->created_at ? $cat->created_at->format('d M Y') : '-' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Department --}}
                                    <td>
                                        @php
                                            $deptName = optional($cat->department)->name ?? 'General';
                                            $deptClass = 'badge-soft-secondary';
                                            if (stripos($deptName, 'Sales') !== false || stripos($deptName, 'NSD') !== false) {
                                                $deptClass = 'badge-soft-purple';
                                            } elseif (stripos($deptName, 'Operation') !== false || stripos($deptName, 'OD') !== false) {
                                                $deptClass = 'badge-soft-info';
                                            } elseif (stripos($deptName, 'Customer') !== false || stripos($deptName, 'CSD') !== false) {
                                                $deptClass = 'badge-soft-success';
                                            }
                                        @endphp
                                        <span class="badge {{ $deptClass }} font-size-11 px-2 py-1 font-weight-semibold">
                                            {{ $deptName }}
                                        </span>
                                    </td>

                                    {{-- Sub-Categories Count --}}
                                    <td>
                                        <a href="{{ route('administration.project-categories.index', ['tab' => 'sub_categories', 'category_id' => $cat->id]) }}" class="badge badge-soft-primary font-size-11 px-2.5 py-1 font-weight-bold" title="View Sub-Categories">
                                            <i class="mdi mdi-shape mr-1"></i> {{ $cat->sub_categories_count }} Sub-Categories
                                        </a>
                                    </td>

                                    {{-- Linked Projects Count --}}
                                    <td>
                                        <span class="badge badge-light border font-size-11 px-2 py-1 font-weight-bold">
                                            <i class="mdi mdi-briefcase mr-0.5 text-muted"></i> {{ $cat->projects_count }}
                                        </span>
                                    </td>

                                    {{-- Status Toggle --}}
                                    <td>
                                        <label class="status-switch mb-0" title="Toggle active/inactive status">
                                            <input type="checkbox" class="toggle-status-btn" data-type="category" data-id="{{ $cat->id }}" {{ $cat->status ? 'checked' : '' }}>
                                            <span class="status-slider"></span>
                                        </label>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-right" style="padding-right: 20px;">
                                        <div class="d-inline-flex" style="gap: 4px;">
                                            <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5 py-1 border shadow-sm edit-cat-btn" 
                                                data-id="{{ $cat->id }}"
                                                data-category="{{ $cat->category }}"
                                                data-dept_id="{{ $cat->dept_id }}"
                                                data-status="{{ $cat->status ? 1 : 0 }}"
                                                title="Edit Category">
                                                <i class="mdi mdi-pencil font-size-13 text-primary"></i>
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5 py-1 border shadow-sm delete-cat-btn" 
                                                data-id="{{ $cat->id }}"
                                                data-category="{{ $cat->category }}"
                                                data-subcount="{{ $cat->sub_categories_count }}"
                                                title="Delete Category">
                                                <i class="mdi mdi-trash-can-outline font-size-13 text-danger"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>

{{-- ============================================================ --}}
{{-- MODALS                                                       --}}
{{-- ============================================================ --}}

{{-- 1. ADD SUB-CATEGORY MODAL --}}
<div class="modal fade" id="addSubCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title font-weight-bold text-white d-flex align-items-center">
                    <i class="mdi mdi-shape-plus mr-2 font-size-20"></i> Add Project Sub-Category
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('administration.project-categories.sub-category.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- Parent Category Selector --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Parent Project Category <span class="text-danger">*</span>
                        </label>
                        <select name="proj_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Select Parent Category --</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->category }} ({{ optional($cat->department)->name ?? 'General' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted font-size-11">The parent category this sub-category belongs to.</small>
                    </div>

                    {{-- Sub-Category Name --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Sub-Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dynamic Website, Payment Gateway, Logo Design..." required>
                    </div>

                    {{-- Status --}}
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="sub_cat_status_add" name="status" value="1" checked>
                            <label class="custom-control-label font-weight-medium font-size-13" for="sub_cat_status_add">
                                Active (Enabled in project creation & filters)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold">
                        <i class="mdi mdi-check mr-1"></i> Save Sub-Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. EDIT SUB-CATEGORY MODAL --}}
<div class="modal fade" id="editSubCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title font-weight-bold text-white d-flex align-items-center">
                    <i class="mdi mdi-pencil mr-2 font-size-20"></i> Edit Project Sub-Category
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editSubCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    {{-- Parent Category Selector --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Parent Project Category <span class="text-danger">*</span>
                        </label>
                        <select name="proj_id" id="edit_sub_proj_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Select Parent Category --</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->category }} ({{ optional($cat->department)->name ?? 'General' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sub-Category Name --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Sub-Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="edit_sub_name" class="form-control" required>
                    </div>

                    {{-- Status --}}
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_sub_status" name="status" value="1">
                            <label class="custom-control-label font-weight-medium font-size-13" for="edit_sub_status">
                                Active (Enabled in project creation & filters)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold">
                        <i class="mdi mdi-check mr-1"></i> Update Sub-Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. ADD PARENT CATEGORY MODAL --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title font-weight-bold text-white d-flex align-items-center">
                    <i class="mdi mdi-folder-plus mr-2 font-size-20 text-warning"></i> Add Parent Project Category
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('administration.project-categories.category.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- Department --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Department <span class="text-danger">*</span>
                        </label>
                        <select name="dept_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category Name --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Web Development, SEO, Graphic Design..." required>
                    </div>

                    {{-- Status --}}
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="cat_status_add" name="status" value="1" checked>
                            <label class="custom-control-label font-weight-medium font-size-13" for="cat_status_add">
                                Active (Enabled in project creation & filters)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm rounded-pill px-4 font-weight-bold">
                        <i class="mdi mdi-check mr-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 4. EDIT PARENT CATEGORY MODAL --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title font-weight-bold text-white d-flex align-items-center">
                    <i class="mdi mdi-pencil mr-2 font-size-20 text-warning"></i> Edit Parent Project Category
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    {{-- Department --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Department <span class="text-danger">*</span>
                        </label>
                        <select name="dept_id" id="edit_cat_dept_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category Name --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold font-size-12 text-muted text-uppercase">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="category" id="edit_cat_name" class="form-control" required>
                    </div>

                    {{-- Status --}}
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_cat_status" name="status" value="1">
                            <label class="custom-control-label font-weight-medium font-size-13" for="edit_cat_status">
                                Active (Enabled in project creation & filters)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm rounded-pill px-4 font-weight-bold">
                        <i class="mdi mdi-check mr-1"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden Delete Forms --}}
<form id="deleteSubCategoryForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<form id="deleteCategoryForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 dropdowns
        if ($('.select2').length) {
            $('.select2').select2({
                width: '100%'
            });
        }

        // Edit Sub-Category Click
        $('.edit-sub-btn').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var projId = $(this).data('proj_id');
            var status = $(this).data('status');

            $('#editSubCategoryForm').attr('action', "{{ url('administration/project-categories/sub-category') }}/" + id);
            $('#edit_sub_name').val(name);
            $('#edit_sub_proj_id').val(projId).trigger('change');
            $('#edit_sub_status').prop('checked', status == 1);

            $('#editSubCategoryModal').modal('show');
        });

        // Delete Sub-Category Click
        $('.delete-sub-btn').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');

            swal({
                title: "Delete Sub-Category?",
                text: "Are you sure you want to remove '" + name + "'?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then(function(result) {
                if (result.value || result === true) {
                    var form = $('#deleteSubCategoryForm');
                    form.attr('action', "{{ url('administration/project-categories/sub-category') }}/" + id);
                    form.submit();
                }
            });
        });

        // Edit Category Click
        $('.edit-cat-btn').on('click', function() {
            var id = $(this).data('id');
            var category = $(this).data('category');
            var deptId = $(this).data('dept_id');
            var status = $(this).data('status');

            $('#editCategoryForm').attr('action', "{{ url('administration/project-categories/category') }}/" + id);
            $('#edit_cat_name').val(category);
            $('#edit_cat_dept_id').val(deptId).trigger('change');
            $('#edit_cat_status').prop('checked', status == 1);

            $('#editCategoryModal').modal('show');
        });

        // Delete Category Click
        $('.delete-cat-btn').on('click', function() {
            var id = $(this).data('id');
            var category = $(this).data('category');
            var subcount = $(this).data('subcount');

            var warningText = "Are you sure you want to remove '" + category + "'?";
            if (subcount > 0) {
                warningText += " This category has " + subcount + " sub-categories that will also be removed!";
            }

            swal({
                title: "Delete Parent Category?",
                text: warningText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then(function(result) {
                if (result.value || result === true) {
                    var form = $('#deleteCategoryForm');
                    form.attr('action', "{{ url('administration/project-categories/category') }}/" + id);
                    form.submit();
                }
            });
        });

        // Toggle Status via AJAX
        $('.toggle-status-btn').on('change', function() {
            var checkbox = $(this);
            var type = checkbox.data('type');
            var id = checkbox.data('id');
            var isChecked = checkbox.is(':checked');

            $.ajax({
                url: "{{ route('administration.project-categories.toggle-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    type: type,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Status updated successfully!');
                    } else {
                        toastr.error('Failed to update status.');
                        checkbox.prop('checked', !isChecked);
                    }
                },
                error: function() {
                    toastr.error('An error occurred while updating status.');
                    checkbox.prop('checked', !isChecked);
                }
            });
        });
    });
</script>
@endsection
