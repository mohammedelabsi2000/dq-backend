<?php

namespace App\Http\Requests\UserRole;

use App\Models\UserRole;
use App\Http\Requests\DQFormRequest;

class RemoveRoleRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasPermissionTo('users.roles.update', 'sanctum');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'role_id'    => 'required|exists:roles,id',
            'scope_id'   => 'nullable|integer',
            'scope_type' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'الدور مطلوب',
            'role_id.exists'   => 'الدور غير موجود',
            'scope_id.integer' => 'معرف النطاق يجب أن يكون رقماً',
        ];
    }
}
