<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->toDateTimeString(),

            'courses'       => CourseResource::collection($this->whenLoaded('courses')),
            'courses_count' => $this->when(
                isset($this->courses_count),
                $this->courses_count
            ),
        ];
    }
}
