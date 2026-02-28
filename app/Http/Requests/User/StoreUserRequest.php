<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ConstantHelper;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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

            // Basic
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',

            // Names
            'fName' => 'nullable|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',

            'dob' => 'nullable|date',

            // Foreign Keys
            'mosque_id' => 'nullable|exists:mosques,id',
            'marital_status_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('marital_status')),
            ],
            'prefix_name_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('prefix_name')),
            ],

            // Other fields
            'location' => 'nullable|string|max:255',
            'gender' => 'nullable|in:ذكر,أنثى',
            'numChildren' => 'nullable|integer|min:0',

            'identity' => 'nullable|string|size:9|unique:users,identity',
            'phone' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25',

            'jobname' => 'nullable|string|max:255',
            'job_place' => 'nullable|string|max:255',
            'job_salary' => 'nullable|numeric|min:0',
        ];
    }
}
