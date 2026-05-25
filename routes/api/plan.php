<?php

use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\TrackController;
use Illuminate\Support\Facades\Route;

// Custom Juz CRUD

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('plans', PlanController::class);
    Route::post('plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggleActive');

    Route::get('{plan}/levels', [LevelController::class, 'index'])->name('levels.index');
    Route::apiResource('levels', LevelController::class);
});
