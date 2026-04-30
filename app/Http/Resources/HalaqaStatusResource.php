<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HalaqaStatusResource extends JsonResource
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
            'status_type' => $this->statusType ? new ConstantResource($this->statusType) : null,
            'sponsorship_type' => $this->sponsorshipType ? new ConstantResource($this->sponsorshipType) : null,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'notes' => $this->notes,
        ];
    }
}
