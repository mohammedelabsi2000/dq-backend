<?php

use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\LevelTrackSubjectController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PlanStudentController;
use App\Http\Controllers\api\StudentLevelController;
use App\Http\Controllers\Api\StudentPlanController;
use App\Http\Controllers\Api\StudentSubjectController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Middleware\SetCurrentUserContext;
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

Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->prefix('plan')->group(function () {
    // 1. مسارات الـ Resources الأساسية (توضع دائماً في الأعلى)
    Route::apiResource('plans', PlanController::class);
    Route::apiResource('tracks', TrackController::class);
    Route::apiResource('subjects', SubjectController::class);
    Route::get('subject-requirement-types', [SubjectController::class, 'subject_requirement_types']);

    // 2. مسارات الـ Levels (تأكد من وضع reorder قبل الـ resource)
    Route::post('levels/reorder', [LevelController::class, 'reorder']);
    Route::apiResource('levels', LevelController::class);
    Route::post('levels/{level}/restore', [LevelController::class, 'restore'])->name('levels.restore');

    // 3. المسارات المخصصة والتصحيحية
    Route::post('plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggleActive');

    // تم تغيير الصياغة هنا لتبدأ بكلمة واضحة 'plan-levels' لتجنب التداخل مع الـ IDs
    Route::get('plan-levels/{plan}', [LevelController::class, 'index'])->name('levels.plan.index');

    // Level Track Subjects
    Route::get('level-tracks/{levelTrack}/subjects', [LevelTrackSubjectController::class, 'index']);
    Route::post('level-track-subjects', [LevelTrackSubjectController::class, 'store']);
    Route::get('level-track-subjects/{levelTrackSubject}', [LevelTrackSubjectController::class, 'show']);
    Route::put('level-track-subjects/{levelTrackSubject}', [LevelTrackSubjectController::class, 'update']);
    Route::delete('level-track-subjects/{levelTrackSubject}', [LevelTrackSubjectController::class, 'destroy']);


    Route::prefix('plan-students')->group(function () {
        Route::get('students/{studentId}/history', [PlanStudentController::class, 'studentHistory']);
        Route::get('{planId}', [PlanStudentController::class, 'getStudentsByPlan']);
    });

    Route::apiResource('plan-students', PlanStudentController::class)
        /* ->only(['index', 'store', 'show', 'update']) */ ;

    // Route::put('plan-students/{studentPlan}/move-level', [PlanStudentController::class, 'moveLevel']);
    // Route::put('plan-students/{studentPlan}/close', [PlanStudentController::class, 'close']);
    // Route::put('plan-students/{studentPlan}/set-main', [PlanStudentController::class, 'setMain']);

    //  Route::prefix('level-tracks/{levelTrack}/subjects')->group(function () {
    //     Route::get('/',  [LevelTrackSubjectController::class, 'index']);  // جلب الكل
    //     Route::post('/', [LevelTrackSubjectController::class, 'store']);  // إضافة متعددة
    //     Route::put('/',  [LevelTrackSubjectController::class, 'update']); // sync كامل
    // });

    // Route::get('level-track-subjects/{levelTrackSubject}',    [LevelTrackSubjectController::class, 'show']);
    // Route::delete('level-track-subjects/{levelTrackSubject}', [LevelTrackSubjectController::class, 'destroy']);

    Route::get('{plan}/students', [PlanController::class, 'getStudentsByPlan'])->name('plans.students');
    Route::post('{plan}/students', [PlanController::class, 'storeStudentsByPlan'])->name('plans.students.post');
    Route::put('{plan}/students/{student}', [PlanController::class, 'updateStudentByPlan'])->name('plans.students.update');
    Route::delete('{plan}/students/{student}', [PlanController::class, 'deleteStudentByPlan'])->name('plans.students.delete');
});

Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->group(function () {
    Route::get('standard-circles', [SubjectController::class, 'standardCircles']);
    Route::apiResource('student-subject', StudentSubjectController::class);

    /**
     * Start of Student Plan Routes
     */

    Route::prefix('student')->group(function () {
        Route::get('{student}/plans', [StudentPlanController::class, 'plans'])->name('student.plans');
        Route::get('{student}/unrelatedPlans', [StudentPlanController::class, 'unrelatedPlans'])->name('student.unrelatedPlans');
        Route::get('{student}/plans/{plan}', [StudentPlanController::class, 'planLevels'])->name('student.plan.levels');
        Route::get('{student}/plans/{plan}/level/{level}', [StudentLevelController::class, 'levelSubjects'])->name('student.plan.level.subjects');


    });
    /**
     * End of Student Plan Routes
     */
});
