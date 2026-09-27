<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Level;
use App\Models\Plan;
use App\Models\Student;
use App\Models\StudentPlan;
use App\Models\StudentSubject;

class StudentLevelController extends Controller
{
    public function levelSubjects(Student $student, Plan $plan, Level $level)
    {
        // 1. التأكد أن المستوى تابع لهذه الخطة
        abort_unless(
            $level->plan_id === $plan->id,
            404,
            'هذا المستوى لا ينتمي لهذه الخطة'
        );

        // 2. التأكد أن الطالب مسجل حالياً (نشط) في هذه الخطة
        $studentPlan = StudentPlan::byStudent($student->id)
            ->where('plan_id', $plan->id)
            ->active()
            ->first();

        abort_unless($studentPlan, 404, 'الطالب غير مسجل حالياً في هذه الخطة');

        // 3. تحميل المستوى مع مساراته ومواد كل مسار
        $level->load(['levelTracks.track', 'levelTracks.subjects']);

        // 4. جلب سجلات الطالب في مواد هذا المستوى، مفهرسة حسب subject_id
        $studentSubjects = StudentSubject::where('student_id', $student->id)
            ->where('level_id', $level->id)
            ->get()
            ->keyBy('subject_id');

        // 5. ربط كل مادة ببيانات الطالب المطابقة (إن وجدت) عبر علاقة وهمية
        foreach ($level->levelTracks as $levelTrack) {
            foreach ($levelTrack->subjects as $subject) {
                $subject->setRelation(
                    'currentStudentSubject',
                    $studentSubjects->get($subject->id)
                );
            }
        }

        // 6. تجهيز الخطة لتحتوي فقط على هذا المستوى (بدل جميع مستوياتها)
        $plan->setRelation('levels', collect([$level]));

        return $this->success(
            new PlanResource($plan),
            'تم جلب بيانات مستوى الطالب بنجاح',
            200
        );
    }
}