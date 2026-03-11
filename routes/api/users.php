<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\UserRoleController;

// All user-related routes are protected by Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    // basic CRUD for users
    Route::apiResource('users', UserController::class);

    // user images (upload/list)
    Route::get('users/{user}/images', [ImageController::class, 'userImages']);

    // manage roles assigned to a user
    Route::prefix('users/{user}/roles')->group(function () {
        Route::get('/', [UserRoleController::class, 'index']);
        Route::post('/', [UserRoleController::class, 'assign']);
        Route::put('/', [UserRoleController::class, 'sync']);
        Route::delete('/', [UserRoleController::class, 'remove']);
    });
});
