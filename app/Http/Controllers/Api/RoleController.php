<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $roles = Role::with('roleAbilities')->get();

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRoleRequest $request)
    {
        $role = Role::createWithAbilities($request->validated());

        return response()->json([
            'message' => 'Role created successfully',
            'data'    => new RoleResource($role->load('roleAbilities')),
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Role $role
     * @return RoleResource
     */
    public function show(Role $role)
    {
        return new RoleResource($role->load('roleAbilities'));
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

        return response()->json([
            'message' => 'Role updated successfully',
            'data'    => new RoleResource($role->load('roleAbilities')),
        ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        $role->deleteRole();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    public function abilities()
    {
        // return response()->json([
        //     'data' => config('abilities'),
        // ]);

        return response()->json([
            'abilities' => config('abilities'),
        ]);
    }
}
