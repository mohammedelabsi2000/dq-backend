<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ApprovalController,
    ApprovalController as ApiApprovalController,
    AttendanceController,
    BranchController,
    CenterController,
    CertificateController,
    ConstantController,
    ConstantTypeController,
    CourseController,
    DailyAchievementController,
    GradeController,
    HalaqaController,
    HalaqaStatusController,
    HalaqaStudentController,
    IdQueryController,
    ImageController,
    MosqueController,
    PlanAssignmentController,
    PlanController,
    PlanStudentController,
    RegionController,
    StudentController,
    TrackController,
    StatisticsController
};
use App\Models\User;

// Load all API route files from the api directory
foreach (glob(__DIR__ . '/api/*.php') as $file) {
    require $file;
}

Route::middleware('auth:sanctum')->group(function () {
    // Constants management
    Route::apiResource('constant_types', ConstantTypeController::class);
    Route::apiResource('constants', ConstantController::class);

    // ── الاعتمادات ──────────────────────────────────────
    Route::prefix('approvals')->group(function () {
        Route::get('/', [ApprovalController::class, 'index']);
        Route::post('{approvalRequest}/approve', [ApprovalController::class, 'approve']);
        Route::post('{approvalRequest}/reject', [ApprovalController::class, 'reject']);
        Route::post('{approvalRequest}/resubmit', [ApprovalController::class, 'resubmit']);
        Route::post('{approvalRequest}/cancel', [ApprovalController::class, 'cancel']);
    });

    // Geographical hierarchy management (Regions -> Branches -> [Centers & Mosques] -> Halaqas)
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('mosques', MosqueController::class);
    Route::apiResource('centers', CenterController::class);
    Route::apiResource('halaqas', HalaqaController::class);



    // Students management
    Route::apiResource('students', StudentController::class);
    Route::prefix('students')->group(function () {
        Route::post('import', [StudentController::class, 'import']);
        Route::post('import-with-relations', [StudentController::class, 'importWithRelations']);
        Route::get('{student}/images', [ImageController::class, 'studentImages']);
    });

    // Halaqa students assignment
    Route::apiResource('halaqa-students', HalaqaStudentController::class)->except(['update']);
    Route::put('halaqa-students', [HalaqaStudentController::class, 'update']);

    // Halaqa statuses management
    Route::apiResource('halaqa-statuses', HalaqaStatusController::class);

    Route::apiResource('certificates', CertificateController::class);
    Route::prefix('certificates')->group(function () {
        Route::get('{person_type}/{person_id}', [CertificateController::class, 'getPersonCertificates']);
    });

    // Images management (for students, users, qualifications, etc.)
    Route::apiResource('images', ImageController::class)
        ->only(['store', 'destroy', 'index', 'show']);

    // ID Query endpoint
    Route::post('/id-query', [IdQueryController::class, 'sendRequest']);
    Route::get('/statistics', [StatisticsController::class, 'index']);

    // Daily achievements management
    Route::prefix('daily-memorization')->group(function () {
        Route::get('students/{studentId}', [DailyAchievementController::class, 'studentAchievements']);
        Route::get('statistics', [DailyAchievementController::class, 'statistics']);
    });
    Route::apiResource('daily-memorization', DailyAchievementController::class);

});
