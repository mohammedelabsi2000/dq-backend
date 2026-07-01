<?php

namespace App\Http\Requests\StudentPlan;

use App\Http\Requests\DQFormRequest;

class UpdateStudentPlanRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'starting_level_id'  => ['nullable', 'integer', 'exists:levels,id'],
            'current_level_id'   => ['nullable', 'integer', 'exists:levels,id'],
            'from_date'           => ['nullable', 'date'],
            'is_main'             => ['sometimes', 'boolean'],
            'notes'               => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'starting_level_id' => 'المستوى المبدئي',
            'current_level_id'  => 'المستوى الحالي',
            'from_date'         => 'تاريخ الالتحاق',
            'is_main'           => 'الخطة الرئيسية',
            'notes'             => 'الملاحظات',
        ];
    }

    public function messages(): array
    {
        return [
            'starting_level_id.exists' => 'المستوى المحدد غير موجود',
            'current_level_id.exists'  => 'المستوى المحدد غير موجود',
            'from_date.date'           => 'صيغة التاريخ غير صحيحة',
        ];
    }
}
