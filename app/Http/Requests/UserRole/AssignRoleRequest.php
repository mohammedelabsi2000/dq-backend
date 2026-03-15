<?php

namespace App\Http\Requests\UserRole;

use App\Models\Branch;
use App\Models\Center;
use App\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
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
            'role_id'    => 'required|array',
            'scope_id'   => 'nullable|integer',
            'scope_type' => [
                'nullable',
                'string',
                Rule::in([
                    'branch',
                    'region',
                    'center',
                    'halaqa',
                ]),
            ],
            'branch_id' => 'nullable|exists:branches,id',
            'region_id' => 'nullable|exists:regions,id',
            'center_id' => 'nullable|exists:centers,id',
            'halaqa_id' => 'nullable|exists:halaqas,id',

        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required'  => 'الدور مطلوب',
            'role_id.exists'    => 'الدور غير موجود',
            'scope_id.integer'  => 'معرف النطاق يجب أن يكون رقماً',
            'scope_type.in'     => 'نوع النطاق غير صالح',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('halaqa_id')) {
            $this->merge([
                'scope_type' => 'halaqa',
                'scope_id'   => intval($this->input('halaqa_id')),
            ]);
        } elseif ($this->input('center_id')) {
            $this->merge([
                'scope_type' => 'center',
                'scope_id'   => intval($this->input('center_id')),
            ]);
        } elseif ($this->input('region_id')) {
            $this->merge([
                'scope_type' => 'region',
                'scope_id'   => intval($this->input('region_id')),
            ]);
        } elseif ($this->input('branch_id')) {
            $this->merge([
                'scope_type' => 'branch',
                'scope_id'   => intval($this->input('branch_id')),
            ]);
        } else {
            // مدير عام بدون scope
            $this->merge([
                'scope_type' => null,
                'scope_id'   => null,
            ]);
        }
    }
}
