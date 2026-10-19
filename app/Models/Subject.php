<?php

namespace App\Models;

use App\Models\Quran\CustomJuz;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes, HasFactory;

    public static $usesAudit = true;

    protected $guarded = ['id'];

    // ========================
    // Relations
    // ========================

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class)->withDefault();
    }

    public function customJuz(): BelongsTo
    {
        return $this->belongsTo(CustomJuz::class);
    }

    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(Constant::class);
    }

    public function levelTrackSubjects(): HasMany
    {
        return $this->hasMany(LevelTrackSubject::class);
    }

    public function levelTracks(): BelongsToMany
    {
        return $this->belongsToMany(LevelTrack::class, 'level_track_subjects')
            ->withPivot('is_required')
            ->withTimestamps();
    }

    public function subjectRequirements(): HasMany
    {
        return $this->hasMany(SubjectRequirement::class);
    }

    public function studentSubjects()
    {
        return $this->belongsToMany(Student::class, 'student_subjects')
            ->withPivot('level_id', 'result_status_id','grade', 'from_date', 'to_date','grade_date', 'teacher_id', 'notes');
        return $this->hasMany(StudentSubject::class)/* 
            ->withPivot('level_id', 'result_status_id','grade', 'from_date', 'to_date','grade_date', 'teacher_id', 'notes') */;
    }
}
