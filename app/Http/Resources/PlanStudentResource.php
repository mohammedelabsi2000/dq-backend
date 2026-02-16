<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'plan_id' => $this->plan_id,
            'student_id' => $this->student_id,
            'assignment_type' => $this->assignment_type,
            'criteria' => $this->criteria,

            // بيانات الطالب المرتبط
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->name,
                    'dob' => $this->student->dob,
                    'branch_id' => $this->student->branch_id,
                    'region_id' => $this->student->region_id,
                    'last_memorized' => $this->student->last_memorized,
                ];
            }),

            // التواريخ
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // روابط مفيدة
            'links' => [
                'self' => url("/api/plan-assignments/{$this->id}"),
                'plan' => url("/api/plans/{$this->plan_id}"),
                'student' => url("/api/students/{$this->student_id}"),
            ],
        ];
    }
}
