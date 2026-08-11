<?php

use App\Http\Controllers\Api\HalaqaController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Middleware\SetCurrentUserContext;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->prefix('export')->group(function () {
    Route::get('halaqas', [HalaqaController::class, 'export']);
    Route::get('students', [StudentController::class, 'export']);
});
