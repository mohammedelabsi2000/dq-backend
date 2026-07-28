<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalCourse extends Model
{
    use HasFactory;
    use SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = [
        'course_name',
        'notes',
        'hours',
        'provider',
        'place',
        'certificate_link',
        'person_type',
        'person_id',
        'type_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // polymorphic relation
    public function person()
    {
        return $this->morphTo();
    }

    // relation to type
    public function type()
    {
        return $this->belongsTo(Constant::class, 'type_id');
    }

      // علاقة Polymorphic مع الصور (يمكن أكثر من صورة لكل مؤهل)
    public function images()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
