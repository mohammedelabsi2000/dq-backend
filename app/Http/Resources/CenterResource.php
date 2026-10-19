<?php

namespace App\Http\Resources;

use App\Models\Mosque;
use App\Models\Region;
use App\Support\LookupCache;
use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // إن لم تكن المنطقة/المسجد محمّلين مسبقاً يُجلبان مرة واحدة لكل طلب بدل استعلام لكل صف
        $lookup = app(LookupCache::class);
        $region = $this->relationLoaded('region') ? $this->region : $lookup->find(Region::class, $this->region_id);
        $mosque = $this->relationLoaded('mosque') ? $this->mosque : $lookup->find(Mosque::class, $this->mosque_id);

        return [
            'id' => $this->id,
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
            'name' => $this->name,
            'notes' => $this->notes,

            'region' => new RegionResource($region),

            'mosque' => $mosque
                ? new MosqueResource($mosque)
                : null,

            'gender' => $this->gender,
        ];
    }
}
