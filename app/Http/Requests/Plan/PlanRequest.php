<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'duration'      => ['required', 'integer', 'min:1'],
            'duration_unit' => ['required', 'string', 'in:يوم,اسبوع,شهر,سنة'],
            'is_active'     => ['sometimes', 'boolean'],
            'tolerance'     => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'          => 'اسم الخطة',
            'description'   => 'الوصف',
            'duration'      => 'المدة',
            'duration_unit' => 'وحدة المدة',
            'is_active'     => 'الحالة',
            'tolerance'     => 'السماحية',
        ];
    }
}
