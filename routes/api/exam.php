<?php

use App\Http\Controllers\Api\ExamCommitteeController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\TrialExamController;
use App\Http\Controllers\Api\RecitationExamController;
use App\Http\Middleware\SetCurrentUserContext;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->prefix('exam')->group(function () {
    // السرد والاختبار التجريبي في قائمة واحدة (كل صف مميَّز بنوعه)
    Route::get('exams', [ExamController::class, 'index']);
    Route::apiResource('trial-exams', TrialExamController::class);
    Route::apiResource('recitation-exams', RecitationExamController::class);

    // لجان الاختبارات النهائية وتنسيب المرشحين لها
    Route::get('final-exam-candidates', [ExamCommitteeController::class, 'candidates']);
    Route::get('committees/{committee}/candidates', [ExamCommitteeController::class, 'committeeCandidates']);
    Route::get('committees/{committee}/students', [ExamCommitteeController::class, 'students']);
    Route::post('committees/{committee}/students', [ExamCommitteeController::class, 'assignStudents']);
    Route::delete('committees/{committee}/students/{studentSubject}', [ExamCommitteeController::class, 'removeStudent']);
    Route::apiResource('committees', ExamCommitteeController::class);
});
