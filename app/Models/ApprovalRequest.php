<?php
// app/Models/ApprovalRequest.php

namespace App\Models;

use App\Enums\ApprovalLevel;
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
        'current_level',
        'status',
        'requested_by',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'status'        => ApprovalStatus::class,
        'current_level' => ApprovalLevel::class,
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

    public function logs()
    {
        return $this->hasMany(ApprovalLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function approve(User $actor, ?string $notes = null): void
    {
        // dd($this->status);
        if ($this->status !== ApprovalStatus::Pending) {
            throw new \Exception('لا يمكن الموافقة على طلب غير معلق.');
        }

        DB::transaction(function () use ($actor, $notes) {
            $this->logs()->create([
                'level'    => $this->current_level,
                'action'   => 'approved',
                'acted_by' => $actor->id,
                'notes'    => $notes,
            ]);

            $nextLevel = $this->current_level->next();

            if ($nextLevel === null) {
                // اعتماد نهائي
                $this->update(['status' => ApprovalStatus::Approved]);
                $this->approvable->update(['is_approved' => true, 'is_active'   => true,]);
            } else {
                // الانتقال للمرحلة التالية
                $this->update(['current_level' => $nextLevel]);
            }
        });
    }

    public function reject(User $actor, string $reason): void
    {
        if ($this->status !== ApprovalStatus::Pending) {
            throw new \Exception('لا يمكن رفض طلب غير معلق.');
            // return $this->error('لا يمكن رفض طلب غير معلق.', 400);
        }

        DB::transaction(function () use ($actor, $reason) {
            $this->logs()->create([
                'level'    => $this->current_level,
                'action'   => 'rejected',
                'acted_by' => $actor->id,
                'notes'    => $reason,
            ]);

            $this->update([
                'status'           => ApprovalStatus::Rejected,
                'rejection_reason' => $reason,
            ]);
        });
    }

    public function resubmit(?string $notes = null): void
    {
        if ($this->status !== ApprovalStatus::Rejected) {
            throw new \Exception('لا يمكن إعادة إرسال طلب غير مرفوض.');
        }

        // نرجع للمستوى الأول الذي بدأ منه الطلب (أول log)
        $firstLevel = $this->logs()->oldest()->first()?->level;

        $this->update([
            'status'           => ApprovalStatus::Pending,
            'current_level'    => $firstLevel ?? ApprovalLevel::Region,
            'rejection_reason' => null,
            'notes'            => $notes ?? $this->notes,
        ]);
    }

    public function cancel(User $actor): void
    {
        if ($this->status !== ApprovalStatus::Rejected) {
            throw new \Exception('لا يمكن إلغاء طلب غير مرفوض.');
        }

        // فقط مقدم الطلب يمكنه الإلغاء
        if ($this->requested_by !== $actor->id) {
            throw new \Exception('ليس لديك صلاحية إلغاء هذا الطلب.');
        }

        DB::transaction(function () use ($actor) {
            $this->logs()->create([
                'level'    => $this->current_level,
                'action'   => 'cancelled',
                'acted_by' => $actor->id,
                'notes'    => 'تم إلغاء الطلب من قبل مقدمه.',
            ]);

            $this->update(['status' => ApprovalStatus::Cancelled]);

            // soft delete للكيان
            $this->approvable?->delete();
        });
    }

    public function canActOn(User $user): bool
    {
        // المستوى الحالي للطلب
        $currentLevel = $this->current_level;

        // فقط طلبات Pending يمكن التصرف عليها
        if ($this->status !== ApprovalStatus::Pending) {
            return false;
        }

        return match ($currentLevel) {
            ApprovalLevel::Admin  => $user->isGlobalAdmin(),
            ApprovalLevel::Branch => $user->getScopeIds('branch')->isNotEmpty(),
            ApprovalLevel::Region => $user->getScopeIds('region')->isNotEmpty(),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeVisibleTo(Builder $query, User $user, ?string $type = null): Builder
    {
        if ($user->isGlobalAdmin()) {
            $q = $query->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('current_level', ApprovalLevel::Admin)
                        ->where('status', ApprovalStatus::Pending);
                })->orWhereIn('status', [
                    ApprovalStatus::Approved,
                    ApprovalStatus::Rejected,
                ]);
            });

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
                // طلبات نطاق الفرع المعلقة في مرحلة Branch
                $q->where(function ($q) use ($requesterIds) {
                    $q->whereIn('requested_by', $requesterIds)
                        ->where('current_level', ApprovalLevel::Branch)
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
                // طلبات نطاق المنطقة المعلقة في مرحلة Region
                $q->where(function ($q) use ($requesterIds) {
                    $q->whereIn('requested_by', $requesterIds)
                        ->where('current_level', ApprovalLevel::Region)
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
