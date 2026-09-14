<?php

namespace App\Http\Requests\SponsorBranchQuota;

use App\Models\Sponsor;
use App\Http\Requests\DQFormRequest;

class StoreSponsorBranchQuotaRequest extends DQFormRequest
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
            'branch_id' => 'required|integer|exists:branches,id',
            'gender' => 'required|in:ذكر,أنثى',
            'quota' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            /** @var Sponsor $sponsor */
            $sponsor = $this->route('sponsor');

            if (!$this->filled('branch_id') || !$this->filled('gender') || !$this->filled('quota')) {
                return;
            }

            $branchId = (int) $this->input('branch_id');
            $gender = $this->input('gender');
            $quota = (int) $this->input('quota');

            $alreadyExists = $sponsor->branchQuotas()
                ->where('branch_id', $branchId)
                ->where('gender', $gender)
                ->exists();

            if ($alreadyExists) {
                $validator->errors()->add(
                    'branch_id',
                    'يوجد بالفعل حصة مخصصة لهذا الفرع بنفس الجنس، عدّل الحصة الموجودة بدلاً من إنشاء واحدة جديدة'
                );

                return;
            }

            $requiredColumn = $gender === 'ذكر' ? 'required_halaqat_male' : 'required_halaqat_female';
            $totalRequired = $sponsor->{$requiredColumn};

            $alreadyAllocated = $sponsor->branchQuotas()->where('gender', $gender)->sum('quota');

            if ($alreadyAllocated + $quota > $totalRequired) {
                $remaining = max(0, $totalRequired - $alreadyAllocated);

                $validator->errors()->add(
                    'quota',
                    "الحصة المطلوبة تتجاوز ما تبقى من إجمالي حصص الكفيل لهذا الجنس (المتبقي غير الموزّع: {$remaining})"
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'يجب اختيار الفرع',
            'branch_id.exists' => 'الفرع المحدد غير موجود',
            'gender.required' => 'يجب تحديد الجنس',
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
            'quota.required' => 'يجب تحديد عدد الحلقات المخصصة لهذا الفرع',
            'quota.min' => 'يجب أن تكون الحصة أكبر من صفر',
        ];
    }
}
