<?php

namespace App\Http\Requests\Halaqa;

use App\Helpers\ConstantHelper;
use App\Http\Requests\DQFormRequest;
use Illuminate\Validation\Rule;

class UpdateHalaqaRequest extends DQFormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',

            'reference_type' => [
                'sometimes',
                'required',
                Rule::in([
                    \App\Models\Center::class,
                    \App\Models\Region::class,
                ])
            ],
            'reference_id' => [
                'sometimes',
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $type = request('reference_type');

                    if (!$type || !class_exists($type)) {
                        $fail('نوع المرجع غير صالح.');
                        return;
                    }

                    if (!$type::where('id', $value)->exists()) {
                        $fail('المعرف غير موجود في المرجع المحدد.');
                    }
                },
            ],

            'type_id' => [
                'sometimes',
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('halaqa_types')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // name
            'name.required' => 'اسم الحلقة مطلوب.',
            'name.string' => 'اسم الحلقة يجب أن يكون نص.',
            'name.max' => 'اسم الحلقة يجب ألا يتجاوز 255 حرف.',

            // location
            'location.string' => 'الموقع يجب أن يكون نص.',
            'location.max' => 'الموقع يجب ألا يتجاوز 255 حرف.',

            // description
            'description.string' => 'الوصف يجب أن يكون نص.',

            // reference_type
            'reference_type.required' => 'نوع المرجع مطلوب.',
            'reference_type.in' => 'نوع المرجع غير صالح.',

            // reference_id
            'reference_id.required' => 'معرف المرجع مطلوب.',
            'reference_id.integer' => 'معرف المرجع يجب أن يكون رقم صحيح.',

            // type_id
            'type_id.required' => 'نوع الحلقة مطلوب.',
            'type_id.in' => 'نوع الحلقة غير موجود في النظام.',
        ];
    }
}
