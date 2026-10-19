<?php

namespace App\Http\Resources;

use App\Support\ScopeCache;
use Illuminate\Http\Resources\Json\JsonResource;

class ScopeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // الكيان يُجلب مرة واحدة لكل طلب مهما تكرر في الصفوف
        $name = app(ScopeCache::class)->entity($this->scope_type, $this->scope_id)?->name;

        return [
            'type' => $this->scope_type,
            'id'   => $this->scope_id,
            'name' => $name,
        ];
    }
}
