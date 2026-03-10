<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AcademicQualificationController;
use App\Http\Controllers\Api\AccessTokensController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CenterController;
use App\Http\Controllers\Api\MosqueController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ConstantTypeController;
use App\Http\Controllers\Api\ConstantController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\PersonalCourseController;
use App\Http\Controllers\Api\HalaqaController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HalaqaStudentController;
use App\Http\Controllers\Api\IdQueryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\PlanAssignmentController;
use App\Http\Controllers\Api\PlanStudentController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserRolesController;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return Auth::guard('sanctum')->user();
    // $user = $request->user();
    // $user->loadMissing('roles.roleAbilities');

    // return [
    //     'user'      => $user,
    //     'abilities' => $user->roles
    //         ->flatMap(fn($role) => $role->roleAbilities)
    //         ->unique('ability')
    //         ->values()
    //         ->map(fn($ability) => [
    //             'ability' => $ability->ability,
    //             'type'    => $ability->type,
    //         ]),
    // ];
});
/*******************************Version 1********************************************** */

Route::post('auth/access-tokens', [AccessTokensController::class, 'store'])
    ->middleware('guest:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('change-password', [AccessTokensController::class, 'updatePassword']);
    Route::delete('auth/access-tokens/{token?}', [AccessTokensController::class, 'destroy'])
        ->middleware('auth:sanctum');
    Route::apiResource('users', UserController::class);
    Route::apiResource('academic-qualifications', AcademicQualificationController::class);

    Route::apiResource('personal-courses', PersonalCourseController::class);

    // راوت احضار شهادات اليوزر person-courses
    Route::get('person-courses/{person_type}/{person_id}', [PersonalCourseController::class, 'getPersonCourses']);
    // راوت احضار شهادات اليوزر academic-qualifications
    Route::get(
        'academic-qualifications/{person_type}/{person_id}',
        [AcademicQualificationController::class, 'getPersonQualifications']
    );

    Route::apiResource('branches', BranchController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('mosques', MosqueController::class);
    Route::apiResource('centers', CenterController::class);
    Route::apiResource('halaqas', HalaqaController::class);

    Route::apiResource('constant_types', ConstantTypeController::class);
    Route::apiResource('constants', ConstantController::class);

    Route::apiResource('students', StudentController::class);
    Route::post('students/import', [StudentController::class, 'import']);

    Route::apiResource('images', ImageController::class)
        ->only(['store', 'destroy', 'index', 'show']);
    Route::get('users/{user}/images', [ImageController::class, 'userImages']);
    Route::get('students/{student}/images', [ImageController::class, 'studentImages']);

    // الصلاحيات المتاحة
    Route::get('abilities', [RoleController::class, 'abilities']);
    // Roles CRUD
    Route::apiResource('roles', RoleController::class);
    Route::post('/id-query', [IdQueryController::class, 'sendRequest']);

    // User Roles
    Route::prefix('users/{user}/roles')->group(function () {
        Route::get('/',    [UserRoleController::class, 'index']);
        Route::post('/',   [UserRoleController::class, 'assign']);
        Route::put('/',    [UserRoleController::class, 'sync']);
        Route::delete('/', [UserRoleController::class, 'remove']);
    });
});
/****************************************Version 1******************************************************* */

// Route::post('register', [AuthController::class, 'register']);
// Route::post('login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('logout', [AuthController::class, 'logout']);
//     Route::get('profile', [AuthController::class, 'profile']);
// });
Route::apiResource('plans', PlanController::class);
Route::apiResource('attendances', AttendanceController::class);

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

// Route::prefix('students')->group(function () {
//     Route::apiResource('', StudentController::class);
//     Route::post('import', [StudentController::class, 'import']);
// });




// Route::apiResource('roles', RolesController::class);
// راوت احضار شهادات اليوزر person-courses
Route::get('personal-courses/{person_type}/{person_id}', [PersonalCourseController::class, 'getPersonCourses']);
// راوت احضار شهادات اليوزر academic-qualifications
Route::get(
    'academic-qualifications/{person_type}/{person_id}',
    [AcademicQualificationController::class, 'getPersonQualifications']
);
// Route::apiResource('roles', RolesController::class);

// Route::get('users/{user}/roles', [UserRolesController::class, 'index']);
// Route::post('users/{user}/roles', [UserRolesController::class, 'store']);
// Route::delete('users/{user}/roles/{role}', [UserRolesController::class, 'destroy']);
// Route::post('/id-query', [IdQueryController::class, 'sendRequest']);



// اسناد الطلاب لحلقات
Route::prefix('halaqa-students')->group(function () {
    Route::get('/', [HalaqaStudentController::class, 'index']);
    Route::get('{id}', [HalaqaStudentController::class, 'show']);
    Route::post('/', [HalaqaStudentController::class, 'store']);
    Route::put('{id}', [HalaqaStudentController::class, 'update']);
    Route::delete('{id}', [HalaqaStudentController::class, 'destroy']);
});
