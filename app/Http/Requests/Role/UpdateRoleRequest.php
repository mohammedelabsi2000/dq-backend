<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('role'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'        => ['required', 'string', Rule::unique('roles', 'name')->ignore($this->role->id)],
            'give_all'    => 'boolean',
            'abilities'   => 'array',
            // 'abilities.*' => Rule::in(array_keys(config('abilities'))),
            'abilities.*' => Rule::in(
                collect(config('abilities'))->flatten(1)->pluck('ability')->toArray()
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم الدور مطلوب',
            'name.unique'        => 'اسم الدور موجود مسبقاً',
            'abilities.array'    => 'الصلاحيات يجب أن تكون مصفوفة',
            'abilities.*.in'     => 'إحدى الصلاحيات غير صالحة',
        ];
    }
}
