<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum PlanType: string implements HasLabelAndCode
{
    case Main = 'main';
    case Sub = 'sub';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Main => ['en' => 'Main', 'ar' => 'رئيسية'],
            self::Sub => ['en' => 'Sub', 'ar' => 'فرعية'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}
