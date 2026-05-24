<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelTrackCourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'level_track_id'  => $this->level_track_id,
            'course_id'       => $this->course_id,
            'is_required'     => $this->is_required,
            'order'           => $this->order,
            'created_at'      => $this->created_at?->toDateTimeString(),

            'level_track'     => new LevelTrackResource($this->whenLoaded('levelTrack')),
            'course'          => new CourseResource($this->whenLoaded('course')),
        ];
    }
}
