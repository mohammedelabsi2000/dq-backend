<?php

namespace App\Http\Requests\Grade;

use App\Http\Requests\DQFormRequest;

class UpdateGradeRequest extends DQFormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'DQ_range_from' => ['sometimes', 'required', 'string', 'max:50'],
            'DQ_range_to' => ['sometimes', 'required', 'string', 'max:50'],
        ];
    }
}
