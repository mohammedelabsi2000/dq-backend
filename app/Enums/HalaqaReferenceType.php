<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;
use App\Models\Center;
use App\Models\Region;

enum HalaqaReferenceType: string implements HasLabelAndCode
{
    case Center = 'center';
    case Region = 'region';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Center => ['en' => 'Center', 'ar' => 'مركز'],
            self::Region => ['en' => 'Region', 'ar' => 'محلية'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }

    public function model(): ?string
    {
        return match ($this) {
            self::Center => Center::class,
            self::Region => Region::class,
        };
    }
}