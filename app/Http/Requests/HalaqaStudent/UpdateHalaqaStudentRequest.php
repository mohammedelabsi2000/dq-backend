<?php

namespace App\Http\Requests\HalaqaStudent;

use App\Helpers\ConstantHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHalaqaStudentRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'halaqa_id' => 'sometimes|required|exists:halaqas,id',
            'student_id' => 'sometimes|required|exists:students,id',
            'from_date' => 'sometimes|required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'enrollment_status_id' => [
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('enrollment_status')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'halaqa_id.exists' => 'الحلقة المحددة غير موجودة',
            'student_id.exists' => 'الطالب المحدد غير موجود',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو مساوي لتاريخ البداية',
            'enrollment_status_id.exists' => 'الحالة المحددة غير موجودة',
        ];
    }
}
