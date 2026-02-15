<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanAssignment;
use Illuminate\Http\Request;

class PlanStudentController extends Controller
{
    // عرض الطلاب المسندين للخطة
    public function index(Plan $plan)
    {
        // جلب الطلاب المسندين
        $students = $plan->assignments()->with('student')->paginate(50);

        return view('students.index', compact('plan', 'students'));
    }
}
