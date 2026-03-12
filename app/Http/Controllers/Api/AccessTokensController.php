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
                    'type' => $ability->type,
                ]);
            // $user->makeHidden('roles');
            return $this->success([
                'token' => $token->plainTextToken,
                'user' => $user,
                'abilities' => $abilities,
            ], "تم تسجيل الدخول بنجاح", 201);



            // return $this->success(['token' => $token->plainTextToken, 'user' => $user], "Ok", 201);


            // return Response::json([
            //     'code' => 1,
            //     'token' => $token->plainTextToken,
            //     'user' => $user
            // ], 201);
        }

        return $this->error("بيانات الدخول غير صحيحة", 401, null);

        // return Response::json([
        //     'code' => 0,
        //     'message' => 'Invalid credentials'
        // ], 401);
    }

    // To delete token
    public function destroy(Request $request, $token = null)
    {
        // $user = Auth::guard('sanctum')->user();
        $user = $request->user();
        if (null === $token) {
            $user->currentAccessToken()->delete();
            return $this->success(null, "تم تسجيل الخروج بنجاح", 200);
        }

        $personalAccessToken = PersonalAccessToken::findToken($token);
        if ($user->id == $personalAccessToken->tokenable_id && get_class($user) == $personalAccessToken->tokenable_type) {
            $personalAccessToken->delete();
            return $this->success($user, "تم تسجيل الخروج بنجاح", 200);
        }

        return $this->error("غير مصرح!", 401, null);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        #Match The Old Password
        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return $this->error("كلمة المرور القديمة غير صحيحة!", 404, null);
            // return $this->apiResponse("Old Password Doesn't match!", 404);
        }

        $authModel = get_class(auth()->user());
        $authClass = class_basename($authModel);
        if ($authClass == 'User') {
            User::whereId(auth()->user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);

            return $this->success(null, "تم تغيير كلمة المرور بنجاح!", 200);
        }
    }
}
