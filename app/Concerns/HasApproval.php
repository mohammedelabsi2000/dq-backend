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
    // protected static function bootHasApproval(): void
    // {
    //     // إخفاء غير المعتمدين تلقائياً في كل query
    //     static::addGlobalScope('approved', function (Builder $builder) {
    //         $builder->where('is_approved', true);
    //     });
    // }

    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    public function submitForApproval(User $requester): ApprovalRequest
    {
        return $this->approvalRequest()->create([
            'current_level' => ApprovalLevel::Region,
            'status'        => ApprovalStatus::Pending,
            'requested_by'  => $requester->id,
        ]);
    }

    // لعرض المعلقة والمرفوضة (للمسؤولين)
    public static function withPending(): Builder
    {
        return static::withoutGlobalScope('approved');
    }

    public function isPending(): bool
    {
        return $this->approvalRequest?->status === ApprovalStatus::Pending;
    }

    public function isRejected(): bool
    {
        return $this->approvalRequest?->status === ApprovalStatus::Rejected;
    }
}
