<?php

namespace App\Models;

use App\Enums\ApprovalLevel;
use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// app/Models/ApprovalRequest.php
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
    ];

    protected $casts = [
        'status'        => ApprovalStatus::class,
        'current_level' => ApprovalLevel::class,
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function logs()
    {
        return $this->hasMany(ApprovalLog::class);
    }
    public function approve(User $actor, ?string $notes = null): void
    {
        DB::transaction(function () use ($actor, $notes) {

            // 1. تسجيل اللوج
            $this->logs()->create([
                'level'    => $this->current_level,
                'action'   => 'approved',
                'acted_by' => $actor->id,
                'notes'    => $notes,
            ]);

            $nextLevel = $this->current_level->next();

            if ($nextLevel === null) {
                // 2. اعتماد نهائي
                $this->update([
                    'status' => ApprovalStatus::Approved,
                ]);

                // 3. تفعيل العنصر
                $this->approvable->update([
                    'is_approved' => true,
                ]);
            } else {
                // انتقال للمرحلة التالية
                $this->update([
                    'current_level' => $nextLevel,
                ]);
            }
        });
    }

    public function reject(User $actor, string $reason): void
    {
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

    // public function approve(User $actor, ?string $notes = null): void
    // {
    //     // تسجيل الخطوة
    //     $this->logs()->create([
    //         'level'    => $this->current_level,
    //         'action'   => 'approved',
    //         'acted_by' => $actor->id,
    //         'notes'    => $notes,
    //     ]);

    //     $nextLevel = $this->current_level->next();

    //     if ($nextLevel === null) {
    //         // اكتمل سير العمل
    //         $this->update(['status' => ApprovalStatus::Approved]);
    //         $this->approvable->update(['is_approved' => true]);
    //     } else {
    //         $this->update(['current_level' => $nextLevel]);
    //     }
    // }

    // public function reject(User $actor, string $reason): void
    // {
    //     $this->logs()->create([
    //         'level'    => $this->current_level,
    //         'action'   => 'rejected',
    //         'acted_by' => $actor->id,
    //         'notes'    => $reason,
    //     ]);

    //     $this->update([
    //         'status'           => ApprovalStatus::Rejected,
    //         'rejection_reason' => $reason,
    //     ]);
    // }

    public function resubmit(): void
    {
        $this->update([
            'status'           => ApprovalStatus::Pending,
            'current_level'    => ApprovalLevel::Region,
            'rejection_reason' => null,
        ]);
    }
}
