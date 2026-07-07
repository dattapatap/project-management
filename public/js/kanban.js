
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
                var fromStatus = evt.from.getAttribute('data-status');
                var taskId = itemEl.getAttribute('data-id');

                if (evt.from !== evt.to) {
                    // Rule 1: Once moved to InProgress or Completed, cannot go back to ToDo
                    if ((fromStatus === 'InProgress' || fromStatus === 'Completed') && targetStatus === 'ToDo') {
                        alertify.alert('Validation Error', 'Task cannot be moved back to ToDo once it is in progress or completed.', function() {
                            location.reload();
                        });
                        return;
                    }

                    // Rule 4: Cannot move directly from ToDo to Completed
                    if (fromStatus === 'ToDo' && targetStatus === 'Completed') {
                        alertify.alert('Validation Error', 'Task must be in In Progress status before it can be Completed.', function() {
                            location.reload();
                        });
                        return;
                    }

                    // Rule 2: Only Admin, BM, PM, TL can move Completed back to InProgress
                    var isManagement = window.WMS_USER ? window.WMS_USER.is_management : false;
                    if (fromStatus === 'Completed' && targetStatus === 'InProgress' && !isManagement) {
                        alertify.alert('Access Denied', 'Only administrators and managers can move completed tasks back to In Progress.', function() {
                            location.reload();
                        });
                        return;
                    }

                    // Rule 3: Before moving to InProgress or Completed, show confirmation warning
                    if (targetStatus === 'InProgress' || targetStatus === 'Completed') {
                        var title = 'Move Task';
                        var msg = 'Are you sure you want to move this task to ' + targetStatus + '? You will not be able to revert it back to ToDo.';
                        if (targetStatus === 'Completed') {
                            title = 'Complete Task';
                            msg = 'Are you sure you want to complete this task? You will not be able to revert it back to ToDo.';
                        }
                        
                        alertify.confirm(title, msg, 
                            function() {
                                // OK button clicked: update task status
                                updateTaskStatus(taskId, targetStatus);
                            }, 
                            function() {
                                // Cancel button clicked: reload page to revert
                                location.reload();
                            }
                        );
                    } else {
                        updateTaskStatus(taskId, targetStatus);
                    }
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
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                } else {
                    alertify.alert('Validation Error', response.message, function() {
                        location.reload(); // Reload to revert UI state if failed
                    });
                }
            },
            error: function (xhr) {
                alertify.alert('Error', 'Something went wrong!', function() {
                    location.reload();
                });
            }
        });
    }
});
