<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentPlan\CloseStudentPlanRequest;
use App\Http\Requests\StudentPlan\EnrollStudentPlanRequest;
use App\Http\Requests\StudentPlan\MoveLevelRequest;
use App\Http\Requests\StudentPlan\UpdateStudentPlanRequest;
use App\Http\Resources\StudentPlanResource;
use App\Models\Level;
use App\Models\Plan;
use App\Models\StudentPlan;
use Illuminate\Http\Request;

class StudentPlanController extends Controller
{
    /**
     * جلب جميع سجلات التحاق الطلاب بالخطط
     */
    public function index(Request $request)
    {
        $query = StudentPlan::query()
            ->with(['student', 'plan', 'currentLevel']);

        if ($request->filled('student_id')) {
            $query->byStudent($request->integer('student_id'));
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->integer('plan_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $studentPlans = $query->latest('from_date')->paginate($request->integer('per_page', 15));

        return $this->success(
            StudentPlanResource::collection($studentPlans),
            'بيانات التحاق الطلاب بالخطط'
        );
    }

    /**
     * التحاق عدة طلاب بخطة جديدة (يمكن أن يكون لديهم أكثر من خطة بنفس الوقت)
     */
    public function store(EnrollStudentPlanRequest $request)
    {
        $validated = $request->validated();

        $startingLevelId = $validated['starting_level_id']
            ?? Level::where('plan_id', $validated['plan_id'])->orderBy('order')->value('id');

        $plan = Plan::findOrFail($validated['plan_id']);

        // is_main تُستنتج تلقائياً: تكون رئيسية إذا كانت الخطة من نوع رئيسية ونشطة
        $isMain = $plan->type === \App\Enums\PlanType::Main && $plan->is_active;
        $studentPlans = collect();

        foreach ($validated['student_ids'] as $studentId) {
            // هل هذه أول خطة نشطة للطالب؟ تصبح رئيسية تلقائياً حتى لو لم تكن الخطة الرئيسية الفعالة
            $hasOtherActivePlans = StudentPlan::where('student_id', $studentId)
                ->active()
                ->exists();

            $shouldBeMain = $isMain || !$hasOtherActivePlans;

            $studentPlan = StudentPlan::create([
                'student_id'        => $studentId,
                'plan_id'           => $validated['plan_id'],
                'starting_level_id' => $startingLevelId,
                'current_level_id'  => $startingLevelId,
                'from_date'         => $validated['from_date'],
                'is_main'           => $shouldBeMain,
                'status'            => 'active',
                'notes'             => $validated['notes'] ?? null,
            ]);

            // لو طُلبت كرئيسية صراحة، ألغِ الرئيسية عن باقي الخطط النشطة
            if ($shouldBeMain) {
                $studentPlan->setAsMain();
            }

            $studentPlans->push($studentPlan);
        }

        // $studentPlans->load(['student', 'plan', 'currentLevel', 'startingLevel']);

        return $this->success(
            StudentPlanResource::collection($studentPlans),
            'تم تسجيل التحاق الطلاب بالخطة بنجاح',
            201
        );
    }

    /**
     * تعيين خطة كرئيسية للطالب
     */
    public function setMain(StudentPlan $studentPlan)
    {
        if (!is_null($studentPlan->to_date)) {
            return $this->error('لا يمكن تعيين خطة غير نشطة كرئيسية', 422);
        }

        $studentPlan->setAsMain();
        $studentPlan->load(['student', 'plan', 'currentLevel']);

        return $this->success(
            new StudentPlanResource($studentPlan),
            'تم تعيين الخطة كرئيسية بنجاح'
        );
    }

    /**
     * عرض سجل التحاق واحد
     */
    public function show(StudentPlan $studentPlan)
    {
        $studentPlan->load([
            'student',
            'plan',
            'startingLevel',
            'currentLevel',
        ]);

        return $this->success(
            new StudentPlanResource($studentPlan),
            'بيانات التحاق الطالب'
        );
    }

    /**
     * تعديل بيانات التحاق طالب بخطة
     */
    public function update(UpdateStudentPlanRequest $request, StudentPlan $plan_student)
    {
        $validated = $request->validated();

        // لو تم تعديل is_main إلى true، ألغِ الرئيسية عن باقي الخطط النشطة
        if (isset($validated['is_main']) && $validated['is_main']) {
            $plan_student->setAsMain();
        }

        // تحديث البيانات
        $plan_student->update($validated);

        $plan_student->load(['student', 'plan', 'currentLevel', 'startingLevel']);

        return $this->success(
            new StudentPlanResource($plan_student),
            'تم تعديل بيانات التحاق بنجاح'
        );
    }

    /**
     * نقل الطالب لمستوى جديد داخل نفس الخطة
     */
    public function moveLevel(MoveLevelRequest $request, StudentPlan $studentPlan)
    {
        if (!is_null($studentPlan->to_date)) {
            return $this->error('لا يمكن نقل مستوى لخطة غير نشطة', 422);
        }

        $studentPlan->moveToLevel(
            $request->validated('level_id'),
            $request->validated('date'),
            $request->validated('notes')
        );

        $studentPlan->load(['currentLevel']);

        return $this->success(
            new StudentPlanResource($studentPlan),
            'تم نقل الطالب للمستوى الجديد بنجاح'
        );
    }

    /**
     * إغلاق خطة الطالب (إكمال / انتقال / انقطاع)
     */
    public function close(CloseStudentPlanRequest $request, StudentPlan $studentPlan)
    {
        if (!is_null($studentPlan->to_date)) {
            return $this->error('هذه الخطة مغلقة بالفعل', 422);
        }

        $studentPlan->closePlan(
            \App\Enums\StudentPlanStatus::from($request->validated('status')),
            $request->validated('date'),
            $request->validated('notes')
        );

        $studentPlan->load(['student', 'plan', 'currentLevel']);

        return $this->success(
            new StudentPlanResource($studentPlan),
            'تم إغلاق خطة الطالب بنجاح'
        );
    }

    /**
     * سجل تاريخ التحاق طالب معين بالخطط (تاريخ كامل)
     */
    public function studentHistory(int $studentId)
    {
        $studentPlans = StudentPlan::byStudent($studentId)
            ->with(['plan', 'startingLevel', 'currentLevel'])
            ->orderByDesc('from_date')
            ->get();

        return $this->success(
            StudentPlanResource::collection($studentPlans),
            'تاريخ التحاق الطالب بالخطط'
        );
    }

    /**
     * جلب الطلاب الملتحقين بخطة معينة
     */
    public function getStudentsByPlan(Request $request, int $planId)
    {
        $query = StudentPlan::where('plan_id', $planId)
            ->with(['student', 'currentLevel', 'startingLevel']);

        $q = $this->applyFilters($query, [
            'searchColumns' => ['student.name'],
            'orderColumn' => 'from_date',
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

        $studentPlans = $query->latest('from_date')->paginate($request->integer('per_page', 15));

        return $this->successWithPagination(
            StudentPlanResource::collection($studentPlans),
            $q,
            'طلاب الخطة'
        );
    }

    public function destroy(StudentPlan $plan_student)
    {
        $plan_student->delete();

        return $this->success(null, 'تم حذف خطة الطالب بنجاح');
    }
}
