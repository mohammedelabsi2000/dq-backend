<?php

use App\Http\Controllers\Api\TrialExamController;
use App\Http\Controllers\Api\RecitationExamController;
use App\Http\Middleware\SetCurrentUserContext;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->prefix('exam')->group(function () {
    Route::apiResource('trial-exams', TrialExamController::class);
    // Route::apiResource('recitation-exams', RecitationExamController::class);
});
