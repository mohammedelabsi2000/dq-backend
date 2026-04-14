<?php

namespace App\Models;

use App\Traits\Auditable;
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
    ];

    protected $casts = [
        'dob' => 'date',
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

        // مدير فرع فقط ← يوسع لكل مناطق الفرع
        if ($branchIds->isNotEmpty() && $regionIds->isEmpty() && $centerIds->isEmpty() && $halaqaIds->isEmpty()) {
            $regionIds = $regionIds->merge(
                Region::whereIn('branch_id', $branchIds)->pluck('id')
            )->unique();
        }

        // جمع المساجد المسموح بها عبر المناطق
        $mosqueIds = collect();

        if ($regionIds->isNotEmpty()) {
            $mosqueIds = $mosqueIds->merge(
                Mosque::whereIn('region_id', $regionIds)->pluck('id')
            )->unique();
        }

        if ($mosqueIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('mosque_id', $mosqueIds);
    }

    public function previousAchievement()
    {
        return $this->hasOne(PreviousAchievement::class);
    }
}
