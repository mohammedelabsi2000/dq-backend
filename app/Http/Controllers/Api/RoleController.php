<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Traits\ApiResponser;
use App\Models\Role;
use App\Traits\QueryFilterTrait;

class RoleController extends Controller
{
    use ApiResponser, QueryFilterTrait;
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->authorize('viewAny', Role::class);
        // $roles = Role::with('roleAbilities')->get();
        $query = Role::query();
        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn'   => 'created_at',
            'limit'         => '*',
        ]);
        $query = $q['query'];
        $total = $q['count'];
        $roles = $query->with('roleAbilities')->get();
        return $this->apiResponse([
            'total' => $total,
            'skip'  => $q['skip'],
            'limit' => $q['limit'],
            'data'  => RoleResource::collection($roles),
        ], 'success', 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRoleRequest $request)
    {
        $role = Role::createWithAbilities($request->validated());

        return $this->success(new RoleResource($role->load('roleAbilities')), 'تم إنشاء الدور بنجاح', 201);

        // return response()->json([
        //     'message' => 'Role created successfully',
        //     'data'    => new RoleResource($role->load('roleAbilities')),
        // ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Role $role
     * @return RoleResource
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);
        return $this->success(new RoleResource($role->load('roleAbilities')), 'بيانات الدور');
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
        $role->updateWithAbilities($request->validated());
        return $this->success(new RoleResource($role->load('roleAbilities')), 'تم تحديث الدور بنجاح');


        // return response()->json([
        //     'message' => 'Role updated successfully',
        //     'data'    => new RoleResource($role->load('roleAbilities')),
        // ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);
        $role->deleteRole();
        return $this->success(null, 'تم حذف الدور بنجاح');
        // return $this->successMessage('تم حذف الدور بنجاح');

        // return response()->json([
        //     'message' => 'Role deleted successfully',
        // ]);
    }

    public function abilities()
    {
        // return response()->json([
        //     'data' => config('abilities'),
        // ]);

        $abilties = config('abilities');

        $target_arr = [];

        foreach ($abilties as $key => $value) {
            $target_arr[] = [
                'label' => $key,
                'items' => $value
            ];
        }

        return response()->json([
            'abilities' => $target_arr,
        ]);
    }
}
