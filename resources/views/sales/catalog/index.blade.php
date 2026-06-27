@extends('layouts.app')

@section('content')
<div class="container-fluid erp-page pb-5">
    <div class="erp-page-header my-4">
        <div class="erp-page-header__main">
            <h4 class="erp-page-title">
                <i class="mdi mdi-book-open-outline mr-2 text-primary"></i>Product & Service Catalog
            </h4>
            <p class="erp-page-subtitle">Track and configure service catalogs</p>
        </div>
        <div class="erp-page-header__actions">
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm mr-2">
                <i class="mdi mdi-arrow-left mr-1"></i>Back to Home
            </a>
            @if(Auth::user()->hasRole(['Admin', 'Project-Manager', 'Branch-Manager', 'Team-Leader']))
            <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addCatalogModal">
                <i class="mdi mdi-plus mr-1"></i>Add Catalog Item
            </button>
            @endif
        </div>
    </div>

    <!-- Catalog Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-white border shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="catalog-table" class="table table-centered table-nowrap table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Billing Cycle</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    @if(Auth::user()->hasRole(['Admin', 'Project-Manager', 'Branch-Manager', 'Team-Leader']))
                                    <th class="text-center">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($catalogs as $item)
                                    <tr>
                                        <td class="font-weight-semibold text-dark">{{ $item->name }}</td>
                                        <td>
                                            <span class="badge badge-pill badge-soft-primary font-weight-semibold px-2.5 py-1">{{ $item->category }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted font-weight-medium"><i class="mdi mdi-sync mr-1"></i>{{ ucfirst($item->billing_cycle) }}</span>
                                        </td>
                                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" class="text-muted font-weight-medium" title="{{ $item->description }}">
                                            {{ $item->description ?? 'No description' }}
                                        </td>
                                        <td>
                                            <span class="badge badge-pill {{ $item->is_active ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} font-weight-bold px-2.5 py-1">
                                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        @if(Auth::user()->hasRole(['Admin', 'Project-Manager', 'Branch-Manager', 'Team-Leader']))
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-info edit-btn mr-1" 
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-category="{{ $item->category }}"
                                                    data-billing_cycle="{{ $item->billing_cycle }}"
                                                    data-description="{{ $item->description }}">
                                                <i class="mdi mdi-pencil-outline"></i>
                                            </button>
                                            <button class="btn btn-sm {{ $item->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} toggle-status-btn"
                                                    data-id="{{ $item->id }}">
                                                <i class="mdi {{ $item->is_active ? 'mdi-close-circle-outline' : 'mdi-checkbox-marked-circle-outline' }}"></i>
                                            </button>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCatalogModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="mdi mdi-plus-box-outline mr-2 text-primary"></i>Add Service Catalog Item</h5>
                <button type="button" class="close text-dark btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCatalogForm">
                @csrf
                <div class="modal-body text-dark">
                    <div class="form-group">
                        <label class="font-weight-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control border" required placeholder="e.g. Premium SEO, Enterprise Web Design">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-control border" required>
                            <option value="Development">Development</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Design">Design</option>
                            <option value="CSD">CSD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Billing Cycle <span class="text-danger">*</span></label>
                        <select name="billing_cycle" class="form-control border" required>
                            <option value="one-time">One-time</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Description</label>
                        <textarea name="description" class="form-control border" rows="3" placeholder="Enter deliverables details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary btnmdlclose" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary shadow-sm">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCatalogModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark"><i class="mdi mdi-pencil-box-outline mr-2 text-info"></i>Edit Catalog Item</h5>
                <button type="button" class="close text-dark btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCatalogForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-id">
                <div class="modal-body text-dark">
                    <div class="form-group">
                        <label class="font-weight-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit-name" name="name" class="form-control border" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Category <span class="text-danger">*</span></label>
                        <select id="edit-category" name="category" class="form-control border" required>
                            <option value="Development">Development</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Design">Design</option>
                            <option value="CSD">CSD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Billing Cycle <span class="text-danger">*</span></label>
                        <select id="edit-billing_cycle" name="billing_cycle" class="form-control border" required>
                            <option value="one-time">One-time</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-semibold">Description</label>
                        <textarea id="edit-description" name="description" class="form-control border" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary btnmdlclose" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info shadow-sm text-white">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#catalog-table').DataTable({
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        // Add form submit
        $('#addCatalogForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('sales.catalog.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        alertify.success(response.message);
                        setTimeout(() => window.location.reload(), 800);
                    }
                },
                error: function(xhr) {
                    alertify.error(xhr.responseJSON?.message || "Validation or server error occurred.");
                }
            });
        });

        // Edit button click
        $('.edit-btn').click(function() {
            const btn = $(this);
            $('#edit-id').val(btn.data('id'));
            $('#edit-name').val(btn.data('name'));
            $('#edit-category').val(btn.data('category'));
            $('#edit-billing_cycle').val(btn.data('billing_cycle'));
            $('#edit-description').val(btn.data('description'));
            $('#editCatalogModal').modal('show');
        });

        // Edit form submit
        $('#editCatalogForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit-id').val();
            $.ajax({
                url: "{{ url('/') }}/sales/catalog/" + id,
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        alertify.success(response.message);
                        setTimeout(() => window.location.reload(), 800);
                    }
                },
                error: function(xhr) {
                    alertify.error(xhr.responseJSON?.message || "Validation or server error.");
                }
            });
        });

        // Toggle active status
        $('.toggle-status-btn').click(function() {
            const id = $(this).data('id');
            alertify.confirm("Confirm Status Change", "Are you sure you want to toggle this catalog item status?", 
                function() {
                    $.ajax({
                        url: "{{ url('/') }}/sales/catalog/" + id + "/toggle",
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                alertify.success(response.message);
                                setTimeout(() => window.location.reload(), 800);
                            }
                        }
                    });
                },
                function() {}
            );
        });
    });
</script>
@endsection
