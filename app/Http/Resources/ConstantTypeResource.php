<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstantTypeResource extends JsonResource
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
            'description' => $this->description,
            'notes' => $this->notes,

            // الثوابت المرتبطة بهذا النوع
            'constants' => $this->whenLoaded('constants', function () {
                return ConstantResource::collection($this->constants);
            }),

            // إحصائيات الثوابت
            'constants_count' => $this->when($this->constants_count !== null, $this->constants_count),
            'active_constants_count' => $this->whenLoaded('constants', function () {
                return $this->constants->where('is_active', true)->count();
            }),

            // الثوابت الرئيسية (بدون parent) من هذا النوع
            'parent_constants' => $this->whenLoaded('constants', function () {
                return ConstantResource::collection(
                    $this->constants->whereNull('parent_id')
                );
            }),

            // الهيكل الهرمي للثوابت
            'constants_hierarchy' => $this->whenLoaded('constants', function () {
                return $this->getConstantsHierarchy();
            }),

            // التواريخ
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // روابط
            'links' => [
                'self' => url("/api/constant-types/{$this->id}"),
                'constants' => url("/api/constants?type_id={$this->id}"),
            ],
        ];
    }

    /**
     * بناء الهيكل الهرمي للثوابت
     */
    private function getConstantsHierarchy(): array
    {
        $constants = $this->constants;
        $parents = $constants->whereNull('parent_id');

        return $parents->map(function ($parent) use ($constants) {
            return [
                'id' => $parent->id,
                'name' => $parent->name,
                'is_active' => $parent->is_active,
                'children' => $constants->where('parent_id', $parent->id)
                    ->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'is_active' => $child->is_active,
                        ];
                    })->values(),
            ];
        })->values()->toArray();
    }
}