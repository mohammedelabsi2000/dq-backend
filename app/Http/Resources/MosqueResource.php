<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MosqueResource extends JsonResource
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
            'region'        => new RegionResource($this->whenLoaded('region')),
            'centers_count' => $this->whenCounted('centers'),
            'centers'       => CenterResource::collection($this->whenLoaded('centers')),
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
