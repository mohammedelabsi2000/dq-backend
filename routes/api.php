<?php

use App\Http\Middleware\SetCurrentUserContext;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ApprovalController,
    AreaController,
    BranchController,
    CenterController,
    CertificateController,
    ConstantController,
    ConstantTypeController,
    DailyAchievementController,
    HalaqaController,
    HalaqaSponsorshipController,
    HalaqaStatusController,
    HalaqaStudentController,
    IdQueryController,
    ImageController,
    MosqueController,
    RegionController,
    SponsorBranchQuotaController,
    SponsorController,
    StudentController,
    StatisticsController
};

// Load all API route files from the api directory
foreach (glob(__DIR__ . '/api/*.php') as $file) {
    require $file;
}

Route::middleware(['auth:sanctum', SetCurrentUserContext::class])->group(function () {
    // Constants management
    Route::apiResource('constant_types', ConstantTypeController::class);
    Route::apiResource('constants', ConstantController::class);

    // ── الاعتمادات ──────────────────────────────────────
    Route::prefix('approvals')->group(function () {
        Route::get('/', [ApprovalController::class, 'index']);
        Route::post('bulk-approve', [ApprovalController::class, 'bulkApprove']);
        Route::post('bulk-reject', [ApprovalController::class, 'bulkReject']);
        Route::post('{approvalRequest}/approve', [ApprovalController::class, 'approve']);
        Route::post('{approvalRequest}/reject', [ApprovalController::class, 'reject']);
        Route::post('{approvalRequest}/resubmit', [ApprovalController::class, 'resubmit']);
    });

    // External areas (branches/regions) fetched from the AFP API
    Route::prefix('standard-branches')->group(function () {
        Route::get('/', [AreaController::class, 'index']);
        Route::get('{id}/standard-regions', [AreaController::class, 'subAreas']);
    });

    // Geographical hierarchy management (Regions -> Branches -> [Centers & Mosques] -> Halaqas)
    Route::apiResource('branches', BranchController::class);
    Route::post('branches/{branch}/restore', [BranchController::class, 'restore']);
    Route::apiResource('regions', RegionController::class);
    Route::post('regions/{region}/restore', [RegionController::class, 'restore']);
    Route::apiResource('mosques', MosqueController::class);
    Route::post('mosques/{mosque}/restore', [MosqueController::class, 'restore']);
    Route::apiResource('centers', CenterController::class);
    Route::post('centers/{center}/restore', [CenterController::class, 'restore']);
    Route::apiResource('halaqas', HalaqaController::class);
    Route::post('halaqas/{halaqa}/restore', [HalaqaController::class, 'restore']);

    // Sponsors (الكفلاء) management
    Route::apiResource('sponsors', SponsorController::class);
    Route::get('sponsors/{sponsor}/halaqas', [SponsorController::class, 'halaqas']);

    // Sponsor branch quotas (توزيع حصص الكفيل على الأفرع)
    Route::prefix('sponsors/{sponsor}/branch-quotas')->group(function () {
        Route::get('/', [SponsorBranchQuotaController::class, 'index']);
        Route::post('/', [SponsorBranchQuotaController::class, 'store']);
        Route::put('{sponsorBranchQuota}', [SponsorBranchQuotaController::class, 'update']);
        Route::delete('{sponsorBranchQuota}', [SponsorBranchQuotaController::class, 'destroy']);
    });

    // Halaqa <-> Sponsor linking ("إضافة كفالة للحلقة" / "إدارة كفالات الحلقة")
    Route::prefix('halaqas/{halaqa}/sponsorships')->group(function () {
        Route::get('/', [HalaqaSponsorshipController::class, 'index']);
        Route::get('eligible-sponsors', [HalaqaSponsorshipController::class, 'eligibleSponsors']);
        Route::post('/', [HalaqaSponsorshipController::class, 'store']);
        Route::post('{halaqaSponsorship}/stop', [HalaqaSponsorshipController::class, 'stop']);
    });



    // Students management
    Route::apiResource('students', StudentController::class);
    Route::prefix('students')->group(function () {
        Route::post('import', [StudentController::class, 'import']);
        Route::post('import-with-relations', [StudentController::class, 'importWithRelations']);
        Route::get('{student}/images', [ImageController::class, 'studentImages']);
        Route::post('{student}/restore', [StudentController::class, 'restore']);
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
