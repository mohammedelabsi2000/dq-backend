<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsor extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = [
        'name',
        'project_number',
        'follow_up_entity',
        'sponsorship_type',
        'start_date',
        'end_date',
        'required_halaqat_male',
        'required_halaqat_female',
        'students_per_halaqa_male',
        'students_per_halaqa_female',
        'student_type_id',
        'student_type_other_note',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
    ];

    public function studentType()
    {
        return $this->belongsTo(Constant::class, 'student_type_id');
    }

    public function halaqaSponsorships()
    {
        return $this->hasMany(HalaqaSponsorship::class);
    }

    public function activeHalaqaSponsorships()
    {
        return $this->hasMany(HalaqaSponsorship::class)->whereNull('to_date');
    }

    public function attachments()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function scopeIsActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * عدد الحلقات المتبقية التي يمكن للكفيل كفالتها من جنس معيّن، بناءً على
     * الحد الذي أدخله (required_halaqat_male/female) مطروحًا منه الحلقات المرتبطة به حاليًا وفعّالة.
     */
    public function remainingCapacityFor(string $gender): int
    {
        $requiredColumn = $gender === 'ذكر' ? 'required_halaqat_male' : 'required_halaqat_female';

        $assignedCount = $this->activeHalaqaSponsorships()
            ->whereHas('halaqa', fn ($q) => $q->where('gender', $gender))
            ->count();

        return max(0, $this->{$requiredColumn} - $assignedCount);
    }
}
