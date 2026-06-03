<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;
use App\Models\Center;
use App\Models\Region;

enum PeriodUnit: string implements HasLabelAndCode
{

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}