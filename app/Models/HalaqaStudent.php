<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HalaqaStudent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
    'from_date' => 'date',
    'to_date' => 'date',
];

    protected $table = 'halaqa_students';

    /**
     * الأعمدة القابلة للتعبئة
     */
    protected $fillable = [
        'halaqa_id',
        'student_id',
        'from_date',
        'to_date',
        'status_id',
    ];

    /**
     * علاقة مع جدول Halaqa
     */
    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class, 'halaqa_id');
    }

    /**
     * علاقة مع جدول Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * علاقة مع جدول Status
     */
    public function status()
    {
        return $this->belongsTo(Constant::class, 'status_id');
    }
}
