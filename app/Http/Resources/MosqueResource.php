<?php

namespace App\Http\Resources;

use App\Models\Region;
use App\Support\LookupCache;
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
            'id' => $this->id,
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
            'name' => $this->name,
            'notes' => $this->notes,
            // 'region'        => new RegionResource($this->region),
            // إن لم تكن المنطقة محمّلة مسبقاً تُجلب مرة واحدة لكل طلب بدل استعلام لكل صف
            'region' => new RegionResource(
                $this->relationLoaded('region')
                    ? $this->region
                    : app(LookupCache::class)->find(Region::class, $this->region_id)
            ),
            'centers_count' => $this->whenCounted('centers'),
            'centers' => CenterResource::collection($this->whenLoaded('centers')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
