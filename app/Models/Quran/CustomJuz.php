<?php

namespace App\Models\Quran;

use Illuminate\Database\Eloquent\Model;

class CustomJuz extends Model
{
    protected $table = "custom_juz";
    protected $guarded = [];

    // Always load surah relationships
    protected $with = ['start_surah', 'end_surah'];

    public function start_surah()
    {
        return $this->belongsTo(Surah::class, 'start_surah_id');
    }

    public function end_surah()
    {
        return $this->belongsTo(Surah::class, 'end_surah_id');
    }
}
