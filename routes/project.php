<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamsController;

Route::prefix('projects')->middleware(['auth', 'restrict.wms'])->group(function(){

    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/create', [ProjectController::class, 'create']);
    Route::get('/search', [ProjectController::class, 'search']);
    Route::get('/timeline', [ProjectController::class, 'timeline'])->name('projects.timeline');
    Route::get('/resources', [\App\Http\Controllers\Od\ResourceController::class, 'index'])->name('projects.resources');
    Route::get('/active-list', [ProjectController::class, 'activeProjectsList'])->name('projects.active-list');

    Route::get('/taskboard/{project}', [TaskController::class, 'index']);
    Route::get('/taskboard/{task}/edit', [TaskController::class, 'edit']);
    Route::post('/taskboard/{task}/update', [TaskController::class, 'update']);
    Route::post('/taskboard/{task}/destroy', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/taskboard/move', [TaskController::class, 'moveTask']);
    Route::post('/taskboard/changestatus', [TaskController::class, 'changestatus']);

    // Task Timer Routes
    Route::post('/tasks/{task}/timer/start', [TaskController::class, 'startTimer'])->name('tasks.timer.start');
    Route::post('/tasks/{task}/timer/pause', [TaskController::class, 'pauseTimer'])->name('tasks.timer.pause');

    // Global Shift Timer Routes
    Route::get('/global-timer/status', [\App\Http\Controllers\GlobalTimerController::class, 'status'])->name('global-timer.status');
    Route::post('/global-timer/start', [\App\Http\Controllers\GlobalTimerController::class, 'start'])->name('global-timer.start');
    Route::post('/global-timer/pause', [\App\Http\Controllers\GlobalTimerController::class, 'pause'])->name('global-timer.pause');
    Route::post('/global-timer/stop', [\App\Http\Controllers\GlobalTimerController::class, 'stop'])->name('global-timer.stop');

    Route::get('/task/{taskid}/history', [TaskController::class, 'show']);
    Route::post('/task/progress', [TaskController::class, 'updateProgress']);
    Route::post('/task/comment', [TaskController::class, 'addComment']);
    Route::post('/tasks/nudge/{task}', [TaskController::class, 'nudge'])->name('tasks.nudge');


    Route::post('/changestatus', [ProjectController::class, 'status']);
    Route::get('/{project}/edit', [ProjectController::class, 'edit']);
    Route::post('/{project}/update', [ProjectController::class, 'update']);
    Route::post('/{project}/projectupdate', [ProjectController::class, 'projectupdate']);
    Route::post('/{project}/addtask', [TaskController::class, 'addtask']);

    Route::post('/assignToTeam', [ProjectController::class, 'assignToTeam']);
    Route::get('/get-team-leaders', [ProjectController::class, 'getTeamLeadersByCategory']);
    Route::post('/assign-to-tl', [ProjectController::class, 'assignToTL']);

});
