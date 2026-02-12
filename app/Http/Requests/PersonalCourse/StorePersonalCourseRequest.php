<?php

namespace App\Http\Requests\PersonalCourse;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonalCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'course_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'hours' => 'nullable|integer',
            'provider' => 'nullable|string|max:255',
            'place' => 'nullable|string|max:255',
            'certificate_link' => 'nullable|url|max:255',
            'type_id' => 'required|exists:constants,id',
            'person_id' => 'required',
            'person_type' => 'required|string',
        ];
    }
}
