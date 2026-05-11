<?php

namespace App\Policies;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApprovalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('approvals.show');
    }

    public function view(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (!$user->hasPermissionTo('approvals.show')) {
            return false;
        }

        if ($approvalRequest->requested_by === $user->id) {
            return true;
        }

        return ApprovalRequest::visibleTo($user)
            ->where('id', $approvalRequest->id)
            ->exists();
    }

    public function approve(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (!$user->hasPermissionTo('approvals.approve')) {
            return false;
        }

        // return ApprovalRequest::visibleTo($user)
        //     ->where('id', $approvalRequest->id)
        //     ->exists()
        //     && $approvalRequest->canActOn($user);
        return true;
    }

    public function reject(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (!$user->hasPermissionTo('approvals.reject')) {
            return false;
        }

        // return ApprovalRequest::visibleTo($user)
        //     ->where('id', $approvalRequest->id)
        //     ->exists()
        //     && $approvalRequest->canActOn($user);
        return true;
    }

    public function resubmit(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (!$user->hasPermissionTo('approvals.resubmit')) {
            return false;
        }

        return $approvalRequest->requested_by === $user->id
            && $approvalRequest->status === ApprovalStatus::Rejected;
    }


    public function cancel(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (!$user->hasPermissionTo('approvals.cancel')) {
            return false;
        }

        // فقط مقدم الطلب + الطلب مرفوض
        // return $approvalRequest->requested_by === $user->id
        //     && $approvalRequest->status === ApprovalStatus::Rejected;
        return true;
    }
}
