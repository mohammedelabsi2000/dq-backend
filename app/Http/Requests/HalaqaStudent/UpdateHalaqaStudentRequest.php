<?php

namespace App\Http\Requests\HalaqaStudent;

use App\Helpers\ConstantHelper;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use Illuminate\Validation\Rule;
use App\Http\Requests\DQFormRequest;

class UpdateHalaqaStudentRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $halaqaStudent = $this->route('halaqa_student');

        // التحقق من صلاحية تعديل التسجيل الحالي
        if (!$this->user()->can('update', $halaqaStudent)) {
            return false;
        }

        // إذا تم تغيير الحلقة، تحقق من صلاحية الحلقة الجديدة
        if ($this->has('halaqa_id') && $this->input('halaqa_id') != $halaqaStudent->halaqa_id) {
            $newHalaqa = Halaqa::findOrFail($this->input('halaqa_id'));
            return $this->user()->can('create', [HalaqaStudent::class, $newHalaqa]);
        }

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
            'enrollment_status_id.in' => 'الحالة المحددة غير موجودة',
        ];
    }
}
