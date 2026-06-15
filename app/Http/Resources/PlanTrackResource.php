<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanTrackResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'track_id' => $this->track_id,
            'track_name' => $this->track?->name,
            'is_required' => $this->is_required,
            'weight' => $this->weight,

            'subjects' => $this->whenLoaded('subjects', function () {
                return PlanTrackSubjectResource::collection($this->subjects);
            }),
        ];
    }
}
