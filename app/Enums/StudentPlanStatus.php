<?php

namespace App\Enums;

enum StudentPlanStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case TRANSFERRED = 'transferred';
    case DROPPED = 'dropped';

    public function getLabel(): string
    {
        return match($this) {
            self::ACTIVE => 'نشط',
            self::COMPLETED => 'مكتمل',
            self::TRANSFERRED => 'منتقل',
            self::DROPPED => 'منقطع',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}