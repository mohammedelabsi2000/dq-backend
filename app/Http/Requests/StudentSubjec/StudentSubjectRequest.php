<?php

namespace App\Http\Requests\StudentSubjec;

use App\Helpers\ConstantHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentSubjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => [
                'sometimes',
                'integer',
                'exists:students,id',
            ],

            'subject_id' => [
                'sometimes',
                'integer',
                'exists:subjects,id',
            ],

            'level_id' => [
                'nullable',
                'integer',
                'exists:levels,id',
            ],

            'result_status_key' => [
                'nullable',
                'string',
                Rule::in(ConstantHelper::getConstantKeysByType('result_status')),
            ],

            'grade' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],

            'grade_date' => [
                'nullable',
                'date',
            ],

            'teacher_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'الطالب مطلوب.',
            'student_id.exists' => 'الطالب المحدد غير موجود.',

            'subject_id.required' => 'المساق مطلوب.',
            'subject_id.exists' => 'المساق المحدد غير موجود.',

            'level_id.exists' => 'المستوى المحدد غير موجود.',

            'result_status_key.in' => 'حالة النتيجة المحددة غير موجودة.',

            'grade.numeric' => 'يجب أن تكون الدرجة رقمًا.',
            'grade.min' => 'يجب ألا تقل الدرجة عن 0.',
            'grade.max' => 'يجب ألا تزيد الدرجة عن 100.',

            'from_date.date' => 'تاريخ البداية غير صالح.',

            'to_date.date' => 'تاريخ النهاية غير صالح.',
            'to_date.after_or_equal' => 'يجب أن يكون تاريخ النهاية بعد أو مساويًا لتاريخ البداية.',

            'grade_date.date' => 'تاريخ الدرجة غير صالح.',

            'teacher_id.exists' => 'المدرس المحدد غير موجود.',

            'notes.string' => 'يجب أن تكون الملاحظات نصًا.',
        ];
    }
}
