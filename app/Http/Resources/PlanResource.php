<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'duration'    => $this->duration,
            'duration_unit' => $this->duration_unit,
            'is_active'   => $this->is_active,
            'tolerance'   => $this->tolerance,
            'created_at'  => $this->created_at?->toDateTimeString(),

            // يُحمَّل فقط إذا كان موجوداً في الـ eager load
            'levels'      => LevelResource::collection($this->whenLoaded('levels')),
            'levels_count' => $this->when(
                isset($this->levels_count),
                $this->levels_count
            ),
        ];
    }
}
