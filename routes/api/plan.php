<?php

use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\LevelTrackSubjectController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\StudentPlanController;
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
    Route::get('subject-requirement-types', [SubjectController::class, 'subject_requirement_types']);

    // 2. مسارات الـ Levels (تأكد من وضع reorder قبل الـ resource)
    Route::post('levels/reorder', [LevelController::class, 'reorder']);
    Route::apiResource('levels', LevelController::class);

    // 3. المسارات المخصصة والتصحيحية
    Route::post('plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggleActive');

    // تم تغيير الصياغة هنا لتبدأ بكلمة واضحة 'plan-levels' لتجنب التداخل مع الـ IDs
    Route::get('plan-levels/{plan}', [LevelController::class, 'index'])->name('levels.plan.index');

    // Level Track Subjects
    Route::get('level-tracks/{levelTrack}/subjects',          [LevelTrackSubjectController::class, 'index']);
    Route::post('level-track-subjects',                       [LevelTrackSubjectController::class, 'store']);
    Route::get('level-track-subjects/{levelTrackSubject}',    [LevelTrackSubjectController::class, 'show']);
    Route::put('level-track-subjects/{levelTrackSubject}',  [LevelTrackSubjectController::class, 'update']);
    Route::delete('level-track-subjects/{levelTrackSubject}', [LevelTrackSubjectController::class, 'destroy']);


    Route::prefix('plan-students')->group(function () {
        Route::get('students/{studentId}/history', [StudentPlanController::class, 'studentHistory']);
        Route::get('{planId}', [StudentPlanController::class, 'getStudentsByPlan']);
    });

    Route::apiResource('plan-students', StudentPlanController::class)
        /* ->only(['index', 'store', 'show', 'update']) */;

    // Route::put('plan-students/{studentPlan}/move-level', [StudentPlanController::class, 'moveLevel']);
    // Route::put('plan-students/{studentPlan}/close', [StudentPlanController::class, 'close']);
    // Route::put('plan-students/{studentPlan}/set-main', [StudentPlanController::class, 'setMain']);

    //  Route::prefix('level-tracks/{levelTrack}/subjects')->group(function () {
    //     Route::get('/',  [LevelTrackSubjectController::class, 'index']);  // جلب الكل
    //     Route::post('/', [LevelTrackSubjectController::class, 'store']);  // إضافة متعددة
    //     Route::put('/',  [LevelTrackSubjectController::class, 'update']); // sync كامل
    // });

    // Route::get('level-track-subjects/{levelTrackSubject}',    [LevelTrackSubjectController::class, 'show']);
    // Route::delete('level-track-subjects/{levelTrackSubject}', [LevelTrackSubjectController::class, 'destroy']);

    Route::get('{plan}/students', [PlanController::class, 'getStudentsByPlan'])->name('plans.students');
    Route::post('{plan}/students', [PlanController::class, 'storeStudentsByPlan'])->name('plans.students.post');
});
