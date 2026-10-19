<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentLevelResource extends JsonResource
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
            'level_id' => $this->level_id,
            'level' => new LevelResource(
                $this->whenLoaded('level')
            ),
            'from_date' => $this->from_date?->format('Y-m-d'),
            'to_date' => $this->to_date?->format('Y-m-d'),
            'notes' => $this->notes,

            // جميع المواد في المستوى
            'all_subjects' => SubjectResource::collection(
                $this->whenLoaded('level.subjects')
            ),

            // المواد الخاصة بالطالب مع نسبة الإنجاز
            'student_subjects' => StudentSubjectResource::collection(
                $this->whenLoaded('studentSubjects')
            ),

            'completion_percentage' => $this->calculateCompletionPercentage(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    /**
     * حساب نسبة إنجاز الطالب في المواد
     */
    private function calculateCompletionPercentage(): float
    {
        if (!$this->relationLoaded('studentSubjects') || $this->studentSubjects->isEmpty()) {
            return 0.0;
        }

        $totalSubjects = $this->studentSubjects->count();
        $completedSubjects = $this->studentSubjects
            ->where('result_status_id', '!=', null)
            ->count();

        return $totalSubjects > 0 ? round(($completedSubjects / $totalSubjects) * 100, 2) : 0.0;
    }
}
