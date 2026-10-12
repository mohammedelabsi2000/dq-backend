<?php

namespace App\Http\Resources;

use App\Models\Branch;
use App\Support\LookupCache;
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
            'id'                  => $this->id,
            'standard_region_id'  => $this->standard_region_id,
            'name'                => $this->name,
            'notes'               => $this->notes,
            // إن لم يكن الفرع محمّلاً مسبقاً يُجلب مرة واحدة لكل طلب بدل استعلام لكل صف
            'branch'              => new BranchResource(
                $this->relationLoaded('branch')
                    ? $this->branch
                    : app(LookupCache::class)->find(Branch::class, $this->branch_id)
            ),
            'mosques_count'       => $this->whenCounted('mosques'),
            'mosques'             => MosqueResource::collection($this->whenLoaded('mosques')),
        ];
    }
}
