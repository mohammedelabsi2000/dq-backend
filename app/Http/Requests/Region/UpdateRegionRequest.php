<?php

namespace App\Http\Requests\Region;

use App\Models\Branch;
use App\Models\Region;
use App\Http\Requests\DQFormRequest;

class UpdateRegionRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('region'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'      => 'sometimes|required|string|max:255',
            'branch_id' => 'sometimes|required|exists:branches,id',
            'notes'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المنطقة مطلوب',
            'branch_id.required' => 'يجب اختيار الفرع',
            'branch_id.exists'   => 'الفرع المحدد غير موجود',
        ];
    }
}
