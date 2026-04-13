<?php

namespace App\Http\Requests\Grade;

use App\Http\Requests\DQFormRequest;

class StoreGradeRequest extends DQFormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'DQ_range_from' => ['nullable', 'string'],
            'DQ_range_to' => ['nullable', 'string'],
        ];
    }
}
