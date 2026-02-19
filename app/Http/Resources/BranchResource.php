<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'max_replacement_limit' => $this->max_replacement_limit,
            'min_replacement_limit' => $this->min_replacement_limit,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        // إضافة المناطق فقط إذا كانت محملة
        if ($this->relationLoaded('regions') && $this->regions->isNotEmpty()) {
            $data['regions'] = $this->regions->map(function ($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->name,
                    // أضف حقول المنطقة الأخرى حسب الحاجة
                ];
            });
        }

        // إضافة عدد المناطق (إذا طلب)
        if ($request->boolean('with_regions_count')) {
            $data['regions_count'] = $this->when($this->regions_count !== null, $this->regions_count);
        }

        return $data;
    }
}
