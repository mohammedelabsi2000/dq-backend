<?php

namespace App\Enums;

enum ApprovalLevel: string
{
    case Region = 'region';
    case Branch = 'branch';
    case Admin  = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Region => 'مدير المنطقة',
            self::Branch => 'مدير الفرع',
            self::Admin  => 'الإدارة العليا',
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Region => self::Branch,
            self::Branch => self::Admin,
            self::Admin  => null,
        };
    }
}
