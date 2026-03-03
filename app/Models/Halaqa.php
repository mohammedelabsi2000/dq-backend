<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Halaqa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'halaqas';

    protected $fillable = [
        'name',
        'location',
        'description',
        'reference_type',
        'reference_id',
        'type_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Polymorphic relation (reference)
     */
    public function reference()
    {
        if ($this->reference_type == \App\Models\Center::class) {
            // $this->reference;
        }
        return $this->morphTo();
    }

    /**
     * Type relation (constants table)
     */
    public function type()
    {
        return $this->belongsTo(Constant::class, 'type_id');
    }

    /**
     * Get the student enrollments for this halaqa.
     */
    public function studentEnrollments()
    {
        return $this->hasMany(HalaqaStudent::class);
    }

    /**
     * Get the students enrolled in this halaqa.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'halaqa_students')
            ->withPivot(['from_date', 'to_date', 'status_id'])
            ->withTimestamps();
        // ->using(HalaqaStudent::class);
    }
}
