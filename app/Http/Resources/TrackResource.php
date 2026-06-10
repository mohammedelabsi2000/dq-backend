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
            // مساقات المسار (تُملأ من المستوى عبر LevelResource)
            'subjects'      => SubjectResource::collection($this->whenLoaded('subjects')),
            // 'created_at'    => $this->created_at?->toDateTimeString(),

            // 'subjects'      => SubjectResource::collection($this->whenLoaded('subjects')),
            // 'subjects_count' => $this->when(
            //     isset($this->subjects_count),
            //     $this->subjects_count
            // ),
        ];
    }
}
