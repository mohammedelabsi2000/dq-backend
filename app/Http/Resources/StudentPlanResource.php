<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentPlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'plan' => new PlanResource($this),

            'students' => StudentResource::collection($this->students),

            // 'student_ids' => $this->students->pluck('id'),

        ];
    }
}