<?php

namespace App\Models;

use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Center extends Model implements BelongsToHierarchy
{
    use HasFactory;

    protected $fillable = ['name', 'notes', 'region_id', 'mosque_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function halaqat()
    {
        return $this->hasMany(Halaqa::class);
    }

    public function getHierarchyIds(): array
    {
        $this->loadMissing('region');

        return [
            ['id' => $this->region->branch_id, 'type' => 'branch'],
            ['id' => $this->region_id,          'type' => 'region'],
            ['id' => $this->id,                 'type' => 'center'],
        ];
    }

    public function getHierarchyData()
    {
        $this->loadMissing('region');

        return [
            ['id' => $this->region->branch_id, 'type' => 'branch', 'name' => $this->region->branch->name],
            ['id' => $this->region_id,          'type' => 'region', 'name' => $this->region->name],
            ['id' => $this->id,                 'type' => 'center', 'name' => $this->name],
        ];
    }
}
