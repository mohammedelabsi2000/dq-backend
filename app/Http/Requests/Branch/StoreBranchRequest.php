<?php

namespace App\Http\Requests\Branch;

use App\Models\Branch;
use App\Http\Requests\DQFormRequest;

class StoreBranchRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Branch::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'                  => 'required|string|max:255',
            'notes'                 => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الفرع مطلوب',
            'name.max'      => 'اسم الفرع يجب ألا يتجاوز 255 حرف',
        ];
    }
}
