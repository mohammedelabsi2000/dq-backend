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
            'certificate_link' => 'nullable|string',
            'date_graduate' => 'nullable|date',
            'provider' => 'nullable|string',
            'certificate_type' => 'required|in:academy,course',
            'academic_qualification_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('academic_degree')),
            ],
            'major_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('major')),
            ],
            'course_name' => 'nullable|string',
            'course_type_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('course_type')),
            ],
            'notes' => 'nullable|string',
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
            'academic_qualification_id.in' => 'الدرجة العلمية غير صالحة',
            'major_id.in' => 'التخصص غير صالح',
            'course_type_id.in' => 'نوع الدورة غير صالح',
            'certificate_file.max' => 'حجم الملف يجب أن لا يتجاوز 5 ميغابايت',
            'certificate_file.mimes' => 'صيغة الملف يجب أن تكون PDF',
        ];
    }
}
