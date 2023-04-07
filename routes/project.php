<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamsController;

Route::prefix('projects')->middleware(['auth'])->group(function(){

    Route::get('/', [ProjectController::class, 'index']);

    Route::get('/taskboard/{project}', [TaskController::class, 'index']);
    Route::get('/{project}/edit', [ProjectController::class, 'edit']);
    Route::post('/{project}/update', [ProjectController::class, 'update']);
    Route::post('/{project}/projectupdate', [ProjectController::class, 'projectupdate']);
    Route::post('/{project}/addtask', [TaskController::class, 'addtask']);

    Route::post('/assignToTeam', [ProjectController::class, 'assignToTeam']);



});
