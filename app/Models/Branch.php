<?php

namespace App\Models;

use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model implements BelongsToHierarchy
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function getHierarchyIds(): array
    {
        return [
            ['id' => $this->id, 'type' => 'branch'],
        ];
    }
}
