<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRole\AssignRoleRequest;
use App\Http\Requests\UserRole\RemoveRoleRequest;
use App\Http\Requests\UserRole\SyncRoleRequest;
use App\Http\Resources\UserRoleResource;
use App\Models\Branch;
use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function index(User $user)
    {
        $user->loadMissing('roles.roleAbilities');

        return UserRoleResource::collection($user->roles);
    }

    public function assign(AssignRoleRequest $request, User $user)
    {
        $data  = $request->validated();
        $role  = Role::findOrFail($data['role_id']);
        $scope = $this->resolveScope($data['scope_type'] ?? null, $data['scope_id'] ?? null);

        $user->assignRole($role, $scope);

        return response()->json([
            'message' => 'Role assigned successfully',
        ], 201);
    }

    public function sync(SyncRoleRequest $request, User $user)
    {
        $data = $request->validated();

        $oldRole  = Role::findOrFail($data['old_role_id']);
        $oldScope = $this->resolveScope($data['old_scope_type'] ?? null, $data['old_scope_id'] ?? null);

        $newRole  = Role::findOrFail($data['new_role_id']);
        $newScope = $this->resolveScope($data['new_scope_type'] ?? null, $data['new_scope_id'] ?? null);

        $user->syncRole($oldRole, $oldScope, $newRole, $newScope);

        return response()->json([
            'message' => 'Role updated successfully',
        ]);
    }

    public function remove(RemoveRoleRequest $request, User $user)
    {
        $data  = $request->validated();
        $role  = Role::findOrFail($data['role_id']);
        $scope = $this->resolveScope($data['scope_type'] ?? null, $data['scope_id'] ?? null);

        $user->removeRole($role, $scope);

        return response()->json([
            'message' => 'Role removed successfully',
        ]);
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
