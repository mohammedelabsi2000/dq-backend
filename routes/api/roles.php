<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;

Route::middleware('auth:sanctum')->group(function () {
    // Available abilities
    Route::get('abilities', [RoleController::class, 'abilities']);

    // Roles CRUD
    Route::apiResource('roles', RoleController::class);
});