<?php

namespace App\Http\Requests\AcademicQualification;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ConstantHelper;
use App\Models\AcademicQualification;
use Illuminate\Validation\Rule;

class StoreAcademicQualificationRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', AcademicQualification::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {

        // 'marital_status_id' => [
        //             'nullable',
        //             Rule::in(ConstantHelper::getConstantIdsByType('marital_status')),
        //         ],
        //         'prefix_name_id' => [
        //             'nullable',
        //             Rule::in(ConstantHelper::getConstantIdsByType('prefix_name')),
        //         ],

        return [
            'academic_degree_id' => [
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('academic_degree')),
            ],
            'major_id' => [
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('major')),
            ],
            'person_type' => 'required|string',
            'person_id' => 'required|integer',

            'detail' => 'nullable|string|max:255',
            'date_graduate' => 'nullable|digits:4|integer',
            'certificate_link' => 'nullable|url',
            'educational_institution' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            // Validation للملفات المتعددة
            // 'images' => 'nullable|array',
            // 'images.*' => 'file|mimes:pdf|max:5120'
            'certificate_file' => 'nullable|file|mimes:pdf|max:5120'
        ];
    }

    public function messages()
    {
        return [
            'academic_degree_id.required' => 'حقل الدرجة العلمية مطلوب.',
            'academic_degree_id.in' => 'الدرجة العلمية المحددة غير موجودة.',

            'major_id.required' => 'حقل التخصص مطلوب.',
            'major_id.in' => 'التخصص المحدد غير موجود.',

            'person_type.required' => 'نوع الشخص مطلوب.',
            'person_type.string' => 'نوع الشخص يجب أن يكون نصاً.',

            'person_id.required' => 'معرف الشخص مطلوب.',
            'person_id.integer' => 'معرف الشخص يجب أن يكون رقماً صحيحاً.',

            'detail.string' => 'التفاصيل يجب أن تكون نصاً.',
            'detail.max' => 'التفاصيل يجب ألا تتجاوز 255 حرفاً.',

            'date_graduate.integer' => 'سنة التخرج يجب أن تكون رقماً.',
            'date_graduate.digits' => 'سنة التخرج يجب أن تكون مكونة من 4 أرقام.',

            'certificate_link.url' => 'رابط الشهادة يجب أن يكون رابطاً صحيحاً.',

            'educational_institution.string' => 'اسم المؤسسة التعليمية يجب أن يكون نصاً.',
            'educational_institution.max' => 'اسم المؤسسة التعليمية يجب ألا يتجاوز 255 حرفاً.',

            'notes.string' => 'الملاحظات يجب أن تكون نصاً.',

            // 'images.array' => 'حقل الملفات يجب أن يكون مصفوفة.',

            // 'images.*.file' => 'كل ملف يجب أن يكون ملفاً صالحاً.',
            // 'images.*.mimes' => 'يجب أن يكون الملف بصيغة PDF فقط.',
            // 'images.*.max' => 'حجم الملف يجب ألا يتجاوز 5 ميجابايت.',

            'certificate_file.file' => 'كل ملف يجب أن يكون ملفاً صالحاً.',
            'certificate_file.mimes' => 'يجب أن يكون الملف بصيغة PDF فقط.',
            'certificate_file.max' => 'حجم الملف يجب ألا يتجاوز 5 ميجابايت.',

        ];
    }
}
