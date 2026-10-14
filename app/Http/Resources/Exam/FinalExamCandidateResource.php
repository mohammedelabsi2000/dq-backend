<?php

namespace App\Http\Resources\Exam;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * طالب مرشح للاختبار النهائي في مادة (StudentSubject اجتاز الاختبار التجريبي).
 * يُستخدم لقائمة المرشحين ولقائمة طلاب اللجنة.
 */
class FinalExamCandidateResource extends JsonResource
{
    public function toArray($request)
    {
        $student = $this->student;
        $mosque = $student?->mosque;
        $region = $mosque?->region;
        $trialExam = $this->latestTrialExam;
        $assignment = $this->activeCommitteeAssignment;
        $finalExam = $this->relationLoaded('finalExams') ? $this->finalExams->sortByDesc('attempt_number')->first() : null;

        return [
            // المعرّف المستخدم عند التنسيب للجنة
            'student_subject_id' => $this->id,

            'student' => [
                'id' => $student?->id,
                'full_name' => $student?->full_name,
                'identity' => $student?->identity,
                'gender' => $student?->gender?->value,
            ],

            'subject' => $this->subject?->only(['id', 'title']),
            'plan' => $this->level?->plan?->only(['id', 'name']),
            'level' => $this->level?->only(['id', 'name']),

            'branch' => $region?->branch?->only(['id', 'name']),
            'region' => $region?->only(['id', 'name']),
            'mosque' => $mosque?->only(['id', 'name']),

            // نتيجة الاختبار التجريبي التي رشّحته
            'trial_exam' => $trialExam ? [
                'id' => $trialExam->id,
                'mark' => $trialExam->mark,
                'success_mark' => $trialExam->success_mark,
                'attempt_number' => $trialExam->attempt_number,
                'exam_date' => $trialExam->exam_date?->format('Y-m-d'),
            ] : null,

            // اللجنة المنسَّب لها وتنتظر نتيجته (null لو لم يُنسَّب، أو وصلت نتيجته)
            'committee' => $assignment?->committee?->only(['id', 'name']),

            // نتيجة الاختبار النهائي كما وصلت من الجودة (في قائمة طلاب اللجنة فقط)
            'final_exam' => $this->when($this->relationLoaded('finalExams'), fn() => $finalExam ? [
                'id' => $finalExam->id,
                'mark' => $finalExam->mark,
                'success_mark' => $finalExam->success_mark,
                'result' => $finalExam->result,
                'attempt_number' => $finalExam->attempt_number,
                'exam_date' => $finalExam->exam_date?->format('Y-m-d'),
                'notes' => $finalExam->notes,
            ] : null),
        ];
    }
}
