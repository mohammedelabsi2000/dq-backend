<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRole\AssignRoleRequest;
use App\Http\Requests\UserRole\RemoveRoleRequest;
use App\Http\Requests\UserRole\SyncRoleRequest;
use App\Http\Resources\UserRoleResource;
use App\Http\Traits\ApiResponser;
use App\Models\Branch;
use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Traits\QueryFilterTrait;

class UserRoleController extends Controller
{
    use ApiResponser, QueryFilterTrait;
    public function index(User $user)
    {
        // $this->authorize('viewRoles', $user);
        return $this->success(UserRoleResource::collection($user->roles), 'الادوار المخصصة للمستخدم');
        // $user->loadMissing('roles.roleAbilities');

        // return UserRoleResource::collection($user->roles);
    }

    public function assign(AssignRoleRequest $request, User $user)
    {
        $data  = $request->validated();
        $role  = Role::findOrFail($data['role_id']);
        $scope = $this->resolveScope($data['scope_type'] ?? null, $data['scope_id'] ?? null);

        $scope_type  = $data['scope_type'] ?? null;
        $scope_id    = $data['scope_id'] ?? null;
        $result = array_map(function ($role) use ($user, $scope_type, $scope_id) {
            return [
                'role_id' => $role,
                'authorizable_id' => $user->id,
                'authorizable_type' => $user->getMorphClass(),
                'scope_id' => $scope_id,
                'scope_type' => $scope_type,
            ];
        }, $data['role_id']);

        UserRole::upsert($result, ['role_id', 'authorizable_id', 'authorizable_type'], ['scope_id' => $scope_id, 'scope_type' => $scope_type]);

        // $user->assignRole($role, $scope);
        return $this->success(null, 'تم إسناد الدور للمستخدم بنجاح', 201);
    }

    public function sync(SyncRoleRequest $request, User $user)
    {
        $data = $request->validated();

        $oldRole  = Role::findOrFail($data['old_role_id']);
        $oldScope = $this->resolveScope($data['old_scope_type'] ?? null, $data['old_scope_id'] ?? null);

        $newRole  = Role::findOrFail($data['new_role_id']);
        $newScope = $this->resolveScope($data['new_scope_type'] ?? null, $data['new_scope_id'] ?? null);

        $user->syncRole($oldRole, $oldScope, $newRole, $newScope);
        return $this->success(null, 'تم تحديث دور المستخدم بنجاح');
    }

    public function remove(RemoveRoleRequest $request, User $user)
    {
        $data  = $request->validated();
        $role  = Role::findOrFail($data['role_id']);
        $scope = $this->resolveScope($data['scope_type'] ?? null, $data['scope_id'] ?? null);

        $user->removeRole($role, $scope);
        return $this->success(null, 'تم إزالة الدور من المستخدم بنجاح');
    }

    private function resolveScope(?string $scopeType, ?int $scopeId)
    {
        if (!$scopeType || !$scopeId) {
            return null;
        }

        $map = [
            'branch' => Branch::class,
            'region' => Region::class,
            'center' => Center::class,
            'halaqa' => Halaqa::class,
        ];

        $modelClass = $map[$scopeType] ?? abort(422, 'Invalid scope type');

        return $modelClass::findOrFail($scopeId);
    }
}
