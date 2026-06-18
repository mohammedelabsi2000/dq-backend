<?php

namespace App\Http\Resources;

use App\Enums\ApprovalLevel;
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
        $halaqa_status = [];
        $this->whenLoaded('lastStatus', function () use (&$halaqa_status) {
            $halaqa_status = [
                'status_type' => $this->lastStatus->statusType ? new ConstantResource($this->lastStatus->statusType) : null,
                'sponsorship_type' => $this->lastStatus->sponsorshipType ? new ConstantResource($this->lastStatus->sponsorshipType) : null,
                'sponsor_entity' => $this->lastStatus->sponsor_entity,
                'from_date' => $this->lastStatus->from_date,
                'to_date' => $this->lastStatus->to_date,
                'status_notes' => $this->lastStatus->notes,
            ];
        });
        
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
            // 'from_date' => $this->from_date,
            // 'to_date' => $this->to_date,

            // 'last_status' => $this->whenLoaded('lastStatus', function () {
            //     return new HalaqaStatusResource($this->lastStatus);
            // }),

            ...$halaqa_status,

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

            'approval' => $this->when(
                $this->relationLoaded('approvalRequest'),
                fn() => $this->approvalRequest ? [
                    'is_approved' => $this->is_approved,
                    'status' => $this->approvalRequest->status->label(),
                    'current_level' => $this->approvalRequest->current_level->label(),
                    'rejection_reason' => $this->approvalRequest->rejection_reason,
                    'requested_at' => $this->approvalRequest->created_at?->format('Y-m-d H:i'),
                    'logs' => $this->when(
                        $this->approvalRequest->relationLoaded('logs'),
                        fn() => $this->approvalRequest->logs->map(fn($log) => [
                            'level' => ApprovalLevel::from($log->level)->label(),
                            'action' => $log->action === 'approved' ? 'موافقة' : 'رفض',
                            'acted_by' => $log->actor?->full_name ?? $log->actor?->name,
                            'notes' => $log->notes,
                            'created_at' => $log->created_at?->format('Y-m-d H:i'),
                        ])
                    ),
                ] : [
                    'is_approved' => $this->is_approved,
                    'status' => null,
                    'current_level' => null,
                ]
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
