<?php

namespace App\Models\Quran;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    protected $connection = 'quran';
    protected $guarded = [];
}
