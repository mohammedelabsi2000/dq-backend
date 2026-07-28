<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicQualification extends Model
{
    protected $appends = ['full_name']; // رح يظهر تلقائياً لو حولنا الموديل لـ JSON

  
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
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static $usesAudit = true;

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    /* ================= Relations ================= */

    public function academicDegree()
    {
        return $this->belongsTo(Constant::class, 'academic_degree_id');
    }
    public function getPersonNameAttribute()
    {
        if ($this->person) {
            // مثال: إذا الشخص عنده fname و lname
            return $this->person->fname . ' ' . $this->person->lname;
        }
        return $this->person_type . ' #' . $this->person_id; // fallback
    }
    /**
     * This method defines a relationship between the AcademicQualification model and the Constant model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function major()
    {
        return $this->belongsTo(Constant::class, 'major_id');
    }

    /**
     * This method defines a polymorphic relationship between the AcademicQualification model and any model that can be associated with it (like User or Student)
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function person()
    {
        return $this->morphTo();
    }

    // علاقة Polymorphic مع الصور (يمكن أكثر من صورة لكل مؤهل)
    public function images()
    {
        return $this->morphOne(Image::class, 'imageable');
    }


}
