<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Center extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'notes', 'mosque_id'];

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }
}
