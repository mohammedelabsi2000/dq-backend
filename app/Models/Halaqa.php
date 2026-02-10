<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Halaqa extends Model
{
    use HasFactory;

    protected $table = 'halaqas';

    protected $fillable = [
        'name',
        'location',
        'description',
        'center_id',
        'constant_id',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    // غيّر Constant إلى اسم موديل الثوابت الحقيقي عندك (مثلاً Thabit)
    public function constant()
    {
        return $this->belongsTo(Constant::class);
    }
}
