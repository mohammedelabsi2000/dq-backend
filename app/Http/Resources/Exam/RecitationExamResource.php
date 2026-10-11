<?php

namespace App\Http\Resources\Exam;

use App\Http\Resources\StudentResource;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RecitationExamResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student' => $this->student ? (new StudentResource($this->student))->simple() : null,
            'subject_id' => $this->subject_id,
            'subject' => $this->subject ? (new SubjectResource($this->subject))->simple() : null,
            'student_subject_id' => $this->student_subject_id,

            'attempt_number' => $this->attempt_number,
            // عدد مرات الرسوب السابقة في السرد
            'sard_failur_counter' => $this->attempt_number - 1,

            'errors_count' => $this->errors_count,
            'alerts_count' => $this->alerts_count,
            // المسموح به: يُنسخ من إعدادات المادة عند إنشاء السجل
            'errors_count_allowed' => $this->errors_count_allowed,
            'alerts_count_allowed' => $this->alerts_count_allowed,

            // pending (بانتظار النتيجة) | passed | failed
            'sard_status' => $this->status(),

            'exam_date' => $this->exam_date?->format('Y-m-d'),
            'notes' => $this->notes,
        ];
    }
}
