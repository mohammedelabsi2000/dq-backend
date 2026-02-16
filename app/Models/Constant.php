<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Constant extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'constant_type_id',
        'parent_id',
        'is_active',
        'notes',
    ];

    // العلاقة مع نوع الثابت
    public function type()
    {
        return $this->belongsTo(ConstantType::class, 'constant_type_id');
    }

    // العلاقة مع نوع الثابت
    public function constantType()
    {
        return $this->belongsTo(ConstantType::class, 'constant_type_id');
    }

    // العلاقة مع الثابت الأب
    public function parent()
    {
        return $this->belongsTo(Constant::class, 'parent_id');
    }

    // (اختياري) الأبناء
    public function children()
    {
        return $this->hasMany(Constant::class, 'parent_id');
    }

    public function halaqat()
    {
        return $this->hasMany(Halaqa::class);
    }
}
