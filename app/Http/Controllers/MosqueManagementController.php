<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mosque;
use App\Models\Center;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\Plan;
use App\Models\PlanLevel;
use App\Models\Grade;
use App\Models\AcademicQualification;
use App\Models\PersonalCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class MosqueManagementController extends Controller
{
    /**
     * Constructor with middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_dashboard')->only(['dashboard']);
        $this->middleware('permission:manage_users')->only(['users', 'storeUser', 'updateUser', 'destroyUser']);
        $this->middleware('permission:manage_mosques')->only(['mosques', 'storeMosque', 'updateMosque', 'destroyMosque']);
        $this->middleware('permission:manage_constants')->only(['constants', 'constantTypes']);
    }







    // ==================== CENTER CONTROLLER ====================

    /**
     * Display centers list
     */
    public function centers(Request $request)
    {
        $query = Center::with(['mosque.region.branch']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('mosque_id')) {
            $query->where('mosque_id', $request->mosque_id);
        }

        $centers = $query->withCount('users')->latest()->paginate(15);
        $mosques = Mosque::all();

        return view('centers.index', compact('centers', 'mosques'));
    }

    /**
     * Show create center form
     */
    public function createCenter()
    {
        $mosques = Mosque::with('region.branch')->get();
        return view('centers.form', compact('mosques'));
    }

    /**
     * Store new center
     */
    public function storeCenter(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mosque_id' => 'required|exists:mosques,id',
            'notes' => 'nullable|string',
        ]);

        Center::create($request->all());

        return redirect()->route('centers.index')
            ->with('success', 'تم إضافة المركز بنجاح');
    }

    /**
     * Show center details
     */
    public function showCenter($id)
    {
        $center = Center::with(['mosque.region.branch', 'users'])->findOrFail($id);
        return view('centers.show', compact('center'));
    }

    /**
     * Show edit center form
     */
    public function editCenter($id)
    {
        $center = Center::findOrFail($id);
        $mosques = Mosque::with('region.branch')->get();
        return view('centers.form', compact('center', 'mosques'));
    }

    /**
     * Update center
     */
    public function updateCenter(Request $request, $id)
    {
        $center = Center::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'mosque_id' => 'required|exists:mosques,id',
            'notes' => 'nullable|string',
        ]);

        $center->update($request->all());

        return redirect()->route('centers.show', $center->id)
            ->with('success', 'تم تحديث المركز بنجاح');
    }

    /**
     * Delete center
     */
    public function destroyCenter($id)
    {
        $center = Center::findOrFail($id);

        if ($center->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المركز لوجود مستخدمين مرتبطين به');
        }

        $center->delete();

        return redirect()->route('centers.index')
            ->with('success', 'تم حذف المركز بنجاح');
    }

    /**
     * Get centers by mosque (AJAX)
     */
    public function getCentersByMosque($mosqueId)
    {
        $centers = Center::where('mosque_id', $mosqueId)->get(['id', 'name']);
        return response()->json($centers);
    }

    // ==================== BRANCH CONTROLLER ====================

    /**
     * Display branches list
     */
    public function branches(Request $request)
    {
        $branches = Branch::withCount('regions')->latest()->paginate(15);
        return view('branches.index', compact('branches'));
    }

    /**
     * Show create branch form
     */
    public function createBranch()
    {
        return view('branches.form');
    }

    /**
     * Store new branch
     */
    public function storeBranch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_replacement_limit' => 'required|integer|min:0',
            'min_replacement_limit' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'تم إضافة الفرع بنجاح');
    }

    /**
     * Show branch details
     */
    public function showBranch($id)
    {
        $branch = Branch::with(['regions.mosques.centers'])->findOrFail($id);
        return view('branches.show', compact('branch'));
    }

    /**
     * Show edit branch form
     */
    public function editBranch($id)
    {
        $branch = Branch::findOrFail($id);
        return view('branches.form', compact('branch'));
    }

    /**
     * Update branch
     */
    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'max_replacement_limit' => 'required|integer|min:0',
            'min_replacement_limit' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.show', $branch->id)
            ->with('success', 'تم تحديث الفرع بنجاح');
    }

    /**
     * Delete branch
     */
    public function destroyBranch($id)
    {
        $branch = Branch::findOrFail($id);

        if ($branch->regions()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الفرع لوجود مناطق تابعة له');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'تم حذف الفرع بنجاح');
    }

    /**
     * Get regions by branch (AJAX)
     */
    public function getRegionsByBranch($branchId)
    {
        $regions = Region::where('branch_id', $branchId)->get(['id', 'name']);
        return response()->json($regions);
    }

    // ==================== REGION CONTROLLER ====================

    /**
     * Display regions list
     */
    public function regions(Request $request)
    {
        $query = Region::with('branch');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $regions = $query->withCount('mosques')->latest()->paginate(15);
        $branches = Branch::all();

        return view('regions.index', compact('regions', 'branches'));
    }

    /**
     * Show create region form
     */
    public function createRegion()
    {
        $branches = Branch::all();
        return view('regions.form', compact('branches'));
    }

    /**
     * Store new region
     */
    public function storeRegion(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        Region::create($request->all());

        return redirect()->route('regions.index')
            ->with('success', 'تم إضافة المنطقة بنجاح');
    }

    /**
     * Show region details
     */
    public function showRegion($id)
    {
        $region = Region::with(['branch', 'mosques.centers'])->findOrFail($id);
        return view('regions.show', compact('region'));
    }

    /**
     * Show edit region form
     */
    public function editRegion($id)
    {
        $region = Region::findOrFail($id);
        $branches = Branch::all();
        return view('regions.form', compact('region', 'branches'));
    }

    /**
     * Update region
     */
    public function updateRegion(Request $request, $id)
    {
        $region = Region::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        $region->update($request->all());

        return redirect()->route('regions.show', $region->id)
            ->with('success', 'تم تحديث المنطقة بنجاح');
    }

    /**
     * Delete region
     */
    public function destroyRegion($id)
    {
        $region = Region::findOrFail($id);

        if ($region->mosques()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المنطقة لوجود مساجد تابعة لها');
        }

        $region->delete();

        return redirect()->route('regions.index')
            ->with('success', 'تم حذف المنطقة بنجاح');
    }

    // ==================== CONSTANT CONTROLLER ====================

    /**
     * Display constants list
     */
    public function constants(Request $request)
    {
        $query = Constant::with(['type', 'parent']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type_id')) {
            $query->where('constant_type_id', $request->type_id);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $constants = $query->latest()->paginate(15);
        $types = ConstantType::all();

        return view('constants.index', compact('constants', 'types'));
    }

    /**
     * Show create constant form
     */
    public function createConstant()
    {
        $types = ConstantType::all();
        $parents = Constant::all();
        return view('constants.form', compact('types', 'parents'));
    }

    /**
     * Store new constant
     */
    public function storeConstant(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'constant_type_id' => 'required|exists:constant_types,id',
            'parent_id' => 'nullable|exists:constants,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        Constant::create($request->all());

        return redirect()->route('constants.index')
            ->with('success', 'تم إضافة الثابت بنجاح');
    }

    /**
     * Show constant details
     */
    public function showConstant($id)
    {
        $constant = Constant::with(['type', 'parent', 'children'])->findOrFail($id);
        return view('constants.show', compact('constant'));
    }

    /**
     * Show edit constant form
     */
    public function editConstant($id)
    {
        $constant = Constant::findOrFail($id);
        $types = ConstantType::all();
        $parents = Constant::where('id', '!=', $id)->get();
        return view('constants.form', compact('constant', 'types', 'parents'));
    }

    /**
     * Update constant
     */
    public function updateConstant(Request $request, $id)
    {
        $constant = Constant::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'constant_type_id' => 'required|exists:constant_types,id',
            'parent_id' => 'nullable|exists:constants,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $constant->update($request->all());

        return redirect()->route('constants.show', $constant->id)
            ->with('success', 'تم تحديث الثابت بنجاح');
    }

    /**
     * Delete constant
     */
    public function destroyConstant($id)
    {
        $constant = Constant::findOrFail($id);

        if ($constant->children()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الثابت لوجود ثوابت فرعية تابعة له');
        }

        $constant->delete();

        return redirect()->route('constants.index')
            ->with('success', 'تم حذف الثابت بنجاح');
    }

    /**
     * Toggle constant status
     */
    public function toggleConstantStatus($id)
    {
        $constant = Constant::findOrFail($id);
        $constant->update(['is_active' => !$constant->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $constant->is_active
        ]);
    }

    /**
     * Get constants by type (AJAX)
     */
    public function getConstantsByType($typeId)
    {
        $constants = Constant::where('constant_type_id', $typeId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($constants);
    }

    /**
     * Get active constants by type (AJAX)
     */
    public function getActiveConstantsByType($typeId)
    {
        $constants = Constant::where('constant_type_id', $typeId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($constants);
    }

    // ==================== CONSTANT TYPE CONTROLLER ====================

    /**
     * Display constant types list
     */
    public function constantTypes(Request $request)
    {
        $types = ConstantType::withCount('constants')->latest()->paginate(15);
        return view('constant-types.index', compact('types'));
    }

    /**
     * Show create constant type form
     */
    public function createConstantType()
    {
        return view('constant-types.form');
    }

    /**
     * Store new constant type
     */
    public function storeConstantType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:constant_types',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        ConstantType::create($request->all());

        return redirect()->route('constant-types.index')
            ->with('success', 'تم إضافة نوع الثابت بنجاح');
    }

    /**
     * Show constant type details
     */
    public function showConstantType($id)
    {
        $type = ConstantType::with('constants')->findOrFail($id);
        return view('constant-types.show', compact('type'));
    }

    /**
     * Show edit constant type form
     */
    public function editConstantType($id)
    {
        $type = ConstantType::findOrFail($id);
        return view('constant-types.form', compact('type'));
    }

    /**
     * Update constant type
     */
    public function updateConstantType(Request $request, $id)
    {
        $type = ConstantType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:constant_types,name,' . $id,
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $type->update($request->all());

        return redirect()->route('constant-types.show', $type->id)
            ->with('success', 'تم تحديث نوع الثابت بنجاح');
    }

    /**
     * Delete constant type
     */
    public function destroyConstantType($id)
    {
        $type = ConstantType::findOrFail($id);

        if ($type->constants()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف نوع الثابت لوجود ثوابت تابعة له');
        }

        $type->delete();

        return redirect()->route('constant-types.index')
            ->with('success', 'تم حذف نوع الثابت بنجاح');
    }

    // ==================== PLAN CONTROLLER ====================

    /**
     * Display plans list
     */
    public function plans(Request $request)
    {
        $plans = Plan::with(['type', 'targetGroup'])
            ->withCount('levels')
            ->latest()
            ->paginate(12);

        return view('plans.index', compact('plans'));
    }

    /**
     * Show create plan form
     */
    public function createPlan()
    {
        $planTypes = Constant::whereHas('type', function ($q) {
            $q->where('name', 'plan_type');
        })->get();

        $targetGroups = Constant::whereHas('type', function ($q) {
            $q->where('name', 'target_group');
        })->get();

        return view('plans.form', compact('planTypes', 'targetGroups'));
    }

    /**
     * Store new plan
     */
    public function storePlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:constants,id',
            'target_group_id' => 'required|exists:constants,id',
            'description' => 'nullable|string',
            'level_numbers' => 'nullable|integer|min:1|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $plan = Plan::create($request->all());

        // Create default levels if level_numbers is set
        if ($request->filled('level_numbers')) {
            for ($i = 1; $i <= $request->level_numbers; $i++) {
                $plan->levels()->create([
                    'name' => 'المستوى ' . $i,
                    'level_order' => $i,
                ]);
            }
        }

        return redirect()->route('plans.show', $plan->id)
            ->with('success', 'تم إضافة الخطة بنجاح');
    }

    /**
     * Show plan details
     */
    public function showPlan($id)
    {
        $plan = Plan::with([
            'type',
            'targetGroup',
            'levels' => function ($q) {
                $q->orderBy('level_order');
            }
        ])->findOrFail($id);

        return view('plans.show', compact('plan'));
    }

    /**
     * Show edit plan form
     */
    public function editPlan($id)
    {
        $plan = Plan::findOrFail($id);

        $planTypes = Constant::whereHas('type', function ($q) {
            $q->where('name', 'plan_type');
        })->get();

        $targetGroups = Constant::whereHas('type', function ($q) {
            $q->where('name', 'target_group');
        })->get();

        return view('plans.form', compact('plan', 'planTypes', 'targetGroups'));
    }

    /**
     * Update plan
     */
    public function updatePlan(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:constants,id',
            'target_group_id' => 'required|exists:constants,id',
            'description' => 'nullable|string',
            'level_numbers' => 'nullable|integer|min:1|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $plan->update($request->all());

        return redirect()->route('plans.show', $plan->id)
            ->with('success', 'تم تحديث الخطة بنجاح');
    }

    /**
     * Delete plan
     */
    public function destroyPlan($id)
    {
        $plan = Plan::findOrFail($id);

        DB::beginTransaction();

        try {
            $plan->levels()->delete();
            $plan->delete();

            DB::commit();

            return redirect()->route('plans.index')
                ->with('success', 'تم حذف الخطة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف الخطة: ' . $e->getMessage());
        }
    }

    /**
     * Toggle plan status
     */
    public function togglePlanStatus($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $plan->is_active
        ]);
    }

    /**
     * Duplicate plan
     */
    public function duplicatePlan($id)
    {
        $originalPlan = Plan::with('levels')->findOrFail($id);

        DB::beginTransaction();

        try {
            $newPlan = $originalPlan->replicate();
            $newPlan->name = $originalPlan->name . ' (نسخة)';
            $newPlan->created_at = now();
            $newPlan->updated_at = now();
            $newPlan->save();

            foreach ($originalPlan->levels as $level) {
                $newLevel = $level->replicate();
                $newLevel->plan_id = $newPlan->id;
                $newLevel->save();
            }

            DB::commit();

            return redirect()->route('plans.show', $newPlan->id)
                ->with('success', 'تم نسخ الخطة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء نسخ الخطة: ' . $e->getMessage());
        }
    }

    // ==================== PLAN LEVEL CONTROLLER ====================

    /**
     * Display plan levels list
     */
    public function planLevels(Request $request)
    {
        $levels = PlanLevel::with('plan')->latest()->paginate(15);
        return view('plan-levels.index', compact('levels'));
    }

    /**
     * Show create plan level form
     */
    public function createPlanLevel()
    {
        $plans = Plan::all();
        $timeUnits = Constant::whereHas('type', function ($q) {
            $q->where('name', 'time_unit');
        })->get();

        return view('plan-levels.form', compact('plans', 'timeUnits'));
    }

    /**
     * Store new plan level
     */
    public function storePlanLevel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'level_order' => 'required|integer|min:1',
            'time_of_level' => 'nullable|integer|min:0',
            'time_unit_id' => 'nullable|exists:constants,id',
            'max_time' => 'nullable|integer|min:0',
            'max_time_unit_id' => 'nullable|exists:constants,id',
            'min_time' => 'nullable|integer|min:0',
            'min_time_unit_id' => 'nullable|exists:constants,id',
        ]);

        // Check unique order in plan
        $exists = PlanLevel::where('plan_id', $request->plan_id)
            ->where('level_order', $request->level_order)
            ->exists();

        if ($exists) {
            return back()->with('error', 'رقم المستوى موجود بالفعل في هذه الخطة')
                ->withInput();
        }

        PlanLevel::create($request->all());

        return redirect()->route('plan-levels.index')
            ->with('success', 'تم إضافة مستوى الخطة بنجاح');
    }

    /**
     * Show plan level details
     */
    public function showPlanLevel($id)
    {
        $level = PlanLevel::with(['plan', 'timeUnit', 'maxTimeUnit', 'minTimeUnit'])->findOrFail($id);
        return view('plan-levels.show', compact('level'));
    }

    /**
     * Show edit plan level form
     */
    public function editPlanLevel($id)
    {
        $level = PlanLevel::findOrFail($id);
        $plans = Plan::all();
        $timeUnits = Constant::whereHas('type', function ($q) {
            $q->where('name', 'time_unit');
        })->get();

        return view('plan-levels.form', compact('level', 'plans', 'timeUnits'));
    }

    /**
     * Update plan level
     */
    public function updatePlanLevel(Request $request, $id)
    {
        $level = PlanLevel::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'level_order' => 'required|integer|min:1',
            'time_of_level' => 'nullable|integer|min:0',
            'time_unit_id' => 'nullable|exists:constants,id',
            'max_time' => 'nullable|integer|min:0',
            'max_time_unit_id' => 'nullable|exists:constants,id',
            'min_time' => 'nullable|integer|min:0',
            'min_time_unit_id' => 'nullable|exists:constants,id',
        ]);

        // Check unique order in plan (excluding current)
        $exists = PlanLevel::where('plan_id', $request->plan_id)
            ->where('level_order', $request->level_order)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'رقم المستوى موجود بالفعل في هذه الخطة')
                ->withInput();
        }

        $level->update($request->all());

        return redirect()->route('plan-levels.show', $level->id)
            ->with('success', 'تم تحديث مستوى الخطة بنجاح');
    }

    /**
     * Delete plan level
     */
    public function destroyPlanLevel($id)
    {
        $level = PlanLevel::findOrFail($id);
        $level->delete();

        return redirect()->route('plan-levels.index')
            ->with('success', 'تم حذف مستوى الخطة بنجاح');
    }

    /**
     * Reorder plan levels (AJAX)
     */
    public function reorderPlanLevels(Request $request)
    {
        $request->validate([
            'levels' => 'required|array',
            'levels.*.id' => 'required|exists:plan_levels,id',
            'levels.*.order' => 'required|integer|min:1',
        ]);

        foreach ($request->levels as $item) {
            PlanLevel::where('id', $item['id'])->update(['level_order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    // ==================== GRADE CONTROLLER ====================

    /**
     * Display grades list
     */
    public function grades(Request $request)
    {
        $grades = Grade::latest()->paginate(15);
        return view('grades.index', compact('grades'));
    }

    /**
     * Show create grade form
     */
    public function createGrade()
    {
        return view('grades.form');
    }

    /**
     * Store new grade
     */
    public function storeGrade(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'DQ_range_from' => 'required|string|max:50',
            'DQ_range_to' => 'required|string|max:50',
        ]);

        Grade::create($request->all());

        return redirect()->route('grades.index')
            ->with('success', 'تم إضافة الدرجة بنجاح');
    }

    /**
     * Show grade details
     */
    public function showGrade($id)
    {
        $grade = Grade::findOrFail($id);
        return view('grades.show', compact('grade'));
    }

    /**
     * Show edit grade form
     */
    public function editGrade($id)
    {
        $grade = Grade::findOrFail($id);
        return view('grades.form', compact('grade'));
    }

    /**
     * Update grade
     */
    public function updateGrade(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'DQ_range_from' => 'required|string|max:50',
            'DQ_range_to' => 'required|string|max:50',
        ]);

        $grade->update($request->all());

        return redirect()->route('grades.show', $grade->id)
            ->with('success', 'تم تحديث الدرجة بنجاح');
    }

    /**
     * Delete grade
     */
    public function destroyGrade($id)
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();

        return redirect()->route('grades.index')
            ->with('success', 'تم حذف الدرجة بنجاح');
    }

    // ==================== ACADEMIC QUALIFICATION CONTROLLER ====================

    /**
     * Display academic qualifications list
     */
    public function academicQualifications(Request $request)
    {
        $qualifications = AcademicQualification::with(['academicDegree', 'major', 'person'])
            ->latest()
            ->paginate(15);

        return view('academic-qualifications.index', compact('qualifications'));
    }

    /**
     * Store new academic qualification
     */
    public function storeAcademicQualification(Request $request)
    {
        $request->validate([
            'academic_degree_id' => 'required|exists:constants,id',
            'major_id' => 'required|exists:constants,id',
            'person_type' => 'required|string',
            'person_id' => 'required|integer',
            'detail' => 'nullable|string',
            'date_graduate' => 'nullable|date',
            'educational_institution' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $qualification = AcademicQualification::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $qualification->load(['academicDegree', 'major'])
        ]);
    }

    /**
     * Delete academic qualification
     */
    public function destroyAcademicQualification($id)
    {
        $qualification = AcademicQualification::findOrFail($id);
        $qualification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get qualifications by person (AJAX)
     */
    public function getQualificationsByPerson($personType, $personId)
    {
        $qualifications = AcademicQualification::where('person_type', 'App\\Models\\' . $personType)
            ->where('person_id', $personId)
            ->with(['academicDegree', 'major'])
            ->get();

        return response()->json($qualifications);
    }

    // ==================== PERSONAL COURSE CONTROLLER ====================

    /**
     * Display personal courses list
     */
    public function personalCourses(Request $request)
    {
        $courses = PersonalCourse::with(['type', 'person'])
            ->latest()
            ->paginate(15);

        return view('personal-courses.index', compact('courses'));
    }

    /**
     * Store new personal course
     */
    public function storePersonalCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string',
            'type_id' => 'required|exists:constants,id',
            'person_type' => 'required|string',
            'person_id' => 'required|integer',
            'hours' => 'nullable|integer|min:0',
            'provider' => 'nullable|string',
            'place' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $course = PersonalCourse::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $course->load('type')
        ]);
    }

    /**
     * Delete personal course
     */
    public function destroyPersonalCourse($id)
    {
        $course = PersonalCourse::findOrFail($id);
        $course->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get courses by person (AJAX)
     */
    public function getCoursesByPerson($personType, $personId)
    {
        $courses = PersonalCourse::where('person_type', 'App\\Models\\' . $personType)
            ->where('person_id', $personId)
            ->with('type')
            ->get();

        return response()->json($courses);
    }

    // ==================== PROFILE CONTROLLER ====================

    /**
     * Show user profile
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
        ]);

        $user->update($request->only(['name', 'email', 'phone']));

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'تم تحديث كلمة المرور بنجاح');
    }

    // ==================== REPORT CONTROLLER ====================

    /**
     * Generate users report
     */
    public function usersReport(Request $request)
    {
        $users = User::with(['mosque', 'maritalStatus'])
            ->when($request->from_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->to_date);
            })
            ->get();

        return view('reports.users', compact('users'));
    }

    /**
     * Generate mosques report
     */
    public function mosquesReport(Request $request)
    {
        $mosques = Mosque::with(['region.branch', 'centers', 'users'])
            ->withCount(['centers', 'users'])
            ->get();

        return view('reports.mosques', compact('mosques'));
    }

    /**
     * Generate plans report
     */
    public function plansReport(Request $request)
    {
        $plans = Plan::with(['type', 'targetGroup', 'levels'])
            ->withCount('levels')
            ->get();

        return view('reports.plans', compact('plans'));
    }
}