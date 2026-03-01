<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMosqueRequest extends FormRequest
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
            'name'      => 'sometimes|required|string|max:255',
            'region_id' => 'sometimes|required|exists:regions,id',
            'notes'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المسجد مطلوب',
            'region_id.required' => 'يجب اختيار المنطقة',
            'region_id.exists'   => 'المنطقة المحددة غير موجودة',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
