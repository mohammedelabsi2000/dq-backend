<?php

namespace App\Enums;

use App\Contracts\HasLabelAndCode;

enum SuccessValueType: string implements HasLabelAndCode
{
    case MainMark = 'main_mark';
    case TrialTest = 'trial_test';
    case FinalTest = 'final_test';
    case TrialSardErrors = 'trial_sard_errors';
    case TrialSardAlerts = 'trial_sard_alerts';
    case FinalSardErrors = 'final_sard_errors';
    case FinalSardAlerts = 'final_sard_alerts';
    case EnableSuccessSelect = 'enable_success_select';

    public function code(): string
    {
        return $this->value;
    }

    public function labels(): array
    {
        return match ($this) {
            self::MainMark => ['ar' => 'العلامة الأساسية', 'en' => 'Main Mark'],
            self::TrialTest => ['ar' => 'علامة الاختبار التجريبي', 'en' => 'Trial Test'],
            self::FinalTest => ['ar' => 'علامة الاختبار النهائي', 'en' => 'Final Test'],
            self::TrialSardErrors => ['ar' => 'أخطاء سرد تجريبي', 'en' => 'Trial Sard Errors'],
            self::TrialSardAlerts => ['ar' => 'إنذارات سرد تجريبي', 'en' => 'Trial Sard Alerts'],
            self::FinalSardErrors => ['ar' => 'أخطاء سرد نهائي', 'en' => 'Final Sard Errors'],
            self::FinalSardAlerts => ['ar' => 'إنذارات سرد نهائي', 'en' => 'Final Sard Alerts'],
            self::EnableSuccessSelect => ['ar' => 'تمكين اختيار النجاح', 'en' => 'Enable Success Select'],
        };
    }

    public function label(): string
    {
        $locale = auth()->user()?->locale ?? app()->getLocale();
        return $this->labels()[$locale] ?? $this->labels()['ar'];
    }
}

/*
[
  { name: "min_mark", value: "العلامة الأساسية" },
  { name: "trial_test", value: "علامة الاختبار التجريبي" },
  { name: "trial_sardـerrors", value: "أخطاء السرد التجريبي" },
  { name: "trial_sardـalerts", value: "تنبيهات السرد التجريبي" },
  { name: "final_test", value: "علامة الاختبار النهائي" },
  { name: "final_sardـerrors", value: "أخطاء السرد النهائي" },
  { name: "final_sardـalerts", value: "تنبيهات السرد النهائي" },
  { name: "enable_success_select", value: "تفعيل حالة الاجتياز" },
]
*/