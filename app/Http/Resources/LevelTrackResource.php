<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelTrackResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'track_id'   => $this->track_id,
            'track_name' => $this->track?->name,
            'weight'     => $this->weight,
            'order'      => $this->order,
            'subjects'   => SubjectResource::collection($this->whenLoaded('subjects')),
        ];
    }
}