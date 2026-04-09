<?php

namespace App\Models;

use App\Models\Quran\CustomJuz;
use App\Models\Quran\Surah;
use Illuminate\Database\Eloquent\Model;

class PreviousAchievement extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function memorizedJuz()
    {
        return $this->belongsTo(CustomJuz::class, 'memorized_juz_id');
    }

    public function completedJuz()
    {
        return $this->belongsTo(CustomJuz::class, 'completed_juz_id');
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class, 'surah_id');
    }
}
