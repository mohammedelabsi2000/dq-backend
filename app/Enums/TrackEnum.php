<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;
use App\Models\Track;

enum TrackEnum: string implements HasLabelAndCode
{

    case Memorization = 'حفظ وتثبيت';
    case Academic = 'معرفي';
    case ValueBased = 'قيمي';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Memorization => ['ar' => 'حفظ وتثبيت', 'en' => 'Memorization'],
            self::Academic => ['ar' => 'معرفي', 'en' => 'Academic'],
            self::ValueBased => ['ar' => 'قيمي', 'en' => 'Value Based'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }

    public function id(): int
    {
        return Track::where('name', $this->value)->first()?->id ?? 0;
    }
}