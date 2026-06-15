<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanTrackSubjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->id,
            'subject_name' => $this->name,
            'is_required' => $this->pivot?->is_required ?? false,
            'order' => $this->pivot?->order ?? 1,
        ];
    }
}
