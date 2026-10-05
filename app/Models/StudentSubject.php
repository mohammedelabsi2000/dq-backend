<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentSubject extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'grade_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function plan()
    {
        return $this->hasOneThrough(Plan::class, Level::class, 'id', 'id', 'level_id', 'plan_id');
    }

    public function resultStatus()
    {
        return $this->belongsTo(Constant::class, 'result_status_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function studentLevel()
    {
        return $this->belongsTo(StudentLevel::class);
    }

    public function levelTrack()
    {
        return $this->belongsTo(LevelTrack::class);
    }
}
