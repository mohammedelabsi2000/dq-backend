<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const AUTO_APPROVE_HALAQAS = 'auto_approve_halaqas';
    public const AUTO_APPROVE_STUDENTS = 'auto_approve_students';

    public static $usesAudit = true;

    protected $fillable = [
        'key',
        'value',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'value'     => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public static function isEnabled(string $key, bool $default = false): bool
    {
        $setting = static::where('key', $key)->first();

        return $setting ? (bool) $setting->value : $default;
    }

    /**
     * تحديث قيمة الإعداد مع تسجيل تاريخ الفتح (عند التفعيل) وتاريخ الإغلاق (عند التعطيل)
     */
    public static function setValue(string $key, bool $value): self
    {
        $setting = static::firstOrNew(['key' => $key]);
        $wasEnabled = (bool) $setting->value;

        $setting->value = $value;

        if ($value && !$wasEnabled) {
            $now = now();
            $setting->opened_at = $now;
            $setting->closed_at = null;
            SettingLog::create(['key' => $key, 'start_dt' => $now]);
        } elseif (!$value && $wasEnabled) {
            $now = now();
            $setting->closed_at = $now;
            SettingLog::where('key', $key)->whereNull('end_dt')->update(['end_dt' => $now]);
        }

        $setting->save();

        return $setting;
    }
}
