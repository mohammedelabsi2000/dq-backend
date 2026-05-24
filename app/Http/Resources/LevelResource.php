<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'plan_id'      => $this->plan_id,
            'name'         => $this->name,
            'order'        => $this->order,
            'duration'     => $this->duration,
            'max_duration' => $this->max_duration,
            'min_duration' => $this->min_duration,
            'duration_unit' => $this->duration_unit,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at?->toDateTimeString(),

            'plan'         => new PlanResource($this->whenLoaded('plan')),
            'level_tracks' => LevelTrackResource::collection($this->whenLoaded('levelTracks')),
        ];
    }
}
