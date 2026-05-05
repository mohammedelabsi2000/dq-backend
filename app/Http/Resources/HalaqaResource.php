<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StudentResource;

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
            'region' => $this->whenLoaded('reference', function () {
                return $this->reference instanceof \App\Models\Region ? new RegionResource($this->reference) : null;
            }),
            'center' => $this->whenLoaded('reference', function () {
                return $this->reference instanceof \App\Models\Center ? new CenterResource($this->reference) : null;
            }),
            'students' => StudentResource::collection(
                $this->whenLoaded('students')
            ),
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,

            /*
            |--------------------------------------------------------------------------
            | Supervisor and Students Count
            |--------------------------------------------------------------------------
            */
            'supervisors' => $this->whenLoaded(
                'supervisors',
                fn() => $this->supervisors->first()?->user
            ),
            'students_count' => $this->when(
                $this->relationLoaded('studentEnrollments') || $this->relationLoaded('students'),
                fn() => $this->studentsCount()
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
