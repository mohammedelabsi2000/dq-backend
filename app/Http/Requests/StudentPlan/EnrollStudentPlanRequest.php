<?php

namespace App\Http\Requests\StudentPlan;

use App\Http\Requests\DQFormRequest;
use App\Models\Plan;
use App\Models\StudentPlan;

class EnrollStudentPlanRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enrollStudent', $this->route('plan'));
    }

    public function rules(): array
    {
        return [
            'student_ids'        => ['required', 'array'],
            'student_ids.*'      => ['integer', 'exists:students,id'],
            'plan_id'            => ['required', 'integer', 'exists:plans,id'],
            'starting_level_id'  => ['nullable', 'integer', 'exists:levels,id'],
            'from_date'          => ['required', 'date'],
            'is_main'            => ['sometimes', 'boolean'],
            'notes'              => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            // منع التحاق الطلاب بنفس الخطة مرتين وهي لا تزال نشطة
            if ($this->filled('student_ids') && $this->filled('plan_id')) {
                $alreadyActiveInSamePlan = StudentPlan::whereIn('student_id', $this->student_ids)
                    ->where('plan_id', $this->plan_id)
                    ->active()
                    ->pluck('student_id')
                    ->toArray();

                if (!empty($alreadyActiveInSamePlan)) {
                    $validator->errors()->add(
                        'student_ids',
                        'بعض الطلاب ملتحقين بالفعل بهذه الخطة وهي لا تزال نشطة: ' . implode(', ', $alreadyActiveInSamePlan)
                    );
                }
            }

            // التحقق أن starting_level_id ينتمي فعلاً لنفس plan_id
            if ($this->filled('starting_level_id') && $this->filled('plan_id')) {
                $levelBelongsToPlan = \App\Models\Level::where('id', $this->starting_level_id)
                    ->where('plan_id', $this->plan_id)
                    ->exists();

                if (!$levelBelongsToPlan) {
                    $validator->errors()->add(
                        'starting_level_id',
                        'المستوى المحدد لا ينتمي إلى الخطة المختارة'
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'student_ids'       => 'الطلاب',
            'plan_id'           => 'الخطة',
            'starting_level_id' => 'المستوى المبدئي',
            'from_date'         => 'تاريخ الالتحاق',
            'is_main'           => 'الخطة الرئيسية',
            'notes'             => 'الملاحظات',
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'حقل الطلاب مطلوب',
            'student_ids.array'    => 'حقل الطلاب يجب أن يكون مصفوفة',
            'student_ids.*.exists' => 'بعض الطلاب المحددين غير موجودين',
            'plan_id.required'    => 'حقل الخطة مطلوب',
            'plan_id.exists'      => 'الخطة المحددة غير موجودة',
            'starting_level_id.exists' => 'المستوى المحدد غير موجود',
            'from_date.required'  => 'حقل تاريخ الالتحاق مطلوب',
            'from_date.date'      => 'صيغة التاريخ غير صحيحة',
        ];
    }
}
