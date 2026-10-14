<?php

use App\Http\Controllers\Api\ExamCommitteeController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\TrialExamController;
use App\Http\Controllers\Api\RecitationExamController;
use App\Http\Middleware\SetCurrentUserContext;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->prefix('exam')->group(function () {
    // السرد والاختبار التجريبي في قائمة واحدة (كل صف مميَّز بنوعه)
    Route::get('exams', [ExamController::class, 'index'])->middleware('permission:exams.show');

    // الاختبار التجريبي والسرد التجريبي: نفس المسارات بصلاحية لكل عملية
    $examResources = [
        'trial-exams' => [TrialExamController::class, 'trial_exam'],
        'recitation-exams' => [RecitationExamController::class, 'recitation_exam'],
    ];

    foreach ($examResources as $uri => [$controller, $param]) {
        Route::get($uri, [$controller, 'index'])->middleware('permission:exams.show');
        Route::post($uri, [$controller, 'store'])->middleware('permission:exams.create');
        Route::get("$uri/{{$param}}", [$controller, 'show'])->middleware('permission:exams.show');
        Route::match(['put', 'patch'], "$uri/{{$param}}", [$controller, 'update'])->middleware('permission:exams.update');
        Route::delete("$uri/{{$param}}", [$controller, 'destroy'])->middleware('permission:exams.delete');
    }

    // المرشحون للاختبارات النهائية
    Route::get('final-exam-candidates', [ExamCommitteeController::class, 'candidates'])->middleware('permission:final_exam_candidates.show');

    // لجان الاختبارات النهائية وتنسيب المرشحين لها
    Route::get('committees', [ExamCommitteeController::class, 'index'])->middleware('permission:exam_committees.show');
    Route::post('committees', [ExamCommitteeController::class, 'store'])->middleware('permission:exam_committees.create');
    Route::get('committees/{committee}', [ExamCommitteeController::class, 'show'])->middleware('permission:exam_committees.show');
    Route::match(['put', 'patch'], 'committees/{committee}', [ExamCommitteeController::class, 'update'])->middleware('permission:exam_committees.update');
    Route::delete('committees/{committee}', [ExamCommitteeController::class, 'destroy'])->middleware('permission:exam_committees.delete');

    Route::get('committees/{committee}/students', [ExamCommitteeController::class, 'students'])->middleware('permission:exam_committees.show');
    Route::get('committees/{committee}/candidates', [ExamCommitteeController::class, 'committeeCandidates'])->middleware('permission:exam_committees.assign_students');
    Route::post('committees/{committee}/students', [ExamCommitteeController::class, 'assignStudents'])->middleware('permission:exam_committees.assign_students');
    Route::delete('committees/{committee}/students/{studentSubject}', [ExamCommitteeController::class, 'removeStudent'])->middleware('permission:exam_committees.assign_students');
    Route::post('committees/{committee}/approve', [ExamCommitteeController::class, 'approve'])->middleware('permission:exam_committees.approve');
    Route::post('committees/{committee}/sync-quality', [ExamCommitteeController::class, 'syncQuality'])->middleware('permission:exam_committees.sync_quality');
});
