<?php

namespace App\Http\Requests\UserRole;

use App\Http\Requests\DQFormRequest;
use Illuminate\Validation\Rule;

class AssignScopeRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('users.roles.update', 'sanctum');
    }

    public function rules(): array
    {
        return [
            'scopes'        => 'required|array',
            'scopes.*.type' => [
                'required',
                'string',
                Rule::in(['branch', 'region', 'center', 'halaqa']),
            ],
            'scopes.*.id'   => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'scopes.required'        => 'النطاقات مطلوبة',
            'scopes.array'           => 'النطاقات يجب أن تكون مصفوفة',
            'scopes.*.type.required' => 'نوع النطاق مطلوب',
            'scopes.*.type.in'       => 'نوع النطاق غير صالح',
            'scopes.*.id.required'   => 'معرف النطاق مطلوب',
            'scopes.*.id.integer'    => 'معرف النطاق يجب أن يكون رقماً',
        ];
    }
}
