<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
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
            'notes' => ['nullable', 'string'],
            'max_replacement_limit' => ['required', 'integer', 'min:0'],
            'min_replacement_limit' => ['required', 'integer', 'min:0', 'lte:max_replacement_limit'],
        ];
    }
}
