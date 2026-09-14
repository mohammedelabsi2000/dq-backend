<?php

namespace App\Http\Requests\SponsorBranchQuota;

use App\Models\Sponsor;
use App\Models\SponsorBranchQuota;
use App\Http\Requests\DQFormRequest;

class UpdateSponsorBranchQuotaRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var Sponsor $sponsor */
        $sponsor = $this->route('sponsor');

        return $this->user()->can('update', $sponsor);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'quota' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('quota')) {
                return;
            }

            /** @var Sponsor $sponsor */
            $sponsor = $this->route('sponsor');
            /** @var SponsorBranchQuota $sponsorBranchQuota */
            $sponsorBranchQuota = $this->route('sponsorBranchQuota');

            $quota = (int) $this->input('quota');
            $gender = $sponsorBranchQuota->gender->value;

            $assignedInBranch = $sponsor->activeHalaqaSponsorships()
                ->whereHas('halaqa', fn ($q) => $q->where('gender', $gender)->inBranch($sponsorBranchQuota->branch_id))
                ->count();

            if ($quota < $assignedInBranch) {
                $validator->errors()->add(
                    'quota',
                    "لا يمكن تخفيض الحصة إلى أقل من عدد الحلقات المرتبطة فعلياً بهذا الفرع حالياً ({$assignedInBranch})"
                );

                return;
            }

            $requiredColumn = $gender === 'ذكر' ? 'required_halaqat_male' : 'required_halaqat_female';
            $totalRequired = $sponsor->{$requiredColumn};

            $allocatedByOtherBranches = $sponsor->branchQuotas()
                ->where('gender', $gender)
                ->where('id', '!=', $sponsorBranchQuota->id)
                ->sum('quota');

            if ($allocatedByOtherBranches + $quota > $totalRequired) {
                $remaining = max(0, $totalRequired - $allocatedByOtherBranches);

                $validator->errors()->add(
                    'quota',
                    "الحصة المطلوبة تتجاوز ما تبقى من إجمالي حصص الكفيل لهذا الجنس (الحد الأقصى الممكن: {$remaining})"
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'quota.required' => 'يجب تحديد عدد الحلقات المخصصة لهذا الفرع',
            'quota.min' => 'يجب أن تكون الحصة أكبر من صفر',
        ];
    }
}
