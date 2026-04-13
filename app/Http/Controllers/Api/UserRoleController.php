<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRole\AssignRoleRequest;
use App\Http\Requests\UserRole\AssignScopeRequest;
use App\Http\Resources\UserRoleResource;
use App\Http\Traits\ApiResponser;
use App\Models\User;
use App\Models\UserScope;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    use ApiResponser;

    /*
    |--------------------------------------------------------------------------
    | عرض أدوار المستخدم وصلاحياته
    |--------------------------------------------------------------------------
    */

    public function index(User $user): JsonResponse
    {
        // $this->authorize('viewAny', Role::class);
        // $this->authorize('viewAny', $user);
        // $this->hasPermission('users.roles.update');
        if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        return $this->success([
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'scopes'      => $user->scopes,
        ], 'الأدوار المخصصة للمستخدم');
    }

    /*
    |--------------------------------------------------------------------------
    | تنسيب الأدوار
    |--------------------------------------------------------------------------
    */

    public function assignRoles(AssignRoleRequest $request, User $user): JsonResponse
    {
        // $this->authorize('create', $user);
        // $this->hasPermission('users.roles.update');
        // if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
        //     return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        // }

        $roles = Role::whereIn('id', $request->role_ids)
            ->where('guard_name', 'sanctum')
            ->get();

        $user->syncRoles($roles);

        if ($request->has('scopes')) {
            $user->syncScopes($request->scopes);
        }

        return $this->success(
            $user->getRoleNames(),
            'تم تنسيب الأدوار بنجاح',
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | إدارة الـ Scopes
    |--------------------------------------------------------------------------
    */

    public function assignScopes(AssignScopeRequest $request, User $user): JsonResponse
    {
        // $this->authorize('create', $user);
        // $this->hasPermission('users.roles.update');
        // if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
        //     return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        // }

        $scopes = $request->scopes; // [['type' => 'branch', 'id' => 5], ...]

        $user->syncScopes($scopes);

        return $this->success(
            $user->scopes,
            'تم تنسيب النطاقات بنجاح',
            201
        );
    }

    public function removeScopes(User $user): JsonResponse
    {
        // $this->authorize('delete', $user);
        // $this->hasPermission('users.roles.update');
        if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $user->clearScopes();

        return $this->success(null, 'تم حذف جميع نطاقات المستخدم بنجاح');
    }

    /*
    |--------------------------------------------------------------------------
    | حذف الأدوار
    |--------------------------------------------------------------------------
    */

    public function removeRoles(User $user): JsonResponse
    {
        // $this->authorize('delete', $user);
        // $this->hasPermission('users.roles.update');
        if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $user->syncRoles([]);

        return $this->success(null, 'تم حذف جميع أدوار المستخدم بنجاح');
    }
}
