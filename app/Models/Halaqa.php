<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Halaqa extends Model
{
    use HasFactory;

    protected $table = 'halaqas';

    protected $fillable = [
        'name',
        'location',
        'description',
        // 'center_id',
        'type_id',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    // غيّر Constant إلى اسم موديل الثوابت الحقيقي عندك (مثلاً Thabit)
    public function constant()
    {
        return $this->belongsTo(Constant::class);
    }

    /**
     * Get the student enrollments for this halaqa.
     */
    public function studentEnrollments()
    {
        return $this->hasMany(HalaqaStudent::class);
    }

    /**
     * Get the students enrolled in this halaqa.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'halaqa_students')
            ->withPivot(['from_date', 'to_date', 'status_id'])
            ->withTimestamps()
            ->using(HalaqaStudent::class);
    }
}
