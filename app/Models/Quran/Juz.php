<?php

namespace App\Models\Quran;

class Juz extends QuranModel
{
    protected $table = 'quran_juz';
    protected $guarded = [];

    public function start_surah()
    {
        return $this->belongsTo(Surah::class, 'start_surah_id');
    }

    public function end_surah()
    {
        return $this->belongsTo(Surah::class, 'end_surah_id');
    }
}
