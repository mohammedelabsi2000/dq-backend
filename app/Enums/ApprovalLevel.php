<?php

namespace App\Enums;

use App\Models\User;

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
            self::Admin  => 'المدير العام',
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

    /**
     * أول مستوى اعتماد حسب دور مقدم الطلب
     *
     * مدير المركز  → يبدأ من Region  (يمر على 3 مستويات)
     * مدير المنطقة → يبدأ من Branch  (يمر على مستويين)
     * مدير الفرع   → يبدأ من Admin   (مستوى واحد)
     * المدير العام  → null            (مباشر بدون اعتماد)
     */
    public static function startingLevelForUser(User $requester): ?self
    {
        if ($requester->isGlobalAdmin())                             return null;
        if ($requester->getScopeIds('branch')->isNotEmpty())         return self::Admin;
        if ($requester->getScopeIds('region')->isNotEmpty())         return self::Branch;
        if ($requester->getScopeIds('center')->isNotEmpty())         return self::Region;

        return self::Region; // default
    }
}
