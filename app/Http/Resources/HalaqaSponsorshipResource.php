<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HalaqaSponsorshipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'halaqa_id' => $this->halaqa_id,
            'sponsor_id' => $this->sponsor_id,
            'sponsor' => new SponsorResource($this->whenLoaded('sponsor')),
            'halaqa' => $this->whenLoaded('halaqa', fn () => $this->halaqa->name),
            'from_date' => $this->from_date ? $this->from_date->format('d-m-Y') : null,
            'to_date' => $this->to_date ? $this->to_date->format('d-m-Y') : null,
            'is_active' => is_null($this->to_date),
            'stop_reason' => $this->stop_reason,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
