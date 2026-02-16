<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Student;
use App\Models\PlanAssignment;
use Illuminate\Http\Request;

class PlanAssignmentController extends Controller
{
    // عرض صفحة اختيار الطلاب مع الفلاتر
    public function create(Plan $plan, Request $request)
    {
        $students = Student::query();

        // فلترة حسب الفرع
        if($request->branch_id){
            $students->where('branch_id', $request->branch_id);
        }

        // فلترة حسب المنطقة
        if($request->region_id){
            $students->where('region_id', $request->region_id);
        }

       // فلترة حسب العمر
if($request->min_age || $request->max_age){
    $today = now();
    if($request->min_age){
        $students->where('dob', '<=', $today->subYears($request->min_age));
    }
    if($request->max_age){
        $students->where('dob', '>=', $today->subYears($request->max_age));
    }
}


        // فلترة حسب آخر إنجاز
        if($request->last_memorized){
            $students->where('last_memorized', '>=', $request->last_memorized);
        }

        // فلترة عامة نصية
        if($request->search){
            $students->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('id','like','%'.$request->search.'%');
            });
        }

        $students = $students->paginate(50);

        return view('plan_assignments.create', compact('plan','students'));
    }

    // حفظ الإسناد
    public function store(Request $request, Plan $plan)
    {
        $request->validate([
            'students' => 'required|array'
        ]);

        foreach($request->students as $studentId){
            PlanAssignment::updateOrCreate(
                ['plan_id'=>$plan->id, 'student_id'=>$studentId],
                ['assignment_type'=>'manual','criteria'=>null]
            );
        }

        return redirect()->route('plans.show',$plan->id)
            ->with('success','تم إسناد الطلاب للخطة بنجاح');
    }
}
