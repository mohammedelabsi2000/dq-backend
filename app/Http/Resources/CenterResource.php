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
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'notes'          => $this->notes,

            // ✅ region مباشر على الـ center
            'region'         => $this->whenLoaded('region', fn() => [
                'id'     => $this->region->id,
                'name'   => $this->region->name,
                'branch' => $this->region->relationLoaded('branch') ? [
                    'id'   => $this->region->branch->id,
                    'name' => $this->region->branch->name,
                ] : null,
            ]),

            // ✅ mosque اختياري (nullable)
            'mosque'         => $this->whenLoaded('mosque', fn() => $this->mosque ? [
                'id'   => $this->mosque->id,
                'name' => $this->mosque->name,
            ] : null),

            // 'halaqas_count'  => $this->whenCounted('halaqat'),
            'created_at'     => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
