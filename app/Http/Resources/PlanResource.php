<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'duration_in_days' => $this->duration_in_days,
            'grace_period_days' => $this->grace_period_days,

            // المسارات المرتبطة بالخطة
            'plan_tracks' => $this->whenLoaded('planTracks', function () {
                return $this->planTracks->map(function ($planTrack) {
                    return [
                        'id' => $planTrack->id,
                        'track_id' => $planTrack->track_id,
                        'track_name' => $planTrack->track->name ?? null,
                        'is_required' => $planTrack->is_required,
                        'weight' => $planTrack->weight,
                        'courses' => $planTrack->whenLoaded('courses', function () use ($planTrack) {
                            return $planTrack->courses->map(function ($course) {
                                return [
                                    'id' => $course->id,
                                    'name' => $course->name,
                                    'is_required' => $course->pivot->is_required ?? false,
                                    'order' => $course->pivot->order ?? 1,
                                ];
                            });
                        }),
                    ];
                });
            }),

            // الإحصائيات
            'plan_tracks_count' => $this->whenLoaded('planTracks', $this->planTracks->count()),

            // التواريخ
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // روابط API
            'links' => [
                'self' => url("/api/plans/{$this->id}"),
                'setup' => url("/api/plans/{$this->id}/setup"),
            ],
        ];
    }
}
