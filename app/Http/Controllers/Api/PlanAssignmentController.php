<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanAssignmentResource;
use App\Models\Plan;
use App\Models\Student;
use App\Models\PlanAssignment;
use Illuminate\Http\Request;

class PlanAssignmentController extends Controller
{

    /**
     * عرض الطلاب مع الفلاتر لإسنادهم لخطة
     */
    public function students(Plan $plan, Request $request)
    {
        $students = Student::query();

        // فلترة حسب الفرع
        if ($request->branch_id) {
            $students->where('branch_id', $request->branch_id);
        }

        // فلترة حسب المنطقة
        if ($request->region_id) {
            $students->where('region_id', $request->region_id);
        }

        // فلترة حسب العمر
        if ($request->min_age || $request->max_age) {
            $today = now();

            if ($request->min_age) {
                $students->where('dob', '<=', $today->copy()->subYears($request->min_age));
            }

            if ($request->max_age) {
                $students->where('dob', '>=', $today->copy()->subYears($request->max_age));
            }
        }

        // فلترة حسب آخر إنجاز
        if ($request->last_memorized) {
            $students->where('last_memorized', '>=', $request->last_memorized);
        }

        // بحث عام
        if ($request->search) {
            $students->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('id', 'like', '%' . $request->search . '%');
            });
        }

        $students = $students->paginate(50);

        return $this->apiResponse(
            [
                'plan_id' => $plan->id,
                'students' => $students
            ],
            'تم جلب الطلاب بنجاح',
            200
        );
    }

    /**
     * إسناد الطلاب للخطة
     */
    public function assign(Request $request, Plan $plan)
    {
        $request->validate([
            'students' => 'required|array',
            'students.*' => 'exists:students,id'
        ]);

        $assigned = [];
        foreach ($request->students as $studentId) {
            $assignment = PlanAssignment::updateOrCreate(
                [
                    'plan_id' => $plan->id,
                    'student_id' => $studentId
                ],
                [
                    'assignment_type' => 'manual',
                    'criteria' => null
                ]
            );

            $assigned[] = $assignment;
        }

        return $this->apiResponse(
            PlanAssignmentResource::collection(collect($assigned)->load('student')),
            'تم إسناد الطلاب للخطة بنجاح',
            200
        );
    }
}
