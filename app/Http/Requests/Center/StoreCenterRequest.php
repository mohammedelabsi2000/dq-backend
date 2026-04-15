<?php

namespace App\Http\Requests\Center;

use App\Http\Requests\DQFormRequest;
use App\Models\Center;
use App\Models\Region;

class StoreCenterRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // $region = Region::findOrFail($this->input('region_id'));
        // dd($this->user()->can('create', [Center::class, $region]));
        return $this->user()->hasPermissionTo('centers.create', 'sanctum');

        // return $this->user()->can('create', Center::class);
        // return $this->user()->can("create");
        // $this->authorize('viewAny', Center::class);
        // return $this->user()->can();

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // return [
        //     'name' => ['required', 'string', 'max:255'],
        //     'notes' => ['nullable', 'string'],
        //     'mosque_id' => ['required', 'integer', 'exists:mosques,id'],
        // ];
        return [
            'name'      => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'mosque_id' => 'nullable|exists:mosques,id',
            'notes'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المركز مطلوب',
            'region_id.required' => 'يجب اختيار المنطقة',
            'region_id.exists'   => 'المنطقة المحددة غير موجودة',
            'mosque_id.exists'   => 'المسجد المحدد غير موجود',
        ];
    }
}
