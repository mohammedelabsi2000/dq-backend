<?php

namespace App\Http\Requests\HalaqaStudent;

use Illuminate\Foundation\Http\FormRequest;

class StoreHalaqaStudentRequest extends FormRequest
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
            'halaqa_id' => 'required|exists:halaqas,id',
            'student_id' => 'required|exists:students,id',
            'from_date' => 'required|date',
            // 'to_date' => 'nullable|date|after_or_equal:from_date',
            'status_id' => 'required|exists:constants,id',
        ];
    }

    public function messages(): array
    {
        return [
            'halaqa_id.required' => 'يجب تحديد الحلقة',
            'halaqa_id.exists' => 'الحلقة المحددة غير موجودة',
            'student_id.required' => 'يجب تحديد الطالب',
            'student_id.exists' => 'الطالب المحدد غير موجود',
            'from_date.required' => 'يجب تحديد تاريخ البداية',
            // 'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو مساوي لتاريخ البداية',
            'status_id.required' => 'يجب تحديد الحالة',
            'status_id.exists' => 'الحالة المحددة غير موجودة',
        ];
    }
}
