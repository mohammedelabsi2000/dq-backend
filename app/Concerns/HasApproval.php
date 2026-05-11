<?php
// app/Concerns/HasApproval.php

namespace App\Concerns;

use App\Enums\ApprovalLevel;
use App\Enums\ApprovalStatus;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasApproval
{
    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    // public function submitForApproval(User $requester): ?ApprovalRequest
    // {
    //     if ($this->approvalRequest()->exists()) {
    //         throw new \Exception('يوجد طلب اعتماد مسبق لهذا العنصر.');
    //     }

    //     $startingLevel = ApprovalLevel::startingLevelForUser($requester);

    //     // المدير العام → اعتماد فوري بدون مراحل
    //     if ($startingLevel === null) {
    //         $this->update(['is_approved' => true]);
    //         return null;
    //     }

    //     return $this->approvalRequest()->create([
    //         'current_level' => $startingLevel,
    //         'status'        => ApprovalStatus::Pending,
    //         'requested_by'  => $requester->id,
    //     ]);
    // }

    public function submitForApproval(User $requester, ?string $notes = null): ?ApprovalRequest
    {
        // نتحقق فقط من الطلبات غير الملغاة
        $exists = $this->approvalRequest()
            ->whereNotIn('status', [ApprovalStatus::Cancelled])
            ->exists();

        if ($exists) {
            throw new \Exception('يوجد طلب اعتماد مسبق لهذا العنصر.');
        }

        $startingLevel = ApprovalLevel::startingLevelForUser($requester);

        if ($startingLevel === null) {
            $this->update(['is_approved' => true, 'is_active' => true]);
            return null;
        }

        return $this->approvalRequest()->create([
            'current_level' => $startingLevel,
            'status'        => ApprovalStatus::Pending,
            'requested_by'  => $requester->id,
            'notes'         => $notes,
        ]);
    }

    public function isPending(): bool
    {
        return $this->approvalRequest?->status === ApprovalStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->is_approved === true;
    }

    public function isRejected(): bool
    {
        return $this->approvalRequest?->status === ApprovalStatus::Rejected;
    }

    public static function withPending(): Builder
    {
        return static::withoutGlobalScope('approved');
    }
}
