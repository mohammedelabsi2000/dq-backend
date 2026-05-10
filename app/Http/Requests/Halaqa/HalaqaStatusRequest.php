<?php

namespace App\Http\Requests\Halaqa;

use App\Helpers\ConstantHelper;
use App\Http\Requests\DQFormRequest;
use App\Models\Halaqa;
use Illuminate\Validation\Rule;

class HalaqaStatusRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->isStore()) {
            return $this->user()->can('create', Halaqa::class);
        } elseif ($this->isUpdate()) {
            $halaqa = $this->route('halaqa_status')->halaqa;
            logger($halaqa);
            return $this->user()->can('update', $halaqa);
        }
        return false;
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
            'halaqa_id' => [
                $isUpdate ? 'sometimes' : null,
                'required',
                'exists:halaqas,id',
            ],
            'status_type_id' => ['nullable', Rule::in(ConstantHelper::getConstantIdsByType('status_type'))],
            'sponsorship_type_id' => ['nullable', Rule::in(ConstantHelper::getConstantIdsByType('sponsorship_type'))],
            'from_date' => [
                $isUpdate ? 'sometimes' : null,
                'required',
                'date'
            ],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'halaqa_id.required' => 'يجب اختيار الحلقة.',
            'halaqa_id.exists' => 'الحلقة المحددة غير موجودة.',

            'status_type_id.in' => 'نوع حالة الحلقة غير صالح.',

            'sponsorship_type_id.in' => 'نوع الكفالة غير صالح.',

            'from_date.required' => 'تاريخ البداية مطلوب.',
            'from_date.date' => 'تاريخ البداية يجب أن يكون تاريخاً صحيحاً.',

            'to_date.date' => 'تاريخ النهاية يجب أن يكون تاريخ صحيح.',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.',

            'notes.string' => 'الملاحظات يجب أن تكون نصاً.',
        ];
    }
}
