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

    public function branchQuotas()
    {
        return $this->hasMany(SponsorBranchQuota::class);
    }

    public function scopeIsActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * عدد الحلقات المتبقية التي يمكن للكفيل كفالتها من جنس معيّن، بناءً على
     * الحد الذي أدخله (required_halaqat_male/female) مطروحًا منه الحلقات المرتبطة به حاليًا وفعّالة.
     * هذا هو السقف الإجمالي عبر كل الأفرع مجتمعة.
     */
    public function remainingCapacityFor(string $gender): int
    {
        $requiredColumn = $gender === 'ذكر' ? 'required_halaqat_male' : 'required_halaqat_female';

        $assignedCount = $this->activeHalaqaSponsorships()
            ->whereHas('halaqa', fn ($q) => $q->where('gender', $gender))
            ->count();

        return max(0, $this->{$requiredColumn} - $assignedCount);
    }

    /**
     * عدد الحلقات المتبقية التي يمكن للكفيل كفالتها من جنس معيّن، ضمن فرع معيّن تحديداً.
     *
     * إذا لم توزَّع أي حصة على أي فرع لهذا الجنس، تُستخدم السعة الإجمالية للكفيل (سلوك سابق، غير مقسّم).
     * أما إذا كانت هناك حصص موزعة على الأفرع لهذا الجنس، فأي فرع بلا حصة مخصصة له يُعتبر بلا سعة متبقية إطلاقاً،
     * حتى لا "يسرق" فرع من حصة فرع آخر.
     */
    public function remainingCapacityForBranch(?int $branchId, string $gender): int
    {
        $hasAnyQuotaForGender = $this->branchQuotas()->where('gender', $gender)->exists();

        if (!$hasAnyQuotaForGender) {
            return $this->remainingCapacityFor($gender);
        }

        if (!$branchId) {
            return 0;
        }

        $quota = $this->branchQuotas()
            ->where('branch_id', $branchId)
            ->where('gender', $gender)
            ->first();

        if (!$quota) {
            return 0;
        }

        $assignedInBranch = $this->activeHalaqaSponsorships()
            ->whereHas('halaqa', fn ($q) => $q->where('gender', $gender)->inBranch($branchId))
            ->count();

        return max(0, $quota->quota - $assignedInBranch);
    }

    /**
     * الحصة الكلية للكفيل لجنس معيّن مطروحًا منها ما تم توزيعه فعلاً على الأفرع (سواء استُهلك أم لا).
     * هذا هو "المتبقي غير الموزَّع" الذي ما زال متاحًا لتخصيصه لفرع جديد.
     */
    public function unallocatedQuotaFor(string $gender): int
    {
        $requiredColumn = $gender === 'ذكر' ? 'required_halaqat_male' : 'required_halaqat_female';

        $allocated = $this->relationLoaded('branchQuotas')
            ? $this->branchQuotas->filter(fn (SponsorBranchQuota $q) => $q->gender->value === $gender)->sum('quota')
            : $this->branchQuotas()->where('gender', $gender)->sum('quota');

        return max(0, $this->{$requiredColumn} - $allocated);
    }
}
