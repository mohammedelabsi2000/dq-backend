<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $studentId = $this->route('student')?->id;

        return [
            'identity'          => ['sometimes', 'string', 'max:9', Rule::unique('students', 'identity')->ignore($studentId)],
            'fName'             => 'sometimes|string|max:255',
            'sName'             => 'sometimes|nullable|string|max:255',
            'thName'            => 'sometimes|nullable|string|max:255',
            'family'            => 'sometimes|string|max:255',
            'dob'               => 'nullable|date',
            'mosque_id'         => 'sometimes|exists:mosques,id',
            'location'          => 'nullable|string',
            'gender'            => ['sometimes', Rule::in(['ذكر', 'أنثى'])],
            'marital_status_id' => 'nullable|exists:constants,id',
            'money_status_id'   => 'nullable|exists:constants,id',
            'prefix_name_id'    => 'nullable|exists:constants,id',
            'guardian_id'       => 'sometimes|string|max:9|exists:users,identity',
            'guardian_type_id'  => 'nullable|exists:constants,id',
            'phone'             => 'nullable|string|max:25',
            'whatsapp'          => 'nullable|string|max:25',
        ];
    }

    public function messages(): array
    {
        return [
            'identity.unique'      => 'رقم الهوية مستخدم مسبقاً',
            'identity.max'         => 'رقم الهوية يجب ألا يتجاوز 9 أحرف',
            'fName.max'            => 'الاسم الأول يجب ألا يتجاوز 255 حرف',
            'family.max'           => 'اسم العائلة يجب ألا يتجاوز 255 حرف',
            'mosque_id.exists'     => 'المسجد المحدد غير موجود',
            'gender.in'            => 'الجنس يجب أن يكون ذكر أو أنثى',
            'guardian_id.max'      => 'رقم الهوية يجب ألا يتجاوز 9 أحرف',
            'guardian_id.exists'   => 'ولي الأمر غير موجود في النظام',
            'dob.date'             => 'تاريخ الميلاد غير صالح',
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
