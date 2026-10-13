<?php

namespace App\Http\Resources\Exam;

use App\Enums\Gender;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamCommitteeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,

            // لجنة لعدة مساقات، أو لمساق واحد محدد
            'is_multi_subject' => $this->is_multi_subject,
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn() => $this->subject ? (new SubjectResource($this->subject))->simple() : null),

            'start_date' => $this->start_date?->format('Y-m-d'),

            // الفئة: طلاب أم طالبات
            'gender' => $this->gender?->value,
            'gender_label' => $this->gender === Gender::Male ? 'طلاب' : 'طالبات',

            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch', fn() => $this->branch?->only(['id', 'name'])),
            'region_id' => $this->region_id,
            'region' => $this->whenLoaded('region', fn() => $this->region?->only(['id', 'name'])),

            'proposed_exam_date' => $this->proposed_exam_date?->format('Y-m-d H:i'),
            'exam_location' => $this->exam_location,
            'notes' => $this->notes,

            'students_count' => $this->when(isset($this->committee_students_count), fn() => $this->committee_students_count),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
