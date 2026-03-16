<?php

namespace App\Http\Requests\Constant;

use App\Http\Requests\DQFormRequest;
use App\Models\Constant;

class StoreConstantRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Constant::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:150',
            'constant_type_id' => [
                'required',
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
            'name.required' => 'اسم الثابت مطلوب.',
            'name.max' => 'اسم الثابت لا يمكن أن يزيد عن 150 حرف.',
            'constant_type_id.required' => 'نوع الثابت مطلوب.',
            'constant_type_id.exists' => 'نوع الثابت المحدد غير موجود.',
            'parent_id.exists' => 'الثابت الأب المحدد غير موجود.'
        ];
    }
}
