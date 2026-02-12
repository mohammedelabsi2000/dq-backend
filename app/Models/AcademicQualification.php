<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicQualification extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'academic_degree_id',
        'major_id',
        'person_type',
        'person_id',
        'detail',
        'date_graduate',
        'certificate_link',
        'educational_institution',
        'notes',
    ];

    /* ================= Relations ================= */

    public function academicDegree()
    {
        return $this->belongsTo(Constant::class, 'academic_degree_id');
    }

    public function major()
    {
        return $this->belongsTo(Constant::class, 'major_id');
    }

    public function person()
    {
        return $this->morphTo();
    }
}
