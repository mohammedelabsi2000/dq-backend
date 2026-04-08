<?php

namespace App\Models\Quran;

use Illuminate\Database\Eloquent\Model;

class QuranModel extends Model
{
    // protected $connection = 'quran';
    protected $fillable = [];
    public static function booted()
    {
        static::creating(function ($model) {
            throw new \Exception("لا يمكن إضافة سجلات جديدة إلى هذا النموذج لأنه يمثل القرآن الكريم");
            return false; // يمنع الإضافة
        });

        static::updating(function ($model) {
            throw new \Exception("لا يمكن تعديل السجلات في هذا النموذج لأنه يمثل القرآن الكريم");
            return false; // يمنع التعديل
        });

        static::deleting(function ($model) {
            throw new \Exception("لا يمكن حذف السجلات في هذا النموذج لأنه يمثل القرآن الكريم");
            return false; // يمنع الحذف
        });
    }
    
    public function save(array $options = [])
    {
        throw new \Exception('لايمكن حفظ هذا النموذج لأنه للقرآن الكريم');
    }
}
