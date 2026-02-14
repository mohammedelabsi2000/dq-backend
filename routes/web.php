<?php

use App\Http\Controllers\Api\BranchController as ApiBranchController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    DashboardController,
    UserController,
    MosqueController,
    CenterController,
    BranchController,
    RegionController,
    ConstantTypeController,
    ConstantController,
    PlanController,
    PlanLevelController,
    GradeController,
    AcademicQualificationController,
    PersonalCourseController,
    ProfileController
};
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::resource('branches', BranchController::class);
// Route::resource('branches', ApiBranchController::class);




/*** */

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
require __DIR__ . '/auth.php';

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== Users Management ====================
    Route::resource('users', UserController::class);
    Route::get('users/{user}/qualifications', [UserController::class, 'qualifications'])->name('users.qualifications');
    Route::post('users/{user}/qualifications', [UserController::class, 'storeQualification'])->name('users.qualifications.store');
    Route::delete('users/{user}/qualifications/{qualification}', [UserController::class, 'destroyQualification'])->name('users.qualifications.destroy');

    Route::get('users/{user}/courses', [UserController::class, 'courses'])->name('users.courses');
    Route::post('users/{user}/courses', [UserController::class, 'storeCourse'])->name('users.courses.store');
    Route::delete('users/{user}/courses/{course}', [UserController::class, 'destroyCourse'])->name('users.courses.destroy');

    // ==================== Mosques Management ====================
    Route::resource('mosques', MosqueController::class);
    Route::get('mosques/{mosque}/centers', [MosqueController::class, 'centers'])->name('mosques.centers');
    Route::get('mosques/{mosque}/users', [MosqueController::class, 'users'])->name('mosques.users');
    Route::post('mosques/{mosque}/toggle-status', [MosqueController::class, 'toggleStatus'])->name('mosques.toggle-status');

    // ==================== Centers Management ====================
    Route::resource('centers', CenterController::class);
    Route::get('centers/{center}/users', [CenterController::class, 'users'])->name('centers.users');
    Route::get('centers/{center}/plans', [CenterController::class, 'plans'])->name('centers.plans');
    Route::post('centers/{center}/assign-plan', [CenterController::class, 'assignPlan'])->name('centers.assign-plan');

    // ==================== Branches Management ====================
    // Route::resource('branches', BranchController::class);
    // Route::get('branches/{branch}/regions', [BranchController::class, 'regions'])->name('branches.regions');
    // Route::get('branches/{branch}/mosques', [BranchController::class, 'mosques'])->name('branches.mosques');
    // Route::get('branches/{branch}/statistics', [BranchController::class, 'statistics'])->name('branches.statistics');

    // Route::get('branches/bulk/create', [BranchController::class, 'bulkCreate'])->name('branches.bulk.create');
    // Route::post('branches/bulk', [BranchController::class, 'bulkStore'])->name('branches.bulk.store');


    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('branches.index');
        Route::post('/store', [BranchController::class, 'store'])->name('branches.store');
        Route::put('/{id}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/{id}', [BranchController::class, 'destroy'])->name('branches.destroy');
        Route::post('/multi-delete', [BranchController::class, 'multiDelete'])->name('branches.multiDelete');
        Route::get('/search', [BranchController::class, 'search'])->name('branches.search');
    });
    // ==================== Regions Management ====================
    // Route::resource('regions', RegionController::class);
    // Route::get('regions/{region}/mosques', [RegionController::class, 'mosques'])->name('regions.mosques');
    // Route::get('regions/{region}/centers', [RegionController::class, 'centers'])->name('regions.centers');
    // صفحة المناطق
    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');

    // إضافة منطقة واحدة أو متعددة
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');

    // تعديل منطقة
    Route::put('/regions/{region}', [RegionController::class, 'update'])->name('regions.update');

    // حذف منطقة فردية
    Route::delete('/regions/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');

    // حذف عدة مناطق
    Route::post('/regions/multi-delete', [RegionController::class, 'multiDelete'])->name('regions.multiDelete');

    // بحث مباشر
    Route::get('/regions/search', [RegionController::class, 'search'])->name('regions.search');
    // ==================== Constants Management ====================
    // Constant Types
    Route::resource('constant-types', ConstantTypeController::class);
    Route::post('constant-types/{constantType}/toggle-status', [ConstantTypeController::class, 'toggleStatus'])->name('constant-types.toggle-status');

    // Constants
    Route::resource('constants', ConstantController::class);
    Route::get('constants/by-type/{type}', [ConstantController::class, 'byType'])->name('constants.by-type');
    Route::get('constants/{constant}/children', [ConstantController::class, 'children'])->name('constants.children');
    Route::post('constants/{constant}/toggle-status', [ConstantController::class, 'toggleStatus'])->name('constants.toggle-status');
    Route::post('constants/reorder', [ConstantController::class, 'reorder'])->name('constants.reorder');

    // ==================== Plans Management ====================
    Route::resource('plans', PlanController::class);
    Route::get('plans/{plan}/levels', [PlanController::class, 'levels'])->name('plans.levels');
    Route::get('plans/{plan}/duplicate', [PlanController::class, 'duplicate'])->name('plans.duplicate');
    Route::post('plans/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])->name('plans.toggle-status');
    Route::get('plans/{plan}/export', [PlanController::class, 'export'])->name('plans.export');

    // Plan Levels
    Route::resource('plan-levels', PlanLevelController::class);
    Route::post('plan-levels/reorder', [PlanLevelController::class, 'reorder'])->name('plan-levels.reorder');
    Route::get('plan-levels/{planLevel}/prerequisites', [PlanLevelController::class, 'prerequisites'])->name('plan-levels.prerequisites');

    // ==================== Grades Management ====================
    Route::resource('grades', GradeController::class);
    Route::get('grades/export/pdf', [GradeController::class, 'exportPdf'])->name('grades.export-pdf');
    Route::get('grades/export/excel', [GradeController::class, 'exportExcel'])->name('grades.export-excel');

    // ==================== Academic Qualifications ====================
    Route::resource('academic-qualifications', AcademicQualificationController::class)->except(['create', 'edit']);
    Route::get('academic-qualifications/person/{personType}/{personId}', [AcademicQualificationController::class, 'byPerson'])->name('academic-qualifications.by-person');

    // ==================== Personal Courses ====================
    Route::resource('personal-courses', PersonalCourseController::class)->except(['create', 'edit']);
    Route::get('personal-courses/person/{personType}/{personId}', [PersonalCourseController::class, 'byPerson'])->name('personal-courses.by-person');

    // ==================== Reports ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('users', [App\Http\Controllers\ReportController::class, 'users'])->name('users');
        Route::get('users/export', [App\Http\Controllers\ReportController::class, 'exportUsers'])->name('users.export');

        // Route::get('mosques', [App\Http\Controllers\ReportController::class, 'mosques'])->name('mosques');
        // Route::get('mosques/export', [App\Http\Controllers\ReportController::class, 'exportMosques'])->name('mosques.export');
        // Route::resource('mosques', MosqueController::class);

        Route::get('/mosques', [MosqueController::class, 'index'])->name('mosques.index');
        Route::get('/mosques/search', [MosqueController::class, 'search'])->name('mosques.search');
        Route::get('/regions', [MosqueController::class, 'getRegions']); // لجلب المناطق حسب الفرع
        Route::post('/mosques/store', [MosqueController::class, 'store'])->name('mosques.store');
        Route::post('/mosques/update/{mosque}', [MosqueController::class, 'update'])->name('mosques.update');
        Route::delete('/mosques/destroy/{mosque}', [MosqueController::class, 'destroy'])->name('mosques.destroy');
        Route::post('/mosques/delete-multiple', [MosqueController::class, 'destroyMultiple'])->name('mosques.deleteMultiple');
        Route::get('plans', [App\Http\Controllers\ReportController::class, 'plans'])->name('plans');
        Route::get('plans/export', [App\Http\Controllers\ReportController::class, 'exportPlans'])->name('plans.export');

        Route::get('centers', [App\Http\Controllers\ReportController::class, 'centers'])->name('centers');
        Route::get('centers/export', [App\Http\Controllers\ReportController::class, 'exportCenters'])->name('centers.export');

        Route::get('constants', [App\Http\Controllers\ReportController::class, 'constants'])->name('constants');
        Route::get('constants/export', [App\Http\Controllers\ReportController::class, 'exportConstants'])->name('constants.export');

        Route::get('dashboard', [App\Http\Controllers\ReportController::class, 'dashboard'])->name('dashboard');
    });

    // ==================== API-like Routes for AJAX ====================
    Route::prefix('api')->name('api.')->group(function () {
        // Dependent dropdowns
        Route::get('branches/{branch}/regions', [RegionController::class, 'getByBranch'])->name('branches.regions');
        Route::get('regions/{region}/mosques', [MosqueController::class, 'getByRegion'])->name('regions.mosques');
        Route::get('mosques/{mosque}/centers', [CenterController::class, 'getByMosque'])->name('mosques.centers');

        // Constants by type
        Route::get('constants/type/{typeId}', [ConstantController::class, 'getByType'])->name('constants.by-type');
        Route::get('constants/type/{typeId}/active', [ConstantController::class, 'getActiveByType'])->name('constants.by-type.active');

        // Search
        Route::get('search/users', [UserController::class, 'search'])->name('search.users');
        Route::get('search/mosques', [MosqueController::class, 'search'])->name('search.mosques');
        Route::get('search/plans', [PlanController::class, 'search'])->name('search.plans');
    });
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // System Settings
    Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
    Route::post('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Backup
    Route::get('backup', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup');
    Route::post('backup/create', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backup.create');
    Route::get('backup/download/{fileName}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backup.download');
    Route::delete('backup/delete/{fileName}', [App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('backup.delete');

    // Activity Log
    Route::get('activity-log', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-log');
    Route::get('activity-log/{log}', [App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-log.show');
    Route::delete('activity-log', [App\Http\Controllers\Admin\ActivityLogController::class, 'clear'])->name('activity-log.clear');

    // System Info
    Route::get('system-info', [App\Http\Controllers\Admin\SystemInfoController::class, 'index'])->name('system-info');
});

// Fallback Route
Route::fallback(function () {
    return view('errors.404');
});
