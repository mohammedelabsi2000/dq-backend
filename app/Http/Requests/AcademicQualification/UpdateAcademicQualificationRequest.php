<?php

namespace App\Http\Requests\AcademicQualification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicQualificationRequest extends FormRequest
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
            'academic_degree_id' => 'sometimes|exists:constants,id',
            'major_id' => 'sometimes|exists:constants,id',

            // عادةً ما نمنع تغيير صاحب العلاقة
            'person_type' => 'prohibited',
            'person_id' => 'prohibited',

            'detail' => 'sometimes|nullable|string|max:255',
            'date_graduate' => 'sometimes|nullable|date',
            'certificate_link' => 'sometimes|nullable|url',
            'educational_institution' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string',
        ];
    }
}
