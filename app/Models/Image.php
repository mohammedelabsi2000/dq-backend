<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

   protected $fillable = [
    'imageable_id',
    'imageable_type',
    'file_name',
    'file_path',
    'disk',
    'mime_type',
    'file_size',
    'image_type',
    'sort_order',
    'is_main',
    'notes'
];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    // Polymorphic relation
    public function imageable()
    {
        return $this->morphTo();
    }
}
