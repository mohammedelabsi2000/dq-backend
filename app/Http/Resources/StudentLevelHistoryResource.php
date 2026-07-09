<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentLevelHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'level_id' => $this->level_id,
            'level' => new LevelResource($this->whenLoaded('level')),

            'from_date' => $this->from_date?->format('Y-m-d'),
            'to_date' => $this->to_date?->format('Y-m-d'),

            'is_current' => is_null($this->to_date),

            'notes' => $this->notes,
        ];
    }
}