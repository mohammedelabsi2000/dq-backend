<?php

namespace App\Http\Requests\Student;

use App\Helpers\ConstantHelper;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends BaseStudentRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('student'));
    }

    public function rules()
    {
        $studentId = $this->route('student')?->id;

        return [
            'identity' => ['sometimes', 'digits:9', Rule::unique('students', 'identity')->ignore($studentId)],
            'fName' => 'sometimes|string|max:255',
            'sName' => 'sometimes|nullable|string|max:255',
            'thName' => 'sometimes|nullable|string|max:255',
            'family' => 'sometimes|string|max:255',
            'dob' => 'nullable|date',
            'mosque_id' => 'sometimes|exists:mosques,id',
            'location' => 'nullable|string',
            'gender' => ['sometimes', Rule::in(['ذكر', 'أنثى'])],
            'marital_status_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('marital_status')),
            ],
            'money_status_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('money_status')),
            ],
            'prefix_name_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('prefix_name')),
            ],
            // مراجعة سيناريو إنشاء ولي الأمر
            // |exists:users,identity
            'guardian_id' => 'sometimes|digits:9',
            'guardian_type_id' => [
                'sometimes',
                Rule::in(ConstantHelper::getConstantIdsByType('guardian_type')),
            ],
            'phone' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25',
          
            'halaqa_id' => 'nullable|exists:halaqas,id',
        ]);
    }

    public function messages()
    {
        return [
            'identity.digits' => 'رقم الهوية يجب أن يتكون من 9 أرقام.',
            'identity.unique' => 'رقم الهوية مستخدم مسبقاً لطالب آخر.',

            'fName.string' => 'الاسم الأول يجب أن يكون نصاً.',
            'fName.max' => 'الاسم الأول يجب ألا يتجاوز 255 حرفاً.',

            'sName.string' => 'الاسم الثاني يجب أن يكون نصاً.',
            'sName.max' => 'الاسم الثاني يجب ألا يتجاوز 255 حرفاً.',

            'thName.string' => 'الاسم الثالث يجب أن يكون نصاً.',
            'thName.max' => 'الاسم الثالث يجب ألا يتجاوز 255 حرفاً.',

            'family.string' => 'اسم العائلة يجب أن يكون نصاً.',
            'family.max' => 'اسم العائلة يجب ألا يتجاوز 255 حرفاً.',

            'dob.date' => 'تاريخ الميلاد يجب أن يكون تاريخاً صالحاً.',

            'mosque_id.exists' => 'المسجد المحدد غير موجود.',

            'location.string' => 'الموقع يجب أن يكون نصاً.',

            'gender.in' => 'قيمة الجنس غير صحيحة (المسموح: ذكر أو أنثى).',

            'marital_status_id.in' => 'الحالة الاجتماعية المحددة غير صحيحة.',

            'money_status_id.in' => 'الحالة المادية المحددة غير صحيحة.',

            'prefix_name_id.in' => 'اللقب المحدد غير صحيح.',

            'guardian_id.digits' => 'رقم هوية ولي الأمر يجب أن يكون 9 أرقام.',
            // 'guardian_id.exists' => 'رقم هوية ولي الأمر غير موجود في النظام.',

            'guardian_type_id.in' => 'نوع ولي الأمر المحدد غير صحيح.',

            'phone.string' => 'رقم الهاتف يجب أن يكون نصاً.',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 25 حرفاً.',

            'whatsapp.string' => 'رقم الواتساب يجب أن يكون نصاً.',
            'whatsapp.max' => 'رقم الواتساب يجب ألا يتجاوز 25 حرفاً.',

            'halaqa_id.exists' => 'الحلقة المحددة غير موجودة في النظام.',
        ]);
    }
}
