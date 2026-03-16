<?php

namespace App\Models;

use App\Contracts\BelongsToHierarchy;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Student extends Model implements BelongsToHierarchy
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'identity',
        'fName',
        'sName',
        'thName',
        'family',
        'dob',
        'mosque_id',
        'location',
        'gender',
        'marital_status_id',
        'money_status_id',
        'prefix_name_id',
        'guardian_id',
        'guardian_type_id',
        'phone',
        'whatsapp',
    ];

    protected $appends = ['full_name'];


    public function images()
    {
        return $this->morphMany(\App\Models\Image::class, 'imageable');
    }

    public function mainImage()
    {
        return $this->morphOne(\App\Models\Image::class, 'imageable')
            ->where('is_main', true);
    }


    protected static function booted()
    {
        static::deleting(function ($student) {

            foreach ($student->images as $image) {

                Storage::disk($image->disk)->delete($image->file_path);

                $image->delete();
            }
        });
    }
    // full_name عمود ظاهري في DB لكن نضيفه هنا كاحتياط
    public function getFullNameAttribute()
    {
        return implode(' ', array_filter([
            $this->fName,
            $this->sName,
            $this->thName,
            $this->family,
        ]));
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function maritalStatus()
    {
        return $this->belongsTo(Constant::class, 'marital_status_id');
    }

    public function moneyStatus()
    {
        return $this->belongsTo(Constant::class, 'money_status_id');
    }

    public function guardianType()
    {
        return $this->belongsTo(Constant::class, 'guardian_type_id');
    }

    public function prefixName()
    {
        return $this->belongsTo(Constant::class, 'prefix_name_id');
    }

    public function guardian()
    {
        return $this->belongsTo(User::class, 'guardian_id', 'identity');
    }

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function halaqaEnrollments()
    {
        return $this->hasMany(HalaqaStudent::class);
    }

    public function halaqas()
    {
        return $this->belongsToMany(Halaqa::class, 'halaqa_students')
            ->withPivot(['from_date', 'to_date', 'status_id'])
            ->withTimestamps()
            ->using(HalaqaStudent::class);
    }

    public function getHierarchyIds(): array
    {
        $this->loadMissing('halaqas.reference.region');

        $ids = [];

        foreach ($this->halaqas as $halaqa) {
            $ids[] = ['id' => $halaqa->id, 'type' => 'halaqa'];

            if ($halaqa->reference_type === 'center') {
                $center = $halaqa->reference;

                $ids[] = ['id' => $center->id,                'type' => 'center'];
                $ids[] = ['id' => $center->region_id,          'type' => 'region'];
                $ids[] = ['id' => $center->region->branch_id,  'type' => 'branch'];
            }

            if ($halaqa->reference_type === 'region') {
                $region = $halaqa->reference;

                $ids[] = ['id' => $region->id,        'type' => 'region'];
                $ids[] = ['id' => $region->branch_id, 'type' => 'branch'];
            }
        }

        $ids[] = ['id' => $this->id, 'type' => 'student'];

        return array_values(array_unique($ids, SORT_REGULAR));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $user->loadMissing('roles');

        // مدير عام → يشوف الكل
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $roles = $user->roles;

        if ($roles->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $branchIds = $roles->where('pivot.scope_type', 'branch')->pluck('pivot.scope_id');
        $regionIds = $roles->where('pivot.scope_type', 'region')->pluck('pivot.scope_id');
        $centerIds = $roles->where('pivot.scope_type', 'center')->pluck('pivot.scope_id');
        $halaqaIds = $roles->where('pivot.scope_type', 'halaqa')->pluck('pivot.scope_id');

        return $query->where(function (Builder $q) use ($branchIds, $regionIds, $centerIds, $halaqaIds) {

            // طلاب حلقة محددة
            if ($halaqaIds->isNotEmpty()) {
                $q->orWhereHas(
                    'halaqas',
                    fn($q) =>
                    $q->whereIn('halaqas.id', $halaqaIds)
                );
            }

            // طلاب حلقات مركز محدد
            if ($centerIds->isNotEmpty()) {
                $q->orWhereHas(
                    'halaqas',
                    fn($q) =>
                    $q->where('reference_type', 'center')
                        ->whereIn('reference_id', $centerIds)
                );
            }

            // طلاب حلقات منطقة محددة
            if ($regionIds->isNotEmpty()) {
                $q->orWhereHas(
                    'halaqas',
                    fn($q) =>
                    $q->where('reference_type', 'region')
                        ->whereIn('reference_id', $regionIds)
                );

                $centerIdsFromRegion = Center::whereIn('region_id', $regionIds)->pluck('id');
                if ($centerIdsFromRegion->isNotEmpty()) {
                    $q->orWhereHas(
                        'halaqas',
                        fn($q) =>
                        $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIdsFromRegion)
                    );
                }
            }

            // طلاب حلقات فرع محدد
            if ($branchIds->isNotEmpty()) {
                $regionIdsFromBranch = Region::whereIn('branch_id', $branchIds)->pluck('id');

                if ($regionIdsFromBranch->isNotEmpty()) {
                    $q->orWhereHas(
                        'halaqas',
                        fn($q) =>
                        $q->where('reference_type', 'region')
                            ->whereIn('reference_id', $regionIdsFromBranch)
                    );

                    $centerIdsFromBranch = Center::whereIn('region_id', $regionIdsFromBranch)->pluck('id');
                    if ($centerIdsFromBranch->isNotEmpty()) {
                        $q->orWhereHas(
                            'halaqas',
                            fn($q) =>
                            $q->where('reference_type', 'center')
                                ->whereIn('reference_id', $centerIdsFromBranch)
                        );
                    }
                }
            }
        });
    }
}
