<?php

namespace App\Http\Requests\Plan;

use App\Http\Requests\DQFormRequest;

class StorePlanRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'duration_in_days' => 'required|integer',
            'grace_period_days' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Custom error messages for validation
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'name.required' => 'حقل اسم الخطة مطلوب.',
            'name.string' => 'اسم الخطة يجب أن يكون نصاً.',
            'name.max' => 'اسم الخطة يجب ألا يتجاوز 255 حرفاً.',

            'duration_in_days.required' => 'حقل مدة الخطة مطلوب.',
            'duration_in_days.integer' => 'مدة الخطة يجب أن تكون رقماً صحيحاً.',

            'grace_period_days.integer' => 'فترة السماح يجب أن تكون رقماً صحيحاً.',

            'is_active.boolean' => 'حقل التفعيل يجب أن يكون صحيح أو خطأ (true/false).',
        ];
    }
}
