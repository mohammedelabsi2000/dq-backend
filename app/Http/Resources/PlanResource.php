<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            // 'period_unit' => $this->period_unit->value,
            'period_unit' => $this->period_unit,
            'period_unit_label' => $this->period_unit?->label(),
            'period' => $this->period,
            'min_period' => $this->min_period,
            'max_period' => $this->max_period,
            'tolerance' => $this->tolerance,
            'is_active' => $this->is_active,
            'type' => $this->type,
            'type_label' => $this->type?->label(),
            'age_from' => $this->age_from,
            'age_to' => $this->age_to,

            // يُحمَّل فقط إذا كان موجوداً في الـ eager load
            'levels' => LevelResource::collection($this->whenLoaded('levels')),
            'levels_count' => $this->when(
                isset($this->levels_count),
                $this->levels_count
            ),
        ];
    }
}
