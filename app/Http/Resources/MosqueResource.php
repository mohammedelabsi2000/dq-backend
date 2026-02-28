<?php

namespace App\Http\Resources;

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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'region_id' => $this->region_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        // إضافة بيانات المنطقة فقط إذا كانت محملة
        if ($this->relationLoaded('region') && $this->region) {
            $data['region'] = new RegionResource($this->whenLoaded('region'));
        }


        // إضافة عدد المراكز (إذا طلب)
        if ($request->boolean('with_centers_count')) {
            $data['centers_count'] = $this->when($this->centers_count !== null, $this->centers_count);
        }

        // إضافة المراكز (إذا طلب)
        if ($request->boolean('with_centers') && $this->relationLoaded('centers')) {
            $data['centers'] = CenterResource::collection($this->whenLoaded('centers'));
        }

        // إضافة عدد المستخدمين (إذا طلب)
        if ($request->boolean('with_users_count')) {
            $data['users_count'] = $this->when($this->users_count !== null, $this->users_count);
        }

        // إضافة المستخدمين (إذا طلب)
        if ($request->boolean('with_users') && $this->relationLoaded('users')) {
            $data['users'] = $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // أضف حقول المستخدم الأخرى حسب الحاجة
                ];
            });
        }

        return $data;
    }
}
