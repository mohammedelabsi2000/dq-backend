<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokensController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'device_name' => 'string|max:255'
        ]);

        $login = $request->login;

        $user = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $login)->first()
            : User::where('identity', $login)->first();

        // $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $device_name = $request->post('device_name', $request->userAgent());
            $token = $user->createToken($device_name);

            $user->loadMissing('roles.roleAbilities');

            $abilities = $user->roles
                ->flatMap(fn($role) => $role->roleAbilities)
                ->unique('ability')
                ->values()
                ->map(fn($ability) => [
                    'ability' => $ability->ability,
                    'type'    => $ability->type,
                ]);
            // $user->makeHidden('roles');
            return $this->success([
                'token'     => $token->plainTextToken,
                'user'      => $user,
                'abilities' => $abilities,
            ], "Ok", 201);



            // return $this->success(['token' => $token->plainTextToken, 'user' => $user], "Ok", 201);


            // return Response::json([
            //     'code' => 1,
            //     'token' => $token->plainTextToken,
            //     'user' => $user
            // ], 201);
        }

        return $this->error("Credentials are incorrect", 401, null);

        // return Response::json([
        //     'code' => 0,
        //     'message' => 'Invalid credentials'
        // ], 401);
    }

    // To delete token
    public function destroy($token = null)
    {
        $user = Auth::guard('sanctum')->user();

        if (null === $token) {
            $user->currentAccessToken()->delete();
            return $this->success(null, "Logout successfully!", 200);
        }

        $personalAccessToken = PersonalAccessToken::findToken($token);
        if ($user->id == $personalAccessToken->tokenable_id && get_class($user) == $personalAccessToken->tokenable_type) {
            $personalAccessToken->delete();
            return $this->success($user, "Logout successfully!", 200);
        }

        return $this->error("Unauthorized!", 401, null);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        #Match The Old Password
        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return $this->error("Old Password Doesn't match!", 404, null);
            // return $this->apiResponse("Old Password Doesn't match!", 404);
            return Response::json([
                'message' => "Old Password Doesn't match!",
            ], 404);
        }

        $authModel = get_class(auth()->user());
        $authClass = class_basename($authModel);
        if ($authClass == 'User') {
            User::whereId(auth()->user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);

            return $this->success(null, "Password changed successfully!", 200);
            // return Response::json([
            //     'message' => "Password changed successfully!",
            // ], 200);
        }
    }
}
