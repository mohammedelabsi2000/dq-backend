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
    ];

    protected $casts = [
        'value' => 'boolean',
    ];

    public static function isEnabled(string $key, bool $default = false): bool
    {
        $setting = static::where('key', $key)->first();

        return $setting ? (bool) $setting->value : $default;
    }
}
