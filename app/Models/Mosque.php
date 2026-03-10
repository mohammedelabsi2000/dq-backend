<?php

namespace App\Models;

use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mosque extends Model implements BelongsToHierarchy
{
    use HasFactory;

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

    public function getHierarchyIds(): array
    {
        $this->loadMissing('region');

        return [
            ['id' => $this->region->branch_id, 'type' => 'branch'],
            ['id' => $this->region_id,          'type' => 'region'],
            ['id' => $this->id,                 'type' => 'mosque'],
        ];
    }
}
