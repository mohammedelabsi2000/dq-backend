<?php

namespace App\Http\Controllers\Api;

use App\Concerns\HasVisibilityScope;
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
    use HasVisibilityScope;

    private const SUPER_ADMIN_ROLE = 'المسؤول التقني الأعلى';

    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $authUser = auth()->user();

        $query = User::query()->where('is_approved', true)->visibleTo(auth()->user());

        if (!$authUser->hasRole(self::SUPER_ADMIN_ROLE)) {
            $query->whereDoesntHave('roles', fn($q) => $q->where('name', self::SUPER_ADMIN_ROLE));
        }

        // if ($authUser->isGlobalAdmin() && $request->filled('active')) {
        //     match ($request->input('active')) {
        //         'false' => $query->where('is_active', false),
        //         'all'   => null, // بدون فلتر
        //         default => $query->where('is_active', true),
        //     };
        // } else {
        //     // بقية المديرين → الفعالين فقط دائماً
        //     $query->where('is_active', true);
        // }

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
    // public function store(StoreUserRequest $request)
    // {
    //     $user = User::where('identity', $request['identity'])
    //         ->first();

    //     if ($user) {
    //         return $this->error(
    //             'المستخدم موجود مسبقاً',
    //             422
    //         );
    //     } else {
    //         $data = $request->validated();
    //         $data['password'] = Hash::make($request['password']);

    //         $user = User::withTrashed()->updateOrCreate([
    //             'identity' => $data['identity']
    //         ], $data);

    //         return $this->success(
    //             new UserResource($user),
    //             'تم إنشاء المستخدم بنجاح',
    //             201
    //         );
    //     }
    // }
    public function store(StoreUserRequest $request)
    {
        // نبحث في الكل — معتمد وغير معتمد ومحذوف
        $existing = User::withTrashed()
            ->where('identity', $request->identity)
            ->first();

        if ($existing && !$existing->trashed()) {
            return $this->error('المستخدم موجود مسبقاً', 422);
        }

        $data               = $request->validated();
        $data['password']   = Hash::make($request->password);
        $data['is_approved'] = false;
        $data['is_active']   = false;

        $user = User::withTrashed()->updateOrCreate(
            ['identity' => $data['identity']],
            $data
        );

        // رفع طلب الاعتماد تلقائياً
        // $user->submitForApproval(auth()->user());
        try {
            $approvalRequest = $user->submitForApproval(auth()->user());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }

        if ($approvalRequest === null) {
            $user->update(['is_active' => true]);
        }

        $user->load('approvalRequest');

        $message = $approvalRequest === null
            ? 'تم إنشاء المستخدم وتفعيله مباشرة'    // المدير العام — اعتماد فوري
            : 'تم إنشاء المستخدم وإرساله للاعتماد'; // بقية المديرين

        return $this->success(new UserResource($user), $message, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        if ($user->hasRole(self::SUPER_ADMIN_ROLE) && !auth()->user()->hasRole(self::SUPER_ADMIN_ROLE)) {
            return $this->error('غير موجود', 404);
        }

        $user = $user->load([
            'mosque',
            'maritalStatus',
            'prefix',
            'roles.permissions',
            'approvalRequest',
            'activeScopes'
        ]);
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

        // لا يمكن حذف نفسه
        if ($user->id === auth()->id()) {
            return $this->error('لا يمكنك حذف حسابك بنفسك.', 422);
        }

        $user->delete();
        return $this->success(
            null,
            'تم حذف المسخدم بنجاح'
        );
    }

    public function toggleActive(User $user)
    {
        $this->authorize('toggleActive', $user);

        // لا يمكن إيقاف مستخدم غير معتمد
        if (!$user->is_approved) {
            return $this->error('لا يمكن تفعيل أو إيقاف مستخدم غير معتمد.', 422);
        }

        // لا يمكن إيقاف نفسه
        if ($user->id === auth()->id()) {
            return $this->error('لا يمكنك إيقاف حسابك بنفسك.', 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        $message = $user->is_active ? 'تم تفعيل المستخدم بنجاح.' : 'تم إيقاف المستخدم بنجاح.';

        return $this->success(new UserResource($user), $message);
    }
}
