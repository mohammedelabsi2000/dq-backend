<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'student_id',
        'assignment_type',
        'criteria'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function student()
{
    return $this->belongsTo(Student::class);
}

}
