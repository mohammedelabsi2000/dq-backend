<?php

namespace App\Http\Requests\Student;

use App\Enums\Gender;
use App\Helpers\ConstantHelper;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreStudentRequest extends BaseStudentRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Student::class);
    }

    public function rules()
    {
        return array_merge($this->baseRules(), [
            'identity' => ['nullable', 'digits:9'], //, Rule::unique('students', 'identity')],
            'fName' => 'required|string|max:255',
            'sName' => 'nullable|string|max:255',
            'thName' => 'nullable|string|max:255',
            'family' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'mosque_id' => 'required|exists:mosques,id',
            'location' => 'nullable|string',
            'gender' => ['required', new Enum(Gender::class)],
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
            'guardian_id' => 'required|digits:9',
            'guardian_type_id' => [
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('guardian_type')),
            ],
            'phone' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25',
          
            'halaqa_id' => 'nullable|exists:halaqas,id',
        ]);
    }

    public function messages()
    {
        return array_merge($this->baseMessages(), [
            'identity.digits' => 'رقم الهوية يجب أن يتكون من 9 أرقام.',
            'identity.unique' => 'رقم الهوية مستخدم مسبقاً لطالب آخر.',

            'fName.required' => 'الاسم الأول مطلوب.',
            'fName.string' => 'الاسم الأول يجب أن يكون نصاً.',
            'fName.max' => 'الاسم الأول يجب ألا يتجاوز 255 حرفاً.',

            'sName.string' => 'الاسم الثاني يجب أن يكون نصاً.',
            'sName.max' => 'الاسم الثاني يجب ألا يتجاوز 255 حرفاً.',

            'thName.string' => 'الاسم الثالث يجب أن يكون نصاً.',
            'thName.max' => 'الاسم الثالث يجب ألا يتجاوز 255 حرفاً.',

            'family.required' => 'اسم العائلة مطلوب.',
            'family.string' => 'اسم العائلة يجب أن يكون نصاً.',
            'family.max' => 'اسم العائلة يجب ألا يتجاوز 255 حرفاً.',

            'dob.date' => 'تاريخ الميلاد يجب أن يكون تاريخاً صحيحاً.',

            'mosque_id.required' => 'المسجد مطلوب.',
            'mosque_id.exists' => 'المسجد المحدد غير موجود في النظام.',

            'location.string' => 'الموقع يجب أن يكون نصاً.',

            'gender.required' => 'الجنس مطلوب.',
            'gender.enum' => 'قيمة الجنس يجب أن تكون ذكر أو أنثى.',

            'marital_status_id.in' => 'الحالة الاجتماعية المحددة غير صحيحة.',

            'money_status_id.in' => 'الحالة المادية المحددة غير صحيحة.',

            'prefix_name_id.in' => 'اللقب المحدد غير صحيح.',

            'guardian_id.required' => 'رقم هوية ولي الأمر مطلوب.',
            'guardian_id.digits' => 'رقم هوية ولي الأمر يجب أن يكون 9 أرقام.',
            // 'guardian_id.exists' => 'رقم هوية ولي الأمر غير موجود في النظام.',

            'guardian_type_id.required' => 'صلة القرابة مطلوبة.',
            'guardian_type_id.in' => 'صلة القرابة المحددة غير صحيحة.',

            'phone.string' => 'رقم الهاتف يجب أن يكون نصاً.',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 25 حرفاً.',

            'whatsapp.string' => 'رقم الواتساب يجب أن يكون نصاً.',
            'whatsapp.max' => 'رقم الواتساب يجب ألا يتجاوز 25 حرفاً.',
         
            'halaqa_id.exists' => 'الحلقة المحددة غير موجودة في النظام.',
        ]);
    }
}