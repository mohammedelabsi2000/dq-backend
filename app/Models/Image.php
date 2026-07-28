<?php

namespace App\Models;

use App\Enums\ImageType;
use App\Enums\StorageDisk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

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
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'image_type' => ImageType::class,
        'disk' => StorageDisk::class
    ];

    // Polymorphic relation
    public function imageable()
    {
        return $this->morphTo();
    }
}
