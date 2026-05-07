{{-- Sales Team Leader Oversight Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Year Filter Integration
        const yearFilter = document.getElementById('sales_tl_dashboard_year_filter');
        if (yearFilter) {
            yearFilter.addEventListener('change', function() {
                window.location.href = "{{ url('/home') }}?year=" + this.value + "&tab=tab-tl-team";
            });
        }

        // 2. Apex Donut Chart: Team Conversion Distribution
        if (typeof ApexCharts !== 'undefined' && document.querySelector("#sales-team-distribution-donut")) {
            const seriesData = [
                Number('{{ $adminData["status_distribution"]["Matured"] ?? 0 }}'),
                Number('{{ $adminData["status_distribution"]["Followup"] ?? 0 }}'),
                Number('{{ $adminData["status_distribution"]["Meeting Fixed"] ?? 0 }}'),
                Number('{{ $adminData["status_distribution"]["Fresh"] ?? 0 }}'),
                Number('{{ $adminData["status_distribution"]["Not Interested"] ?? 0 }}')
            ];

            const donutOptions = {
                series: seriesData,
                chart: {
                    type: 'donut',
                    height: 270,
                },
                labels: ['Matured', 'Followup', 'Meeting Fixed', 'Fresh', 'Not Interested'],
                colors: ['#34c38f', '#50a5f1', '#7F00FF', '#556ee6', '#f46a6a'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Leads',
                                    fontSize: '13px',
                                    fontWeight: 600,
                                    color: '#74788d',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    fontFamily: 'Outfit, sans-serif',
                },
                dataLabels: {
                    enabled: false
                }
            };

            const chart = new ApexCharts(document.querySelector("#sales-team-distribution-donut"), donutOptions);
            chart.render();
        }

        // 3. Lead Allocation Trigger (AJAX Assignment)
        $(document).on('click', '.allocate-lead-btn', function() {
            const btn = $(this);
            const leadId = btn.data('lead-id');
            const selectEl = $(`.select-allocation-executive[data-lead-id="${leadId}"]`);
            const execId = selectEl.val();

            if (!execId) {
                toastr.warning("Please choose a Sales Executive first.");
                return;
            }

            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

            $.ajax({
                url: "{{ route('assignUsersToexecutive') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    clientid: leadId,
                    executive: execId
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message || "Lead allocated successfully!");
                        // Fade row out gracefully
                        $(`#alloc-row-${leadId}`).fadeOut(450, function() {
                            $(this).remove();
                            // Check if table is empty to show a beautiful placeholder
                            if ($('[id^="alloc-row-"]').length === 0) {
                                $('#alloc-row-container').html('<tr><td colspan="3" class="text-center py-4 text-muted">All fresh leads allocated. Beautiful job! 🌟</td></tr>');
                            }
                        });
                    } else {
                        toastr.error(response.message || "Failed to allocate lead.");
                    }
                },
                error: function(xhr) {
                    toastr.error("An error occurred during lead allocation.");
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // 4. Global Executive Nudge
        $(document).on('click', '.nudge-executive-btn', function() {
            const btn = $(this);
            const execId = btn.data('exec-id');
            const execName = btn.data('exec-name');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin mr-1"></i> Nudging...');

            $.ajax({
                url: "{{ url('/clients/nudge-exec') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    executive_id: execId
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || "Could not nudge executive.");
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to nudge executive. They might have no active/overdue leads currently.");
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // 5. Specific Lead Callback Nudge
        $(document).on('click', '.nudge-lead-specific-btn', function() {
            const btn = $(this);
            const leadId = btn.data('lead-id');
            const leadName = btn.data('lead-name');
            const execName = btn.data('exec-name');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

            $.ajax({
                url: "{{ url('/client') }}/" + leadId + "/nudge",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || "Could not nudge executive.");
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to nudge executive.");
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
