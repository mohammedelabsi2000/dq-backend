<?php

namespace App\Http\Requests\Subject;

use App\Enums\SubjectType;
use App\Enums\SuccessValueType;
use App\Helpers\ConstantHelper;
use App\Models\Constant;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $method = $this->getMethod();
        if ($method === 'POST') {
            // return $this->user()->hasPermissionTo('subjects.create');
            return $this->user()->can('create', Subject::class);
        } elseif (in_array($method, ['PUT', 'PATCH'])) {
            // return $this->user()->hasPermissionTo('subjects.update');
            return $this->user()->can('update', $this->route('subject'));
        }
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // 'title' => $this->name,
            // 'subject_type_id' => $this->subject_type,

            // 'juzs' => $this->selected_juz,
            // 'surahs' => $this->selected_surah,
            // 'verses' => $this->selected_verse,
            // 'pages' => $this->selected_page,

            // 'sub_title' => collect([
            //     $this->courseName,
            //     $this->programName,
            //     $this->evaluationName,
            // ])->first(fn($value) => filled($value)),
        ]);
    }

    public function rules(): array
    {
        return [
            // 'track_id' => ['nullable', 'exists:tracks,id'],
            'subject_type_id' => ['required', Rule::in(ConstantHelper::getConstantIdsByType('subject_type'))],
            'title' => ['required', 'string', 'max:255'],
            'sub_title' => [
                'nullable',
                Rule::requiredIf(function () {
                    return in_array(
                        $this->subject_type_id,
                        Constant::whereIn('const_key', [
                            SubjectType::Course,
                            SubjectType::Evaluation,
                            SubjectType::Program,
                        ])->pluck('id')->toArray()
                    );
                }),
                'string',
                'max:255',
            ],

            'juzs' => ['nullable', 'array'],
            'juzs.*' => ['integer'],

            'surahs' => ['nullable', 'array'],
            'surahs.*' => ['integer'],

            'verses' => ['nullable', 'array'],
            'verses.*' => ['integer'],

            // 'pages' => ['nullable', 'array'],
            // 'pages.*' => ['integer'],

            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            'subject_requirements' => ['nullable', 'array'],
            'subject_requirements.*.success_value' => ['required', 'numeric', 'between:0,100'],
            'subject_requirements.*.success_value_type' => ['required', 'string', Rule::in(array_column(SuccessValueType::cases(), 'value'))],

        ];
    }

    public function messages()
    {
        return [
            // 'track_id.exists' => 'المسار المحدد غير موجود.',
            'subject_type_id.required' => 'نوع المساق مطلوب.',
            'subject_type_id.in' => 'نوع المساق المحدد غير موجود.',
            'title.required' => 'عنوان المساق مطلوب.',
            'title.string' => 'عنوان المساق يجب أن يكون نصًا.',
            'title.max' => 'عنوان المساق لا يمكن أن يتجاوز 255 حرفًا.',
            'sub_title.required' => 'العنوان الفرعي مطلوب لهذا النوع من المساق.',
            'sub_title.string' => 'العنوان الفرعي يجب أن يكون نصًا.',
            'sub_title.max' => 'العنوان الفرعي لا يمكن أن يتجاوز 255 حرفًا.',

            'juzs.array' => 'الأجزاء يجب أن تكون مصفوفة.',
            'juzs.*.integer' => 'الأجزاء يجب أن تكون أعدادًا صحيحة.',
            'surahs.array' => 'السور يجب أن تكون مصفوفة.',
            'surahs.*.integer' => 'السور يجب أن تكون أعدادًا صحيحة.',
            'verses.array' => 'الآيات يجب أن تكون مصفوفة.',
            'verses.*.integer' => 'الآيات يجب أن تكون أعدادًا صحيحة.',
            // 'pages.array' => 'الصفحات يجب أن تكون مصفوفة.',
            // 'pages.*.integer' => 'الصفحات يجب أن تكون أعدادًا صحيحة.',

            'description.string' => 'الوصف يجب أن يكون نصًا.',
            'notes.string' => 'الملاحظات يجب أن تكون نصًا.',

            'subject_requirements.array' => 'متطلبات المساق يجب أن تكون مصفوفة.',
            'subject_requirements.*.success_value.required' => 'قيمة النجاح مطلوبة.',
            'subject_requirements.*.success_value.numeric' => 'قيمة النجاح يجب أن تكون رقمًا.',
            'subject_requirements.*.success_value.between' => 'قيمة النجاح يجب أن تكون بين 0 و 100.',
            'subject_requirements.*.success_value_type.required' => 'نوع قيمة النجاح مطلوب.',
            'subject_requirements.*.success_value_type.string' => 'نوع قيمة النجاح يجب أن يكون نصًا.',
            'subject_requirements.*.success_value_type.in' => 'نوع قيمة النجاح المحدد غير موجود.',
        ];
    }

    public function attributes(): array
    {
        return [
            // 'track_id' => 'المسار',
            'subject_type_id' => 'نوع المساق',
            'title' => 'عنوان المساق',
            'sub_title' => 'عنوان فرعي',
            'juzs' => 'أجزاء',
            'surahs' => 'سور',
            'verses' => 'آيات',
            'pages' => 'صفحات',
            'description' => 'الوصف',
            'notes' => 'الملاحظات',
            'subject_requirements' => 'متطلبات المساق',
            'subject_requirements.*.success_value' => 'قيمة النجاح',
            'subject_requirements.*.success_value_type' => 'نوع قيمة النجاح',
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
