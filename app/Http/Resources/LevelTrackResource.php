<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelTrackResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'level_id' => $this->level_id,
            'track_id' => $this->track_id,
            'order' => $this->order,
            'weight' => (int) $this->weight,
            'created_at' => $this->created_at?->toDateTimeString(),

            'level' => new LevelResource($this->whenLoaded('level')),
            'track' => new TrackResource($this->whenLoaded('track')),
            'level_track_subjects' => LevelTrackSubjectResource::collection($this->whenLoaded('levelTrackSubjects')),
        ];
    }
}
