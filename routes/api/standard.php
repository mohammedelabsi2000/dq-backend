<?php

use App\Http\Controllers\Api\StandardController;
use App\Http\Middleware\SetCurrentUserContext;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->prefix('standard')->group(function () {
    Route::get('circles', [StandardController::class, 'circles']);
    Route::get('courses', [StandardController::class, 'courses']);
});
