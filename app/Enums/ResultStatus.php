<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;
use App\Helpers\ConstantHelper;

enum ResultStatus: string implements HasLabelAndCode
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Withdraw = 'withdraw';
    case InProgress = 'in_progress';
    case Frozen = 'frozen';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::Passed => ['ar' => 'ناجح', 'en' => 'Passed'],
            self::Failed => ['ar' => 'راسب', 'en' => 'Failed'],
            self::Withdraw => ['ar' => 'منسحب', 'en' => 'Withdraw'],
            self::InProgress => ['ar' => 'قيد الدراسة', 'en' => 'In progress'],
            self::Frozen => ['ar' => 'مؤجل', 'en' => 'Frozen'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }

    public function id(): int
    {
        return ConstantHelper::getConstantIdByKey('result_status', $this->value);
    }
}