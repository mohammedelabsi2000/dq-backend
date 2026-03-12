<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Mosque;
use App\Models\Constant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{    // ==================== USER CONTROLLER ====================

    /**
     * Display users list
     */
    public function index(Request $request)
    {
        $query = User::with(['mosque', 'maritalStatus', 'prefix']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('identity', 'like', "%{$search}%");
            });
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Mosque filter
        if ($request->filled('mosque_id')) {
            $query->where('mosque_id', $request->mosque_id);
        }

        // Marital status filter
        if ($request->filled('marital_status_id')) {
            $query->where('marital_status_id', $request->marital_status_id);
        }

        $users = $query->latest()->paginate(15);

        // Get filter data
        $mosques = Mosque::all();
        $maritalStatuses = Constant::whereHas('type', function ($q) {
            $q->where('name', 'marital_status');
        })->get();

        return view('users.index', compact('users', 'mosques', 'maritalStatuses'));
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        $mosques = Mosque::all();
        $prefixes = Constant::whereHas('type', function ($q) {
            $q->where('name', 'prefix_name');
        })->get();

        $maritalStatuses = Constant::whereHas('type', function ($q) {
            $q->where('name', 'marital_status');
        })->get();

        $academicDegrees = Constant::whereHas('type', function ($q) {
            $q->where('name', 'academic_degree');
        })->get();

        $majors = Constant::whereHas('type', function ($q) {
            $q->where('name', 'major');
        })->get();

        $courseTypes = Constant::whereHas('type', function ($q) {
            $q->where('name', 'course_type');
        })->get();

        return view('users.form', compact(
            'mosques',
            'prefixes',
            'maritalStatuses',
            'academicDegrees',
            'majors',
            'courseTypes'
        ));
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'fName' => 'required|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'identity' => 'nullable|string|unique:users|max:50',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'mosque_id' => 'nullable|exists:mosques,id',
            'location' => 'nullable|string',
            'marital_status_id' => 'nullable|exists:constants,id',
            'numChildren' => 'nullable|integer|min:0',
            'prefix_name_id' => 'nullable|exists:constants,id',
            'jobname' => 'nullable|string',
            'job_place' => 'nullable|string',
            'job_salary' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Create full name
            $fullName = trim($request->fName . ' ' . $request->sName . ' ' . $request->thName . ' ' . $request->family);

            $userData = [
                'name' => $fullName,
                'fName' => $request->fName,
                'sName' => $request->sName,
                'thName' => $request->thName,
                'family' => $request->family,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'identity' => $request->identity,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'mosque_id' => $request->mosque_id,
                'location' => $request->location,
                'marital_status_id' => $request->marital_status_id,
                'numChildren' => $request->numChildren ?? 0,
                'prefix_name_id' => $request->prefix_name_id,
                'jobname' => $request->jobname,
                'job_place' => $request->job_place,
                'job_salary' => $request->job_salary,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('users', 'public');
                $userData['image_path'] = $path;
            }

            $user = User::create($userData);

            // Store academic qualifications
            if ($request->has('qualifications')) {
                foreach ($request->qualifications as $qualification) {
                    if (!empty($qualification['academic_degree_id']) && !empty($qualification['major_id'])) {
                        $user->academicQualifications()->create([
                            'academic_degree_id' => $qualification['academic_degree_id'],
                            'major_id' => $qualification['major_id'],
                            'date_graduate' => $qualification['date_graduate'] ?? null,
                            'educational_institution' => $qualification['educational_institution'] ?? null,
                        ]);
                    }
                }
            }

            // Store courses
            if ($request->has('courses')) {
                foreach ($request->courses as $course) {
                    if (!empty($course['course_name']) && !empty($course['type_id'])) {
                        $user->personalCourses()->create([
                            'course_name' => $course['course_name'],
                            'type_id' => $course['type_id'],
                            'hours' => $course['hours'] ?? null,
                            'provider' => $course['provider'] ?? null,
                            'place' => $course['place'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'تم إضافة المستخدم بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء إضافة المستخدم: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        $user = User::with([
            'mosque.region.branch',
            'maritalStatus',
            'prefix',
            'academicQualifications.academicDegree',
            'academicQualifications.major',
            'personalCourses.type'
        ])->findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $user = User::with(['academicQualifications', 'personalCourses'])->findOrFail($id);

        $mosques = Mosque::all();
        $prefixes = Constant::whereHas('type', function ($q) {
            $q->where('name', 'prefix_name');
        })->get();

        $maritalStatuses = Constant::whereHas('type', function ($q) {
            $q->where('name', 'marital_status');
        })->get();

        $academicDegrees = Constant::whereHas('type', function ($q) {
            $q->where('name', 'academic_degree');
        })->get();

        $majors = Constant::whereHas('type', function ($q) {
            $q->where('name', 'major');
        })->get();

        $courseTypes = Constant::whereHas('type', function ($q) {
            $q->where('name', 'course_type');
        })->get();

        return view('users.form', compact(
            'user',
            'mosques',
            'prefixes',
            'maritalStatuses',
            'academicDegrees',
            'majors',
            'courseTypes'
        ));
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'fName' => 'required|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'identity' => 'nullable|string|max:50|unique:users,identity,' . $id,
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'mosque_id' => 'nullable|exists:mosques,id',
            'location' => 'nullable|string',
            'marital_status_id' => 'nullable|exists:constants,id',
            'numChildren' => 'nullable|integer|min:0',
            'prefix_name_id' => 'nullable|exists:constants,id',
            'jobname' => 'nullable|string',
            'job_place' => 'nullable|string',
            'job_salary' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Update full name
            $fullName = trim($request->fName . ' ' . $request->sName . ' ' . $request->thName . ' ' . $request->family);

            $userData = [
                'name' => $fullName,
                'fName' => $request->fName,
                'sName' => $request->sName,
                'thName' => $request->thName,
                'family' => $request->family,
                'email' => $request->email,
                'identity' => $request->identity,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'mosque_id' => $request->mosque_id,
                'location' => $request->location,
                'marital_status_id' => $request->marital_status_id,
                'numChildren' => $request->numChildren ?? 0,
                'prefix_name_id' => $request->prefix_name_id,
                'jobname' => $request->jobname,
                'job_place' => $request->job_place,
                'job_salary' => $request->job_salary,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($user->image_path) {
                    Storage::disk('public')->delete($user->image_path);
                }
                $path = $request->file('image')->store('users', 'public');
                $userData['image_path'] = $path;
            }

            $user->update($userData);

            // Update password if provided
            if ($request->filled('password')) {
                $request->validate(['password' => 'string|min:8|confirmed']);
                $user->update(['password' => Hash::make($request->password)]);
            }

            DB::commit();

            return redirect()->route('users.show', $user->id)
                ->with('success', 'تم تحديث المستخدم بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تحديث المستخدم: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete user
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();

        try {
            // Delete related data
            $user->academicQualifications()->delete();
            $user->personalCourses()->delete();

            // Delete image
            if ($user->image_path) {
                Storage::disk('public')->delete($user->image_path);
            }

            $user->delete();

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'تم حذف المستخدم بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * Search users (AJAX)
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json($users);
    }
}
