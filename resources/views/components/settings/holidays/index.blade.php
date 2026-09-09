@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    <!-- Header Card -->
    <div class="card mb-4 border shadow-sm" style="border-radius: 16px;">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 12px; width: 44px; height: 44px; font-size: 22px;">
                    <i class="mdi mdi-calendar-star"></i>
                </div>
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold font-size-17">Company Holidays Management</h4>
                    <span class="text-muted font-size-12">Configure annual holidays and non-working days for {{ $year }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0" style="gap: 8px;">
                {{-- Year Filter Form --}}
                <form action="{{ route('settings.holidays.index') }}" method="GET" class="d-inline-flex mr-2">
                    <select name="year" class="form-control form-control-sm font-weight-medium font-size-12" onchange="this.form.submit()" style="height: 36px; border-radius: 8px;">
                        @for ($y = Carbon\Carbon::now()->year + 1; $y >= 2022; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }} Calendar</option>
                        @endfor
                    </select>
                </form>
                <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#addHolidayModal" style="height: 36px; border-radius: 8px;">
                    <i class="mdi mdi-plus mr-1"></i> Add Holiday
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
        <i class="mdi mdi-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Holidays Table Card -->
    <div class="card border shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4 text-muted font-size-11 text-uppercase font-weight-bold">#</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Holiday Name</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Date & Day</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Type</th>
                            <th class="py-3 px-3 text-muted font-size-11 text-uppercase font-weight-bold">Description</th>
                            <th class="py-3 px-4 text-right text-muted font-size-11 text-uppercase font-weight-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $idx => $holiday)
                        @php
                            $hDate = Carbon\Carbon::parse($holiday->holiday_date);
                            $badgeClass = $holiday->type === 'National' ? 'badge-soft-danger' : ($holiday->type === 'Optional' ? 'badge-soft-warning' : 'badge-soft-primary');
                        @endphp
                        <tr>
                            <td class="px-4 font-weight-bold text-muted font-size-12">{{ $idx + 1 }}</td>
                            <td class="px-3">
                                <span class="font-weight-bold text-dark font-size-13">{{ $holiday->name }}</span>
                            </td>
                            <td class="px-3">
                                <span class="font-weight-semibold text-dark font-size-12"><i class="mdi mdi-calendar mr-1 text-primary"></i>{{ $hDate->format('d M, Y') }}</span>
                                <small class="text-muted d-block font-size-11">{{ $hDate->format('l') }}</small>
                            </td>
                            <td class="px-3">
                                <span class="badge {{ $badgeClass }} px-2 py-1 rounded font-size-11 font-weight-bold">{{ $holiday->type }}</span>
                            </td>
                            <td class="px-3 font-size-12 text-muted">
                                {{ $holiday->description ?: '—' }}
                            </td>
                            <td class="px-4 text-right">
                                <button type="button" class="btn btn-light border btn-sm px-2 py-1 mr-1 btn-edit-holiday" data-id="{{ $holiday->id }}" data-name="{{ $holiday->name }}" data-date="{{ $hDate->format('Y-m-d') }}" data-type="{{ $holiday->type }}" data-desc="{{ $holiday->description }}" style="border-radius: 6px;">
                                    <i class="mdi mdi-pencil text-muted font-size-13"></i>
                                </button>
                                <form action="{{ route('settings.holidays.destroy', $holiday->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light border btn-sm px-2 py-1 text-danger" style="border-radius: 6px;">
                                        <i class="mdi mdi-trash-can-outline font-size-13"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="mdi mdi-calendar-remove font-size-24 text-muted d-block mb-1"></i>
                                No holidays registered for {{ $year }}. Click "Add Holiday" to register company holidays.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Holiday Modal --}}
<div class="modal fade" id="addHolidayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form action="{{ route('settings.holidays.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0">Add New Holiday</h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-size-12 font-weight-bold text-dark">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control font-size-13" placeholder="e.g. Independence Day" required style="border-radius: 8px;">
                    </div>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Holiday Date <span class="text-danger">*</span></label>
                            <input type="date" name="holiday_date" class="form-control font-size-13" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Holiday Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="Company">Company Holiday</option>
                                <option value="National">National Holiday</option>
                                <option value="Optional">Optional / Restricted</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-size-12 font-weight-bold text-dark">Description (Optional)</label>
                        <textarea name="description" rows="3" class="form-control font-size-13" placeholder="Additional notes..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Holiday Modal --}}
<div class="modal fade" id="editHolidayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0;">
            <form id="editHolidayForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title font-size-15 font-weight-bold text-dark mb-0">Edit Holiday</h5>
                    <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-size-12 font-weight-bold text-dark">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-holiday-name" class="form-control font-size-13" required style="border-radius: 8px;">
                    </div>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Holiday Date <span class="text-danger">*</span></label>
                            <input type="date" name="holiday_date" id="edit-holiday-date" class="form-control font-size-13" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-size-12 font-weight-bold text-dark">Holiday Type <span class="text-danger">*</span></label>
                            <select name="type" id="edit-holiday-type" class="form-control font-size-13" style="border-radius: 8px;">
                                <option value="Company">Company Holiday</option>
                                <option value="National">National Holiday</option>
                                <option value="Optional">Optional / Restricted</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-size-12 font-weight-bold text-dark">Description (Optional)</label>
                        <textarea name="description" id="edit-holiday-desc" rows="3" class="form-control font-size-13" style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-medium btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">Update Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.btn-edit-holiday').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var date = $(this).data('date');
            var type = $(this).data('type');
            var desc = $(this).data('desc');

            $('#edit-holiday-name').val(name);
            $('#edit-holiday-date').val(date);
            $('#edit-holiday-type').val(type);
            $('#edit-holiday-desc').val(desc);

            var actionUrl = "{{ url('settings/holidays') }}/" + id;
            $('#editHolidayForm').attr('action', actionUrl);

            $('#editHolidayModal').modal('show');
        });
    });
</script>
@endsection
