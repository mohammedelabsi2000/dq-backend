<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ConstantHelper;
use App\Models\User;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [

            // Basic
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',

            // Names
            'fName' => 'nullable|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',

            'dob' => 'required|date',

            // Foreign Keys
            'mosque_id' => 'nullable|exists:mosques,id',
            'marital_status_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('marital_status')),
            ],
            'prefix_name_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('prefix_name')),
            ],

            // Other fields
            'location' => 'nullable|string|max:255',
            'gender' => 'nullable|in:ذكر,أنثى',
            'numChildren' => 'nullable|integer|min:0',

            'identity' => ['nullable', 'digits:9'],
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

            /*
            |--------------------------------------------------------------------------
            | Basic
            |--------------------------------------------------------------------------
            */
            // 'name.required' => 'الاسم مطلوب.',
            'name.string' => 'الاسم يجب أن يكون نصاً.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفاً.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم مسبقاً.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.string' => 'كلمة المرور يجب أن تكون نصاً.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',


            /*
            |--------------------------------------------------------------------------
            | Names
            |--------------------------------------------------------------------------
            */
            'fName.string' => 'الاسم الأول يجب أن يكون نصاً.',
            'fName.max' => 'الاسم الأول يجب ألا يتجاوز 100 حرف.',

            'sName.string' => 'الاسم الثاني يجب أن يكون نصاً.',
            'sName.max' => 'الاسم الثاني يجب ألا يتجاوز 100 حرف.',

            'thName.string' => 'الاسم الثالث يجب أن يكون نصاً.',
            'thName.max' => 'الاسم الثالث يجب ألا يتجاوز 100 حرف.',

            'family.string' => 'اسم العائلة يجب أن يكون نصاً.',
            'family.max' => 'اسم العائلة يجب ألا يتجاوز 100 حرف.',


            /*
            |--------------------------------------------------------------------------
            | Date
            |--------------------------------------------------------------------------
            */
            'dob.date' => 'تاريخ الميلاد يجب أن يكون تاريخاً صحيحاً.',


            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */
            'mosque_id.exists' => 'المسجد المحدد غير موجود.',

            'marital_status_id.in' => 'الحالة الاجتماعية المختارة غير صحيحة.',

            'prefix_name_id.in' => 'اللقب المختار غير صحيح.',


            /*
            |--------------------------------------------------------------------------
            | Other fields
            |--------------------------------------------------------------------------
            */
            'location.string' => 'الموقع يجب أن يكون نصاً.',
            'location.max' => 'الموقع يجب ألا يتجاوز 255 حرفاً.',

            'gender.in' => 'قيمة الجنس يجب أن تكون ذكر أو أنثى.',

            'numChildren.integer' => 'عدد الأبناء يجب أن يكون رقم صحيح.',
            'numChildren.min' => 'عدد الأبناء لا يمكن أن يكون سالباً.',

            'identity.digits' => 'رقم الهوية يجب أن يتكون من 9 أرقام.',
            'identity.unique' => 'رقم الهوية مستخدم مسبقاً.',

            'phone.string' => 'رقم الهاتف يجب أن يكون نصاً.',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 25 حرفاً.',

            'whatsapp.string' => 'رقم الواتساب يجب أن يكون نصاً.',
            'whatsapp.max' => 'رقم الواتساب يجب ألا يتجاوز 25 حرفاً.',

            'jobname.string' => 'اسم الوظيفة يجب أن يكون نصاً.',
            'jobname.max' => 'اسم الوظيفة يجب ألا يتجاوز 255 حرفاً.',

            'job_place.string' => 'مكان العمل يجب أن يكون نصاً.',
            'job_place.max' => 'مكان العمل يجب ألا يتجاوز 255 حرفاً.',

            'job_salary.numeric' => 'الراتب يجب أن يكون رقماً.',
            'job_salary.min' => 'الراتب لا يمكن أن يكون سالباً.',
        ];
    }
}
