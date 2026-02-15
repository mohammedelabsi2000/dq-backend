<?php

use App\Http\Controllers\Api\AcademicQualificationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CenterController;
use App\Http\Controllers\Api\MosqueController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\GradeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ConstantTypeController;
use App\Http\Controllers\Api\ConstantController;
use App\Http\Controllers\API\PersonalCourseController;
use App\Http\Controllers\Api\HalaqaController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PlanLevelController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/
Route::apiResource('constant_types', ConstantTypeController::class);
Route::apiResource('constants', ConstantController::class);

/*
|--------------------------------------------------------------------------
| Locations
|--------------------------------------------------------------------------
*/
Route::apiResource('regions', RegionController::class);
Route::apiResource('branches', BranchController::class);
Route::apiResource('mosques', MosqueController::class);
Route::apiResource('centers', CenterController::class);

/*
|--------------------------------------------------------------------------
| Plans
|--------------------------------------------------------------------------
*/
Route::apiResource('plans', PlanController::class);

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

    /*
    | Soft Delete helpers
    */
    Route::get('trashed', [PlanController::class, 'trashed']);
    Route::post('restore', [PlanController::class, 'restore']);
    Route::delete('force', [PlanController::class, 'forceDelete']);
});

/*
| Show all setups
*/
Route::get('plans-setup', [PlanController::class, 'setupIndex']);

/*
|--------------------------------------------------------------------------
| Plan Levels
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Other Resources
|--------------------------------------------------------------------------
*/
Route::apiResource('grades', GradeController::class);
Route::apiResource('halaqas', HalaqaController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('academic-qualifications', AcademicQualificationController::class);
Route::apiResource('personal-courses', PersonalCourseController::class);
