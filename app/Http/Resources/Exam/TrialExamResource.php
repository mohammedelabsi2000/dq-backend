<?php

namespace App\Http\Resources\Exam;

use App\Http\Resources\StudentResource;
use App\Http\Resources\SubjectResource;
use App\Models\RecitationExam;
use App\Models\TrialExam;
use Illuminate\Http\Resources\Json\JsonResource;

class TrialExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $lastRecitationExam = $this->lastRecitationExam;

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student' => $this->student ? (new StudentResource($this->student))->simple() : null,
            'subject_id' => $this->subject_id,
            'subject' => $this->subject ? (new SubjectResource($this->subject))->simple() : null,
            'student_subject_id' => $this->student_subject_id,
            'attempt_number' => $this->attempt_number,

            'exam_level' => $this->exam_level(),

            'sard_status' => $this->recitation_status(),
            'errors_count' => $lastRecitationExam?->errors_count,
            'alerts_count' => $lastRecitationExam?->alerts_count,
            'errors_count_allowed' => $lastRecitationExam?->errors_count_allowed,
            'alerts_count_allowed' => $lastRecitationExam?->alerts_count_allowed,

            'exam_status' => $this->status(),
            'mark' => $this->mark,
            // تُنسخ من إعدادات المادة عند إنشاء السجل
            'success_mark' => $this->success_mark,

            'exam_failur_counter' => $this->attempt_number - 1,
            'sard_failur_counter' => $lastRecitationExam ? $lastRecitationExam->attempt_number - 1 : null,

            'exam_date' => $this->exam_date?->format('Y-m-d'),
            'notes' => $this->notes,
        ];
    }

    private function recitation_status()
    {
        return $this->lastRecitationExam?->status() ?? RecitationExam::PENDING;
    }

    private function exam_level()
    {
        if ($this->recitation_status() !== RecitationExam::PASSED) {
            return 'السرد التجريبي';
        }

        return $this->status() === TrialExam::PASSED ? 'الاختبار النهائي' : 'الاختبار التجريبي';
    }
}
