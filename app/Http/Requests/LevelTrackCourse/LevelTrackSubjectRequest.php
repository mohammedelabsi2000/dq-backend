<?php

namespace App\Http\Requests\LevelTrackCourse;

use App\Models\LevelTrackSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LevelTrackSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->getMethod()) {
            'POST'        => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            default       => [],
        };
    }

    private function storeRules(): array
    {
        return [
            'level_track_id' => ['required', 'exists:level_tracks,id'],
            'subject_id'     => [
                'required',
                'exists:subjects,id',
                Rule::unique('level_track_subjects')->where(
                    fn($q) => $q->where('level_track_id', $this->level_track_id)
                ),
            ],
            'order'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
            'weight'      => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'level_track_id' => ['sometimes', 'exists:level_tracks,id'],
            'subject_id'     => [
                'sometimes',
                'exists:subjects,id',
                Rule::unique('level_track_subjects')->where(
                    fn($q) => $q->where('level_track_id', $this->level_track_id)
                )->ignore($this->route('levelTrackSubject')->id, 'id'),
            ],
            'order'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
            'weight'      => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
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
<<<<<<< Updated upstream
            'level_track_id' => 'مسار المستوى',
            'subject_id'     => 'المساق',
            'order'          => 'الترتيب',
            'is_required'    => 'مطلوب',
            'weight'         => 'الوزن',
        ];
    }

    public function messages(): array
    {
        return [
            'level_track_id.required' => 'حقل مسار المستوى مطلوب',
            'level_track_id.exists'   => 'مسار المستوى المحدد غير موجود',
            'subject_id.required'     => 'حقل المساق مطلوب',
            'subject_id.exists'       => 'المساق المحدد غير موجود',
            'subject_id.unique'       => 'هذا المساق مضاف مسبقاً لهذا المسار',
            'order.integer'           => 'الترتيب يجب أن يكون رقماً صحيحاً',
            'order.min'               => 'الترتيب يجب أن يكون أكبر من أو يساوي صفر',
            'is_required.boolean'     => 'حقل مطلوب يجب أن يكون صح أو خطأ',
            'weight.numeric'          => 'الوزن يجب أن يكون رقماً',
            'weight.min'              => 'الوزن يجب أن يكون أكبر من أو يساوي صفر',
            'weight.max'              => 'الوزن يجب أن يكون أقل من أو يساوي 100',
=======
            'subjects'               => 'المساقات',
            'subjects.*.subject_id'  => 'المساق',
            'subjects.*.order'       => 'الترتيب',
            'subjects.*.is_required' => 'مطلوب',
>>>>>>> Stashed changes
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