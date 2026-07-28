<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    

    public function academicQualification()
    {
        return $this->belongsTo(Constant::class, 'academic_qualification_id');
    }

    public function major()
    {
        return $this->belongsTo(Constant::class, 'major_id');
    }

    public function courseType()
    {
        return $this->belongsTo(Constant::class, 'course_type_id');
    }

    public function person()
    {
        return $this->morphTo();
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getCertificateTypeLabelAttribute()
    {
        return match ($this->certificate_type) {
            'academy' => 'مؤهل علمي',
            'course' => 'دورة علمية',
            default => null,
        };
    }
}
