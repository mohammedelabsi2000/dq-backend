<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;
use App\Http\Requests\StudentPlan\EnrollStudentPlanRequest;
use App\Http\Requests\StudentPlan\UpdateStudentPlanRequest;
use App\Http\Resources\PlanResource;
use App\Http\Resources\StudentPlanResource;
use App\Models\Level;
use App\Enums\PlanType;
use App\Models\Plan;
use App\Models\Student;
use App\Models\StudentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Display a listing of the plans.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Plan::class);

        $query = Plan::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // فلترة حسب حالة التفعيل
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_levels')) {
            $query->with('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');
        }

        $plans = $query->withCount('levels')->get();

        return $this->successWithPagination(
            PlanResource::collection($plans),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePlanRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePlanRequest $request)
    {
        $plan = Plan::create($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_levels')) {
            $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');
        }

        return $this->success(
            new PlanResource($plan->loadCount('levels')),
            'تم إنشاء الخطة بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Plan $plan)
    {
        $this->authorize('view', $plan);

        // تحميل الخطة مع مستوياتها، ومسارات كل مستوى ومواده، مع أعداد العلاقات
        $plan->load([
            'levels' => function ($query) {
                $query->withCount('levelTracks')
                    ->with([
                        'levelTracks.track',
                        'levelTracks.levelTrackSubjects.subject',
                    ]);
            },
        ])->loadCount('levels');

        return $this->success(
            new PlanResource($plan),
            'بيانات الخطة'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePlanRequest $request
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $plan->update($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_levels')) {
            $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');
        }

        return $this->success(
            new PlanResource($plan->loadCount('levels')),
            'تم تحديث الخطة بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Plan $plan)
    {
        $this->authorize('delete', $plan);

        // تحقق من وجود مستويات تابعة قبل الحذف
        if ($plan->levels()->exists()) {
            return $this->error(
                'لا يمكن حذف الخطة لأنها تحتوي على مستويات تابعة',
                400
            );
        }

        $plan->delete();

        return $this->success(
            null,
            'تم حذف الخطة بنجاح'
        );
    }

    /**
     * Toggle the active state of the plan.
     *
     * @param Plan $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Plan $plan)
    {
        $this->authorize('toggle_active', $plan);

        $plan->update(['is_active' => !$plan->is_active]);

        return $this->success(
            new PlanResource($plan),
            $plan->is_active ? 'تم تفعيل الخطة' : 'تم تعطيل الخطة'
        );
    }

    /**
     * Reorder a set of plans.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        $this->authorize('reorder', Plan::class);

        $items = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:plans,id'],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ])['items'];

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                Plan::whereKey($item['id'])->update(['order' => $item['order']]);
            }
        });

        return $this->success(
            null,
            'تم تحديث الترتيب بنجاح'
        );
    }


    public function getStudentsByPlan(Request $request, int $planId)
    {
        $this->authorize('showPlanStudents', Plan::find($planId));

        $query = Plan::where('id', $planId)
            ->with('students');

        $q = $this->applyFilters($query, [
            'searchColumns' => ['students.name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];

        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->filled('level_id')) {
            $query->where('current_level_id', $request->integer('level_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $studentPlans = $query->first();

        return $this->successWithPagination(
            new StudentPlanResource($studentPlans),
            ['total' => $studentPlans->students->count(), 'skip' => 0, 'limit' => 10],
            'success',
            200
        );
    }

    public function storeStudentsByPlan(EnrollStudentPlanRequest $request, Plan $plan)
    {
        $validated = $request->validated();

        // Check if user can enroll all specified students (hierarchical scope)
        $visibleStudentCount = Student::whereIn('id', $validated['student_ids'])
            ->visibleTo(auth()->user())
            ->count();

        if ($visibleStudentCount !== count($validated['student_ids'])) {
            return $this->error('ليس لديك صلاحية لتسجيل بعض الطلاب المحددين في هذه الخطة', 403);
        }

        $startingLevelId = $validated['starting_level_id']
            ?? Level::where('plan_id', $plan->id)->orderBy('order')->first()?->id;

        // is_main تُستنتج تلقائياً: تكون رئيسية فقط إذا كانت هذه هي الخطة الرئيسية الفعالة الوحيدة
        $isMain = $plan->isActiveMain();

        $studentsIds = $validated['student_ids'];

        $students = Student::whereIn('id', $studentsIds)->get();

        foreach ($students as $student) {

            $hasOtherActivePlans = $student->plans()->wherePivotNull('to_date')->exists();

            $data = [
                'student_id' => $student->id,
                'plan_id' => $plan->id,
                'starting_level_id' => $startingLevelId,
                'current_level_id' => $startingLevelId,
                'from_date' => $validated['from_date'],
                'is_main' => $isMain,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ];

            if (!$hasOtherActivePlans) {
                // كل طالب يجب أن يكون له خطة رئيسية واحدة على الأقل
                $data['is_main'] = true;
            } elseif ($isMain) {
                $plans_ids = $student->plans()->wherePivotNull('to_date')->pluck('plans.id')->toArray();
                $updateData = array_fill_keys($plans_ids, ['is_main' => 0]);
                $student->plans()->syncWithoutDetaching($updateData);
            }

            $student->plans()->syncWithPivotValues(
                [$plan->id],
                $data,
                false
            );
        }

        return $this->success(
            null,
            'تم تسجيل التحاق الطلاب بالخطة بنجاح',
            201
        );
    }

    public function updateStudentByPlan(UpdateStudentPlanRequest $request, $planId, $studentId)
    {
        $validated = $request->validated();

        // Check if user can manage this student (hierarchical scope)
        if (!Student::where('id', $studentId)->visibleTo(auth()->user())->exists()) {
            return $this->error('ليس لديك صلاحية لتعديل بيانات هذا الطالب', 403);
        }

        $plan = Plan::findOrFail($planId);

        // الحصول على السجل الحالي للطالب في هذه الخطة
        $currentPivot = $plan->students()->where('students.id', $studentId)->first()?->pivot;
        // logger($currentPivot);

        if (!$currentPivot) {
            return $this->error(
                'الطالب غير مسجل في هذه الخطة',
                404
            );
        }

        // التحقق 3: إذا كانت الخطة غير نشطة لا يمكن التعديل عليها
        if ($currentPivot->to_date !== null) {
            return $this->error(
                'لا يمكن تعديل خطة غير نشطة',
                400
            );
        }

        // الحصول على جميع الخطط النشطة للطالب باستخدام علاقة pivot
        $student = Student::findOrFail($studentId);
        $activePlans = $student->plans()
            ->whereNull('student_plans.to_date')
            ->get();

        $activeMainPlans = $activePlans->where('pivot.is_main', true);
        $activeNonMainPlans = $activePlans->where('pivot.is_main', false);

        $newIsMain = $validated['is_main'] ?? $currentPivot->is_main;
        $newToDate = $validated['to_date'] ?? $currentPivot->to_date;

        // التحقق 4: لا يمكن إنهاء خطة رئيسية نشطة والطالب له خطط أخرى نشطة غير رئيسية
        if ($currentPivot->is_main && $currentPivot->to_date === null && $newToDate !== null) {
            if ($activeNonMainPlans->count() > 0) {
                return $this->error(
                    'لا يمكن إنهاء الخطة الرئيسية النشطة والطالب لديه خطط أخرى نشطة غير رئيسية، يجب تحويل إحدى الخطط غير الرئيسية إلى رئيسية أولاً',
                    400
                );
            }
        }

        // التحقق 1: إذا كان له خطة رئيسية واحدة نشطة فقط ويريد التعديل إلى غير رئيسية
        if ($currentPivot->is_main && !$newIsMain && $activeMainPlans->count() === 1) {
            return $this->error(
                'لا يمكن تحويل الخطة الرئيسية الوحيدة إلى غير رئيسية، يجب أن يكون للطالب خطة رئيسية نشطة واحدة على الأقل',
                400
            );
        }

        // التحقق 2: إذا كان يريد تحويل خطة غير رئيسية إلى رئيسية
        if (!$currentPivot->is_main && $newIsMain) {
            // إلغاء الرئيسية عن جميع الخطط النشطة الأخرى باستخدام علاقة pivot
            $student->plans()
                ->whereNull('student_plans.to_date')
                ->where('student_plans.is_main', true)
                ->update(['student_plans.is_main' => false]);
        }

        $plan->students()->updateExistingPivot($studentId, [
            'starting_level_id' => $validated['starting_level_id'],
            'current_level_id' => $validated['current_level_id'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'is_main' => $newIsMain,
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        $plan->load('students');

        return $this->success(
            new StudentPlanResource($plan),
            'تم تحديث الخطة بنجاح',
            200
        );
    }

    public function deleteStudentByPlan($planId, $studentId)
    {
        $plan = Plan::findOrFail($planId);
        $this->authorize('deleteStudent', $plan);

        if (!Student::where('id', $studentId)->visibleTo(auth()->user())->exists()) {
            return $this->error('ليس لديك صلاحية لحذف هذا الطالب من الخطة', 403);
        }

        $plan->students()->detach($studentId);

        return $this->success(null, 'تم حذف الطالب من الخطة بنجاح', 200);
    }
}
