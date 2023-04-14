<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamsController;

Route::prefix('projects')->middleware(['auth'])->group(function(){

    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/create', [ProjectController::class, 'create']);
    Route::get('/search', [ProjectController::class, 'search']);

    Route::get('/taskboard/{project}', [TaskController::class, 'index']);
    Route::get('/taskboard/{task}/edit', [TaskController::class, 'edit']);
    Route::post('/taskboard/{task}/update', [TaskController::class, 'update']);
    Route::post('/taskboard/{task}/destroy', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/taskboard/changestatus', [TaskController::class, 'changestatus']);

    Route::post('/taskboard/addLog', [TaskController::class, 'addTaskLog']);

    Route::get('/task/{taskid}/history', [TaskController::class, 'show']);
    Route::post('/task/progress', [TaskController::class, 'updateProgress']);
    Route::post('/task/comment', [TaskController::class, 'addComment']);


    Route::post('/changestatus', [ProjectController::class, 'status']);
    Route::get('/{project}/edit', [ProjectController::class, 'edit']);
    Route::post('/{project}/update', [ProjectController::class, 'update']);
    Route::post('/{project}/projectupdate', [ProjectController::class, 'projectupdate']);
    Route::post('/{project}/addtask', [TaskController::class, 'addtask']);

    Route::post('/assignToTeam', [ProjectController::class, 'assignToTeam']);



});
