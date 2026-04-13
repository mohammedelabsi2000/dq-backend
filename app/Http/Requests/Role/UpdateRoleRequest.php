<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return $this->user()->can('update', $this->route('role'));
        return $this->user()->hasPermissionTo('roles.update', 'sanctum');
    }

    public function rules(): array
    {
        return [
            'name'        => [
                'required',
                'string',
                Rule::unique('roles', 'name')->ignore($this->route('role')->id),
            ],
            'give_all'    => 'boolean',
            'abilities'   => 'array',
            'abilities.*' => 'integer|exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم الدور مطلوب',
            'name.unique'        => 'اسم الدور موجود مسبقاً',
            'abilities.array'    => 'الصلاحيات يجب أن تكون مصفوفة',
            'abilities.*.exists' => 'إحدى الصلاحيات غير موجودة',
        ];
    }
}
