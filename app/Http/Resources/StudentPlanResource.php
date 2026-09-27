<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentPlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'plan' => new PlanResource($this->whenLoaded('plan')),

            /* 'current_level' => new LevelResource(
                $this->whenLoaded('currentLevel')
            ), */
            'current_level' => $this->when(
                $this->current_level_id,
                fn() => new LevelResource($this->currentLevel)
            ),

            'current_level_id' => $this->current_level_id,

            // بيانات التحاق الطالب بالخطة
            'id' => $this->id,
            'is_main' => $this->is_main,
            'status' => $this->status,
            'from_date' => $this->from_date ? $this->from_date->format('Y-m-d') : null,
            'to_date' => $this->to_date ? $this->to_date->format('Y-m-d') : null,
            'notes' => $this->notes,
        ];

        return [
            'student' => new StudentResource($this->resource),
            // ...(new StudentResource($this->resource))->toArray($request),
            'student_plans' => $this->whenLoaded('plans', function () use ($request) {
                return $this->plans->map(function ($plan) use ($request) {
                    $studentPlan = $plan->pivot;
                    return [
                        'plan' => new PlanResource($plan),
                        'is_main' => $studentPlan->is_main,
                        'status' => $studentPlan->status,
                        'starting_level_id' => $studentPlan->starting_level_id,
                        'current_level_id' => $studentPlan->current_level_id,
                        // 'current_level' => new LevelResource($studentPlan->currentLevel),
                        'from_date' => $studentPlan->from_date,
                        'to_date' => $studentPlan->to_date,
                        'notes' => $studentPlan->notes,
                    ];
                });
            }),
            'student_levels' => $this->whenLoaded('levels', function () use ($request) {
                return $this->levels->map(function ($level) use ($request) {
                    $studentLevel = $level->pivot;
                    return [
                        'level' => new LevelResource($level),
                        'from_date' => $studentLevel->from_date,
                        'to_date' => $studentLevel->to_date,
                        'notes' => $studentLevel->notes,
                    ];
                });
            }),
        ];

        return [
            'id' => $this->id,
            'plan_id' => $this->plan_id,
            'student_id' => $this->student_id,
            'assignment_type' => $this->assignment_type?->label(),
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