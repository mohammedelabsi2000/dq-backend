<?php

namespace App\Http\Resources;

use App\Enums\PeriodUnit;
use Illuminate\Http\Resources\Json\JsonResource;

class LevelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'plan_id'      => $this->plan_id,
            'plan_label'   => $this->plan?->name,
            'name'         => $this->name,
            'order'        => $this->order,
            'period_unit' => $this->period_unit,
            'period_unit_label' => PeriodUnit::from($this->period_unit)->label(),
            'period'       => $this->period,
            'max_period'   => $this->max_period,
            'min_period'   => $this->min_period,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at?->toDateTimeString(),

            'plan'         => new PlanResource($this->whenLoaded('plan')),
            'tracks' => LevelTrackResource::collection($this->whenLoaded('levelTracks')),
            'level_tracks_count' => $this->level_tracks_count,
        ];
    }
}
