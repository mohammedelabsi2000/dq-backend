<?php
// app/Models/ApprovalRequest.php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'requested_by',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function approvable()
    {
        return $this->morphTo()->withTrashed();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function approve(User $actor): void
    {
        if ($this->status !== ApprovalStatus::Pending) {
            throw new \Exception('لا يمكن الموافقة على طلب غير معلق.');
        }

        DB::transaction(function () {
            $this->update(['status' => ApprovalStatus::Approved]);
            $this->approvable->update($this->approvable->approvedAttributes());
        });
    }

    public function reject(User $actor, string $reason): void
    {
        if ($this->status !== ApprovalStatus::Pending) {
            throw new \Exception('لا يمكن رفض طلب غير معلق.');
        }

        DB::transaction(function () use ($reason) {
            $this->update([
                'status'           => ApprovalStatus::Rejected,
                'rejection_reason' => $reason,
            ]);
        });
    }

    public function resubmit(): void
    {
        if ($this->status !== ApprovalStatus::Rejected) {
            throw new \Exception('لا يمكن إعادة إرسال طلب غير مرفوض.');
        }

        $this->update([
            'status'           => ApprovalStatus::Pending,
            'rejection_reason' => null,
        ]);
    }

    public function canActOn(User $user): bool
    {
        return $this->status === ApprovalStatus::Pending;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeVisibleTo(Builder $query, User $user, ?string $type = null): Builder
    {
        if ($user->isGlobalAdmin()) {
            $q = $query;

            if ($type) $q->where('approvable_type', $type);
            return $q;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');

        // ── مدير فرع ─────────────────────────────────────────────────────────
        if ($branchIds->isNotEmpty()) {
            $regionIdsInBranch = Region::whereIn('branch_id', $branchIds)->pluck('id');
            $centerIdsInBranch = Center::whereIn('region_id', $regionIdsInBranch)->pluck('id');

            $requesterIds = UserScope::whereNull('to_date')
                ->where(function ($q) use ($regionIdsInBranch, $centerIdsInBranch) {
                    $q->where(function ($q) use ($regionIdsInBranch) {
                        $q->where('scope_type', 'region')
                            ->whereIn('scope_id', $regionIdsInBranch);
                    })->orWhere(function ($q) use ($centerIdsInBranch) {
                        $q->where('scope_type', 'center')
                            ->whereIn('scope_id', $centerIdsInBranch);
                    });
                })
                ->pluck('user_id')
                ->unique();

            $q = $query->where(function ($q) use ($requesterIds, $user) {
                // طلبات نطاق الفرع المعلقة
                $q->where(function ($q) use ($requesterIds) {
                    $q->whereIn('requested_by', $requesterIds)
                        ->where('status', ApprovalStatus::Pending);
                })
                    // طلبات نطاق الفرع المكتملة (معتمد/مرفوض)
                    ->orWhere(function ($q) use ($requesterIds) {
                        $q->whereIn('requested_by', $requesterIds)
                            ->whereIn('status', [
                                ApprovalStatus::Approved,
                                ApprovalStatus::Rejected,
                            ]);
                    })
                    // ✅ طلبات قدّمها هو شخصياً (مرفوضة أو معتمدة)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('requested_by', $user->id)
                            ->whereIn('status', [
                                ApprovalStatus::Approved,
                                ApprovalStatus::Rejected,
                                ApprovalStatus::Pending,
                            ]);
                    });
            });

            if ($type) $q->where('approvable_type', $type);
            return $q;
        }

        // ── مدير منطقة ───────────────────────────────────────────────────────
        if ($regionIds->isNotEmpty()) {
            $centerIdsInRegion = Center::whereIn('region_id', $regionIds)->pluck('id');

            $requesterIds = UserScope::whereNull('to_date')
                ->where('scope_type', 'center')
                ->whereIn('scope_id', $centerIdsInRegion)
                ->pluck('user_id')
                ->unique();

            $q = $query->where(function ($q) use ($requesterIds, $user) {
                // طلبات نطاق المنطقة المعلقة
                $q->where(function ($q) use ($requesterIds) {
                    $q->whereIn('requested_by', $requesterIds)
                        ->where('status', ApprovalStatus::Pending);
                })
                    // طلبات نطاق المنطقة المكتملة
                    ->orWhere(function ($q) use ($requesterIds) {
                        $q->whereIn('requested_by', $requesterIds)
                            ->whereIn('status', [
                                ApprovalStatus::Approved,
                                ApprovalStatus::Rejected,
                            ]);
                    })
                    // ✅ طلبات قدّمها هو شخصياً (مرفوضة أو معتمدة)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('requested_by', $user->id)
                            ->whereIn('status', [
                                ApprovalStatus::Approved,
                                ApprovalStatus::Rejected,
                                ApprovalStatus::Pending,
                            ]);
                    });
            });

            if ($type) $q->where('approvable_type', $type);
            return $q;
        }

        // ── مدير مركز أو أي مستخدم آخر ──────────────────────────────────────
        // يرى فقط طلباته هو التي قدّمها
        $q = $query->where('requested_by', $user->id)
            ->whereIn('status', [
                ApprovalStatus::Approved,
                ApprovalStatus::Rejected,
                ApprovalStatus::Pending,
            ]);

        if ($type) $q->where('approvable_type', $type);
        return $q;
    }
}
