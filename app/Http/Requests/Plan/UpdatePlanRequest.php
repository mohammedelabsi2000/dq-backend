<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'type_id' => 'sometimes|required|exists:constants,id',
            'description' => 'nullable|string',
            'target_group_id' => 'sometimes|required|exists:constants,id',
            'level_numbers' => 'nullable|integer',
            'notes' => 'nullable|string',
        ];
    }
}
