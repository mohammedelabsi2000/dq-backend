<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum ImageType: string implements HasLabelAndCode
{
    case Profile = 'profile';
    case Cover = 'cover';
    case Gallery = 'gallery';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Profile => ['en' => 'Profile', 'ar' => 'صورة شخصية'],
            self::Cover => ['en' => 'Cover', 'ar' => 'غلاف'],
            self::Gallery => ['en' => 'Gallery', 'ar' => 'معرض'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}