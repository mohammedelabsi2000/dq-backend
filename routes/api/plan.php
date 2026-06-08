<?php

use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\SubjectController;
use Illuminate\Support\Facades\Route;

// Custom Juz CRUD

// Route::middleware('auth:sanctum')->prefix('plan')->group(function () {
//     Route::post('plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggleActive');
    
//     Route::apiResource('tracks', TrackController::class);
    
//     Route::apiResource('subjects', SubjectController::class);
    
//     // Route::get('{plan}/levels', [LevelController::class, 'index'])->name('levels.index');
//     Route::post('levels/reorder', [LevelController::class, 'reorder']);
//     Route::apiResource('levels', LevelController::class);
//     Route::apiResource('plans', PlanController::class);

// });

Route::middleware('auth:sanctum')->prefix('plan')->group(function () {
    // 1. مسارات الـ Resources الأساسية (توضع دائماً في الأعلى)
    Route::apiResource('plans', PlanController::class);
    Route::apiResource('tracks', TrackController::class);
    Route::apiResource('subjects', SubjectController::class);
    
    // 2. مسارات الـ Levels (تأكد من وضع reorder قبل الـ resource)
    Route::post('levels/reorder', [LevelController::class, 'reorder']);
    Route::apiResource('levels', LevelController::class);
    
    // 3. المسارات المخصصة والتصحيحية
    Route::post('plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggleActive');
    
    // تم تغيير الصياغة هنا لتبدأ بكلمة واضحة 'plan-levels' لتجنب التداخل مع الـ IDs
    Route::get('plan-levels/{plan}', [LevelController::class, 'index'])->name('levels.plan.index');
});
