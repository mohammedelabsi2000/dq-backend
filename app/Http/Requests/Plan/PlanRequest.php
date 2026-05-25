<?php

namespace App\Http\Requests\Plan;

use App\Enums\PeriodUnit;
use App\Http\Requests\DQFormRequest;
use Illuminate\Validation\Rules\Enum;

class PlanRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'period_unit' => ['required', 'string', new Enum(PeriodUnit::class)],
            'period' => ['required', 'integer', 'min:1'],
            'min_period' => ['nullable', 'integer', 'min:1'],
            'max_period' => ['nullable', 'integer', 'min:1'],
            'tolerance' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم الخطة مطلوب.',
            'name.string' => 'اسم الخطة يجب أن يكون نصاً.',
            'name.max' => 'اسم الخطة لا يجب أن يتجاوز 255 حرفاً.',

            'description.string' => 'الوصف يجب أن يكون نصاً.',

            'period_unit.required' => 'وحدة المدة مطلوبة.',
            'period_unit.string' => 'وحدة المدة يجب أن تكون نصاً.',
            'period_unit.enum' => 'وحدة المدة غير صالحة.',

            'period.required' => 'المدة مطلوبة.',
            'period.integer' => 'المدة يجب أن تكون عدداً صحيحاً.',
            'period.min' => 'المدة يجب أن تكون على الأقل 1.',

            'min_period.integer' => 'المدة الدنيا يجب أن تكون عدداً صحيحاً.',
            'min_period.min' => 'المدة الدنيا يجب أن تكون على الأقل 1.',

            'max_period.integer' => 'المدة القصوى يجب أن تكون عدداً صحيحاً.',
            'max_period.min' => 'المدة القصوى يجب أن تكون على الأقل 1.',


            'tolerance.integer' => 'السماحية يجب أن تكون عدداً صحيحاً.',
            'tolerance.min' => 'السماحية لا يمكن أن تكون سالبة.',

        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم الخطة',
            'description' => 'الوصف',
            'period' => 'المدة',
            'period_unit' => 'وحدة المدة',
            'is_active' => 'الحالة',
            'tolerance' => 'السماحية',
        ];
    }
}
