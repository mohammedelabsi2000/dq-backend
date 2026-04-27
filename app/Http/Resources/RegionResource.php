<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
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
            'id'            => $this->id,
            'name'          => $this->name,
            'notes'         => $this->notes,
            'branch'        => new BranchResource($this->branch),
            'mosques_count' => $this->whenCounted('mosques'),
            'mosques'       => MosqueResource::collection($this->whenLoaded('mosques')),
        ];
    }
}
