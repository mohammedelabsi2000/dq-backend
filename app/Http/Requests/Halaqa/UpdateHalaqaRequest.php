<?php

namespace App\Http\Requests\Halaqa;

use App\Helpers\ConstantHelper;
use App\Http\Requests\DQFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\Relation;

class UpdateHalaqaRequest extends DQFormRequest
{
    public function authorize()
    {
        // return true;
        return $this->user()->can('update', $this->route('halaqa'));
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        if (!$this->input('center_id')) {
            $this->merge([
                'reference_type' => 'region',
                'reference_id' => intval($this->input('region_id')),
            ]);
        } else {
            $this->merge([
                'reference_type' => 'center',
                'reference_id' => intval($this->input('center_id')),
            ]);
        }
    }


    /** Get the validation rules that apply to the request.
     *s
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',

            'reference_type' => [
                'sometimes',
                'required',
                Rule::in([
                    'center',
                    'region',
                ])
            ],

            'reference_id' => [
                'sometimes',
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // $type = $this->input('reference_type');
                    $type = Relation::getMorphedModel($this->input('reference_type'));

                    if (!$type || !class_exists($type)) {
                        $fail('نوع المرجع غير صالح.');
                        return;
                    }

                    if (!$type::where('id', $value)->exists()) {
                        $fail('المعرف غير موجود في المرجع المحدد.');
                    }
                },
            ],

            'type_id' => [
                'sometimes',
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('halaqa_types')),
            ],
        ];
    }

    /**
     * Get custom error messages for validation failures.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الحلقة مطلوب.',
            'name.string' => 'اسم الحلقة يجب أن يكون نص.',
            'name.max' => 'اسم الحلقة يجب ألا يتجاوز 255 حرف.',

            'location.string' => 'الموقع يجب أن يكون نص.',
            'location.max' => 'الموقع يجب ألا يتجاوز 255 حرف.',

            'description.string' => 'الوصف يجب أن يكون نص.',

            'reference_type.required' => 'نوع المرجع مطلوب.',
            'reference_type.in' => 'نوع المرجع غير صالح.',

            'reference_id.required' => 'معرف المرجع مطلوب.',
            'reference_id.integer' => 'معرف المرجع يجب أن يكون رقم صحيح.',

            'type_id.required' => 'نوع الحلقة مطلوب.',
            'type_id.in' => 'نوع الحلقة غير موجود في النظام.',
        ];
    }
}
