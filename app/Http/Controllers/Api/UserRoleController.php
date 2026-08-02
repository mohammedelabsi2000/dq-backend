<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRole\AssignRoleRequest;
use App\Http\Requests\UserRole\AssignScopeRequest;
use App\Models\User;
use App\Services\UserRoleService;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    private const SUPER_ADMIN_ROLE = 'المسؤول التقني الأعلى';

    public function __construct(protected UserRoleService $userRoleService) {}

    /*
    |--------------------------------------------------------------------------
    | عرض أدوار المستخدم وصلاحياته
    |--------------------------------------------------------------------------
    */

    public function index(User $user)
    {
        if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $roleNames = $user->getRoleNames();
        $permissionNames = $user->getAllPermissions()->pluck('name');

        if (!auth()->user()->hasRole(self::SUPER_ADMIN_ROLE)) {
            $roleNames = $roleNames->reject(fn($name) => $name === self::SUPER_ADMIN_ROLE)->values();
        }

        return $this->success([
            'roles'       => $roleNames,
            'permissions' => $permissionNames,
            'scopes'      => $user->scopes()->whereNull('to_date')->get(),
        ], 'الأدوار المخصصة للمستخدم');
    }

    /*
    |--------------------------------------------------------------------------
    | تنسيب الأدوار
    |--------------------------------------------------------------------------
    */

    // public function assignRoles(AssignRoleRequest $request, User $user)
    // {
    //     $roles = Role::whereIn('id', $request->role_ids)
    //         ->where('guard_name', 'sanctum')
    //         ->get();

    //     $user->syncRoles($roles);

    //     if ($request->has('scopes')) {
    //         $user->syncScopes($request->scopes);
    //     }

    //     return $this->success(
    //         $user->getRoleNames(),
    //         'تم تنسيب الأدوار بنجاح',
    //         201
    //     );
    // }

    public function assignRoles(AssignRoleRequest $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return $this->error('لا يمكنك تعديل صلاحياتك الخاصة', 422);
        }

        if (!auth()->user()->hasRole(self::SUPER_ADMIN_ROLE)) {
            if ($user->hasRole(self::SUPER_ADMIN_ROLE)) {
                return $this->error('لا يمكنك تعديل أدوار هذا المستخدم', 403);
            }

            $superAdminRoleId = Role::where('name', self::SUPER_ADMIN_ROLE)->where('guard_name', 'sanctum')->value('id');
            if ($superAdminRoleId && in_array($superAdminRoleId, $request->role_ids)) {
                return $this->error('لا يمكنك إسناد دور ' . self::SUPER_ADMIN_ROLE, 403);
            }
        }

        if (!$this->canAssignRoles(auth()->user(), $request->role_ids)) {
            return $this->error('لا يمكنك إسناد دور يحتوي صلاحيات لا تملكها أنت نفسك', 403);
        }

        $this->userRoleService->assignRolesWithScopes(
            $user,
            $request->role_ids,
            $request->scopes ?? []
        );

        return $this->success(
            $user->getRoleNames(),
            'تم تنسيب الأدوار بنجاح',
            201
        );
    }

    /**
     * المدير العام يمكنه إسناد أي دور؛ غيره لا يمكنه إسناد دور يمنح صلاحيات لا يملكها هو نفسه،
     * منعاً لتصعيد الصلاحيات عبر إسناد دور أعلى من صلاحياته لحساب آخر.
     */
    private function canAssignRoles(User $actor, array $roleIds): bool
    {
        if ($actor->isGlobalAdmin()) {
            return true;
        }

        $rolePermissionIds = Role::whereIn('id', $roleIds)
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('id')
            ->unique();

        $actorPermissionIds = $actor->getAllPermissions()->pluck('id');

        return $rolePermissionIds->diff($actorPermissionIds)->isEmpty();
    }

    /*
    |--------------------------------------------------------------------------
    | إدارة الـ Scopes
    |--------------------------------------------------------------------------
    */

    public function assignScopes(AssignScopeRequest $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return $this->error('لا يمكنك تعديل صلاحياتك الخاصة', 403);
        }

        if ($user->hasRole(self::SUPER_ADMIN_ROLE) && !auth()->user()->hasRole(self::SUPER_ADMIN_ROLE)) {
            return $this->error('لا يمكنك تعديل نطاقات هذا المستخدم', 403);
        }

        $scopes = $request->scopes; // [['type' => 'branch', 'id' => 5], ...]

        $user->syncScopes($scopes);

        return $this->success(
            $user->scopes,
            'تم تنسيب النطاقات بنجاح',
            201
        );
    }

    public function removeScopes(User $user)
    {
        if (auth()->id() === $user->id) {
            return $this->error('لا يمكنك تعديل صلاحياتك الخاصة', 403);
        }

        if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        if ($user->hasRole(self::SUPER_ADMIN_ROLE) && !auth()->user()->hasRole(self::SUPER_ADMIN_ROLE)) {
            return $this->error('لا يمكنك تعديل نطاقات هذا المستخدم', 403);
        }

        $user->clearScopes();

        return $this->success(null, 'تم حذف جميع نطاقات المستخدم بنجاح');
    }

    /*
    |--------------------------------------------------------------------------
    | حذف الأدوار
    |--------------------------------------------------------------------------
    */

    public function removeRoles(User $user)
    {
        if (auth()->id() === $user->id) {
            return $this->error('لا يمكنك تعديل صلاحياتك الخاصة', 403);
        }

        if (!auth()->user()->hasPermissionTo('users.roles.update', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        if ($user->hasRole(self::SUPER_ADMIN_ROLE) && !auth()->user()->hasRole(self::SUPER_ADMIN_ROLE)) {
            return $this->error('لا يمكنك تعديل أدوار هذا المستخدم', 403);
        }

        $user->syncRoles([]);

        return $this->success(null, 'تم حذف جميع أدوار المستخدم بنجاح');
    }
}
