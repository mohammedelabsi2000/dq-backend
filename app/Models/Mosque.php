<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\HasVisibilityScope;

class Mosque extends Model
{
    use HasFactory, HasVisibilityScope;

    protected $fillable = ['name', 'notes', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function centers()
    {
        return $this->hasMany(Center::class);
    }

    /**
     * العلاقة مع المستخدمين
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
