<?php

namespace App\Http\Requests;

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
            'level_track_id' => ['required', 'exists:level_tracks,id'],
            'subject_id'     => ['required', 'exists:subjects,id'],
            'is_required'    => ['sometimes', 'boolean'],
            'order'          => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'level_track_id' => 'مستوى المسار',
            'subject_id'     => 'المساق',
            'is_required'    => 'إلزامي',
            'order'          => 'الترتيب',
        ];
    }
}
