<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        /* return response()->json(
            User::with(['mosque', 'maritalStatus', 'prefix'])->latest()->paginate(15),
            200
        ); */
        $users = User::get();

        return $this->apiResponse(
            UserResource::collection($users),
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fName' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',
            'dob' => 'nullable|date',
            'mosque_id' => 'nullable|exists:mosques,id',
            'location' => 'nullable|string|max:191',
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'marital_status_id' => 'nullable|exists:constants,id',
            'numChildren' => 'nullable|integer',
            'identity' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'jobname' => 'nullable|string|max:191',
            'job_place' => 'nullable|string|max:191',
            'job_salary' => 'nullable|numeric',
            'prefix_name_id' => 'nullable|exists:constants,id',
            // 'image_id' => 'nullable|exists:images,id', // لو رح تضيف لاحقًا
        ]);

        // تشفير الباسوورد
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     */
    public function show(User $user)
    {
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
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'fName' => 'nullable|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',
            'dob' => 'nullable|date',
            'mosque_id' => 'nullable|exists:mosques,id',
            'location' => 'nullable|string|max:191',
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'marital_status_id' => 'nullable|exists:constants,id',
            'numChildren' => 'nullable|integer',
            'identity' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'jobname' => 'nullable|string|max:191',
            'job_place' => 'nullable|string|max:191',
            'job_salary' => 'nullable|numeric',
            'prefix_name_id' => 'nullable|exists:constants,id',
            // 'image_id' => 'nullable|exists:images,id',
        ]);

        // تشفير الباسوورد لو تم تغييره
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}
