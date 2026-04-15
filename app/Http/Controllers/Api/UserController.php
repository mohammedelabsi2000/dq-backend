<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        // $query = User::query()->visibleTo(auth()->user());
        $query = User::query();
        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => ['full_name', 'identity'],
            'orderColumn' => 'created_at',
        ]);
        
        $users = $query->with(['mosque', 'mosque.region', 'mosque.region.branch', 'maritalStatus', 'prefix', 'roles'])->get();

        return $this->successWithPagination(
            UserResource::collection($users),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::where('identity', $request['identity'])
            ->first();

        if ($user) {
            return $this->error(
                'المستخدم موجود مسبقاً',
                409
            );
        } else {
            $data = $request->validated();
            $data['password'] = Hash::make($request['password']);

            $user = User::withTrashed()->updateOrCreate([
                'identity' => $data['identity']
            ], $data);

            return $this->success(
                new UserResource($user),
                'تم إنشاء المستخدم بنجاح',
                201
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user = $user->load(['mosque', 'maritalStatus', 'prefix', 'roles.roleAbilities']);
        return $this->success(
            new UserResource($user),
            'بيانات المستخدم',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $data = $request->validated();
        // تشفير الباسوورد لو تم تغييره
        if ($request->input('password')) {
            $data['password'] = Hash::make($request['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $this->success(
            new UserResource($user),
            'تم تحديث بيانات المستخدم بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return $this->success(
            null,
            'تم حذف المسخدم بنجاح'
        );
    }
}
