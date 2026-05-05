
$(document).ready(function () {
    var kanbanDrags = document.querySelectorAll('.kanban-drag');

    kanbanDrags.forEach(function (el) {
        new Sortable(el, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'ghost',
            onEnd: function (evt) {
                var itemEl = evt.item;
                var targetStatus = evt.to.getAttribute('data-status');
                var taskId = itemEl.getAttribute('data-id');

                if (evt.from !== evt.to) {
                    updateTaskStatus(taskId, targetStatus);
                }
            }
        });
    });

    function updateTaskStatus(taskId, status) {
        $.ajax({
            url: '/projects/taskboard/move',
            type: 'POST',
            data: {
                task_id: taskId,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    alertify.success(response.message);
                    // Optionally update progress bar in UI if moved to Completed
                    if (status === 'Completed') {
                        $('[data-id="' + taskId + '"] .progress-bar').css('width', '100%').attr('aria-valuenow', 100);
                        $('[data-id="' + taskId + '"] .project-metrics__metric-group-item__value').text('100 %');
                    }
                } else {
                    alertify.error(response.message);
                    location.reload(); // Reload to revert UI state if failed
                }
            },
            error: function (xhr) {
                alertify.error('Something went wrong!');
                location.reload();
            }
        });
    }
});
