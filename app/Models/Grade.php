<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

      protected $fillable = [
        'name',
        'DQ_range_from',
        'DQ_range_to',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
