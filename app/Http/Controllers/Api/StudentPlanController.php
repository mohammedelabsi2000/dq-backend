<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssignmentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Http\Resources\StudentPlanDetailsResource;
use App\Http\Resources\StudentPlanResource;
use App\Http\Resources\StudentPlansResource;
use App\Models\Plan;
use App\Models\PlanAssignment;
use App\Models\Student;
use App\Models\StudentPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class StudentPlanController extends Controller
{

    /**
     * 
     */

    /* public function studentPlans(int $studentId)
    {
        $studentPlans = StudentPlan::byStudent($studentId)
            ->with(['plan', 'currentLevel', 'startingLevel'])
            ->orderByDesc('from_date')
            ->get();

        return $this->success(
            StudentPlanResource::collection($studentPlans),
            'خطط الطالب'
        );
    } */




    // GET: عرض الطلاب المسندين للخطة
    public function plans(Student $student)
    {
        $this->authorize('view', $student);

        $student = $student->load([
            'studentPlans.plan',
            'studentPlans.currentLevel',
        ]);

        return $this->success(
            new StudentPlansResource($student),
            'تم جلب خطط الطالب بنجاح',
            200
        );

        /* $plan = Plan::findOrFail($plan_id);

        $students = $plan->assignments()->with('student')->paginate(50);

        return $this->success(
            [
                'plan' => $plan,
                'students' => StudentPlanResource::collection($students)
            ],
            'تم جلب الطلاب المسندين بنجاح',
            200
        ); */
    }

    public function planLevels(Student $student, Plan $plan)
    {
        $this->authorize('view', $student);

        $student = $student->load(
            [
                'studentPlans' => function ($query) use ($plan) {
                    // if ($plan) {
                    $query->where('plan_id', $plan->id);
                    // }
                    $query->with([
                        // 'plan',
                        'currentLevel',
                    ]);
                },

                'studentLevels' => function ($query) use ($plan) {
                    // if ($plan) {
                    $query->whereHas('level', function ($query) use ($plan) {
                        $query->where('plan_id', $plan->id);
                    });
                    // }
        
                    $query->with('level');
                },
            ]
        );

        return $this->success(
            new StudentPlanDetailsResource($student),
            'تم جلب مستويات الطالب في الخطة بنجاح',
            200
        );
    }

    public function unrelatedPlans(Student $student)
    {
        $this->authorize('view', $student);

        $unrelatedPlans = Plan::whereDoesntHave('studentPlans', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })->get();

        return $this->success(
            PlanResource::collection($unrelatedPlans),
            'تم جلب الخطط غير المسندة للطالب بنجاح',
            200
        );
    }

    // POST: إضافة/إسناد طالب للخطة
    public function assignStudent(Request $request, $plan_id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'assignment_type' => ['nullable', 'string', new Enum(AssignmentType::class)]
        ]);

        $plan = Plan::findOrFail($plan_id);

        // تحقق إذا الطالب مسند بالفعل
        $exists = PlanAssignment::where('plan_id', $plan->id)
            ->where('student_id', $request->student_id)
            ->exists();

        if ($exists) {
            return $this->success(
                null,
                'الطالب مسند مسبقاً لهذه الخطة',
                409
            );
        }

        $assignment = PlanAssignment::create([
            'plan_id' => $plan->id,
            'student_id' => $request->student_id,
            'assignment_type' => $request->input('assignment_type', AssignmentType::Manual),
        ]);

        $assignment->load('student');

        return $this->success(
            new StudentPlanResource($assignment),
            'تم إسناد الطالب بنجاح',
            201
        );
    }
}
