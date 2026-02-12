<?php

namespace App\Http\Requests\AcademicQualification;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicQualificationRequest extends FormRequest
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
            'academic_degree_id' => 'required|exists:constants,id',
            'major_id' => 'required|exists:constants,id',

            'person_type' => 'required|string',
            'person_id' => 'required|integer',

            'detail' => 'nullable|string|max:255',
            'date_graduate' => 'nullable|date',
            'certificate_link' => 'nullable|url',
            'educational_institution' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
