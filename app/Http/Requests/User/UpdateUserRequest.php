<?php

namespace App\Http\Requests\User;

use App\Helpers\ConstantHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user'); // تأكد اسم الباراميتر في route

        return [

            'name' => 'sometimes|required|string|max:255',

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'password' => 'nullable|string|min:6|confirmed',

            'fName' => 'nullable|string|max:100',
            'sName' => 'nullable|string|max:100',
            'thName' => 'nullable|string|max:100',
            'family' => 'nullable|string|max:100',

            'dob' => 'nullable|date',

            'mosque_id' => 'nullable|exists:mosques,id',
            'marital_status_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('marital_status')),
            ],
            'prefix_name_id' => [
                'nullable',
                Rule::in(ConstantHelper::getConstantIdsByType('prefix_name')),
            ],

            'location' => 'nullable|string|max:255',
            'gender' => 'nullable|in:ذكر,أنثى',
            'numChildren' => 'nullable|integer|min:0',

            'identity' => [
                'nullable',
                'string',
                'size:9',
                Rule::unique('users', 'identity')->ignore($userId),
            ],

            'phone' => 'nullable|string|max:25',
            'whatsapp' => 'nullable|string|max:25',

            'jobname' => 'nullable|string|max:255',
            'job_place' => 'nullable|string|max:255',
            'job_salary' => 'nullable|numeric|min:0',
        ];
    }
}
