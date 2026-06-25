<?php

namespace App\Enums;

enum AchievementStatus: string
{
    case COMPLETED = 'completed';
    // case PARTIAL = 'partial';
    case RETRY = 'retry';

    public function getLabel(): string
    {
        return match($this) {
            self::COMPLETED => 'مكتمل',
            // self::PARTIAL => 'جزئي',
            self::RETRY => 'معاد',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
