<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;
use App\Models\Center;
use App\Models\Region;

enum PeriodUnit: string implements HasLabelAndCode
{
    case Year = 'year';
    case Month = 'month';
    case Week = 'week';
    case Day = 'day';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Year => ['en' => 'Year', 'ar' => 'سنة'],
            self::Month => ['en' => 'Month', 'ar' => 'شهر'],
            self::Week => ['en' => 'Week', 'ar' => 'أسبوع'],
            self::Day => ['en' => 'Day', 'ar' => 'يوم'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}