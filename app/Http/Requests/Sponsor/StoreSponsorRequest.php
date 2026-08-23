<?php

namespace App\Http\Requests\Sponsor;

use App\Models\Constant;
use App\Models\Sponsor;
use App\Http\Requests\DQFormRequest;

class StoreSponsorRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Sponsor::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'project_number' => 'nullable|string|max:100',
            'follow_up_entity' => 'required|string|max:255',
            'sponsorship_type' => 'required|in:دائمة,مؤقتة',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'required_halaqat_male' => 'required|integer|min:0',
            'required_halaqat_female' => 'required|integer|min:0',
            'students_per_halaqa_male' => 'nullable|integer|min:0',
            'students_per_halaqa_female' => 'nullable|integer|min:0',
            'student_type_id' => 'required|integer|exists:constants,id',
            'student_type_other_note' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('required_halaqat_male') && $this->filled('required_halaqat_female')) {
                if ((int) $this->input('required_halaqat_male') === 0 && (int) $this->input('required_halaqat_female') === 0) {
                    $validator->errors()->add(
                        'required_halaqat_male',
                        'يجب تحديد عدد حلقات مطلوبة لطلاب أو طالبات على الأقل'
                    );
                }
            }

            if ($this->filled('student_type_id') && $this->isOtherStudentType((int) $this->input('student_type_id')) && !$this->filled('student_type_other_note')) {
                $validator->errors()->add(
                    'student_type_other_note',
                    'يجب تحديد نوع الطلاب عند اختيار "أخرى"'
                );
            }
        });
    }

    protected function isOtherStudentType(int $constantId): bool
    {
        return Constant::whereKey($constantId)
            ->whereHas('type', fn ($q) => $q->where('name', 'sponsor_student_type'))
            ->where('name', 'أخرى')
            ->exists();
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم أو جهة الكفالة مطلوب',
            'follow_up_entity.required' => 'جهة المتابعة مطلوبة',
            'sponsorship_type.required' => 'نوع الكفالة مطلوب',
            'sponsorship_type.in' => 'نوع الكفالة يجب أن يكون دائمة أو مؤقتة',
            'start_date.required' => 'تاريخ البدء مطلوب',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء',
            'required_halaqat_male.required' => 'عدد الحلقات المطلوبة للطلاب مطلوب',
            'required_halaqat_female.required' => 'عدد الحلقات المطلوبة للطالبات مطلوب',
            'student_type_id.required' => 'نوع الطلاب المطلوب مطلوب',
            'student_type_id.exists' => 'نوع الطلاب المحدد غير موجود',
        ];
    }
}
