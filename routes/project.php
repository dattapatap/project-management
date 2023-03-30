<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamsController;

Route::prefix('projects')->middleware(['auth'])->group(function(){

    Route::get('/', [ProjectController::class, 'index']);
    Route::post('/assignToTeam', [ProjectController::class, 'assignToTeam']);



});
