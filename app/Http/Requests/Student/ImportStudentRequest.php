<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportStudentRequest extends FormRequest
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
            'file' => 'required|mimes:xlsx,xls',
            'halaqa_id' => 'required|exists:halaqas,id',
        ];
    }

    public function messages()
    {
        return [
            // ملف الإكسل
            'file.required' => 'الرجاء رفع ملف الإكسل.',
            'file.mimes' => 'يجب أن يكون الملف من نوع: xlsx أو xls.',

            // الحلقات
            'halaqa_id.required' => 'الرجاء اختيار الحلقة.',
            'halaqa_id.exists' => 'الحلقة المحددة غير موجودة',
        ];
    }

    /**
     * Override فشل الـ validation ليكون JSON response بدلاً من redirect
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422));
    }
}
