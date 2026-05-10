<?php

namespace App\Enums;

// app/Enums/ApprovalStatus.php
enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الانتظار',
            self::Approved => 'معتمد',
            self::Rejected => 'مرفوض',
            self::Cancelled => 'ملغي',
        };
    }
}
