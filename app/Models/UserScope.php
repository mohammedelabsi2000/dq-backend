<?php

namespace App\Models;

use App\Support\ScopeCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserScope extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static $usesAudit = true;

    protected $fillable = [
        'user_id',
        'role_id',
        'scope_type',
        'scope_id',
        'from_date',
        'to_date',
        'created_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];


    protected static function booted()
    {
        // أي تعديل على النطاقات يُبطل الذاكرة المؤقتة للنطاقات داخل نفس الطلب
        static::saved(fn() => app(ScopeCache::class)->flush());
        static::deleted(fn() => app(ScopeCache::class)->flush());
    }

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
        // كيانات الهرمية تُجلب مرة واحدة لكل طلب مهما تكررت بين المستخدمين
        $cache = app(ScopeCache::class);

        if ($this->scope_type === 'region') {
            $region = $cache->entity('region', $this->scope_id);
            if ($region) {
                $result->push(new self(['scope_type' => 'branch', 'scope_id' => $region->branch_id]));
            }
        }

        if ($this->scope_type === 'center') {
            $center = $cache->entity('center', $this->scope_id);
            if ($center) {
                $result->push(new self(['scope_type' => 'region', 'scope_id' => $center->region_id]));
                $result->push(new self(['scope_type' => 'branch', 'scope_id' => $cache->entity('region', $center->region_id)?->branch_id]));
            }
        }

        if ($this->scope_type === 'halaqa') {
            $halaqa = $cache->entity('halaqa', $this->scope_id);
            if ($halaqa?->reference_type?->value === 'center') {
                $center = $cache->entity('center', $halaqa->reference_id);
                if ($center) {
                    $result->push(new self(['scope_type' => 'center', 'scope_id' => $center->id]));
                    $result->push(new self(['scope_type' => 'region', 'scope_id' => $center->region_id]));
                    $result->push(new self(['scope_type' => 'branch', 'scope_id' => $cache->entity('region', $center->region_id)?->branch_id]));
                }
            } elseif ($halaqa?->reference_type?->value === 'region') {
                $region = $cache->entity('region', $halaqa->reference_id);
                if ($region) {
                    $result->push(new self(['scope_type' => 'region', 'scope_id' => $region->id]));
                    $result->push(new self(['scope_type' => 'branch', 'scope_id' => $region->branch_id]));
                }
            }
        }

        return $result;
    }
}
