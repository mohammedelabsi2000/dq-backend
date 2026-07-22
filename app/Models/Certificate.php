<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

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

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
