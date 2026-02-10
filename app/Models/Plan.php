<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'name',
        'type_id',
        'description',
        'target_group_id',
        'level_numbers',
        'notes',
    ];

    // العلاقات
    public function type()
    {
        return $this->belongsTo(Constant::class, 'type_id');
    }

    public function targetGroup()
    {
        return $this->belongsTo(Constant::class, 'target_group_id');
    }
}
