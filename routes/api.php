<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AcademicQualificationController,
    AttendanceController,
    BranchController,
    CenterController,
    ConstantController,
    ConstantTypeController,
    CourseController,
    GradeController,
    HalaqaController,
    HalaqaStudentController,
    IdQueryController,
    ImageController,
    MosqueController,
    PersonalCourseController,
    PlanAssignmentController,
    PlanController,
    PlanStudentController,
    RegionController,
    StudentController,
    TrackController,
    StatisticsController
};

// Load all API route files from the api directory
foreach (glob(__DIR__ . '/api/*.php') as $file) {
    require $file;
}

Route::middleware('auth:sanctum')->group(function () {
    // Constants management
    Route::apiResource('constant_types', ConstantTypeController::class);
    Route::apiResource('constants', ConstantController::class);

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
        Route::get('{student}/images', [ImageController::class, 'studentImages']);
    });

    // Halaqa students assignment
    Route::apiResource('halaqa-students', HalaqaStudentController::class);

    // Academic qualifications management
    Route::apiResource('academic-qualifications', AcademicQualificationController::class);
    Route::prefix('academic-qualifications')->group(function () {
        Route::get('{person_type}/{person_id}', [AcademicQualificationController::class, 'getPersonQualifications']);
    });

    // Personal courses management
    Route::apiResource('personal-courses', PersonalCourseController::class);
    Route::prefix('personal-courses')->group(function () {
        Route::get('{person_type}/{person_id}', [PersonalCourseController::class, 'getPersonCourses']);
    });

    // Images management (for students, users, qualifications, etc.)
    Route::apiResource('images', ImageController::class)
        ->only(['store', 'destroy', 'index', 'show']);

    // ID Query endpoint
    Route::post('/id-query', [IdQueryController::class, 'sendRequest']);
    Route::get('/statistics', [StatisticsController::class, 'index']);
});

// Route::post('register', [AuthController::class, 'register']);
// Route::post('login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('logout', [AuthController::class, 'logout']);
//     Route::get('profile', [AuthController::class, 'profile']);
// });
// Route::apiResource('plans', PlanController::class);
// Route::apiResource('attendances', AttendanceController::class);

// Route::prefix('attendances')->group(function () {
//     Route::get('/', [AttendanceController::class, 'index']);
//     Route::post('/', [AttendanceController::class, 'store']);
//     Route::put('/{attendance}', [AttendanceController::class, 'show']);
//     Route::put('/{attendance}', [AttendanceController::class, 'update']);
//     Route::delete('/{attendance}', [AttendanceController::class, 'destroy']);
// });

/*
|--------------------------------------------------------------------------
| Plans & tracks & courses
|--------------------------------------------------------------------------
*/
Route::apiResource('plans', PlanController::class);
Route::apiResource('tracks', TrackController::class);
Route::apiResource('courses', CourseController::class);

/*
| Plan Setup (التركيب)
*/
Route::prefix('plans/{plan}')->group(function () {

    // setup data (tracks + courses + selected)
    Route::get('setup', [PlanController::class, 'setup']);

    // save setup
    Route::post('setup', [PlanController::class, 'saveSetup']);

    // show one setup
    Route::get('setup-show', [PlanController::class, 'showSetup']);

    // delete setup
    Route::delete('setup', [PlanController::class, 'deleteSetup']);

    // عرض الطلاب مع الفلاتر
    Route::get('students', [PlanAssignmentController::class, 'students']);

    // إسناد الطلاب
    Route::post('assign', [PlanAssignmentController::class, 'assign']);
    /*
    | Soft Delete helpers
    */
    Route::get('trashed', [PlanController::class, 'trashed']);
    Route::post('restore', [PlanController::class, 'restore']);
    Route::delete('force', [PlanController::class, 'forceDelete']);
});


Route::prefix('plans')->group(function () {
    // GET all students assigned to a plan
    Route::get('{plan_id}/students', [PlanStudentController::class, 'index']);

    // POST: assign student to plan
    Route::post('{plan_id}/students', [PlanStudentController::class, 'assignStudent']);
});
/*
| Show all setups
*/
Route::get('plans-setup', [PlanController::class, 'setupIndex']);

Route::apiResource('grades', GradeController::class);
