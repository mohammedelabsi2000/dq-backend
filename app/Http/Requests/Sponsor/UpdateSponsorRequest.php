<?php

namespace App\Http\Requests\Sponsor;

use App\Models\Constant;
use App\Http\Requests\DQFormRequest;

class UpdateSponsorRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('sponsor'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'project_number' => 'nullable|string|max:100',
            'follow_up_entity' => 'sometimes|required|string|max:255',
            'sponsorship_type' => 'sometimes|required|in:دائمة,مؤقتة',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'required_halaqat_male' => 'sometimes|required|integer|min:0',
            'required_halaqat_female' => 'sometimes|required|integer|min:0',
            'students_per_halaqa_male' => 'nullable|integer|min:0',
            'students_per_halaqa_female' => 'nullable|integer|min:0',
            'student_type_id' => 'sometimes|required|integer|exists:constants,id',
            'student_type_other_note' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sponsor = $this->route('sponsor');

            $requiredMale = $this->filled('required_halaqat_male')
                ? (int) $this->input('required_halaqat_male')
                : $sponsor?->required_halaqat_male;

            $requiredFemale = $this->filled('required_halaqat_female')
                ? (int) $this->input('required_halaqat_female')
                : $sponsor?->required_halaqat_female;

            if ($requiredMale === 0 && $requiredFemale === 0) {
                $validator->errors()->add(
                    'required_halaqat_male',
                    'يجب تحديد عدد حلقات مطلوبة لطلاب أو طالبات على الأقل'
                );
            }

            $studentTypeId = $this->filled('student_type_id')
                ? (int) $this->input('student_type_id')
                : $sponsor?->student_type_id;

            $otherNote = $this->has('student_type_other_note')
                ? $this->input('student_type_other_note')
                : $sponsor?->student_type_other_note;

            if ($studentTypeId && $this->isOtherStudentType($studentTypeId) && empty($otherNote)) {
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
            'sponsorship_type.in' => 'نوع الكفالة يجب أن يكون دائمة أو مؤقتة',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء',
            'student_type_id.exists' => 'نوع الطلاب المحدد غير موجود',
        ];
    }
}
