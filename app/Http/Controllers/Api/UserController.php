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
        $query = User::query()->visibleTo(auth()->user());
        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => ['full_name', 'identity'],
            'orderColumn' => 'created_at',
        ]);
        /* $q = $this->applyFilters($query, [
            'searchColumns' => ['full_name', 'identity'],
            'orderColumn' => 'created_at',
        ]);

        $query = $q['query'];
        $total = $q['count'];
        $skip = $q['skip'];
        $limit = $q['limit']; */
        // $total = $query->count();
        $users = $query->with(['mosque', 'mosque.region', 'mosque.region.branch', 'maritalStatus', 'prefix'])->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $skip,
            'limit' => $limit,
            'data' => UserResource::collection($users),
        ], 'success', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
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


        /////////////////////////////////////
        /* $request['password'] = Hash::make($request['password']);
        $data = $request->validated();

        $user = User::withTrashed()
            ->where('identity', $data['identity'])
            ->first();

        if ($user) {
            // إذا كان محذوف نرجعه
            if ($user->trashed()) {
                $user->restore();
            }

            // نحدث البيانات
            $user->update($data);

            return $this->success(
                new UserResource($user),
                'تم استعادة المستخدم بنجاح',
                201
            );
        }
        // تشفير الباسوورد 

        $user = User::create($data); */
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user = $user->load(['mosque', 'maritalStatus', 'prefix']);
        return $this->apiResponse(
            new UserResource($user),
            'success',
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
        $my_request = $request->validated();
        // تشفير الباسوورد لو تم تغييره
        if ($request->input('password')) {
            $request['password'] = Hash::make($request['password']);
        } else {
            unset($my_request['password']);
        }

        $user->updateOrCreate(
            ['identity' => $request['identity']],
            $my_request
        );

        return $this->success(
            new UserResource($user),
            'تم تحديث بيانات المسخدم بنجاح'
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
