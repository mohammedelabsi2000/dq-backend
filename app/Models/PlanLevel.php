<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plan_id',
        'level_order',
        'time_of_level',
        'time_unit_id',
        'max_time',
        'max_time_unit_id',
        'min_time',
        'min_time_unit_id',
    ];

    // public function levels()
    // {
    //     return $this->hasMany(PlanLevel::class)
    //         ->orderBy('level_order');
    // }


    // كل مستوى تابع لخطة واحدة
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // الوحدة الزمنية الأساسية للمستوى
    public function timeUnit()
    {
        return $this->belongsTo(Constant::class, 'time_unit_id');
    }

    // وحدة الزمن للحد الأعلى
    public function maxTimeUnit()
    {
        return $this->belongsTo(Constant::class, 'max_time_unit_id');
    }

    // وحدة الزمن للحد الأدنى
    public function minTimeUnit()
    {
        return $this->belongsTo(Constant::class, 'min_time_unit_id');
    }
}
