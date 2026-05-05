<div id="mdlProjectHistory" class="modal fade" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Project History:</h5>
                <button type="button" class="close btnmdlclose" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="project-history-content">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).on('click', '.btn_project_history', function() {
            var projectid = $(this).attr('projectid');
            $('#mdlProjectHistory').modal('show');
            $('#project-history-content').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                type: 'GET',
                url: base_url + '/projects/' + projectid + '/history',
                success: function(response) {
                    if (response.success == true) {
                        var html = '<ul class="verti-timeline list-unstyled">';
                        if (response.histories.length > 0) {
                            response.histories.forEach(function(history) {
                                var date = new Date(history.date).toLocaleDateString('en-GB', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                html += '<li class="event-list">';
                                html += '<div class="event-date text-primary">' + date + '</div>';
                                html += '<h5>' + (history.user ? history.user.name : "System") + '</h5>';
                                html += '<p class="text-muted">' + history.comments + '</p>';
                                html += '</li>';
                            });
                        } else {
                            html += '<div class="text-center py-4">No history found.</div>';
                        }
                        html += '</ul>';
                        $('#project-history-content').html(html);
                    } else {
                        $('#project-history-content').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#project-history-content').html('<div class="alert alert-danger">Failed to fetch history.</div>');
                }
            });
        });
    });
</script>

<style>
    .verti-timeline {
        border-left: 3px solid #f1f1f1;
        margin: 0 10px;
        padding-left: 30px;
    }

    .verti-timeline .event-list {
        position: relative;
        padding-bottom: 20px;
    }

    .verti-timeline .event-list:before {
        content: "";
        position: absolute;
        left: -37px;
        top: 0;
        background-color: #fff;
        border: 3px solid #556ee6;
        border-radius: 50%;
        height: 12px;
        width: 12px;
    }

    .verti-timeline .event-list .event-date {
        position: absolute;
        left: -150px;
        top: 0;
        width: 130px;
        text-align: right;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .verti-timeline .event-list .event-date {
            position: relative;
            left: 0;
            width: auto;
            text-align: left;
            margin-bottom: 5px;
        }

        .verti-timeline {
            margin-left: 0;
        }
    }
</style>
