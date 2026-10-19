<?php

namespace App\Http\Requests\Center;

use App\Http\Requests\DQFormRequest;
use App\Models\Center;
use App\Models\Region;
use App\Rules\GenderVisibilityRule;
use Illuminate\Validation\Rule;

class StoreCenterRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Center::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // اسم المركز لا يتكرر في نفس المنطقة لنفس الجنس
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('centers', 'name')
                    ->where('region_id', $this->input('region_id'))
                    ->where('gender', $this->input('gender'))
                    ->whereNull('deleted_at'),
            ],
            'region_id' => 'required|exists:regions,id',
            'mosque_id' => 'nullable|exists:mosques,id',
            'notes' => 'nullable|string',
            'gender' => ['required', new GenderVisibilityRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المركز مطلوب',
            'name.unique' => 'يوجد مركز بنفس الاسم في هذه المنطقة لنفس الجنس',
            'region_id.required' => 'يجب اختيار المنطقة',
            'region_id.exists' => 'المنطقة المحددة غير موجودة',
            'mosque_id.exists' => 'المسجد المحدد غير موجود',
            'gender.required' => 'يجب اختيار الجنس'
        ];
    }
}
