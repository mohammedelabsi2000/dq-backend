<?php

namespace App\Http\Requests\LevelTrackCourse;

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

    public function attributes(): array
    {
        return [
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
        ];
    }
}
