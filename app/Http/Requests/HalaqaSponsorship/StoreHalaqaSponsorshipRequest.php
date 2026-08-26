<?php

namespace App\Http\Requests\HalaqaSponsorship;

use App\Models\Halaqa;
use App\Http\Requests\DQFormRequest;

class StoreHalaqaSponsorshipRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var Halaqa $halaqa */
        $halaqa = $this->route('halaqa');

        return $this->user()->can('update', $halaqa);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sponsor_id' => 'required|integer|exists:sponsors,id',
            'from_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'sponsor_id.required' => 'يجب اختيار الكفيل',
            'sponsor_id.exists' => 'الكفيل المحدد غير موجود',
        ];
    }
}
