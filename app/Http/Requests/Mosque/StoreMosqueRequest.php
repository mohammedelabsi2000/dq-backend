<?php

namespace App\Http\Requests\Mosque;

use App\Models\Mosque;
use App\Models\Region;
use App\Http\Requests\DQFormRequest;

class StoreMosqueRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Mosque::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'         => 'required|string|max:255',
            'region_id'    => 'required|exists:regions,id',
            'notes'        => 'nullable|string',
            'create_center' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المسجد مطلوب',
            'region_id.required' => 'يجب اختيار المنطقة',
            'region_id.exists'   => 'المنطقة المحددة غير موجودة',
        ];
    }
}
