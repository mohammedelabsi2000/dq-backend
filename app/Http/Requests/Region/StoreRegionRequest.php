<?php

namespace App\Http\Requests\Region;

use App\Http\Traits\ApiResponser;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRegionRequest extends FormRequest
{
    use ApiResponser;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        $branch = Branch::findOrFail($this->input('branch_id'));

        return $this->user()->can('create', [Region::class, $branch]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'      => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'notes'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المنطقة مطلوب',
            'branch_id.required' => 'يجب اختيار الفرع',
            'branch_id.exists'   => 'الفرع المحدد غير موجود',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->validationError([
            // 'status'  => false,
            // 'message' => 'خطأ في البيانات المدخلة',
            $validator->errors(),
        ]));
    }
}
