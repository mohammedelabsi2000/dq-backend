<?php

namespace App\Http\Requests\StudentPlan;

use App\Http\Requests\DQFormRequest;

class MoveLevelRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'date'     => ['nullable', 'date'],
            'notes'    => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $studentPlan = $this->route('studentPlan');

            if ($studentPlan && $this->filled('level_id')) {
                $levelBelongsToPlan = \App\Models\Level::where('id', $this->level_id)
                    ->where('plan_id', $studentPlan->plan_id)
                    ->exists();

                if (!$levelBelongsToPlan) {
                    $validator->errors()->add(
                        'level_id',
                        'المستوى المحدد لا ينتمي إلى خطة الطالب الحالية'
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'level_id' => 'المستوى',
            'date'     => 'التاريخ',
            'notes'    => 'الملاحظات',
        ];
    }

    public function messages(): array
    {
        return [
            'level_id.required' => 'حقل المستوى مطلوب',
            'level_id.exists'   => 'المستوى المحدد غير موجود',
        ];
    }
}