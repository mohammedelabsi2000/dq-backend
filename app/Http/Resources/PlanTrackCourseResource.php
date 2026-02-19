<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanTrackCourseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'course_id' => $this->id,
            'course_name' => $this->name,
            'is_required' => $this->pivot?->is_required ?? false,
            'order' => $this->pivot?->order ?? 1,
        ];
    }
}
