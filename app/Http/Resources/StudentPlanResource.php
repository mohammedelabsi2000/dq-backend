<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentPlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),

            'plan_id' => $this->plan_id,
            'plan' => new PlanResource($this->whenLoaded('plan')),

            'starting_level_id' => $this->starting_level_id,
            'starting_level' => new LevelResource($this->whenLoaded('startingLevel')),

            'current_level_id' => $this->current_level_id,
            'current_level' => new LevelResource($this->whenLoaded('currentLevel')),

            'from_date' => $this->from_date?->format('Y-m-d'),
            'to_date' => $this->to_date?->format('Y-m-d'),

            'is_main' => $this->is_main,

            'status' => $this->status,
            'status_label' => $this->status?->getLabel(),

            'is_active' => is_null($this->to_date),

            'notes' => $this->notes,

            'level_history' => StudentLevelHistoryResource::collection($this->whenLoaded('levelHistory')),
        ];
    }
}