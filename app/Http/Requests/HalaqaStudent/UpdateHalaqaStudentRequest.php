<?php

namespace App\Http\Requests\HalaqaStudent;

use App\Helpers\ConstantHelper;
use App\Rules\ActiveHalaqaRule;
use Illuminate\Validation\Rule;
use App\Http\Requests\DQFormRequest;
use App\Models\HalaqaStudent;

class UpdateHalaqaStudentRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $halaqaId = $this->input('halaqa_id') ? (int) $this->input('halaqa_id') : null;

        return $this->user()->can('create', [HalaqaStudent::class, $halaqaId]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'halaqa_id' => ['sometimes', 'required', 'exists:halaqas,id', new ActiveHalaqaRule()],
            'students' => 'required|array|min:1',
            'students.*' => 'exists:students,id',
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
            'students.*.exists' => 'الطالب المحدد غير موجود',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو مساوي لتاريخ البداية',
            'enrollment_status_id.in' => 'الحالة المحددة غير موجودة',
        ];
    }
}
