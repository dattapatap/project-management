{{-- Sales Executive / Team Leader Dept 1 Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    $(document).ready(function() {
        var isPersonalWorkspace = {{ ($activeTab ?? '') == 'tab-tl-personal' ? 'true' : 'false' }};
        // CSRF Token header set up for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 1. Initializing Yajra Server-side Datatable
        var dtColumns = [{
                data: 'DT_RowIndex',
                name: 'id',
                orderable: true,
                searchable: false
            },
            {
                data: 'name',
                name: 'name',
                orderable: false,
                searchable: true,
                render: function(data, type, row) {
                    var detailUrl = base_url + '/clients/' + btoa(row.id.toString()) + '/sts';
                    return '<a href="' + detailUrl + '" class="font-weight-bold detail-link" style="color: #495057; text-decoration: none; transition: color 0.2s ease-in-out;" onmouseover="this.style.color=\'#7F00FF\'; this.style.textDecoration=\'underline\'" onmouseout="this.style.color=\'#495057\'; this.style.textDecoration=\'none\'"><i class="bx bx-buildings mr-1 text-muted" style="font-size: 13px;"></i>' + data + '</a>';
                }
            },
            {
                data: 'mobile',
                name: 'mobile',
                orderable: false,
                searchable: true
            },
            {
                data: 'category',
                name: 'history.category',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<span class="badge badge-soft-info">' + data + '</span>';
                }
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    var badgeClass = 'badge-soft-secondary';
                    if (data === 'Fresh') badgeClass = 'badge-soft-info';
                    else if (data === 'Followup') badgeClass = 'badge-soft-warning';
                    else if (data === 'Meeting Fixed') badgeClass = 'badge-soft-primary';
                    else if (data === 'Matured') badgeClass = 'badge-soft-success';
                    return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                }
            },
            {
                data: 'remarks',
                name: 'history.remarks',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<span class="text-muted text-wrap d-inline-block" style="max-width: 250px;" title="' + data + '">' + (data ? (data.length > 35 ? data.substr(0, 35) + '...' : data) : 'No remarks yet') + '</span>';
                }
            },
            {
                data: 'tbro',
                name: 'history.tbro',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<span class="text-warning font-weight-bold"><i class="bx bx-calendar-event align-middle mr-1"></i>' + data + '</span>';
                }
            }
        ];

        @if(isAdminOrTeamLeader($user))
        if (!isPersonalWorkspace) {
            dtColumns.push({
                data: 'telereferral',
                name: 'telereferral.name',
                orderable: false,
                searchable: true,
                render: function(data, type, row) {
                    return data ? '<span class="badge badge-soft-primary">' + data + '</span>' : '<span class="text-muted">Unassigned</span>';
                }
            });
        }
        @endif

        dtColumns.push({
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
        });

        var datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                type: 'GET',
                url: base_url + "/todays/tbros" + (isPersonalWorkspace ? "?personal=1" : ""),
                error: function(err) {
                    console.log(err);
                }
            },
            columns: dtColumns
        });

        // 2. Fetching & Rendering Sales Analytics Chart
        $.ajax({
            url: base_url + "/home/chartdata" + (isPersonalWorkspace ? "?personal=1" : ""),
            method: 'GET',
            success: function(response) {
                var options = {
                    series: [{
                        name: 'Matured Clients',
                        type: 'column',
                        data: response.counts
                    }],
                    chart: {
                        height: 330,
                        type: 'line',
                        toolbar: {
                            show: false
                        }
                    },
                    stroke: {
                        width: [0, 4]
                    },
                    colors: ['#7367f0'],
                    labels: response.months,
                    xaxis: {
                        type: 'category'
                    },
                    yaxis: [{
                        title: {
                            text: 'Matured Deals',
                        },
                    }],
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };

                var chart = new ApexCharts(document.querySelector("#line-column-chart"), options);
                chart.render();
            }
        });

        // 2b. Rendering Sales Status Conversion stage Donut charts (Premium design)
        if (typeof ApexCharts !== 'undefined') {
            var statusData = window.salesStatusDistribution || {};
            var labels = Object.keys(statusData);
            var series = Object.values(statusData).map(Number);

            var totalCount = series.reduce(function(a, b) {
                return a + b;
            }, 0);

            // Fallback for visual representation
            if (totalCount === 0) {
                labels = ['Fresh', 'Followup', 'Meeting Fixed', 'Matured', 'Not Interested'];
                series = [0, 0, 0, 0, 0];
            }

            var pieOptions = {
                chart: {
                    type: 'donut',
                    height: 270,
                    toolbar: {
                        show: false
                    }
                },
                series: series,
                labels: labels,
                colors: ['#34c38f', '#f1b44c', '#50a5f1', '#74788d', '#f46a6a'],
                legend: {
                    show: true,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontFamily: 'Outfit, sans-serif',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '13px',
                                    fontFamily: 'Outfit, sans-serif',
                                    fontWeight: 600,
                                    color: '#74788d',
                                    offsetY: -8
                                },
                                value: {
                                    show: true,
                                    fontSize: '20px',
                                    fontFamily: 'Outfit, sans-serif',
                                    fontWeight: 700,
                                    color: '#343a40',
                                    offsetY: 4,
                                    formatter: function(val) {
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total Leads',
                                    color: '#74788d',
                                    fontSize: '11px',
                                    fontFamily: 'Outfit, sans-serif',
                                    fontWeight: 500,
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce(function(a, b) {
                                            return a + b;
                                        }, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    colors: ['#ffffff']
                }
            };

            if (document.querySelector("#sales-stage-pie-chart")) {
                var salesChart = new ApexCharts(document.querySelector("#sales-stage-pie-chart"), pieOptions);
                salesChart.render();
            }

            if (document.querySelector("#team-stage-pie-chart")) {
                var teamChart = new ApexCharts(document.querySelector("#team-stage-pie-chart"), pieOptions);
                teamChart.render();
            }
        }

        // 3. Real-Time Nudge Executive Action Click Handler
        $(document).on('click', '.nudge-exec-btn', function() {
            var btn = $(this);
            var execId = btn.data('exec-id');
            var execName = btn.data('exec-name');
            var overdueCount = btn.data('overdue-count');

            // Set button to loading state
            btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Nudging...');

            $.ajax({
                url: base_url + '/clients/nudge-exec',
                type: 'POST',
                data: {
                    executive_id: execId
                },
                success: function(res) {
                    if (res.status) {
                        showSuccessToast(res.message);
                        btn.html('<i class="bx bx-check"></i> Nudged!').addClass('btn-soft-success').removeClass('btn-soft-danger');
                    } else {
                        showErrorToast(res.message || 'Failed to nudge executive.');
                        btn.prop('disabled', false).html('<i class="bx bx-bell align-middle"></i> Nudge');
                    }
                },
                error: function(err) {
                    showErrorToast('Failed to contact server. Please try again.');
                    btn.prop('disabled', false).html('<i class="bx bx-bell align-middle"></i> Nudge');
                }
            });
        });

        // 4. Lead Allocation Modal Trigger
        $(document).on('click', '.open-allocation-modal-btn', function() {
            var btn = $(this);
            var clientId = btn.data('client-id');
            var clientName = btn.data('client-name');

            $('#allocClientId').val(clientId);
            $('#allocClientName').text(clientName);
            $('#leadAllocationModal').modal('show');
        });

        // 5. Lead Allocation Form Submit Handler
        $('#leadAllocationForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');

            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Allocating...');

            $.ajax({
                url: "{{ route('assignUsersToexecutive') }}",
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.status) {
                        showSuccessToast(res.message || 'Lead assigned successfully!');
                        $('#leadAllocationModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1200);
                    } else {
                        showErrorToast(res.message || 'Failed to assign client.');
                        submitBtn.prop('disabled', false).html('<i class="bx bx-check-shield mr-1"></i> Confirm Assignment');
                    }
                },
                error: function(err) {
                    showErrorToast('An error occurred. Please try again.');
                    submitBtn.prop('disabled', false).html('<i class="bx bx-check-shield mr-1"></i> Confirm Assignment');
                }
            });
        });

        // 🗓 6. Asynchronous Year Filter Auto-Submit Trigger
        $(document).on('change', '.select-year-filter-trigger', function() {
            var selectedYear = $(this).val();
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('year', selectedYear);
            window.location.href = currentUrl.toString();
        });

        // 🪄 7. AI Smart Pitch Modal Generator Hooks
        $(document).on('click', '.ai-smart-pitch-btn', function() {
            var btn = $(this);
            var clientId = btn.data('client-id');
            var name = btn.data('client-name');
            var status = btn.data('client-status');
            var sentiment = btn.data('client-sentiment');

            // Store attributes onto modal element for dynamic radio switching
            var modal = $('#aiPitchModal');
            modal.data('client-name', name);
            modal.data('client-status', status);
            modal.data('client-sentiment', sentiment);

            // Set up visual labels in modal header
            $('#aiClientName').text(name);
            $('#aiClientStatusBadge').text(status)
                .removeClass()
                .addClass('badge')
                .addClass(status === 'Meeting Fixed' ? 'badge-soft-info' : 'badge-soft-warning');

            // Reset copy message and generate initial WhatsApp draft
            $('#aiCopySuccessMsg').hide();
            $('#pitchOptionWhatsApp').addClass('active');
            $('#pitchOptionEmail').removeClass('active');
            $('input[name="pitchType"][value="whatsapp"]').prop('checked', true);

            var initialDraft = generatePitchDraft(name, status, sentiment, 'whatsapp');
            $('#aiGeneratedDraftText').val(initialDraft);

            modal.modal('show');
        });

        // Handle radio selection changes for communication medium
        $('input[name="pitchType"]').on('change', function() {
            var selectedMedium = $(this).val();
            var modal = $('#aiPitchModal');
            var name = modal.data('client-name');
            var status = modal.data('client-status');
            var sentiment = modal.data('client-sentiment');

            var draft = generatePitchDraft(name, status, sentiment, selectedMedium);
            $('#aiGeneratedDraftText').val(draft);
        });

        // Copy button execution
        $(document).on('click', '#aiCopyPitchBtn', function() {
            var draftText = $('#aiGeneratedDraftText').val();
            navigator.clipboard.writeText(draftText).then(function() {
                $('#aiCopySuccessMsg').fadeIn().delay(2000).fadeOut();
            }, function() {
                // cross-browser fallback textarea select
                var copyTextarea = document.createElement("textarea");
                copyTextarea.value = draftText;
                document.body.appendChild(copyTextarea);
                copyTextarea.select();
                document.execCommand("copy");
                document.body.removeChild(copyTextarea);
                $('#aiCopySuccessMsg').fadeIn().delay(2000).fadeOut();
            });
        });

        // Heuristic AI Draft Text Generator Model
        function generatePitchDraft(name, status, sentiment, medium) {
            var greeting = "Hi " + name + ",";
            var body = "";
            var closing = "\n\nBest regards,\n" + "{{ $user->name }}";

            if (medium === 'whatsapp') {
                greeting = "*Hey " + name + "!* 👋";
                closing = "\n\nBest regards,\n*" + "{{ $user->name }}*";

                if (status === 'Meeting Fixed') {
                    body = "\n\nHope you're having a productive week! I'm reaching out to confirm our upcoming portfolio demonstration meeting. I've prepared a custom proposal matching your specific requirements. Let me know if the scheduled time still works for you!";
                } else if (status === 'Followup') {
                    if (sentiment === 'Hesitant (Price)') {
                        body = "\n\nIt was great speaking with you earlier. Regarding our pricing structures, I wanted to let you know that we have introduced highly customizable, modular payment tiers that can align perfectly with your budget. I would love to share a quick draft of a split package with you. Let's do a 2-minute chat?";
                    } else if (sentiment === 'Hesitant (Timing)') {
                        body = "\n\nI completely understand that timing is critical. I wanted to drop a quick note to say we're always here to support whenever you're ready. I'll share our latest corporate portfolio deck so you can review it at your convenience. Let's touch base next week?";
                    } else {
                        body = "\n\nJust wanted to drop a quick message to check if you've had a chance to review the package details we discussed. I would love to answer any questions you might have or adjust the custom outline further. Let me know when you are free for a brief call!";
                    }
                } else {
                    body = "\n\nThank you for connecting with us! I've registered your details in our system. I am preparing some tailored packages and corporate outlines that align with your business goals. I will share them shortly. Let me know if you have any immediate questions!";
                }
            } else { // email
                if (status === 'Meeting Fixed') {
                    body = "\n\nI hope this email finds you well.\n\nI am writing to formally confirm our upcoming scheduled meeting. I have prepared a tailored corporate presentation and customized package outline specifically optimized for your department requirements.\n\nPlease let me know if there are any specific team members you would like me to invite, or if the current scheduled slot is still suitable for you. Looking forward to our discussion.";
                } else if (status === 'Followup') {
                    if (sentiment === 'Hesitant (Price)') {
                        body = "\n\nI hope this email finds you well.\n\nFollowing up on our recent conversation, I completely appreciate your focus on budget alignment. To address this, our team has designed some customized corporate packages featuring highly flexible, split payment tiers.\n\nI have attached our modular pricing matrix to this email, and would love to schedule a brief 5-minute call to show you how we can align our services to your preferred cashflow layout.";
                    } else if (sentiment === 'Hesitant (Timing)') {
                        body = "\n\nI hope this email finds you well.\n\nI completely understand that timing is critical, and we want to ensure you have complete space to evaluate options at your preferred pace.\n\nI have attached our complete corporate case study folder for your future convenience. I will schedule a brief check-in for next month to see if we can assist you then.";
                    } else {
                        body = "\n\nI hope this email finds you well.\n\nI am writing to follow up on the custom services proposal we sent over last week. I would love to schedule a brief call to walk you through our portfolio results or clarify any questions you might have regarding our proposal.\n\nPlease let me know your availability for a quick 10-minute check-in this week.";
                    }
                } else {
                    body = "\n\nI hope this email finds you well.\n\nThank you for expressing interest in our corporate solutions. Your lead profile has been allocated to me, and I am currently reviewing your business requirements.\n\nI am drafting some initial package options and case studies that match your profile. I will share them shortly, but in the meantime, please let me know if there's any specific immediate painpoint you'd like us to focus on.";
                }
            }

            return greeting + body + closing;
        }

        // Close modal explicitly when any dismiss element is clicked
        $(document).on('click', '[data-dismiss="modal"]', function() {
            $(this).closest('.modal').modal('hide');
        });

        // Toggle matured warning in DSR modal
        $(document).on('change', '#dsrStatus', function() {
            if ($(this).val() === 'Matured') {
                $('#dsrMaturedWarning').slideDown();
            } else {
                $('#dsrMaturedWarning').slideUp();
            }
        });

        // 📋 DSR Update Popup & AJAX Submission Handler
        $(document).on('click', '.update-dsr-btn', function(e) {
            e.preventDefault();
            var clientId = $(this).data('client-id');
            var clientName = $(this).data('client-name');

            // Populate Modal Fields
            $('#dsrClientId').val(clientId);
            $('#dsrClientNameLabel').text(clientName);
            $('#dsrRemarksInput').val(''); // clear previous remarks
            $('#dsrStatus').val(''); // reset status
            $('#dsrMaturedWarning').hide();

            // Set dynamic time in HH:MM AM/PM format
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            var strTime = (hours < 10 ? '0' + hours : hours) + ':' + minutes + ' ' + ampm;
            $('#dsrTbroTime').val(strTime);

            // Set default date to today's date (YYYY-MM-DD format for native inputs)
            var day = ("0" + now.getDate()).slice(-2);
            var month = ("0" + (now.getMonth() + 1)).slice(-2);
            var todayStr = now.getFullYear() + "-" + month + "-" + day;
            $('#dsrTbroDate').val(todayStr);

            // Show Modal
            $('#updateDsrModal').modal('show');
        });

        $('#dsrUpdateForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var actionUrl = form.attr('action');
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnHtml = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin mr-1"></i> Saving...');

            var formData = new FormData(form[0]);

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                    if (response.status || response.code == 200) {
                        $('#updateDsrModal').modal('hide');
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'DSR updated successfully!');
                        } else {
                            showSuccessToast(response.message || 'DSR updated successfully!');
                        }

                        // Reload the dashboard to immediately showcase live statistics updates
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Failed to update DSR.');
                        } else {
                            showErrorToast(response.message || 'Failed to update DSR.');
                        }
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                    var errMsg = 'An error occurred while saving.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var firstErrKey = Object.keys(errors)[0];
                        if (firstErrKey && errors[firstErrKey].length > 0) {
                            errMsg = errors[firstErrKey][0];
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errMsg);
                    } else {
                        showErrorToast(errMsg);
                    }
                }
            });
        });

        // 📋 STS Update Popup & AJAX Submission Handler
        $(document).on('click', '.update-sts-btn', function(e) {
            e.preventDefault();
            var clientId = $(this).data('client-id');
            var clientName = $(this).data('client-name');

            // Populate Modal Fields
            $('#stsClientId').val(clientId);
            $('#stsClientNameLabel').text(clientName);
            $('#stsRemarksInput').val(''); // clear previous remarks
            $('#stsStatus').val(''); // reset status
            $('#stsAttachmentType').val('');
            $('#stsAttachment').val('');
            $('#stsAttachment').siblings('.custom-file-label').html('Choose file');

            // Set dynamic time in HH:MM AM/PM format
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            var strTime = (hours < 10 ? '0' + hours : hours) + ':' + minutes + ' ' + ampm;
            $('#stsTbroTime').val(strTime);

            // Set default date to today's date (YYYY-MM-DD format for native inputs)
            var day = ("0" + now.getDate()).slice(-2);
            var month = ("0" + (now.getMonth() + 1)).slice(-2);
            var todayStr = now.getFullYear() + "-" + month + "-" + day;
            $('#stsTbroDate').val(todayStr);

            // Show Modal
            $('#updateStsModal').modal('show');
        });

        // Update file input label on selection
        $(document).on('change', '#stsAttachment', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass("selected").html(fileName ? fileName : 'Choose file');
        });

        $('#stsUpdateForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var actionUrl = form.attr('action');
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnHtml = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin mr-1"></i> Saving...');

            var formData = new FormData(form[0]);

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                    if (response.status || response.code == 200) {
                        $('#updateStsModal').modal('hide');
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'STS updated successfully!');
                        } else {
                            showSuccessToast(response.message || 'STS updated successfully!');
                        }

                        // Reload the dashboard to immediately showcase live statistics updates
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Failed to update STS.');
                        } else {
                            showErrorToast(response.message || 'Failed to update STS.');
                        }
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                    var errMsg = 'An error occurred while saving.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var firstErrKey = Object.keys(errors)[0];
                        if (firstErrKey && errors[firstErrKey].length > 0) {
                            errMsg = errors[firstErrKey][0];
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errMsg);
                    } else {
                        showErrorToast(errMsg);
                    }
                }
            });
        });

        // Helper Toast Notifications (Sleek custom floating indicators)
        function showSuccessToast(message) {
            var toast = $('<div class="floating-toast bg-success text-white px-4 py-3 rounded-lg shadow-lg" style="position:fixed; bottom:20px; right:20px; z-index:9999; display:none; border-radius:12px; font-weight:600; font-family:\'Outfit\', sans-serif;"><i class="bx bx-check-circle mr-2 align-middle font-size-16"></i>' + message + '</div>');
            $('body').append(toast);
            toast.fadeIn().delay(3000).fadeOut(function() {
                $(this).remove();
            });
        }

        function showErrorToast(message) {
            var toast = $('<div class="floating-toast bg-danger text-white px-4 py-3 rounded-lg shadow-lg" style="position:fixed; bottom:20px; right:20px; z-index:9999; display:none; border-radius:12px; font-weight:600; font-family:\'Outfit\', sans-serif;"><i class="bx bx-error-circle mr-2 align-middle font-size-16"></i>' + message + '</div>');
            $('body').append(toast);
            toast.fadeIn().delay(3000).fadeOut(function() {
                $(this).remove();
            });
        }
    });
</script>
