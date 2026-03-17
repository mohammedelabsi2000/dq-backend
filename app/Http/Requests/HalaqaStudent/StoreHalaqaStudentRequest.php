<?php

namespace App\Http\Requests\HalaqaStudent;

use App\Helpers\ConstantHelper;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHalaqaStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $halaqa = Halaqa::findOrFail($this->input('halaqa_id'));

        return $this->user()->can('create', [HalaqaStudent::class, $halaqa]);
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
            'students.*' => 'required|exists:students,id',
            'from_date' => 'required|date',
            // 'to_date' => 'nullable|date|after_or_equal:from_date',
            'enrollment_status_id' => [
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('enrollment_status')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'halaqa_id.required' => 'يجب تحديد الحلقة',
            'halaqa_id.exists' => 'الحلقة المحددة غير موجودة',
            'students.*.required' => 'يجب تحديد الطالب',
            'students.*.exists' => 'الطالب المحدد غير موجود',
            'from_date.required' => 'يجب تحديد تاريخ البداية',
            // 'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو مساوي لتاريخ البداية',
            'enrollment_status_id.required' => 'يجب تحديد الحالة',
            'enrollment_status_id.in' => 'الحالة المحددة غير موجودة',
        ];
    }
}
