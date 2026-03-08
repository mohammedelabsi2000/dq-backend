<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Center;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserRolesController extends Controller
{
    public function index(User $user)
    {
        // Gate::authorize('users.view');

        // return response()->json($user->load('roles.roleAbilities'));
        $user->loadMissing('roles.roleAbilities');

        return $this->success(
            $user->roles->map(fn($role) => [
                'id'             => $role->id,
                'name'           => $role->name,
                'scope_type'     => $role->pivot->scope_type,
                'scope_id'       => $role->pivot->scope_id,
                'role_abilities' => $role->roleAbilities,
            ]),
            'أدوار المستخدم'
        );
    }

    public function store(Request $request, User $user)
    {
        // Gate::authorize('users.update');

        $request->validate([
            'role_id'    => 'required|exists:roles,id',
            'scope_type' => 'nullable|string|in:branch,region,center',
            'scope_id'   => 'nullable|required_with:scope_type|integer',
        ]);

        // التحقق أن الـ scope_id موجود فعلاً
        if ($request->filled('scope_type') && $request->filled('scope_id')) {
            $this->validateScope(
                $request->scope_type,
                $request->scope_id
            );
        }

        $scopeType = $this->resolveScopeType($request->scope_type);

        // تنسيب الـ role مع الـ scope
        $user->roles()->syncWithoutDetaching([
            $request->role_id => [
                'scope_id'   => $request->scope_id,
                'scope_type' => $scopeType,
            ],
        ]);

        $user->loadMissing('roles.roleAbilities');

        return $this->success(
            $user->roles->map(fn($role) => [
                'id'             => $role->id,
                'name'           => $role->name,
                'scope_type'     => $role->pivot->scope_type,
                'scope_id'       => $role->pivot->scope_id,
                'role_abilities' => $role->roleAbilities,
            ]),
            'تم تنسيب الدور للمستخدم بنجاح'
        );

        // $user->roles()->syncWithoutDetaching($request->role_id);

        // return response()->json($user->load('roles.roleAbilities'));
    }

    public function destroy(User $user, Role $role)
    {
        // Gate::authorize('users.update');


        $user->roles()->detach($role->id);

        return $this->success(
            null,
            'تم إزالة الدور من المستخدم بنجاح'
        );

        // $user->roles()->detach($role->id);

        // return response()->json($user->load('roles.roleAbilities'));
    }

    private function resolveScopeType(?string $type): ?string
    {
        return match ($type) {
            'branch' => Branch::class,
            'region' => Region::class,
            'center' => Center::class,
            default  => null,
        };
    }

    private function validateScope(string $type, int $id): void
    {
        $model = match ($type) {
            'branch' => Branch::find($id),
            'region' => Region::find($id),
            'center' => Center::find($id),
            default  => null,
        };

        if (!$model) {
            abort(422, "الـ {$type} رقم {$id} غير موجود");
        }
    }
}
