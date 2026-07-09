<?php

namespace App\Http\Requests\StudentPlan;

use App\Enums\StudentPlanStatus;
use App\Http\Requests\DQFormRequest;

class CloseStudentPlanRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:completed,transferred,dropped'],
            'date'   => ['nullable', 'date'],
            'notes'  => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'الحالة',
            'date'   => 'التاريخ',
            'notes'  => 'الملاحظات',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حقل الحالة مطلوب',
            'status.in'       => 'الحالة يجب أن تكون: مكتمل، منتقل، أو منقطع',
        ];
    }
}