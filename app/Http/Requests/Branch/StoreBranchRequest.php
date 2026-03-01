<?php

namespace App\Http\Requests\Branch;

use App\Http\Traits\ApiResponser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBranchRequest extends FormRequest
{
    use ApiResponser;
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
            'name'                  => 'required|string|max:255',
            'notes'                 => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الفرع مطلوب',
            'name.max'      => 'اسم الفرع يجب ألا يتجاوز 255 حرف',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->validationError([
            // 'status'  => false,
            // 'message' => 'خطأ في البيانات المدخلة',
            'errors'  => $validator->errors(),
        ]));
    }
}
