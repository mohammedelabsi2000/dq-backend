<?php

namespace App\Http\Requests\PersonalCourse;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalCourseRequest extends FormRequest
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
            'course_name' => 'sometimes|required|string|max:255',
            'notes' => 'sometimes|nullable|string',
            'hours' => 'sometimes|nullable|integer',
            'provider' => 'sometimes|nullable|string|max:255',
            'place' => 'sometimes|nullable|string|max:255',
            'certificate_link' => 'sometimes|nullable|url|max:255',
            'type_id' => 'sometimes|required|exists:constants,id',
        ];
    }
}
