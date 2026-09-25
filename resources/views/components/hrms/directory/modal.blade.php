@php
    $allStaffEmployees = app(\App\Services\Hrms\StaffDirectoryService::class)->getDirectory();
    $staffDepts = \App\Models\Department::orderBy('name')->get();
@endphp

<!-- Staff Directory Interactive Modal -->
<div class="modal fade" id="staffDirectoryModal" tabindex="-1" role="dialog" aria-labelledby="staffDirectoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 960px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            {{-- Modal Header --}}
            <div class="modal-header px-4 py-3.5 bg-white border-bottom align-items-center justify-content-between" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex align-items-center justify-content-center text-primary" style="background: #eef2ff; border-radius: 12px; width: 42px; height: 42px; font-size: 22px;">
                        <i class="mdi mdi-account-group-outline"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark font-size-17 mb-0" id="staffDirectoryModalLabel">Staff Directory</h5>
                        <span class="text-muted font-size-12">Search and connect with colleagues across all departments ({{ $allStaffEmployees->count() }} active members)</span>
                    </div>
                </div>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="font-size: 24px; opacity: 0.6; padding: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Modal Body: Search & Filters --}}
            <div class="modal-body p-4 bg-light" style="background-color: #f8fafc !important;">
                {{-- Search Bar & Filter Buttons --}}
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-7 mb-2 mb-md-0">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 text-muted" style="border-radius: 10px 0 0 10px;">
                                            <i class="mdi mdi-magnify font-size-16"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="staffModalSearchInput" class="form-control border-left-0 bg-white" placeholder="Search by name, designation, code, email, phone..." style="border-radius: 0 10px 10px 0;" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex align-items-center justify-content-md-end flex-wrap" style="gap: 6px;" id="staffModalDeptFilters">
                                    <button type="button" class="btn btn-sm btn-primary staff-dept-filter-btn px-2.5 py-1 font-size-11 font-weight-bold active" data-dept="" style="border-radius: 8px;">
                                        All
                                    </button>
                                    @foreach($staffDepts as $dept)
                                    <button type="button" class="btn btn-sm btn-light border staff-dept-filter-btn px-2.5 py-1 font-size-11 font-weight-bold" data-dept="{{ $dept->id }}" style="border-radius: 8px;">
                                        {{ $dept->name }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Staff Full-Width Grid List with Custom Scrollbar --}}
                <div class="row" id="staffModalCardsContainer" style="max-height: 540px; overflow-y: auto; padding-right: 4px;">
                    @foreach($allStaffEmployees as $emp)
                    @php
                        $user = $emp->userAccount;
                        $deptId = $user?->departments?->department ?? '';
                        $deptName = $user?->departments?->dept?->name ?? 'General';
                        $branchName = $user?->branch?->branch?->name ?? 'Head Office';
                        $roleName = $user?->roles->pluck('name')->first() ?? 'Employee';
                        $deptColor = match(strtolower($deptName)) {
                            'sales', 'nsd' => 'purple',
                            'operations', 'od' => 'primary',
                            'customer service', 'csd' => 'success',
                            default => 'info',
                        };
                        $searchableText = strtolower($emp->name . ' ' . $emp->designation . ' ' . $emp->mem_code . ' ' . ($user?->email ?? '') . ' ' . ($emp->alt_number ?? '') . ' ' . $deptName . ' ' . $branchName);
                    @endphp
                    <div class="col-12 mb-3 staff-card-item" data-dept="{{ $deptId }}" data-search="{{ $searchableText }}">
                        <div class="card border-0 shadow-sm staff-single-card mb-0" style="border-radius: 16px; background: #ffffff; transition: transform 0.2s ease, box-shadow 0.2s ease; border-left: 4px solid var(--{{ $deptColor }}, #4f46e5) !important;">
                            <div class="card-body p-3.5">
                                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                                    {{-- Left: Avatar + Name & Designation (with generous gap) --}}
                                    <div class="d-flex align-items-center flex-grow-1" style="min-width: 260px;">
                                        <div class="position-relative mr-3 flex-shrink-0" style="margin-right: 18px !important;">
                                            @if($user?->profile)
                                                <img src="{{ asset('storage/' . $user->profile) }}" alt="{{ $emp->name }}" class="rounded-circle border shadow-sm" style="width: 52px; height: 52px; object-fit: cover;">
                                            @else
                                                <img src="{{ Avatar::create($emp->name)->toBase64() }}" alt="{{ $emp->name }}" class="rounded-circle border shadow-sm" style="width: 52px; height: 52px;">
                                            @endif
                                            <span class="position-absolute badge badge-dot badge-success" style="bottom: 0; right: 0; width: 12px; height: 12px; border: 2px solid #fff;" title="Active"></span>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                                <h6 class="mb-0 text-dark font-weight-bold font-size-15 text-truncate" title="{{ $emp->name }}">{{ $emp->name }}</h6>
                                                <span class="badge badge-soft-{{ $deptColor }} font-size-10 font-weight-bold px-2 py-0.5" style="border-radius: 6px;">{{ $deptName }}</span>
                                                <span class="badge badge-light border text-muted font-size-10 px-2 py-0.5" style="border-radius: 6px;">{{ $branchName }}</span>
                                                @if($emp->mem_code)
                                                <span class="badge badge-soft-secondary font-size-10 px-1.5 py-0.5" style="border-radius: 6px;">#{{ $emp->mem_code }}</span>
                                                @endif
                                                @php $stBadge = $emp->status_badge; @endphp
                                                <span class="badge {{ $stBadge['class'] }} font-size-10 px-2 py-0.5 font-weight-semibold" style="border: 1px solid {{ $stBadge['border'] }}; border-radius: 6px;">{{ $stBadge['label'] }}</span>
                                            </div>
                                            <span class="text-muted font-size-12 d-block text-truncate mt-1">
                                                {{ $emp->designation ?? $roleName }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Middle: Contact Info --}}
                                    <div class="d-flex flex-column" style="gap: 4px; min-width: 220px;">
                                        @if($user?->email || $emp->alt_email)
                                        <div class="d-flex align-items-center text-truncate font-size-12">
                                            <i class="mdi mdi-email-outline mr-2 text-primary flex-shrink-0" style="font-size: 15px;"></i>
                                            <a href="mailto:{{ $user?->email ?? $emp->alt_email }}" class="text-dark text-truncate">{{ $user?->email ?? $emp->alt_email }}</a>
                                        </div>
                                        @endif
                                        @if($emp->alt_number)
                                        <div class="d-flex align-items-center text-truncate font-size-12">
                                            <i class="mdi mdi-phone-outline mr-2 text-success flex-shrink-0" style="font-size: 15px;"></i>
                                            <a href="tel:{{ $emp->alt_number }}" class="text-dark">{{ $emp->alt_number }}</a>
                                        </div>
                                        @endif
                                        <div class="d-flex align-items-center font-size-11 text-muted" style="gap: 12px;">
                                            @if($emp->formatted_joining)
                                            <span><i class="mdi mdi-calendar-badge mr-1 text-warning"></i> Joined: <strong class="text-dark">{{ $emp->formatted_joining }}</strong></span>
                                            @endif
                                            @if($emp->dob)
                                            <span><i class="mdi mdi-cake-variant mr-1 text-danger"></i> Birthday: <strong class="text-dark">{{ \Carbon\Carbon::parse($emp->dob)->format('d M') }}</strong></span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Right: Direct Actions --}}
                                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 8px;">
                                        @if($user?->email)
                                        <a href="mailto:{{ $user->email }}" class="btn btn-sm btn-soft-primary px-3 py-1.5 font-size-11 font-weight-bold" style="border-radius: 8px;">
                                            <i class="mdi mdi-email-outline mr-1"></i> Email
                                        </a>
                                        @endif
                                        @if($emp->alt_number)
                                        <a href="tel:{{ $emp->alt_number }}" class="btn btn-sm btn-soft-success px-3 py-1.5 font-size-11 font-weight-bold" style="border-radius: 8px;">
                                            <i class="mdi mdi-phone mr-1"></i> Call
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Empty Search Results State --}}
                    <div class="col-12 text-center py-5 d-none" id="staffModalEmptyState">
                        <div class="p-4 text-muted">
                            <i class="mdi mdi-account-search-outline font-size-36 d-block mb-1 text-muted"></i>
                            <h6 class="text-dark font-weight-bold mb-1">No staff members found</h6>
                            <p class="text-muted font-size-12 mb-0">Try changing your search keywords or department filter.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer px-4 py-2.5 bg-white border-top justify-content-between">
                <span class="text-muted font-size-12" id="staffModalResultCount">Showing all {{ $allStaffEmployees->count() }} employees</span>
                <button type="button" class="btn btn-sm btn-secondary px-3 font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('staffModalSearchInput');
    var deptButtons = document.querySelectorAll('.staff-dept-filter-btn');
    var cardItems = document.querySelectorAll('.staff-card-item');
    var emptyState = document.getElementById('staffModalEmptyState');
    var resultCount = document.getElementById('staffModalResultCount');

    var currentDept = '';
    var currentQuery = '';

    function filterStaffCards() {
        var visibleCount = 0;
        cardItems.forEach(function(card) {
            var cardDept = card.getAttribute('data-dept') || '';
            var cardSearch = card.getAttribute('data-search') || '';

            var matchesDept = (currentDept === '' || cardDept === currentDept);
            var matchesQuery = (currentQuery === '' || cardSearch.indexOf(currentQuery) !== -1);

            if (matchesDept && matchesQuery) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            emptyState.classList.remove('d-none');
        } else {
            emptyState.classList.add('d-none');
        }

        resultCount.innerText = 'Showing ' + visibleCount + ' employees';
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentQuery = this.value.toLowerCase().trim();
            filterStaffCards();
        });
    }

    deptButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            deptButtons.forEach(function(b) {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-light');
            });
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-light');

            currentDept = this.getAttribute('data-dept') || '';
            filterStaffCards();
        });
    });
});
</script>
