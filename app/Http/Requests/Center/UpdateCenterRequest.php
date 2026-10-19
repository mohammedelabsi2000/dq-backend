<?php

namespace App\Http\Requests\Center;

use App\Http\Requests\DQFormRequest;
use App\Rules\GenderVisibilityRule;
use Illuminate\Validation\Rule;

class UpdateCenterRequest extends DQFormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('center'));
    }

    /**
     * تغيير المنطقة أو الجنس دون إرسال الاسم يجب أن يُفحص فيه تكرار الاسم أيضاً
     */
    protected function prepareForValidation()
    {
        if (!$this->has('name')) {
            $this->merge(['name' => $this->route('center')->name]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $center = $this->route('center');

        return [
            // اسم المركز لا يتكرر في نفس المنطقة لنفس الجنس (القيم غير المُرسلة تؤخذ من المركز الحالي)
            'name'      => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('centers', 'name')
                    ->ignore($center->id)
                    ->where('region_id', $this->input('region_id', $center->region_id))
                    ->where('gender', $this->input('gender', $center->gender?->value ?? $center->gender))
                    ->whereNull('deleted_at'),
            ],
            'region_id' => 'sometimes|required|exists:regions,id',
            'mosque_id' => 'nullable|exists:mosques,id',
            'notes'     => 'nullable|string',
            'gender' => ['required', new GenderVisibilityRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المركز مطلوب',
            'name.unique'        => 'يوجد مركز بنفس الاسم في هذه المنطقة لنفس الجنس',
            'region_id.required' => 'يجب اختيار المنطقة',
            'region_id.exists'   => 'المنطقة المحددة غير موجودة',
            'mosque_id.exists'   => 'المسجد المحدد غير موجود',
            'gender.required' => 'يجب اختيار الجنس'
        ];
    }
}
