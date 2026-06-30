<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum MemorizationDirection: string implements HasLabelAndCode
{
    case Ascending = 'ascending';
    case Descending = 'descending';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Ascending => ['ar' => 'تصاعدي (من البداية للنهاية)', 'en' => 'Ascending (Start to End)'],
            self::Descending => ['ar' => 'تنازلي (من النهاية للبداية)', 'en' => 'Descending (End to Start)'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}
