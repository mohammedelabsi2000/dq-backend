<?php

use App\Http\Controllers\Api\PlanController;
use Illuminate\Support\Facades\Route;

// Custom Juz CRUD

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('plans', PlanController::class);
});