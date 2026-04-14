<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokensController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'device_name' => 'string|max:255'
        ], [
            'login.required' => 'حقل البريد الإلكتروني أو الهوية مطلوب',
            'password.required' => 'حقل كلمة المرور مطلوب',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 6 أحرف',
        ]);

        $login = $request->login;

        $user = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $login)->first()
            : User::where('identity', $login)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $device_name = $request->post('device_name', $request->userAgent());
            $token = $user->createToken($device_name);

            return $this->success([
                'token'       => $token->plainTextToken,
                'user'        => $user,
                'roles'       => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                'scopes'      => $user->scopes,
            ], "تم تسجيل الدخول بنجاح", 201);
        }

        return $this->error("بيانات الدخول غير صحيحة", 401, null);
    }

    // To delete token
    public function destroy(Request $request, $token = null)
    {
        $user = Auth::guard('sanctum')->user();
        if (null === $token) {
            $request->user()->currentAccessToken()->delete();
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
        ], [
            'old_password.required' => 'حقل كلمة المرور القديمة مطلوب',
            'new_password.required' => 'حقل كلمة المرور الجديدة مطلوب',
            'new_password.confirmed' => 'تأكيد كلمة المرور الجديدة غير متطابق',
        ]);

        #Match The Old Password
        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return $this->error("كلمة المرور القديمة غير صحيحة!", 404, null);
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
