<?php

namespace App\Models\Quran;

use Illuminate\Database\Eloquent\Model;

class QuranModel extends Model
{
    protected $connection = 'quran';
    public static function booted()
    {
        static::creating(function ($model) {
            throw new \Exception("Not allowed");
            return false; // يمنع الإضافة
        });

        static::updating(function ($model) {
            throw new \Exception("Not allowed");
            return false; // يمنع التعديل
        });

        static::deleting(function ($model) {
            throw new \Exception("Not allowed");
            return false; // يمنع الحذف
        });
    }
    
    public function save(array $options = [])
    {
        throw new \Exception('Saving not allowed for this model');
    }
}
