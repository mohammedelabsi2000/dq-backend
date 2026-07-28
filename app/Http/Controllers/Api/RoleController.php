<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('roles.show', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $query = Role::query();
        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn'   => 'created_at',
            'limit'         => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];
        $roles = $query->with('permissions')->get();
        return $this->successWithPagination(
            RoleResource::collection($roles),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        $permissions = ($validated['give_all'] ?? false)
            ? Permission::all()
            : Permission::whereIn('id', $validated['abilities'] ?? [])->get();

        if (!$this->canGrantPermissions(auth()->user(), $permissions)) {
            return $this->error('لا يمكنك منح صلاحيات لا تملكها أنت نفسك', 403);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'sanctum',
        ]);

        $role->syncPermissions($permissions);

        return $this->success(new RoleResource($role->load('permissions')), 'تم إنشاء الدور بنجاح', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Role $role)
    {
        if (!auth()->user()->hasPermissionTo('roles.show', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        return $this->success(new RoleResource($role->load('permissions')), 'بيانات الدور');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        $permissions = ($validated['give_all'] ?? false)
            ? Permission::all()
            : Permission::whereIn('id', $validated['abilities'] ?? [])->get();

        // الصلاحيات التي سيفقدها الدور والتي سيكتسبها، كلاهما يجب أن يملكهما الفاعل نفسه
        $changedPermissions = $role->permissions
            ->pluck('id')
            ->merge($permissions->pluck('id'))
            ->unique();

        if (!$this->canGrantPermissions(auth()->user(), Permission::whereIn('id', $changedPermissions)->get())) {
            return $this->error('لا يمكنك تعديل صلاحيات لا تملكها أنت نفسك', 403);
        }

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($permissions);

        return $this->success(new RoleResource($role->load('permissions')), 'تم تحديث الدور بنجاح');
    }

    /**
     * المدير العام يستطيع منح أي صلاحية؛ غيره لا يمكنه منح/تعديل صلاحية لا يملكها هو نفسه،
     * وإلا أمكنه تصعيد صلاحياته عبر تعديل دور موجود أو إنشاء دور جديد بكل الصلاحيات.
     */
    private function canGrantPermissions(User $actor, \Illuminate\Support\Collection $permissions): bool
    {
        if ($actor->isGlobalAdmin()) {
            return true;
        }

        $actorPermissionIds = $actor->getAllPermissions()->pluck('id');

        return $permissions->pluck('id')->diff($actorPermissionIds)->isEmpty();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        if (!auth()->user()->hasPermissionTo('roles.delete', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $role->delete();

        return $this->success(null, 'تم حذف الدور بنجاح');
    }

    /**
     * Get all available abilities grouped by category.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function abilities()
    {
        if (!auth()->user()->hasPermissionTo('roles.show', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $permissions = Permission::where('guard_name', 'sanctum')
            ->get();

        return $this->success(PermissionResource::collection($permissions), 'الصلاحيات', 200);
    }
}
