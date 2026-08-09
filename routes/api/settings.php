<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);
});
