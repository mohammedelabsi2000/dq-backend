<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            // نوع الثابت (علاقة مباشرة)
            'constant_type' => [
                'id' => $this->constant_type_id,
                'name' => $this->whenLoaded('constantType', function () {
                    return $this->constantType->name ?? null;
                }),
            ],

            // الثابت الأب (إذا وجد)
            'parent' => $this->when($this->parent_id, function () {
                return [
                    'id' => $this->parent_id,
                    'name' => $this->whenLoaded('parent', function () {
                        return $this->parent->name ?? null;
                    }),
                ];
            }),

            // الأبناء (sub-constants)
            'children' => $this->whenLoaded('children', function () {
                return self::collection($this->children);
            }),

            // الحالة
            'is_active' => (bool) $this->is_active,
            'status' => $this->is_active ? 'active' : 'inactive',

            // ملاحظات
            'notes' => $this->notes,

            // التواريخ (إذا كانت موجودة في الـ migration)
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // روابط
            'links' => [
                'self' => url("/api/constants/{$this->id}"),
                'type' => url("/api/constant-types/{$this->constant_type_id}"),
                'parent' => $this->parent_id ? url("/api/constants/{$this->parent_id}") : null,
            ],
        ];
    }
}