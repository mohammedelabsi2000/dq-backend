<?php

namespace App\Http\Resources;

use App\Models\Quran\Juz;
use App\Models\Quran\Surah;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // 'track_id'    => $this->track_id,
            'subject_type_id' => $this->subject_type_id,

            'title' => $this->title,
            'sub_title' => $this->sub_title,
            // Numeric arrays stored as JSON in the database, so we decode them before returning
            'juzs' => json_decode($this->juzs),
            'juz_labels' => Juz::find(json_decode($this->juzs))->pluck('name')->toArray(),
            'surahs' => json_decode($this->surahs),
            'surah_labels' => Surah::find(json_decode($this->surahs))->pluck('name_ar')->toArray(),
            'verses' => json_decode($this->verses),
            'pages' => json_decode($this->pages),

            // 'description' => $this->description,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),

            // 'track'       => new TrackResource($this->whenLoaded('track')),
            'subject_type' => new ConstantResource($this->whenLoaded('subjectType')),
        ];
    }
}
