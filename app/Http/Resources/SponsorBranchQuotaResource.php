<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SponsorBranchQuotaResource extends JsonResource
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
            'sponsor_id' => $this->sponsor_id,
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'gender' => $this->gender?->value,
            'quota' => $this->quota,
            'remaining' => $this->when(
                $this->relationLoaded('sponsor'),
                fn () => $this->sponsor->remainingCapacityForBranch($this->branch_id, $this->gender?->value)
            ),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
