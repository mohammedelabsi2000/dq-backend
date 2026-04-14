<?php

namespace App\Http\Requests\UserRole;

use App\Models\Branch;
use App\Models\Center;
use App\Models\Region;
use App\Models\UserRole;
use Illuminate\Validation\Rule;
use App\Http\Requests\DQFormRequest;

class SyncRoleRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $targetUser = $this->route('user');
        return $this->user()->can('sync', [UserRole::class, $targetUser]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // القديم
            'old_role_id'    => 'required|exists:roles,id',
            'old_scope_id'   => 'nullable|integer',
            'old_scope_type' => 'nullable|string',

            // الجديد
            'new_role_id'    => 'required|exists:roles,id',
            'new_scope_id'   => 'nullable|integer',
            'new_scope_type' => [
                'nullable',
                'string',
                Rule::in([
                    Branch::class,
                    Region::class,
                    Center::class,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'old_role_id.required' => 'الدور القديم مطلوب',
            'old_role_id.exists'   => 'الدور القديم غير موجود',
            'new_role_id.required' => 'الدور الجديد مطلوب',
            'new_role_id.exists'   => 'الدور الجديد غير موجود',
            'new_scope_type.in'    => 'نوع النطاق غير صالح',
        ];
    }
}
