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
            'subject_type_id' => $this->subject_type_id,

            'title'        => $this->title,
            'sub_title'    => $this->sub_title,
            'juzs'         => $this->juzs,
            'surahs'       => $this->surahs,
            'verses'       => $this->verses,
            'pages'        => $this->pages,

            'description' => $this->description,
            'notes'       => $this->notes,
            'created_at'  => $this->created_at?->toDateTimeString(),

            'track'       => new TrackResource($this->whenLoaded('track')),
        ];
    }
}
