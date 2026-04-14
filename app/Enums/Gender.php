<?php
namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum Gender: string implements HasLabelAndCode
{
    case Male = 'ذكر';
    case Female = 'أنثى';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Male => ['en' => 'Male', 'ar' => 'ذكر'],
            self::Female => ['en' => 'Female', 'ar' => 'أنثى'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['en'];
    }
}