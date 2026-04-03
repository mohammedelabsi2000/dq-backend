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
            'students' => 'required|array|min:1',
            'students.*' => 'exists:students,id',
            'from_date' => 'required|date',
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
            'students.required' => 'يجب تحديد الطلاب',
            'students.array' => 'الطلاب يجب أن يكونوا مصفوفة',
            'students.min' => 'يجب تحديد طالب واحد على الأقل',
            'students.*.exists' => 'أحد الطلاب غير موجود',
            'from_date.required' => 'يجب تحديد تاريخ البداية',
            'from_date.date' => 'تاريخ البداية غير صالح',
            'enrollment_status_id.required' => 'يجب تحديد الحالة',
            'enrollment_status_id.in' => 'الحالة المحددة غير موجودة',
        ];
    }
}
