<?php

namespace App\Http\Requests\Branch;

use App\Http\Requests\DQFormRequest;

class UpdateBranchRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('branch'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'                  => 'sometimes|required|string|max:255',
            'standard_branch_id'    => 'nullable|integer|unique:branches,standard_branch_id,' . $this->route('branch')?->id,
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
