<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelTrackSubjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'level_track_id'  => $this->level_track_id,
            'subject_id'      => $this->subject_id,
            'is_required'     => $this->is_required,
            'order'           => $this->order,
            'created_at'      => $this->created_at?->toDateTimeString(),

            'level_track'     => new LevelTrackResource($this->whenLoaded('levelTrack')),
            'subject'         => new SubjectResource($this->whenLoaded('subject')),
        ];
    }
}
