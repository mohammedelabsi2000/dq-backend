<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum AssignmentType: string implements HasLabelAndCode
{
    case Manual = 'manual';
    case Age = 'age';
    case LastMemorized = 'last_memorized';
    case Evaluation = 'evaluation';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Manual => ['en' => 'Manual', 'ar' => 'يدوي'],
            self::Age => ['en' => 'Age', 'ar' => 'العمر'],
            self::LastMemorized => ['en' => 'Last Memorized', 'ar' => 'آخر محفوظ'],
            self::Evaluation => ['en' => 'Evaluation', 'ar' => 'تقييم'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}