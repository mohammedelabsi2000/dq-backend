<?php

namespace App\Enums;

enum AchievementType: string
{
    case NEW_MEMORIZATION = 'new_memorization';
    case REVISION = 'revision';
    // case RECITATION = 'recitation';
    case EXAM = 'exam';

    public function getLabel(): string
    {
        return match($this) {
            self::NEW_MEMORIZATION => 'حفظ جديد',
            self::REVISION => 'مراجعة',
            // self::RECITATION => 'تسميع',
            self::EXAM => 'اختبار',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
