<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LevelTrackCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level_track_id' => ['required', 'exists:level_tracks,id'],
            'course_id'      => ['required', 'exists:courses,id'],
            'is_required'    => ['sometimes', 'boolean'],
            'order'          => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'level_track_id' => 'مستوى المسار',
            'course_id'      => 'المساق',
            'is_required'    => 'إلزامي',
            'order'          => 'الترتيب',
        ];
    }
}
