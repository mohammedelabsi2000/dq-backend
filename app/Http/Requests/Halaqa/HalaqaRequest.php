<?php

namespace App\Http\Requests\Halaqa;

use App\Http\Requests\DQFormRequest;
use App\Enums\HalaqaReferenceType;
use App\Helpers\ConstantHelper;
use App\Models\Halaqa;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Rules\Enum;

class HalaqaRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $referenceType = $this->input('reference_type');
        $referenceId = $this->input('reference_id') ? (int) $this->input('reference_id') : null;

        if ($this->isStore()) {
            return $this->user()->can('create', [Halaqa::class, $referenceType, $referenceId]);
        } elseif ($this->isUpdate()) {
            return $this->user()->can('update', [$this->route('halaqa'), $referenceType, $referenceId]);
        }
        return false;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        if (!($this->isStore() || $this->isUpdate())) {
            return;
        }

        $centerId = $this->input('center_id');
        $regionId = $this->input('region_id');

        if ($centerId) {
            $this->merge([
                'reference_type' => HalaqaReferenceType::Center->code(),
                'reference_id' => (int) $centerId,
            ]);
        } elseif ($regionId) {
            $this->merge([
                'reference_type' => HalaqaReferenceType::Region->code(),
                'reference_id' => (int) $regionId,
            ]);
        }

        /* if ($this->isStore()) {
            if ($this->filled('center_id')) {
                $this->merge([
                    'reference_type' => HalaqaReferenceType::Center->code(),
                    'reference_id' => (int) $this->center_id,
                ]);
            } elseif ($this->filled('region_id')) {
                $this->merge([
                    'reference_type' => HalaqaReferenceType::Region->code(),
                    'reference_id' => (int) $this->region_id,
                ]);
            }
        } elseif ($this->isUpdate()) {
            if (!$this->input('center_id')) {
                $this->merge([
                    'reference_type' => HalaqaReferenceType::Region->code(),
                    'reference_id' => intval($this->input('region_id')),
                ]);
            } else {
                $this->merge([
                    'reference_type' => HalaqaReferenceType::Center->code(),
                    'reference_id' => intval($this->input('center_id')),
                ]);
            }
        } */
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $isStore = $this->isStore();
        $isUpdate = $this->isUpdate();

        return [
            'center_id' => [
                'nullable',
                'integer',
                'exists:centers,id',
            ],

            'region_id' => [
                'nullable',
                'integer',
                'exists:regions,id',
            ],

            'name' => [
                $isUpdate ? 'sometimes' : null,
                'required',
                'string',
                'max:255',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'reference_type' => [
                $isUpdate ? 'sometimes' : null,
                'required',
                new Enum(HalaqaReferenceType::class),
            ],

            'reference_id' => [
                $isUpdate ? 'sometimes' : null,
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $typeKey = $this->isUpdate()
                        ? $this->input('reference_type')
                        : $this->reference_type;

                    $type = Relation::getMorphedModel($typeKey);

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
                $isUpdate ? 'sometimes' : null,
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('halaqa_types')),
            ],
            'status_type_id' => [
                'required',
                Rule::in(ConstantHelper::getConstantIdsByType('status_type')),
            ],

            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date',],
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
            'reference_type.enum' => 'نوع المرجع المحدد غير صالح.',

            // reference_id
            'reference_id.required' => 'المعرف مطلوب.',
            'reference_id.integer' => 'المعرف يجب أن يكون رقم صحيح.',

            // type_id
            'type_id.required' => 'نوع الحلقة مطلوب.',
            'type_id.in' => 'نوع الحلقة المحدد غير صالح.',

            // from_date
            'from_date.date' => 'تاريخ البداية يجب أن يكون تاريخ صحيح.',

            // to_date
            'to_date.date' => 'تاريخ النهاية يجب أن يكون تاريخ صحيح.',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.',
        ];
    }
}
