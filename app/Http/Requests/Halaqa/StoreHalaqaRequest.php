<?php

namespace App\Http\Requests\Halaqa;

use App\Helpers\ConstantHelper;
use App\Http\Requests\DQFormRequest;
use App\Models\Audit;
use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Region;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\Relation;

class StoreHalaqaRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $reference = null;
        if ($this->input('center_id')) {
            $reference = Center::find($this->input('center_id'));
        } elseif ($this->input('region_id')) {
            $reference = Region::find($this->input('region_id'));
        }
        return $this->user()->can('create', [Halaqa::class, $reference]);

        // نجيب الـ reference (Region أو Center)
        // $referenceType = $this->input('reference_type');
        // $referenceId   = $this->input('reference_id');

        // $reference = match ($referenceType) {
        //     'region' => Region::findOrFail($referenceId),
        //     'center' => Center::findOrFail($referenceId),
        //     // default  => abort(422, 'Invalid reference type'),

        //     default  => abort(422, 'Invalid reference type'),
        // };

        // return $this->user()->can('create', [Halaqa::class, $reference]);
        // return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        if ($this->filled('center_id')) {
            $this->merge([
                'reference_type' => 'center',
                'reference_id' => (int) $this->center_id,
            ]);
        } elseif ($this->filled('region_id')) {
            $this->merge([
                'reference_type' => 'region',
                'reference_id' => (int) $this->region_id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'center_id' => 'nullable|integer|exists:centers,id',
            'region_id' => 'nullable|integer|exists:regions,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',

            'reference_type' => [
                'required',
                Rule::in([
                    'center',
                    'region'
                ])
            ],
            'reference_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // $type = $this->reference_type;
                    $type = Relation::getMorphedModel($this->reference_type);
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
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('halaqa_types')),
            ],
        ];
    }

    public function messages()
    {
        return [
            // name
            'name.required' => 'اسم الحلقة مطلوب.',
            'name.string' => 'اسم الحلقة يجب أن يكون نص.',
            'name.max' => 'اسم الحلقة يجب ألا يتجاوز 255 حرف.',

            // location
            'location.string' => 'الموقع يجب أن يكون نص.',
            'location.max' => 'الموقع يجب ألا يتجاوز 255 حرف.',

            // description
            'description.string' => 'الوصف يجب أن يكون نص.',

            // reference_type
            'reference_type.required' => 'نوع المرجع مطلوب.',
            'reference_type.in' => 'نوع المرجع المحدد غير صالح.',

            // reference_id
            'reference_id.required' => 'المعرف مطلوب.',
            'reference_id.integer' => 'المعرف يجب أن يكون رقم صحيح.',

            // type_id
            'type_id.required' => 'نوع الحلقة مطلوب.',
            'type_id.in' => 'نوع الحلقة المحدد غير صالح.',
        ];
    }
}
