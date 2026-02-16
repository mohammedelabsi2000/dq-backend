<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ConstantType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    /**
     * العلاقة مع الثوابت
     * constant_type_id في جدول constants يشير إلى id في جدول constant_types
     */
    public function constants()
    {
        return $this->hasMany(Constant::class, 'constant_type_id');
    }

}
