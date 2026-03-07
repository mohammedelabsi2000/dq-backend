<?php

namespace App\Http\Requests\User;

use App\Helpers\ConstantHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user'); // تأكد اسم الباراميتر في route

        return [

            'name' => 'nullable|string|max:255',

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'password' => 'nullable|string|min:6',

            'fName' => 'nullable|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',

            'dob' => 'nullable|date',

            'mosque_id' => 'nullable|exists:mosques,id',
            'marital_status_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('marital_status')),
            ],
            'prefix_name_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('prefix_name')),
            ],

            'location' => 'nullable|string|max:255',
            'gender' => 'nullable|in:ذكر,أنثى',
            'numChildren' => 'nullable|integer|min:0',

            'identity' => [
                'nullable',
                'string',
                'size:9',
                Rule::unique('users', 'identity')->ignore($userId),
            ],

            'phone' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25',

            'jobname' => 'nullable|string|max:255',
            'job_place' => 'nullable|string|max:255',
            'job_salary' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [

            // name
            'name.string' => 'الاسم يجب أن يكون نصاً.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفاً.',

            // email
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً.',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً.',

            // password
            'password.string' => 'كلمة المرور يجب أن تكون نصاً.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',

            // fName, sName, thName, family
            'fName.string' => 'الاسم الأول يجب أن يكون نصاً.',
            'fName.max' => 'الاسم الأول يجب ألا يتجاوز 100 حرفاً.',

            'sName.string' => 'اسم الأب يجب أن يكون نصاً.',
            'sName.max' => 'اسم الأب يجب ألا يتجاوز 100 حرفاً.',

            'thName.string' => 'اسم الجد يجب أن يكون نصاً.',
            'thName.max' => 'اسم الجد يجب ألا يتجاوز 100 حرفاً.',

            'family.string' => 'اسم العائلة يجب أن يكون نصاً.',
            'family.max' => 'اسم العائلة يجب ألا يتجاوز 100 حرفاً.',

            // dob
            'dob.date' => 'تاريخ الميلاد يجب أن يكون تاريخاً صحيحاً.',

            // mosque
            'mosque_id.exists' => 'المسجد المحدد غير موجود.',

            // marital status
            'marital_status_id.in' => 'الحالة الاجتماعية المختارة غير صحيحة.',

            // prefix name
            'prefix_name_id.in' => 'اللقب المختار غير صحيح.',

            // location
            'location.string' => 'الموقع يجب أن يكون نصاً.',
            'location.max' => 'الموقع يجب ألا يتجاوز 255 حرفاً.',

            // gender
            'gender.in' => 'الجنس يجب أن يكون ذكر أو أنثى.',

            // numChildren
            'numChildren.integer' => 'عدد الأبناء يجب أن يكون رقماً صحيحاً.',
            'numChildren.min' => 'عدد الأبناء لا يمكن أن يكون أقل من صفر.',

            // identity
            'identity.string' => 'رقم الهوية يجب أن يكون نصاً.',
            'identity.size' => 'رقم الهوية يجب أن يتكون من 9 أرقام.',
            'identity.unique' => 'رقم الهوية مستخدم مسبقاً.',

            // phone
            'phone.string' => 'رقم الجوال يجب أن يكون نصاً.',
            'phone.max' => 'رقم الجوال يجب ألا يتجاوز 25 حرفاً.',

            // whatsapp
            'whatsapp.string' => 'رقم الواتساب يجب أن يكون نصاً.',
            'whatsapp.max' => 'رقم الواتساب يجب ألا يتجاوز 25 حرفاً.',

            // job
            'jobname.string' => 'المسمى الوظيفي يجب أن يكون نصاً.',
            'jobname.max' => 'المسمى الوظيفي يجب ألا يتجاوز 255 حرفاً.',

            'job_place.string' => 'مكان العمل يجب أن يكون نصاً.',
            'job_place.max' => 'مكان العمل يجب ألا يتجاوز 255 حرفاً.',

            'job_salary.numeric' => 'الراتب يجب أن يكون رقماً.',
            'job_salary.min' => 'الراتب لا يمكن أن يكون أقل من صفر.',
        ];
    }
}
