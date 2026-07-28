<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = [
        'attendable_id',
        'attendable_type',
        'halaqa_id',
        'date',
        'status_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    public function attendable()
    {
        return $this->morphTo();
    }

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class);
    }

    public function status()
    {
        return $this->belongsTo(Constant::class, 'status_id');
    }
}
