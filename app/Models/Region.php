<?php

namespace App\Models;

use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model implements BelongsToHierarchy
{
    use HasFactory;

    protected $fillable = ['name', 'branch_id', 'notes'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function mosques()
    {
        return $this->hasMany(Mosque::class);
    }

    public function getHierarchyIds(): array
    {
        return [
            ['id' => $this->branch_id, 'type' => Branch::class],
            ['id' => $this->id,        'type' => self::class],
        ];
    }
}
