<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentLevelDetailsResource;
use App\Http\Resources\StudentSubjectResource;
use App\Models\Level;
use App\Models\Plan;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;

class StudentLevelController extends Controller
{
    public function levelSubjects(Student $student, Plan $plan, Level $level)
    {
        /* $studentSubjects = Subject::whereHas('studentSubjects', function ($query) use ($student, $plan, $level) {
            $query->where('student_id', $student->id)
                // ->where('plan_id', $plan->id)
                ->where('level_id', $level->id);
        })
            ->with([
                'studentSubjects' => function ($query) use ($student, $plan, $level) {
                    $query->where('student_id', $student->id)
                        // ->where('plan_id', $plan->id)
                        ->where('level_id', $level->id);
                }
            ])
            ->get(); */
        $studentSubjects = StudentSubject::where('student_id', $student->id)
            ->where('level_id', $level->id)
            ->with(['subject', 'level'])
            ->get();
            
        return $this->success(
            StudentSubjectResource::collection($studentSubjects),
            'تم جلب بيانات مستوى الطالب بنجاح',
            200
        );

    }
}
