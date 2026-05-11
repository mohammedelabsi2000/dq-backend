<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserScope extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role_id',
        'scope_type',
        'scope_id',
        'from_date',
        'to_date',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // public function scopeActive($query)
    // {
    //     $now = now();

    //     return $query
    //         ->where(function ($q) use ($now) {
    //             $q->whereNull('from_date')
    //                 ->orWhere('from_date', '<=', $now);
    //         })
    //         ->whereNull('to_date');
    // }

    // public function scopeActive($query)
    // {
    //     $now = now();

    //     return $query
    //         ->where(function ($q) use ($now) {
    //             $q->whereNull('from_date')
    //                 ->orWhere('from_date', '<=', $now);
    //         })
    //         ->whereNull('to_date');   // ← هذا كافٍ، البيانات القديمة to_date = null تبقى نشطة
    // }
    public function scopeActive($query)
    {
        $now = now();

        return $query
            ->where(function ($q) use ($now) {
                $q->whereNull('from_date')
                    ->orWhere('from_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                // ✅ to_date إما null أو في المستقبل
                $q->whereNull('to_date')
                    ->orWhere('to_date', '>', $now);
            });
    }

    public function scopeInactive($query)
    {
        return $query->whereNotNull('to_date')
            ->where('to_date', '<', now());
    }


    // في UserScope model
    public function toHierarchyCollection(): \Illuminate\Support\Collection
    {
        $result = collect([$this]);

        if ($this->scope_type === 'region') {
            $region = Region::find($this->scope_id);
            if ($region) {
                $result->push(new self(['scope_type' => 'branch', 'scope_id' => $region->branch_id]));
            }
        }

        if ($this->scope_type === 'center') {
            $center = Center::with('region')->find($this->scope_id);
            if ($center) {
                $result->push(new self(['scope_type' => 'region', 'scope_id' => $center->region_id]));
                $result->push(new self(['scope_type' => 'branch', 'scope_id' => $center->region->branch_id]));
            }
        }

        if ($this->scope_type === 'halaqa') {
            $halaqa = Halaqa::find($this->scope_id);
            if ($halaqa?->reference_type?->value === 'center') {
                $center = Center::with('region')->find($halaqa->reference_id);
                if ($center) {
                    $result->push(new self(['scope_type' => 'center', 'scope_id' => $center->id]));
                    $result->push(new self(['scope_type' => 'region', 'scope_id' => $center->region_id]));
                    $result->push(new self(['scope_type' => 'branch', 'scope_id' => $center->region->branch_id]));
                }
            } elseif ($halaqa?->reference_type?->value === 'region') {
                $region = Region::find($halaqa->reference_id);
                if ($region) {
                    $result->push(new self(['scope_type' => 'region', 'scope_id' => $region->id]));
                    $result->push(new self(['scope_type' => 'branch', 'scope_id' => $region->branch_id]));
                }
            }
        }

        return $result;
    }
}
