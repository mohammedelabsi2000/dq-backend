<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;
use App\Models\Center;
use App\Models\Region;

enum SubjectType: string implements HasLabelAndCode
{
    case Course = 'course';
    case Evaluation = 'evaluation';
    case Explanation = 'explanation';
    case LimitedMemorization = 'limitedMemorization';
    case Meanings = 'meanings';
    case Memorization = 'memorization';
    case Program = 'program';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Course => ['ar' => 'دورة معرفية', 'en' => 'Course'],
            self::Evaluation => ['ar' => 'قيمي / تربوي', 'en' => 'Evaluation'],
            self::Explanation => ['ar' => 'تفسير', 'en' => 'Explanation'],
            self::LimitedMemorization => ['ar' => 'حفظ مقطع محدد', 'en' => 'Limited Memorization'],
            self::Meanings => ['ar' => 'معاني', 'en' => 'Meanings'],
            self::Memorization => ['ar' => 'حفظ وتثبيت', 'en' => 'Memorization'],
            self::Program => ['ar' => 'برنامج رديف', 'en' => 'Program'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}