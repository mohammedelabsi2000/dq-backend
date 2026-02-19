<?php

namespace App\Http\Resources;

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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'mosque_id' => $this->mosque_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        // إضافة بيانات المسجد فقط إذا كانت محملة
        if ($this->relationLoaded('mosque') && $this->mosque) {
            $data['mosque'] = [
                'id' => $this->mosque->id,
                'name' => $this->mosque->name,
            ];
        }

        // إضافة عدد الحلقات (إذا طلب)
        if ($request->boolean('with_halaqat_count')) {
            $data['halaqat_count'] = $this->when($this->halaqat_count !== null, $this->halaqat_count);
        }

        // إضافة الحلقات (إذا طلب)
        if ($request->boolean('with_halaqat') && $this->relationLoaded('halaqat')) {
            // إذا كان لديك HalaqaResource استخدمه، وإلا استخدم مصفوفة بسيطة
            $data['halaqat'] = $this->halaqat->map(function ($halaqa) {
                return [
                    'id' => $halaqa->id,
                    'name' => $halaqa->name,
                    // أضف الحقول الأخرى حسب الحاجة
                ];
            });
        }

        return $data;
    }
}
