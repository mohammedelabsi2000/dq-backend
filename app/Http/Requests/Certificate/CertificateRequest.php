<?php

namespace App\Http\Requests\Certificate;

use App\Helpers\ConstantHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CertificateRequest extends FormRequest
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
            'person_type' => 'required|string',
            'person_id' => 'required|integer',
            'date_graduate' => 'nullable|date',
            'certificate_type' => 'required|in:academy,course',
            'academic_degree_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('academic_degree')),
            ],
            'qualification_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('major')),
            ],
            'course_type_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('course_type')),
            ],
            'certificate_file' => 'nullable|file|mimes:pdf|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'person_type.required' => 'حقل نوع الشخص مطلوب.',
            'person_id.required' => 'حقل معرف الشخص مطلوب.',
            'date_graduate.date' => 'حقل تاريخ الحصول على الشهادة يجب أن يكون تاريخًا صالحًا.',
            'certificate_type.required' => 'حقل نوع الشهادة مطلوب.',
            'certificate_type.in' => 'نوع الشهادة المحدد غير صالح. يجب أن يكون إما "academy" أو "course".',
            'academic_degree_id.in' => 'الدرجة العلمية المحددة غير موجودة.',
            'qualification_id.in' => 'التخصص المحدد غير موجود.',
            'course_type_id.in' => 'نوع الدورة المحدد غير موجود.',
            'certificate_file.file' => 'حقل ملف الشهادة يجب أن يكون ملفًا.',
            'certificate_file.mimes' => 'حقل ملف الشهادة يجب أن يكون من نوع PDF.',
            'certificate_file.max' => 'حجم ملف الشهادة يجب ألا يتجاوز 5 ميغابايت.',
        ];
    }
}
