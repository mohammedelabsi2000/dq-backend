<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum AuditEvent: string implements HasLabelAndCode
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Created => ['en' => 'Created', 'ar' => 'إنشاء'],
            self::Updated => ['en' => 'Updated', 'ar' => 'تعديل'],
            self::Deleted => ['en' => 'Deleted', 'ar' => 'حذف'],
            self::Restored => ['en' => 'Restored', 'ar' => 'استعادة'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}