<?php

namespace App\Http\Requests\PersonalCourse;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ConstantHelper;
use Illuminate\Validation\Rule;

class UpdatePersonalCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('personal_course'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'course_name' => 'sometimes|required|string|max:255',
            'notes' => 'sometimes|nullable|string',
            'hours' => 'sometimes|nullable|integer',
            'provider' => 'sometimes|nullable|string|max:255',
            'place' => 'sometimes|nullable|string|max:255',
            'certificate_link' => 'sometimes|nullable|url|max:255',
            'type_id' => [
                'sometimes',
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('course_type')),
            ],

            'certificate_file' => 'nullable|file|mimes:pdf|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'course_name.required' => 'اسم الدورة مطلوب.',
            'course_name.string' => 'اسم الدورة يجب أن يكون نصًا.',
            'course_name.max' => 'اسم الدورة لا يجب أن يتجاوز 255 حرفًا.',

            'notes.string' => 'الملاحظات يجب أن تكون نصًا.',

            'hours.integer' => 'الساعات يجب أن تكون عددًا صحيحًا.',

            'provider.string' => 'المزود يجب أن يكون نصًا.',
            'provider.max' => 'المزود لا يجب أن يتجاوز 255 حرفًا.',

            'place.string' => 'المكان يجب أن يكون نصًا.',
            'place.max' => 'المكان لا يجب أن يتجاوز 255 حرفًا.',

            'certificate_link.url' => 'رابط الشهادة يجب أن يكون رابطًا صالحًا.',
            'certificate_link.max' => 'رابط الشهادة لا يجب أن يتجاوز 255 حرفًا.',

            'type_id.required' => 'نوع الدورة مطلوب.',
            'type_id.in' => 'نوع الدورة غير صالح.',

            'certificate_file.file' => 'الملف يجب أن يكون ملفاً صالحاً.',
            'certificate_file.mimes' => 'يجب أن يكون الملف بصيغة PDF فقط.',
            'certificate_file.max' => 'حجم الملف يجب ألا يتجاوز 5 ميجابايت.',

        ];
    }
}
