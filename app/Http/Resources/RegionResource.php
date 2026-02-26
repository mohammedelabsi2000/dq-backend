<?php

namespace App\Http\Resources;

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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'branch_id' => $this->branch_id,
            // 'branch' =>  new BranchResource($this->whenLoaded('branch')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        // إضافة بيانات الفرع فقط إذا كانت محملة
        if ($this->relationLoaded('branch') && $this->branch) {
            $data['branch'] = new BranchResource($this->whenLoaded('branch'));
        }

        // إضافة عدد المساجد (إذا طلب)
        if ($request->boolean('with_mosques_count')) {
            $data['mosques_count'] = $this->when($this->mosques_count !== null, $this->mosques_count);
        }

        // إضافة المساجد (إذا طلب)
        if ($request->boolean('with_mosques') && $this->relationLoaded('mosques')) {
            $data['mosques'] = MosqueResource::collection($this->whenLoaded('mosques'));
        }

        return $data;
    }
}
