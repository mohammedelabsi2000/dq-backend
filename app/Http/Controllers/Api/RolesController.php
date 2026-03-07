<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleAbility;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::paginate();
        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'abilities' => 'required|array',
        //     'give_all' => 'nullable|boolean'
        // ]);

        // $role = Role::createWithAbilities($request);

        // // $role = Role::create([
        // //     'name' => $request->post('name'),
        // // ]);

        // // foreach ($request->post('abilities') as $ability) {
        // //     RoleAbility::create([
        // //         'role_id' => $role->id,
        // //         'ability_id' => $ability,
        // //         'type' => 'allow',
        // //     ]);
        // // }

        // return response()->json([
        //     'message' => 'Role created successfully',
        //     'role' => $role
        // ], 201);

        $request->validate([
            'name'        => 'required|string|unique:roles,name',
            'give_all'    => 'boolean',
            'abilities'   => 'array',
            'abilities.*' => 'string|in:' . implode(',', array_keys(config('abilities'))),
        ]);

        $role = Role::createWithAbilities(
            $request->name,
            $this->resolveAbilities($request)
        );

        return response()->json($role->load('roleAbilities'), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return response()->json($role->load('roleAbilities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'abilities' => 'required|array',
        // ]);

        // $role->updateWithAbilities($request);

        // // $role = Role::create([
        // //     'name' => $request->post('name'),
        // // ]);

        // // foreach ($request->post('abilities') as $ability) {
        // //     RoleAbility::create([
        // //         'role_id' => $role->id,
        // //         'ability_id' => $ability,
        // //         'type' => 'allow',
        // //     ]);
        // // }

        // return response()->json('success update');

        $request->validate([
            'name'        => 'required|string|unique:roles,name,' . $role->id,
            'give_all'    => 'boolean',
            'abilities'   => 'array',
            'abilities.*' => 'string|in:' . implode(',', array_keys(config('abilities'))),
        ]);

        $role->updateWithAbilities(
            $request->name,
            $this->resolveAbilities($request)
        );

        return response()->json($role->load('roleAbilities'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        // Role::destroy($id);
        // return response()->json('success delete');

        $role->delete();

        return response()->json(null, 204);
    }

    private function resolveAbilities(Request $request): array
    {
        if ($request->boolean('give_all')) {
            return array_fill_keys(array_keys(config('abilities')), 'allow');
        }

        // المتوقع: abilities = ['branches.view', 'users.create']
        return array_fill_keys($request->abilities ?? [], 'allow');
    }
}
