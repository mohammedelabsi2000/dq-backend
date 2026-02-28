<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HalaqaResource extends JsonResource
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
            'location' => $this->location,
            'description' => $this->description,
            'center_id' => $this->center_id,
            'type_id' => $this->type_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        // إضافة بيانات المركز فقط إذا كانت محملة
        if ($this->relationLoaded('center') && $this->center) {
            $data['center'] = [
                'id' => $this->center->id,
                'name' => $this->center->name,
            ];
        }

        // إضافة بيانات الثابت (Constant) فقط إذا كانت محملة
        if ($this->relationLoaded('constant') && $this->constant) {
            $data['constant'] = [
                'id' => $this->constant->id,
                'name' => $this->constant->name, // افترض أن لديه حقل name
                // أضف حقول الثابت الأخرى حسب الحاجة
            ];
        }

        return $data;
    }
}
