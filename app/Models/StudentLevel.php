<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class StudentLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function plan()
    {
        return $this->hasOneThrough(Plan::class, Level::class);
    }
    
    public function studentSubjects()
    {
        return $this->hasMany(StudentSubject::class);
    }
}
