<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingLog extends Model
{
    public static $usesAudit = true;

    protected $fillable = [
        'key',
        'start_dt',
        'end_dt',
    ];

    protected $casts = [
        'start_dt' => 'datetime',
        'end_dt'   => 'datetime',
    ];
}
