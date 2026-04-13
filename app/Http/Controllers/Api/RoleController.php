<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\PermissionGroupResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Traits\ApiResponser;
use App\Traits\QueryFilterTrait;
use Maatwebsite\Excel\Concerns\ToArray;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use ApiResponser, QueryFilterTrait;

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // $this->authorize('viewAny', Role::class);
        // $this->hasPermission('roles.show');
        if (!auth()->user()->hasPermissionTo('roles.show', 'sanctum')) {
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
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

        return $this->apiResponse([
            'total' => $total,
            'skip'  => $q['skip'],
            'limit' => $q['limit'],
            'data'  => RoleResource::collection($roles),
        ], 'success', 200);
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

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'sanctum',
        ]);

        if ($validated['give_all'] ?? false) {
            $role->syncPermissions(Permission::all());
        } elseif (!empty($validated['abilities'])) {
            $permissions = Permission::whereIn('id', $validated['abilities'])->get();
            $role->syncPermissions($permissions);
        }

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
        // $this->authorize('view', $role);
        // $this->hasPermission('roles.show');
        if (!auth()->user()->hasPermissionTo('roles.show', 'sanctum')) {
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
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

        $role->update(['name' => $validated['name']]);

        // Update permissions
        if ($validated['give_all'] ?? false) {
            $permissions = Permission::all();
            $role->syncPermissions($permissions);
        } elseif (!empty($validated['abilities'])) {
            $permissions = Permission::whereIn('id', $validated['abilities'])->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return $this->success(new RoleResource($role->load('permissions')), 'تم تحديث الدور بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        // $this->authorize('delete', $role);
        // $this->hasPermission('roles.delete');
        if (!auth()->user()->hasPermissionTo('roles.delete', 'sanctum')) {
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
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
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $permissions = Permission::where('guard_name', 'sanctum')
            ->get();
        // ->groupBy(fn($p) => explode('.', $p->name)[0])
        // ->map(fn($items, $key) => [$key => $items])
        // ->values();


        return $this->success(PermissionResource::collection($permissions), 'الصلاحيات', 200);
    }
}
