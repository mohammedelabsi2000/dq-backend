<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id'      => ['required', 'exists:plans,id'],
            'name'         => ['required', 'string', 'max:255'],
            'order'        => ['required', 'integer', 'min:1'],
            'duration'     => ['required', 'integer', 'min:1'],
            'max_duration' => ['required', 'integer', 'min:1', 'gte:duration'],
            'min_duration' => ['required', 'integer', 'min:1', 'lte:duration'],
            'duration_unit' => ['required', 'string', 'in:يوم,اسبوع,شهر,سنة'],
            'notes'        => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'plan_id'      => 'الخطة',
            'name'         => 'اسم المستوى',
            'order'        => 'الترتيب',
            'duration'     => 'المدة الافتراضية',
            'max_duration' => 'أقصى مدة',
            'min_duration' => 'أدنى مدة',
            'duration_unit' => 'وحدة المدة',
            'notes'        => 'الملاحظات',
        ];
    }
}
