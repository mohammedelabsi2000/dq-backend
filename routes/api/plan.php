<?php

use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\SubjectController;
use Illuminate\Support\Facades\Route;

// Custom Juz CRUD

Route::middleware('auth:sanctum')->prefix('plan')->group(function () {
    Route::post('plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggleActive');
    
    Route::apiResource('tracks', TrackController::class);
    
    Route::apiResource('subjects', SubjectController::class);
    
    Route::get('{plan}/levels', [LevelController::class, 'index'])->name('levels.index');
    Route::apiResource('levels', LevelController::class);
    Route::apiResource('plans', PlanController::class);

});
