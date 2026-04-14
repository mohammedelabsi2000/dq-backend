<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Contracts\BelongsToHierarchy;
use App\Enums\Gender;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Student extends Model implements BelongsToHierarchy
{
    use HasFactory, SoftDeletes, Auditable, HasHierarchyScope;

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
        'created_by',
        'updated_by',
        'memorized_juz',
        'completed_juz',
        'surah_id',
        'end_aya',
    ];

    public static function standardRelations()
    {
        return [
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardian',
            'guardianType',
            'prefixName',
            'halaqas' => fn($q) => $q->withPivot(['from_date', 'to_date', 'enrollment_status_id']),
        ];
    }


    protected $casts = [
        'dob' => 'date',
        'gender' => Gender::class
    ];

    public static $usesAudit = true;

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
    public function getGenderTextAttribute()
    {
        return $this->gender?->label() ?? 'غير محدد';
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
            ->withPivot(['from_date', 'to_date', 'enrollment_status_id'])
            ->withTimestamps();
    }

    public function getHierarchyIds(): array
    {
        // تحميل halaqas مع العلاقات المطلوبة فقط
        $this->loadMissing([
            'halaqas' => function ($query) {
                $query->with(['reference'])
                    ->select('halaqas.*');  // تجنب تحميل بيانات pivot
            }
        ]);

        $ids = [];

        foreach ($this->halaqas as $halaqa) {
            $ids[] = ['id' => $halaqa->id, 'type' => 'halaqa'];

            if ($halaqa->reference_type === 'center') {
                $center = $halaqa->reference;

                if ($center) {
                    $ids[] = ['id' => $center->id, 'type' => 'center'];
                    if ($center->region_id) {
                        $ids[] = ['id' => $center->region_id, 'type' => 'region'];

                        // تحميل region للحصول على branch_id
                        $center->loadMissing('region');
                        if ($center->region) {
                            $ids[] = ['id' => $center->region->branch_id, 'type' => 'branch'];
                        }
                    }
                }
            } elseif ($halaqa->reference_type === 'region') {
                $region = $halaqa->reference;

                if ($region) {
                    $ids[] = ['id' => $region->id, 'type' => 'region'];
                    if ($region->branch_id) {
                        $ids[] = ['id' => $region->branch_id, 'type' => 'branch'];
                    }
                }
            }
        }

        $ids[] = ['id' => $this->id, 'type' => 'student'];

        return array_values(array_unique($ids, SORT_REGULAR));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->applyVisibleTo($query, $user, [
            'halaqa' => fn($q, $scopeId) => $q->orWhereHas(
                'halaqas',
                fn($q) => $q->where('halaqas.id', $scopeId)
            ),

            'center' => fn($q, $scopeId) => $q->orWhereHas(
                'halaqas',
                fn($q) => $q->where('reference_type', 'center')
                    ->where('reference_id', $scopeId)
            ),

            'region' => fn($q, $scopeId) => $q->orWhere(function ($q) use ($scopeId) {
                $q->orWhereHas(
                    'halaqas',
                    fn($q) => $q->where('reference_type', 'region')
                        ->where('reference_id', $scopeId)
                );

                $centerIds = Center::where('region_id', $scopeId)->pluck('id');
                if ($centerIds->isNotEmpty()) {
                    $q->orWhereHas(
                        'halaqas',
                        fn($q) => $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIds)
                    );
                }
            }),

            'branch' => fn($q, $scopeId) => $q->orWhere(function ($q) use ($scopeId) {
                $regionIds = Region::where('branch_id', $scopeId)->pluck('id');

                if ($regionIds->isNotEmpty()) {
                    $q->orWhereHas(
                        'halaqas',
                        fn($q) => $q->where('reference_type', 'region')
                            ->whereIn('reference_id', $regionIds)
                    );

                    $centerIds = Center::whereIn('region_id', $regionIds)->pluck('id');
                    if ($centerIds->isNotEmpty()) {
                        $q->orWhereHas(
                            'halaqas',
                            fn($q) => $q->where('reference_type', 'center')
                                ->whereIn('reference_id', $centerIds)
                        );
                    }
                }
            }),
        ]);
    }

    public function scopeWithStandardRelations($query)
    {
        return $query->with(self::standardRelations());
    }
}