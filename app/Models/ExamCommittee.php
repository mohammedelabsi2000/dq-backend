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

    // الحالة عندنا قبل الإرسال؛ بعده تُحفظ تسمية حالة الدفعة في الجودة (status) ومفتاحها (quality_status)
    public const DRAFT = 'مسودة';

    protected $guarded = [];

    protected $casts = [
        'is_multi_subject' => 'boolean',
        'start_date' => 'date',
        'proposed_exam_date' => 'datetime',
        'sent_at' => 'datetime',
        'exam_datetime' => 'datetime',
        'status_updated_at' => 'datetime',
        'gender' => Gender::class,
    ];

    /**
     * تُعدَّل اللجنة وطلابها قبل الإرسال للجودة فقط، أو بعد أن ترفض الجودة الدفعة
     */
    public function isEditable(): bool
    {
        return !$this->standard_committee_id
            || str_contains((string) $this->quality_status, 'reject');
    }

    /**
     * محفّظ لجنة المساق الواحد كما يُرسل للجودة
     */
    public function teacher()
    {
        // محفّظة لجنة الطالبات تظهر حتى لمن لا يرى الجنسين
        return $this->belongsTo(User::class, 'teacher_id')
            ->withoutGlobalScope(\App\Models\Scopes\GenderVisibilityScope::class);
    }

    public function finalExams()
    {
        return $this->hasMany(FinalExam::class);
    }

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
