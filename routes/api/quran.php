<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomJuzController;
use App\Http\Controllers\Api\QuranController;

// Custom Juz CRUD

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('custom-juz', CustomJuzController::class)->parameters([
        'custom-juz' => 'juz'
    ]);
    Route::get('custom-juz/{juz}/surahs', [CustomJuzController::class, 'surahs']);

    Route::get('juz', [QuranController::class, 'juz']);
    Route::get('surah', [QuranController::class, 'surahs']);

});