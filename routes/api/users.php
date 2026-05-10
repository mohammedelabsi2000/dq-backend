<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{UserController, ImageController, UserRoleController};

// All user-related routes are protected by Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    // basic CRUD for users
    Route::apiResource('users', UserController::class);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive']);


    // user images (upload/list)
    Route::get('users/{user}/images', [ImageController::class, 'userImages']);
});
