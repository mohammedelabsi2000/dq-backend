<?php

namespace App\Enums;

enum EvaluationGrade: string
{
    case EXCELLENT = 'excellent';
    case VERY_GOOD = 'very_good';
    case GOOD = 'good';
    case ACCEPTABLE = 'acceptable';
    case WEAK = 'weak';

    public function getLabel(): string
    {
        return match($this) {
            self::EXCELLENT => 'ممتاز',
            self::VERY_GOOD => 'جيد جداً',
            self::GOOD => 'جيد',
            self::ACCEPTABLE => 'مقبول',
            self::WEAK => 'ضعيف',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
