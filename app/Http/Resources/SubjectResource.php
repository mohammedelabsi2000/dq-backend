<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'track_id'    => $this->track_id,
            'name'        => $this->name,
            'description' => $this->description,
            'created_at'  => $this->created_at?->toDateTimeString(),

            'track'       => new TrackResource($this->whenLoaded('track')),
        ];
    }
}
