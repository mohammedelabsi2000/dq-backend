<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserRoleController;

Route::middleware('auth:sanctum')->group(function () {
    // Available abilities
    Route::get('abilities', [RoleController::class, 'abilities']);

    // Roles CRUD
    Route::apiResource('roles', RoleController::class);

    // ->middleware([
    //     'index' => 'permission:roles.show',
    //     'show' => 'permission:roles.show',
    //     'store' => 'permission:roles.create',
    //     'update' => 'permission:roles.update',
    //     'destroy' => 'permission:roles.delete',
    // ])

    Route::prefix('users/{user}')->group(function () {

        // Display user roles and permissions
        Route::get('roles', [UserRoleController::class, 'index']);

        // Assign roles
        Route::post('roles', [UserRoleController::class, 'assignRoles']);

        // Delete all roles
        Route::delete('roles', [UserRoleController::class, 'removeRoles'])->middleware('permission:roles.delete');

        // Assign scopes
        Route::post('scopes', [UserRoleController::class, 'assignScopes'])->middleware('permission:roles.update');

        // Delete all scopes
        Route::delete('scopes', [UserRoleController::class, 'removeScopes'])->middleware('permission:roles.delete');
    });
});
