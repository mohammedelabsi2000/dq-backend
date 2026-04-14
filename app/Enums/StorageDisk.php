<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum StorageDisk: string implements HasLabelAndCode
{
    case Public = 'public';
    case S3 = 's3';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Public => ['en' => 'Public', 'ar' => 'عام'],
            self::S3 => ['en' => 'S3', 'ar' => 'S3'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['en'];
    }
}