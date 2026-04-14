<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\DQFormRequest;

class StoreRoleRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        // return $this->user()->can('create', Role::class);
        return $this->user()->hasPermissionTo('roles.create', 'sanctum');
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|unique:roles,name',
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
