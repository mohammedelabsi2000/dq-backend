<?php

use App\Http\Controllers\Api\AccessTokensController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
    Route::post(
        'change-password',
        [AccessTokensController::class, 'updatePassword']
    );
    Route::delete(
        'auth/access-tokens/{token?}',
        [AccessTokensController::class, 'destroy']
    );
});