<?php

use App\Http\Controllers\Api\AccessTokensController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('auth/access-tokens', [AccessTokensController::class, 'store'])
    ->middleware('guest:sanctum');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return Auth::guard('sanctum')->user();
    });

    Route::post('change-password', [AccessTokensController::class, 'updatePassword']);
    Route::delete('auth/access-tokens/{token?}', [AccessTokensController::class, 'destroy']);
});