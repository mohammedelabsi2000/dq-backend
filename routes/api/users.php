<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{UserController, ImageController, UserRoleController};
use App\Http\Middleware\SetCurrentUserContext;

// All user-related routes are protected by Sanctum authentication
Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->group(function () {
    // basic CRUD for users
    Route::get('users/candidate-teachers', [UserController::class, 'candidateTeachers']);
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/restore', [UserController::class, 'restore']);
    Route::put('users/{user}/toggle-active', [UserController::class, 'toggleActive']);


    // user images (upload/list)
    Route::get('users/{user}/images', [ImageController::class, 'userImages']);
});
