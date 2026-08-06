<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AccessTokensController;
use App\Http\Middleware\SetCurrentUserContext;

// Public routes
Route::post('auth/access-tokens', [AccessTokensController::class, 'store'])
    ->middleware('guest:sanctum');

// Authenticated routes
Route::middleware(['auth:sanctum', SetCurrentUserContext::class, 'idle.timeout'])->group(function () {
    Route::get('/user', function (Request $request) {
        return Auth::guard('sanctum')->user();
    });

    Route::post('change-password', [AccessTokensController::class, 'updatePassword']);
    Route::delete('auth/access-tokens/{token?}', [AccessTokensController::class, 'destroy']);
});