<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * لجنة اختبار نهائي: تُشكَّل بعد نجاح الطلاب في الاختبار التجريبي ويُنسَّب لها المرشحون.
 */
class ExamCommittee extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $guarded = [];

    protected $casts = [
        'is_multi_subject' => 'boolean',
        'start_date' => 'date',
        'proposed_exam_date' => 'datetime',
        'gender' => Gender::class,
    ];

    /**
     * المساق - للجنة المساق الواحد فقط
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * الطلاب المنسَّبون للجنة
     */
    public function committeeStudents()
    {
        return $this->hasMany(ExamCommitteeStudent::class);
    }
}
