<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssignmentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlanStudentResource;
use App\Models\Plan;
use App\Models\PlanAssignment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class PlanStudentController extends Controller
{

    // GET: عرض الطلاب المسندين للخطة
    public function index($plan_id)
    {
        $plan = Plan::findOrFail($plan_id);

        $students = $plan->assignments()->with('student')->paginate(50);

        return $this->apiResponse(
            [
                'plan' => $plan,
                'students' => PlanStudentResource::collection($students)
            ],
            'تم جلب الطلاب المسندين بنجاح',
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
            return $this->apiResponse(
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

        return $this->apiResponse(
            new PlanStudentResource($assignment),
            'تم إسناد الطالب بنجاح',
            201
        );
    }
}
