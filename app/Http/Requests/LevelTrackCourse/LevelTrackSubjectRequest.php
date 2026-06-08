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
        ];
    }

    private function updateRules(): array
    {
        return [
            'order'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'level_track_id' => 'مسار المستوى',
            'subject_id'     => 'المساق',
            'order'          => 'الترتيب',
            'is_required'    => 'مطلوب',
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
        ];
    }
}
