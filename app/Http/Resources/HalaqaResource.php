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
        return [
            'id' => $this->id,

            'name' => $this->name,
            'location' => $this->location,
            'description' => $this->description,

            /*
            |--------------------------------------------------------------------------
            | Type (Constant)
            |--------------------------------------------------------------------------
            */
            'type' => new ConstantResource($this->whenLoaded('type')),

            /*
            |--------------------------------------------------------------------------
            | Polymorphic Reference
            |--------------------------------------------------------------------------
            */
            'reference' => $this->whenLoaded('reference', function () {

                return [
                    'type' => class_basename($this->reference_type),

                    'data' => $this->formatReference(),
                ];
            }),
            'students' => StudentResource::collection(
                $this->whenLoaded('students')
            ),

            /*
            |--------------------------------------------------------------------------
            | Meta
            |--------------------------------------------------------------------------
            */
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Summary of formatReference
     * @return CenterResource|RegionResource|null
     */
    private function formatReference()
    {
        if (!$this->reference) {
            return null;
        }

        // لو مرتبط بـ Center
        if ($this->reference instanceof \App\Models\Center) {
            return new CenterResource($this->reference);
        }

        // لو مرتبط بـ Region
        if ($this->reference instanceof \App\Models\Region) {
            return new RegionResource($this->reference);
        }

        return null;
    }
}
