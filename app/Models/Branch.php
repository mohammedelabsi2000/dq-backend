<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
        'max_replacement_limit',
        'min_replacement_limit',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
