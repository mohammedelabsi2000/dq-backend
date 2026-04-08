<?php

namespace App\Http\Requests\AcademicQualification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicQualificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('academic_qualification'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'academic_degree_id' => 'sometimes|exists:constants,id',
            'major_id' => 'sometimes|exists:constants,id',

            // عادةً ما نمنع تغيير صاحب العلاقة
            'person_type' => 'string',
            'person_id' => 'string',

            'detail' => 'sometimes|nullable|string|max:255',
            'date_graduate' => 'sometimes|nullable|digits:4|integer',
            'certificate_link' => 'sometimes|nullable|url',
            'educational_institution' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string',

            'certificate_file' => 'nullable|file|mimes:pdf|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'academic_degree_id.exists' => 'الدرجة العلمية المحددة غير موجودة.',

            'major_id.exists' => 'التخصص المحدد غير موجود.',

            'person_type.string' => 'نوع الشخص يجب أن يكون نصاً.',

            'person_id.string' => 'معرف الشخص يجب أن يكون نصاً.',

            'detail.string' => 'التفاصيل يجب أن تكون نصاً.',
            'detail.max' => 'التفاصيل يجب ألا تتجاوز 255 حرفاً.',

            'date_graduate.integer' => 'سنة التخرج يجب أن تكون رقماً.',
            'date_graduate.digits' => 'سنة التخرج يجب أن تكون مكونة من 4 أرقام.',

            'certificate_link.url' => 'رابط الشهادة يجب أن يكون رابطاً صحيحاً.',

            'educational_institution.string' => 'اسم المؤسسة التعليمية يجب أن يكون نصاً.',
            'educational_institution.max' => 'اسم المؤسسة التعليمية يجب ألا يتجاوز 255 حرفاً.',

            'notes.string' => 'الملاحظات يجب أن تكون نصاً.',

            'certificate_file.file' => 'الملف يجب أن يكون ملفاً صالحاً.',
            'certificate_file.mimes' => 'يجب أن يكون الملف بصيغة PDF فقط.',
            'certificate_file.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
