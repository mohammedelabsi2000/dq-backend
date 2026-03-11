<?php

use App\Http\Controllers\Api\IdQueryController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Available abilities
    Route::get('abilities', [RoleController::class, 'abilities']);

    // Roles CRUD
    Route::apiResource('roles', RoleController::class);

    // ID Query
    Route::post('/id-query', [IdQueryController::class, 'sendRequest']);
});