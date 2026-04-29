<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Concerns\HasVisibilityScope;

class Student extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasVisibilityScope;

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


    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');
        $centerIds = $user->getScopeIds('center');
        $halaqaIds = $user->getScopeIds('halaqa');

        // محفظ حلقة ← طلاب حلقته فقط
        if ($halaqaIds->isNotEmpty()) {
            return $query->whereHas(
                'halaqaEnrollments',
                fn($q) =>
                $q->whereIn('halaqa_id', $halaqaIds)
            );
        }

        // مدير مركز ← طلاب حلقات مركزه
        if ($centerIds->isNotEmpty()) {
            $halaqaIds = Halaqa::where('reference_type', 'center')
                ->whereIn('reference_id', $centerIds)
                ->pluck('id');

            if ($halaqaIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas(
                'halaqaEnrollments',
                fn($q) =>
                $q->whereIn('halaqa_id', $halaqaIds)
            );
        }

        // مدير منطقة ← طلاب حلقات منطقته (مباشرة أو عبر مراكزها)
        if ($regionIds->isNotEmpty()) {
            $centerIds = Center::whereIn('region_id', $regionIds)->pluck('id');

            $halaqaIds = Halaqa::where(function ($q) use ($regionIds, $centerIds) {
                $q->where(function ($q) use ($regionIds) {
                    $q->where('reference_type', 'region')
                        ->whereIn('reference_id', $regionIds);
                })->orWhere(function ($q) use ($centerIds) {
                    if ($centerIds->isNotEmpty()) {
                        $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIds);
                    }
                });
            })->pluck('id');

            if ($halaqaIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas(
                'halaqaEnrollments',
                fn($q) =>
                $q->whereIn('halaqa_id', $halaqaIds)
            );
        }

        // مدير فرع ← يوسع لمناطق الفرع ثم حلقاتها
        if ($branchIds->isNotEmpty()) {
            $regionIds = Region::whereIn('branch_id', $branchIds)->pluck('id');
            $centerIds = Center::whereIn('region_id', $regionIds)->pluck('id');

            $halaqaIds = Halaqa::where(function ($q) use ($regionIds, $centerIds) {
                $q->where(function ($q) use ($regionIds) {
                    $q->where('reference_type', 'region')
                        ->whereIn('reference_id', $regionIds);
                })->orWhere(function ($q) use ($centerIds) {
                    if ($centerIds->isNotEmpty()) {
                        $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIds);
                    }
                });
            })->pluck('id');

            if ($halaqaIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas(
                'halaqaEnrollments',
                fn($q) =>
                $q->whereIn('halaqa_id', $halaqaIds)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeWithStandardRelations($query)
    {
        return $query->with(self::standardRelations());
    }

    public function scopeByGuardian($query, $guardianId)
    {
        return $query->where('guardian_id', $guardianId);
    }

    public function scopeByMosque($query, $mosqueId)
    {
        return $query->where('mosque_id', $mosqueId);
    }

    public function getAgeAttribute()
    {
        $today = now();
        $age = $today->diffInYears($this->dob);
        return $age;
    }

    public function isActiveInHalaqa($halaqaId)
    {
        return $this->halaqas()->where('halaqa_id', $halaqaId)->whereNull('halaqa_students.to_date')->exists();
    }
}
