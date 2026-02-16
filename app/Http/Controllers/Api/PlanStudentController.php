<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanAssignment;
use Illuminate\Http\Request;

class PlanStudentController extends Controller
{
    // GET: عرض الطلاب المسندين للخطة
    public function index($plan_id)
    {
        $plan = Plan::findOrFail($plan_id);

        $students = $plan->assignments()->with('student')->paginate(50);

        return response()->json([
            'plan' => $plan,
            'students' => $students
        ]);
    }

    // POST: اضافة/اسناد طالب لخطة
    public function assignStudent(Request $request, $plan_id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $plan = Plan::findOrFail($plan_id);

        // تحقق إذا الطالب مسند بالفعل
        $exists = PlanAssignment::where('plan_id', $plan->id)
            ->where('student_id', $request->student_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Student already assigned to this plan.'
            ], 409);
        }

        $assignment = PlanAssignment::create([
            'plan_id' => $plan->id,
            'student_id' => $request->student_id,
            'assignment_type' => $request->input('assignment_type', 'manual'), // قيمة افتراضية

        ]);

        return response()->json([
            'message' => 'Student assigned successfully.',
            'assignment' => $assignment
        ], 201);
    }
}
