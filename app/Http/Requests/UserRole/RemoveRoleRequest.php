<?php

namespace App\Http\Requests\UserRole;

use Illuminate\Foundation\Http\FormRequest;

class RemoveRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
