<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Constant extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = [
        'name',
        'constant_type_id',
        'parent_id',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // العلاقة مع نوع الثابت
    public function type()
    {
        return $this->belongsTo(ConstantType::class, 'constant_type_id');
    }

    public function typeName()
    {
        return $this->belongsTo(ConstantType::class, 'constant_type_id')->select('id', 'name as type_name');
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

    /**
     * Get the halaqa student enrollments with this status.
     */
    public function halaqaStudentEnrollments()
    {
        return $this->hasMany(HalaqaStudent::class, 'status_id');
    }

    // تحقق من إذا هذا الثابت مستخدم في أي جدول
    public function isUsed()
    {
        // أولاً: تحقق إذا هو parent لثوابت أخرى
        if ($this->children()->exists()) {
            return true;
        }

        // ثانياً: تحقق في جداول أخرى (اضف الجداول اللي عندك هنا)
        $foreignTables = [
            'records' => 'constant_id', // مثال جدول records والعمود constant_id
            'invoices' => 'constant_id', // جدول invoices كمثال
            // أضف أي جدول آخر هنا بنفس الصيغة
        ];

        foreach ($foreignTables as $table => $column) {
            $exists = DB::table($table)->where($column, $this->id)->exists();
            if ($exists) {
                return true;
            }
        }

        return false;
    }
}
