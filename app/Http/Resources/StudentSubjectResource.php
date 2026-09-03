<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentSubjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'student_id' => $this->student_id,
            'student' => new StudentResource(
                $this->whenLoaded('student')
            ),

            'subject_id' => $this->subject_id,
            'subject' => new SubjectResource(
                $this->whenLoaded('subject')
            ),

            'level_id' => $this->level_id,
            'level' => new LevelResource(
                $this->whenLoaded('level')
            ),

            'result_status_id' => $this->result_status_id,
            'result_status' => new ConstantResource(
                $this->whenLoaded('resultStatus')
            ),

            'grade' => $this->grade,

            'from_date' => $this->from_date?->format('Y-m-d'),
            'to_date' => $this->to_date?->format('Y-m-d'),
            'grade_date' => $this->grade_date?->format('Y-m-d'),

            'teacher_id' => $this->teacher_id,
            'teacher' => $this->whenLoaded('teacher'),

            'notes' => $this->notes,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
