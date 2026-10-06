<?php

namespace App\Http\Requests\StudentSubjec;

use App\Models\LevelTrackSubject;
use Illuminate\Foundation\Http\FormRequest;

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
                'required',
                'integer',
                'exists:students,id',
            ],

            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'level_id' => [
                'nullable',
                'integer',
                'exists:levels,id',
            ],

            'result_status_id' => [
                'nullable',
                'integer',
                'exists:constants,id',
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

            // القرار عند إسناد مادة سبق أن نجح فيها الطالب: إعادة (retake) أو إعفاء/معادلة (exempt)
            'on_previous_pass' => [
                'nullable',
                'string',
                'in:retake,exempt',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('level_id') || $validator->errors()->hasAny(['subject_id', 'level_id'])) {
                return;
            }

            // المساق يجب أن يكون مربوطاً بأحد مسارات المستوى
            $subjectInLevel = LevelTrackSubject::where('subject_id', $this->subject_id)
                ->whereHas('levelTrack', fn($q) => $q->where('level_id', $this->level_id))
                ->exists();

            if (!$subjectInLevel) {
                $validator->errors()->add('subject_id', 'هذا المساق غير مربوط بأي مسار في المستوى المحدد.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'الطالب مطلوب.',
            'student_id.exists' => 'الطالب المحدد غير موجود.',

            'subject_id.required' => 'المساق مطلوب.',
            'subject_id.exists' => 'المساق المحدد غير موجود.',

            'level_id.exists' => 'المستوى المحدد غير موجود.',

            'result_status_id.exists' => 'حالة النتيجة المحددة غير موجودة.',

            'grade.numeric' => 'يجب أن تكون الدرجة رقمًا.',
            'grade.min' => 'يجب ألا تقل الدرجة عن 0.',
            'grade.max' => 'يجب ألا تزيد الدرجة عن 100.',

            'from_date.date' => 'تاريخ البداية غير صالح.',

            'to_date.date' => 'تاريخ النهاية غير صالح.',
            'to_date.after_or_equal' => 'يجب أن يكون تاريخ النهاية بعد أو مساويًا لتاريخ البداية.',

            'grade_date.date' => 'تاريخ الدرجة غير صالح.',

            'teacher_id.exists' => 'المدرس المحدد غير موجود.',

            'notes.string' => 'يجب أن تكون الملاحظات نصًا.',

            'on_previous_pass.in' => 'القرار يجب أن يكون إعادة (retake) أو إعفاء (exempt).',
        ];
    }
}
