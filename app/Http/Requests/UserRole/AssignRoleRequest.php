<?php

namespace App\Http\Requests\UserRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return $this->user()->can('create', Role::class);
        return $this->user()->hasPermissionTo('users.roles.update', 'sanctum');
    }

    public function rules(): array
    {
        return [
            'role_ids'   => 'required|array',
            'role_ids.*' => 'required|integer|exists:roles,id',
            'scopes'        => 'sometimes|array',
            'scopes.*.type' => [
                'required_with:scopes',
                'string',
                Rule::in(['branch', 'region', 'center', 'halaqa']),
            ],
            'scopes.*.id'   => 'required_with:scopes|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'role_ids.required'   => 'الأدوار مطلوبة',
            'role_ids.array'      => 'الأدوار يجب أن تكون مصفوفة',
            'role_ids.*.required' => 'معرف الدور مطلوب',
            'role_ids.*.integer'  => 'معرف الدور يجب أن يكون رقماً',
            'role_ids.*.exists'   => 'الدور غير موجود',
            'scopes.array'        => 'النطاقات يجب أن تكون مصفوفة',
            'scopes.*.type.required_with' => 'نوع النطاق مطلوب عند إرسال النطاقات',
            'scopes.*.type.string'  => 'نوع النطاق يجب أن يكون نصاً',
            'scopes.*.type.in'    => 'نوع النطاق غير صالح',
            'scopes.*.id.required_with' => 'معرف النطاق مطلوب عند إرسال النطاقات',
            'scopes.*.id.integer' => 'معرف النطاق يجب أن يكون رقماً',
        ];
    }
}
