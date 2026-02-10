<?php

use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\MosqueController;
use App\Http\Controllers\Api\RegionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ConstantTypeController;
use App\Http\Controllers\Api\ConstantController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PlanLevelController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('constant_types', ConstantTypeController::class);
Route::apiResource('constants', ConstantController::class);

Route::apiResource('regions', RegionController::class);
Route::apiResource('branches', BranchController::class);
Route::apiResource('mosques', MosqueController::class);
Route::apiResource('plans', PlanController::class);
// Route::delete('plans/{id}/force', [PlanController::class, 'forceDelete']);
// Route::get('plans/trashed', [PlanController::class, 'trashed']);
// Route::post('plans/{id}/restore', [PlanController::class, 'restore']);


// soft delete helpers
Route::prefix('plans/{plan}')->group(function () {
    Route::get('trashed', [PlanController::class, 'trashed']);
    Route::post('restore', [PlanController::class, 'restore']);
    Route::delete('force', [PlanController::class, 'forceDelete']);
});

Route::apiResource('plan-levels', PlanLevelController::class);

// Soft delete helpers
Route::prefix('plan-levels/{plan_level}')->group(function () {
    // Route::post('trashed', [PlanLevelController::class, 'trashed']);
    Route::post('restore', [PlanLevelController::class, 'restore']);
    Route::delete('force', [PlanLevelController::class, 'forceDelete']);
});