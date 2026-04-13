<?php

namespace App\Http\Requests\AcademicQualification;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ConstantHelper;
use App\Models\AcademicQualification;
use App\Models\Student;
use App\Models\User;
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
        // الحصول على الشخص المراد إنشاء شهادة له
        $personType = $this->input('person_type');
        $personId = $this->input('person_id');

        $person = null;
        if ($personType === 'student') {
            $person = Student::find($personId);
        } elseif ($personType === 'user') {
            $person = User::find($personId);
        }

        return $this->user()->can('create', [AcademicQualification::class, $person]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {

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

            'certificate_file.file' => 'الملف يجب أن يكون ملفاً صالحاً.',
            'certificate_file.mimes' => 'يجب أن يكون الملف بصيغة PDF فقط.',
            'certificate_file.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
