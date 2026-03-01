<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fName'             => 'required|string|max:255',
            'sName'             => 'nullable|string|max:255',
            'thName'            => 'nullable|string|max:255',
            'family'            => 'required|string|max:255',
            'dob'               => 'nullable|date',
            'mosque_id'         => 'required|exists:mosques,id',
            'location'          => 'nullable|string',
            'gender'            => ['required', Rule::in(['ذكر', 'أنثى'])],
            'marital_status_id' => 'nullable|exists:constants,id',
            'money_status_id'   => 'nullable|exists:constants,id',
            'prefix_name_id'    => 'nullable|exists:constants,id',
            'guardian_id'       => 'required|string|max:9|exists:users,identity',
            'guardian_type_id'  => 'nullable|exists:constants,id',
            'phone'             => 'nullable|string|max:25',
            'whatsapp'          => 'nullable|string|max:25',
        ];
    }

    public function messages(): array
    {
        return [
            'fName.required'       => 'الاسم الأول مطلوب',
            'fName.max'            => 'الاسم الأول يجب ألا يتجاوز 255 حرف',
            'family.required'      => 'اسم العائلة مطلوب',
            'family.max'           => 'اسم العائلة يجب ألا يتجاوز 255 حرف',
            'mosque_id.required'   => 'المسجد مطلوب',
            'mosque_id.exists'     => 'المسجد المحدد غير موجود',
            'gender.required'      => 'الجنس مطلوب',
            'gender.in'            => 'الجنس يجب أن يكون ذكر أو أنثى',
            'guardian_id.required' => 'رقم هوية ولي الأمر مطلوب',
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
