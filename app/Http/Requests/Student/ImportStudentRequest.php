<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\DQFormRequest;
use App\Models\Student;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportStudentRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Student::class);
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
}
