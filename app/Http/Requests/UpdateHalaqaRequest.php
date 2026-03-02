<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateHalaqaRequest extends FormRequest
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
        // return [
        //     'name' => ['sometimes', 'required', 'string', 'max:255'],
        //     'location' => ['sometimes', 'nullable', 'string', 'max:255'],
        //     'description' => ['sometimes', 'nullable', 'string'],

        //     'center_id' => ['sometimes', 'required', 'integer', 'exists:centers,id'],
        //     'constant_id' => ['sometimes', 'required', 'integer', 'exists:constants,id'], // غيّر constants
        // ];
        return [
            'name'        => 'sometimes|required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'center_id'   => 'sometimes|required|exists:centers,id',
            'constant_id' => 'sometimes|required|exists:constants,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'اسم الحلقة مطلوب',
            'center_id.required'   => 'يجب اختيار المركز',
            'center_id.exists'     => 'المركز المحدد غير موجود',
            'constant_id.required' => 'يجب اختيار الثابت',
            'constant_id.exists'   => 'الثابت المحدد غير موجود',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->validationError([
            // 'status'  => false,
            // 'message' => 'خطأ في البيانات المدخلة',
            $validator->errors(),
        ]));
    }
}
