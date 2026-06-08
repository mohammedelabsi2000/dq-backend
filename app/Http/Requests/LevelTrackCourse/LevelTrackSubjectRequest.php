<?php

namespace App\Http\Requests\LevelTrackCourse;

use App\Models\LevelTrackSubject;
use Illuminate\Foundation\Http\FormRequest;

class LevelTrackSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subjects'               => ['required', 'array', 'min:1'],
            'subjects.*.subject_id'  => ['required', 'exists:subjects,id', 'distinct'],
            'subjects.*.order'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'subjects.*.is_required' => ['sometimes', 'boolean'],
        ];
    }

    // منع التكرار عند الإضافة (store فقط)
    public function withValidator($validator): void
    {
        if ($this->getMethod() !== 'POST') {
            return;
        }

        $validator->after(function ($validator) {
            $levelTrack = $this->route('levelTrack');

            $alreadyAdded = LevelTrackSubject::where('level_track_id', $levelTrack->id)
                                             ->pluck('subject_id')
                                             ->toArray();

            foreach ((array) $this->subjects as $index => $subject) {
                if (isset($subject['subject_id']) && in_array($subject['subject_id'], $alreadyAdded)) {
                    $validator->errors()->add(
                        "subjects.{$index}.subject_id",
                        'هذا المساق مضاف مسبقاً لهذا المسار'
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'subjects'               => 'المساقات',
            'subjects.*.subject_id'  => 'المساق',
            'subjects.*.order'       => 'الترتيب',
            'subjects.*.is_required' => 'مطلوب',
        ];
    }

    public function messages(): array
    {
        return [
            'subjects.*.subject_id.exists'   => 'المساق المحدد غير موجود',
            'subjects.*.subject_id.required' => 'حقل المساق مطلوب',
            'subjects.*.subject_id.distinct' => 'لا يمكن إضافة نفس المساق مرتين في نفس الطلب',
            'subjects.required'              => 'يجب إضافة مساق واحد على الأقل',
            'subjects.*.order.integer'       => 'الترتيب يجب أن يكون رقماً صحيحاً',
            'subjects.*.is_required.boolean' => 'حقل مطلوب يجب أن يكون صح أو خطأ',
        ];
    }
}