<?php

namespace App\Http\Requests\Halaqa;

use App\Helpers\ConstantHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreHalaqaRequest extends FormRequest
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
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',

            'reference_type' => [
                'required',
                Rule::in([
                    \App\Models\Center::class,
                    \App\Models\Region::class,
                ])
            ],
            'reference_id' => [
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
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('halaqa_types')),
            ],
        ];
    }

    public function messages()
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
            'reference_type.in' => 'نوع المرجع المحدد غير صالح.',

            // reference_id
            'reference_id.required' => 'المعرف مطلوب.',
            'reference_id.integer' => 'المعرف يجب أن يكون رقم صحيح.',

            // type_id
            'type_id.required' => 'نوع الحلقة مطلوب.',
            'type_id.in' => 'نوع الحلقة المحدد غير صالح.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors' => $validator->errors(),
        ], 422));
    }
}
