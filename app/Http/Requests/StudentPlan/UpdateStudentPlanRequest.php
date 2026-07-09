<?php

namespace App\Http\Requests\StudentPlan;

use App\Http\Requests\DQFormRequest;
use App\Models\Plan;

class UpdateStudentPlanRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        $plan = \App\Models\Plan::find($this->route('plan'));
        return $plan ? $this->user()->can('updateStudent', $plan) : false;
    }

    public function rules(): array
    {
        return [
            'starting_level_id'  => ['nullable', 'integer', 'exists:levels,id'],
            'current_level_id'   => ['nullable', 'integer', 'exists:levels,id'],
            'from_date'           => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'is_main'             => ['sometimes', 'boolean'],
            'status' => ['nullable', 'string'],
            'notes'               => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'starting_level_id' => 'المستوى المبدئي',
            'current_level_id'  => 'المستوى الحالي',
            'from_date'         => 'تاريخ الالتحاق',
            'to_date'           => 'تاريخ الانتهاء',
            'is_main'           => 'الخطة الرئيسية',
            'status'            => 'الحالة',
            'notes'             => 'الملاحظات',
        ];
    }

    public function messages(): array
    {
        return [
            'starting_level_id.exists' => 'المستوى المحدد غير موجود',
            'current_level_id.exists'  => 'المستوى المحدد غير موجود',
            'from_date.date'           => 'صيغة التاريخ غير صحيحة',
            'to_date.date'             => 'صيغة تاريخ الانتهاء غير صحيحة',
            'to_date.after_or_equal'   => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ الالتحاق',
        ];
    }
}
