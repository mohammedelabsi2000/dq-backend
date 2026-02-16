<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
            'fName' => 'sometimes|string|max:255',
            'family' => 'sometimes|string|max:255',
            'dob' => 'nullable|date',

            'mosque_id' => 'sometimes|exists:mosques,id',

            'gender' => 'sometimes|in:male,female',

            'marital_status_id' => 'nullable|exists:constants,id',
            'money_status_id' => 'nullable|exists:constants,id',
            'prefix_name_id' => 'nullable|exists:constants,id',
            
            'guardian_id' => 'sometimes|string',
            'guardian_type_id' => 'nullable|exists:constants,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
        ];
    }
}
