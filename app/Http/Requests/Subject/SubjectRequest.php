<?php

namespace App\Http\Requests\Subject;

use App\Enums\SubjectType;
use App\Helpers\ConstantHelper;
use App\Models\Constant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'track_id' => ['required', 'exists:tracks,id'],
            'subject_type_id' => ['required', Rule::in(ConstantHelper::getConstantIdsByType('subject_type'))],
            'title' => ['required', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'juzs' => ['nullable', 'string'],
            'surahs' => ['nullable', 'string'],
            'verses' => ['nullable', 'string'],
            'pages' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'track_id.required' => 'المسار مطلوب.',
            'track_id.exists' => 'المسار المحدد غير موجود.',
            'subject_type_id.required' => 'نوع المساق مطلوب.',
            'subject_type_id.in' => 'نوع المساق المحدد غير موجود.',
            'title.required' => 'عنوان المساق مطلوب.',
            'title.string' => 'عنوان المساق يجب أن يكون نصًا.',
            'title.max' => 'عنوان المساق لا يمكن أن يتجاوز 255 حرفًا.',
            'sub_title.string' => 'العنوان الفرعي يجب أن يكون نصًا.',
            'sub_title.max' => 'العنوان الفرعي لا يمكن أن يتجاوز 255 حرفًا.',
            'juzs.string' => 'الأجزاء يجب أن تكون نصًا.',
            'surahs.string' => 'السور يجب أن تكون نصًا.',
            'verses.string' => 'الآيات يجب أن تكون نصًا.',
            'pages.string' => 'الصفحات يجب أن تكون نصًا.',
            'notes.string' => 'الملاحظات يجب أن تكون نصًا.',
        ];
    }

    public function attributes(): array
    {
        return [
            'track_id' => 'المسار',
            'subject_type_id' => 'نوع المساق',
            'title' => 'عنوان المساق',
            'sub_title' => 'عنوان فرعي',
            'juzs' => 'أجزاء',
            'surahs' => 'سور',
            'verses' => 'آيات',
            'pages' => 'صفحات',
            'description' => 'الوصف',
            'notes' => 'الملاحظات',
        ];
    }

    /**
     * Configure the validator instance.
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $subectIds = Constant::whereIn('const_key', [
                SubjectType::Explanation,
                SubjectType::LimitedMemorization,
                SubjectType::Meanings,
                SubjectType::Memorization,
            ])->pluck('id')->toArray();

            if (!in_array($this->subject_type_id, $subectIds)) {
                return;
            }

            if (
                empty($this->juzs) &&
                empty($this->surahs) &&
                empty($this->verses) &&
                empty($this->pages)
            ) {
                $validator->errors()->add(
                    'subject_details',
                    'يجب إدخال قيمة في أحد الحقول: الأجزاء أو السور أو الآيات أو الصفحات.'
                );
            }
        });
    }
}
