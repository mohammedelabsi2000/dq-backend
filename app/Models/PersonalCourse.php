<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalCourse extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'course_name',
        'notes',
        'hours',
        'provider',
        'place',
        'certificate_link',
        'type_id',
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
}
