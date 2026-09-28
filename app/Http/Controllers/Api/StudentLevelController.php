<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LevelResource;
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
        if ($level->plan_id !== $plan->id) {
            return $this->error('هذا المستوى لا ينتمي لهذه الخطة', 404);
        }
        
        // 2. التأكد أن الطالب مسجل حالياً (نشط) في هذه الخطة
        $studentPlan = StudentPlan::byStudent($student->id)
            ->where('plan_id', $plan->id)
            ->active()
            ->first();

        if (!$studentPlan) {
            return $this->error('الطالب غير مسجل حالياً في هذه الخطة', 404);
        }

        // 3. تحميل المستوى مع مساراته ومواد كل مسار
        $level->load(['levelTracks.track', 'levelTracks.subjects']);

        // 4. جلب سجلات الطالب في مواد هذا المستوى، مفهرسة حسب subject_id
        $studentSubjects = StudentSubject::where('student_id', $student->id)
            ->where('level_id', $level->id)
            ->with('resultStatus')
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
            new LevelResource($level),
            'تم جلب بيانات مستوى الطالب بنجاح',
            200
        );
    }
}