<?php

namespace App\Http\Requests\Constant;

use App\Http\Requests\DQFormRequest;

class UpdateConstantRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('constant'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:150',
            'constant_type_id' => [
                'sometimes',
                'integer',
                'exists:constant_types,id'
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:constants,id'
            ],
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'name.max' => 'اسم الثابت لا يمكن أن يزيد عن 150 حرف.',
            'constant_type_id.exists' => 'نوع الثابت المحدد غير موجود.',
            'parent_id.exists' => 'الثابت الأب المحدد غير موجود.'
        ];
    }
}
