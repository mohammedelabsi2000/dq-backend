<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;
use App\Http\Requests\StudentPlan\EnrollStudentPlanRequest;
use App\Http\Resources\PlanResource;
use App\Http\Resources\StudentPlanResource;
use App\Http\Resources\StudentResource;
use App\Models\Level;
use App\Models\Plan;
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
    // public function show(Request $request, Plan $plan)
    // {
    //     // $this->authorize('view', $plan);

    //     $plan->load('levels.levelTracks.track', 'levels.levelTracks.levelTrackSubjects.subject');

    //     return $this->success(
    //         new PlanResource($plan->loadCount('levels')),
    //         'بيانات الخطة'
    //     );
    // }
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

    public function getStudentsByPlan(Plan $plan)
    {
        $query = $plan->students();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $planStudents = $query->get();
        return $this->successWithPagination(
            StudentPlanResource::collection($planStudents),
            ['total' => $planStudents->count(), 'skip' => 0, 'limit' => 10],
            'success',
            200
        );
    }

    public function storeStudentsByPlan(EnrollStudentPlanRequest $request, Plan $plan)
    {
        $validated = $request->validated();

        $startingLevelId = $validated['starting_level_id']
            ?? Level::where('plan_id', $plan->id)->orderBy('order')->value('id');

        $isMain = $validated['is_main'] ?? false;
        $studentPlans = collect();

        foreach ($validated['student_ids'] as $studentId) {
            // هل هذه أول خطة نشطة للطالب؟ تصبح رئيسية تلقائياً إذا لم يُطلب خلاف ذلك
            $hasOtherActivePlans = StudentPlan::where('student_id', $studentId)
                ->active()
                ->exists();

            $shouldBeMain = $isMain ?? !$hasOtherActivePlans;

            $studentPlan = StudentPlan::create([
                'student_id' => $studentId,
                'plan_id' => $plan->id,
                'starting_level_id' => $startingLevelId,
                'current_level_id' => $startingLevelId,
                'from_date' => $validated['from_date'],
                'is_main' => $shouldBeMain,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            // لو طُلبت كرئيسية صراحة، ألغِ الرئيسية عن باقي الخطط النشطة
            if ($shouldBeMain) {
                $studentPlan->setAsMain();
            }

            // فتح أول سجل في history
            $studentPlan->levelHistory()->create([
                'level_id' => $startingLevelId,
                'from_date' => $validated['from_date'],
                'to_date' => null,
            ]);

            $studentPlans->push($studentPlan);

            return $this->success(
                StudentPlanResource::collection($studentPlans),
                'تم تسجيل التحاق الطلاب بالخطة بنجاح',
                201
            );
        }
    }
}
